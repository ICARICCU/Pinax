<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_controllers_pageEdit_ajax_Save extends pinax_mvc_core_CommandAjax
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    protected $menuId;

    public function execute($data)
    {
        $this->checkPermissionForBackend();
        $this->directOutput = true;
        return $this->save($data, false);
    }


    protected function save($data, $draft, $publishDraft=false)
    {
        $contentProxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.ContentProxy');
        $contentVO = $contentProxy->getContentVO();
        $contentVO->setFromJson($data);
        $this->menuId = $contentVO->getId();
        $r = $contentProxy->saveContent($contentVO,
                            pinax_ObjectValues::get('org.pinax', 'editingLanguageId'),
                            __Config::get('pinaxcms.content.history'),
                            true,
                            true,
                            $draft,
                            $publishDraft
                            );

        if ($r===true) {
            return true;
        } else {
            return array('errors' => array($r));
        }
    }
}
