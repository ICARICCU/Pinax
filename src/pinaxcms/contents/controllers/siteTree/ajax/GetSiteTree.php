<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_controllers_siteTree_ajax_GetSiteTree extends pinax_mvc_core_CommandAjax
{
	use pinax_mvc_core_AuthenticatedCommandTrait;
    use pinaxcms_contents_controllers_PermissionTrait;

    private $pageTypes;
    private $pageId;
    private $languageId;
    private $menuProxy;


    public function execute($id) {
        $this->checkPermissionForBackend();
        $this->directOutput = true;

        $this->setAclFlag();
        $this->pageId = $this->application->getPageId();

        if ($this->user->acl('pinaxcms', 'page.modify.pagetype')) {
            $pageTypeService = pinax_ObjectFactory::createObject('pinaxcms.contents.services.PageTypeService');
            $this->pageTypes = $pageTypeService->getAllPageTypes();
        }

        $this->languageId = pinax_ObjectValues::get('org.pinax', 'editingLanguageId');
        $this->menuProxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.MenuProxy');

        if ($this->aclEnabled && !$this->user->acl('pinaxcms_contentsedit', 'all')) {
            return $this->onlyPagesWithPermission();
        }

        if ($id==0) {
            // root
            return $this->rootMenu();
        }

        return $this->pageLeaf($id);
    }

    private function rootMenu()
    {
        $menu = $this->menuProxy->getRootMenu($this->languageId);
        return [$this->addNode($menu, true)];
    }

    /**
     * @param  int $id
     * @return array
     */
    private function pageLeaf($id)
    {
        $output = [];
        $itMenus = $this->menuProxy->getChildMenusFromId($id, $this->languageId);
        foreach($itMenus as $subMenu) {
            $output[] = $this->addNode($subMenu);
        }
        return $output;
    }

    private function onlyPagesWithPermission()
    {
        $it = pinax_ObjectFactory::createModelIterator( 'pinax.models.Join')
                ->load('loadRelations', [
                                            'name' => __Config::get('DB_PREFIX') . 'menus_tbl#rel_aclBack',
                                            'source' => '',
                                            'dest' => '']);

        $output = [];
        $userRolesId = $this->user->getRoles();
        foreach ($it as $ar) {
            if (in_array($ar->join_FK_dest_id, $userRolesId)) {
                $menu = $this->menuProxy->getMenuFromId($ar->join_FK_source_id, $this->languageId);
                if ($menu) {
                    $output[strip_tags($menu->menudetail_title)] = $this->addNode($menu, false, true);
                }
            }
        }
        ksort($output);
        return array_values($output);
   }

    private function addNode($menu, $isRoot=false, $flat=false) {
        $title = strip_tags($menu->menudetail_title);
        $icon = 'page';
        if ( $menu->menu_pageType=='Empty') {
            $icon = 'folder';
        } else if ( $menu->menu_pageType=='Alias') {
            $icon = 'alias';
        }

        if ( $menu->menu_type == 'HOMEPAGE' ) {
            $icon = 'home';
        } else if ( $menu->menu_type == 'SYSTEM' ) {
            $icon .= ' system';
        }

        if ( $menu->menu_isLocked == 1 ) {
            $icon .= ' lock';
        }

        $node = array(
            'data' => array(
                    'title' => $title,
                    'icon' => $icon
            ),
            'attr' => array(
                    'id' => $menu->menu_id,
                    'rel' => 'default',
                    'class' => '',
                    'title' => $title.' ('.__T($menu->menu_pageType).':'.$menu->menu_id.')',
            ),
            'metadata' => array(),
            'state' =>  ''
        );

        if ( !$menu->menudetail_isVisible ) {
            $node['data']['icon'] .= ' hide';
            $node['attr']['class'] .= ' pinaxcmsSiteTree-nodeHide';
        }
        if ($menu->fieldExists('menudetail_hideInNavigation') &&  $menu->menudetail_hideInNavigation == '1' ) {
            $node['data']['icon'] .= ' pageHideInNav';
            $node['attr']['class'] .= ' pinaxcmsSiteTree-pageHideInNav';
        }
        if (!$flat && ($isRoot || $menu->numChild)) {
            $node['attr']['rel'] = 'folder';
            $node['state'] = 'closed';
        }
        $canEdit = $this->canEdit($menu->menu_id, $this->user->getRoles(), $this->user->acl('pinaxcms_contentsedit', 'all'), $this->user->acl('pinaxcms_contentsedit', 'edit') || $this->user->acl($this->pageId,'editDraft'));


        // stato delle varie azioni da gestire con acl
        $node['metadata']['edit'] = $canEdit;
        $node['metadata']['draft'] = 0;
        $node['metadata']['show'] = $canEdit && $this->user->acl($this->pageId, 'visible');
        $node['metadata']['delete'] = $canEdit && $this->user->acl($this->pageId, 'delete');
        $node['metadata']['preview'] = 0;
        $node['metadata']['publish'] = $canEdit && $this->user->acl($this->pageId, 'publish');
        $node['metadata']['lock'] = $canEdit && $this->user->acl($this->pageId, 'edit');
        $node['metadata']['move'] = $canEdit && $this->user->acl($this->pageId, 'edit');
        $node['metadata']['add'] = true;
        $node['metadata']['duplicatePage'] = $this->user->acl($this->application->getPageId(),'new');
        $node['metadata']['duplicateBranch'] = $this->user->acl($this->application->getPageId(),'new');


        // stato della pagina
        $node['metadata']['isDraft'] = 0; //!$menu->menu_isPublished ? 1 : 0;
        $node['metadata']['isShown'] = $menu->menudetail_isVisible ? 1 : 0;
        $node['metadata']['isLocked'] = $menu->menu_isLocked ? 1 : 0;
        $node['metadata']['hasPreview'] = $menu->menu_hasPreview ? 1 : 0;
        $node['metadata']['pagetype'] = $menu->menu_pageType;

        // rimuove alcune azioni se la pagina non è di tipo PAGE
        if ( $menu->menu_type != 'PAGE' ) {
            $node['metadata']['delete'] = false;
            $node['metadata']['show'] = false;
            $node['metadata']['duplicatePage'] = false;
            $node['metadata']['duplicateBranch'] = false;
        }

        $node['attr']['class'] .= !$node['metadata']['edit'] ? ' pinaxcmsSiteTree-no-edit' : ' pinaxcmsSiteTree-edit';

        if (!$flat && $this->pageTypes && $this->pageTypes[$menu->menu_pageType]) {
            $node['metadata']['acceptparent'] = $this->pageTypes[$menu->menu_pageType]['acceptParent'];
            $found = false;
            foreach($this->pageTypes as $k=>$v) {
                $found = strpos($v['acceptParent'], $menu->menu_pageType)!==false;
                if ($found) break;
            }
            $node['metadata']['add'] = $found;
        }

        return $node;
    }

}
