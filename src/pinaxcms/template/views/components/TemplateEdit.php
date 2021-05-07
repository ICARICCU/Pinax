<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_template_views_components_TemplateEdit extends pinaxcms_views_components_FormEdit
{
    protected $emptySrc;
    protected $editSrc;
    protected $_pageTypeObj;

    /**
     * Init
     *
     * @return  void
     * @access  public
     */
    public function init()
    {
        $this->defineAttribute('global', false, false, COMPONENT_TYPE_BOOLEAN);
        $this->defineAttribute('adm:cssClass', false, __Config::get('pinax.formElement.admCssClass'), COMPONENT_TYPE_STRING);

        // call the superclass for validate the attributes
        parent::init();
    }


    public function process()
    {
        if ($this->getAttribute('global')) {
            $templateXml = 'TemplateAdminGlobal';
            $menuId = 0;
        } else {
            $templateXml = 'TemplateAdmin';
            $menuId = __Request::get('menuId', __Request::get('__id'));
            if (!$menuId) {
    // TODO ERRORE
            }
        }

        $templateProxy = pinax_ObjectFactory::createObject('pinaxcms.template.models.proxy.TemplateProxy');
        $templateName = $templateProxy->getSelectedTemplate();
        if (!$templateName) {
// TODO ERRORE
        }
        $templateProxy->loadTemplateLocale();


        // legge i dati del template
        $data = $templateProxy->getEditDataForMenu($menuId, __Request::exists('loadFromParent'));
        $customTemplate = $templateProxy->getTemplateCustomClass();
        if ($customTemplate && method_exists($customTemplate, 'updateTemplateData')) {
            $customTemplate->updateTemplateData($data);
        }
        $this->setData($data);

        $this->addDefaultComponents();

        $templateRealPath = $templateProxy->getTemplateRealpath();
        if ($templateRealPath) {
            pinax_loadLocaleReal( $templateRealPath.'/classes', $this->_application->getLanguage() );
            pinax_ObjectFactory::attachPageToComponent(
                    $this,
                    $this->_application,
                    $templateXml,
                    $templateRealPath.'/',
                    array(  'idPrefix' => $this->getId().'-',
                            'skipImport' => true,
                            'mode' => 'edit' ),
                    $this->getId().'-');

            parent::process();
            // TODO visualizzare che non ci sono parametri di personalizzazione
        }



    }

    private function addDefaultComponents() {
        $id = '__id';
        $c = pinax_ObjectFactory::createComponent('pinax.components.Hidden', $this->_application, $this, 'pnx:Hidden', $id, $id);
        $this->addChild($c);
        $c->init();
    }
}
