<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_controllers_pageEdit_ajax_SavePublish extends pinaxcms_contents_controllers_pageEdit_ajax_Save
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    public function execute($data)
    {
        $this->checkPermissionForBackend();
        $this->directOutput = true;

        $reloadUrl = __Request::get('reloadUrl');

        $result = $this->save($data, false, true);
        if ($result===true) {
            $url = __Link::addParams(array('status' => pinaxcms_contents_views_components_PageEdit::STATUS_PUBLISHED), false, $reloadUrl);
            return array(
                'evt' => 'pinaxcms.pageEdit',
                'message' => array('menuId' => $this->menuId.'&status='.pinaxcms_contents_views_components_PageEdit::STATUS_PUBLISHED)
                );
        }

        return $result;
    }
}
