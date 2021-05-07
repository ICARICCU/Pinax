<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_languages_controllers_Delete extends pinax_mvc_core_Command
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    public function executeLater($id)
    {
        $this->checkPermissionForBackend();

        if ($id) {
            $proxy = pinax_ObjectFactory::createObject('pinaxcms.languages.models.proxy.LanguagesProxy');
            $proxy->delete($id, null);

            pinax_helpers_Navigation::goHere();
        }
    }
}
