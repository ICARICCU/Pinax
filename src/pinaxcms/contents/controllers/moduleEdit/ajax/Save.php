<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_contents_controllers_moduleEdit_ajax_Save extends pinax_mvc_core_CommandAjax
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    public function execute($data)
    {
        $this->checkPermissionForBackend();

        $data = json_decode($data);
        $pageId = $this->application->getPageId();
        if (!$this->user->acl($pageId, 'visible', null, $data->__id) || !$this->user->acl($pageId, 'edit', null, $data->__id)) {
            pinax_helpers_Navigation::accessDenied($this->user->isLogged());
        }

        $this->directOutput = true;
        return $this->save($data, false);
    }

    protected function save($data, $draft, $publishDraft=false)
    {
        $contentproxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.ModuleContentProxy');
        $result = $contentproxy->saveContent($data, __Config::get('pinaxcms.content.history'), $draft, false, $publishDraft);

        if ($result['__id']) {
            return array('set' => $result);
        }
        else {
            return array('errorFields' => $result);
        }
    }
}
