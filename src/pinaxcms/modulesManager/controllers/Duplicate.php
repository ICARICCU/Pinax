<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_modulesManager_controllers_Duplicate extends pinax_mvc_core_Command
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    public function execute($id, $moduleId)
    {
        $this->checkPermissionForBackend();

        if (!$moduleId) {
            $this->setComponentsAttribute('moduleId', 'value', $id);
            $moduleVO = $this->getModuleVO($id);
            $text = '<p>Duplicazione modulo <b>'.__T($moduleVO->name).'</b> id: '.$moduleVO->id.'<p>';
            $this->setComponentsAttribute('text', 'text', $text);
        } else {
            $moduleVO = $this->getModuleVO($moduleId);
            $duplicateClass = pinax_ObjectFactory::createObject($moduleVO->classPath.'.Duplicate', $moduleVO);


            pinax_Modules::deleteCache();
            $this->changeAction('index');
        }
    }

    private function getModuleVO($id)
    {
// TODO controllare che il modulo sia corretto
        $modules = pinax_Modules::getModules();
        return $modules[$id];
    }
}
