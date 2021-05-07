<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_languages_controllers_Edit extends pinaxcms_contents_controllers_activeRecordEdit_Edit
{
    public function execute($id)
    {
        parent::execute($id);
        $language = __ObjectFactory::createModel('pinaxcms.core.models.Language');
        $this->setComponentsVisibility('language_isVisible', $language->fieldExists('language_isVisible'));
    }
}
