<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_helpers_Modules extends PinaxObject
{
    private static $supportedClasses = array(
        'pinax_components_Fieldset',
        'pinax_components_JSTabGroup',
        'pinax_components_JSTab',
        'pinax_components_Panel',
        'pinax_components_Input'
    );

    private function isComponentSupported($c){
        $clss = self::$supportedClasses;
        $l = count($clss);
        $i = $l;

        while($i-->0){
            if (is_a($c, $clss[$i]))
                return true;
        }

        return false;
    }

    public function getFields($pageId, $getRepChild = false)
    {
        $editForm = $this->getEditForm($pageId);
        $fields = $this->getChildFields($editForm, $getRepChild);
        return $fields;
    }

    public function getModelPath($pageId)
    {
        $editForm = $this->getEditForm($pageId);

        for ($i = 0; $i < count($editForm->childComponents); $i++) {
            $c = $editForm->childComponents[$i];
            $id = $c->getAttribute('id');

            if ($id == '__model') {
                return $c->getAttribute('value');
            }
        }

        return null;
    }

    protected function getEditForm($pageId, $formId='editForm', $formAction='edit')
    {
        $oldAction = __Request::get('action');
        __Request::set('action', $formAction);

        $application = pinax_ObjectValues::get('org.pinax', 'application');
        $originalRootComponent = $application->getRootComponent();

        $siteMap = $application->getSiteMap();
        $siteMapNode = $siteMap->getNodeById($pageId);
        $pageType = $siteMapNode->getAttribute('pageType');

        $path = pinax_Paths::get('APPLICATION_PAGETYPE');
        $templatePath = pinaxcms_Pinaxcms::getSiteTemplatePath();
        $options = array(
            'skipImport' => true,
            'pathTemplate' => $templatePath,
            'mode' => 'edit'
        );
        $pageTypeObj = &pinax_ObjectFactory::createPage($application, $pageType, $path, $options);
        $rootComponent = $application->getRootComponent();
        $rootComponent->init();
        $application->_rootComponent = &$originalRootComponent;
        __Request::set('action', $oldAction);

        return $rootComponent->getComponentById($formId);
    }

    protected function getChildFields($component, $getRepChild = false)
    {
        $fields = array();
        $childComponents = $component->childComponents;
        for ($i = 0; $i < count($childComponents); $i++) {
            $c = $childComponents[$i];
            $id = $c->getAttribute('id');
            $data = $c->getAttribute('data');

            if (( is_subclass_of($c, 'pinax_components_HtmlFormElement') ||
                    ( $this->isComponentSupported($c) && $data)
                    ) && substr($id, 0, 2) != '__' ) {
                $temp = new StdClass;
                $temp->type = $this->getFieldTypeFromComponent($c);
                 if($getRepChild && ($temp->type == pinaxcms_core_models_enum_FieldTypeEnum::REPEATER_IMAGE
                                  || $temp->type == pinaxcms_core_models_enum_FieldTypeEnum::REPEATER_MEDIA
                                  || $temp->type == pinaxcms_core_models_enum_FieldTypeEnum::REPEATER
                                  ) ){
                     $fields = array_merge($fields, $this->getChildFields($c, $getRepChild));
                } else {
                    $temp->id = $id;
                    $temp->label = $c->getAttribute('label');
                    $temp->data = $data;
                    $fields[$id] = $temp;
                }
            } else if ($this->isComponentSupported($c) && !$data) {
                $fields = array_merge($fields, $this->getChildFields($c, $getRepChild));
            }
        }

        return $fields;
    }

    protected function getFieldTypeFromComponent($component)
    {
        $data = $component->getAttribute('data');

        if (strpos($data, 'type=mediapicker') !== false && strpos($data, 'mediatype=IMAGE') !== false) {
            return pinaxcms_core_models_enum_FieldTypeEnum::IMAGE;
        } else if (strpos($data, 'type=mediapicker') !== false && strpos($data, 'mediatype=IMAGE') === false) {
            return pinaxcms_core_models_enum_FieldTypeEnum::MEDIA;
        } else if (strpos($data, 'type=checkbox') !== false) {
            return pinaxcms_core_models_enum_FieldTypeEnum::CHECKBOX;
        } else if (strpos($data, 'type=url') !== false) {
            return pinaxcms_core_models_enum_FieldTypeEnum::URL;
        } else if (strpos($data, 'type=number') !== false) {
            return pinaxcms_core_models_enum_FieldTypeEnum::NUMBER;
        } else if (strpos($data, 'type=repeat') !== false || stripos($data, 'type=FormEditRepeatMandatory') !== false) {
            if (count($component->childComponents)==1) {
                $temp = $this->getFieldTypeFromComponent($component->childComponents[0]);
                if ($temp==pinaxcms_core_models_enum_FieldTypeEnum::IMAGE) {
                    return pinaxcms_core_models_enum_FieldTypeEnum::REPEATER_IMAGE;
                } else if ($temp==pinaxcms_core_models_enum_FieldTypeEnum::MEDIA) {
                    return pinaxcms_core_models_enum_FieldTypeEnum::REPEATER_MEDIA;
                }
            }
            return pinaxcms_core_models_enum_FieldTypeEnum::REPEATER;
        } else {
            return pinaxcms_core_models_enum_FieldTypeEnum::TEXT;
        }
    }
}
