<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_views_components_PageTypeInclude extends pinax_components_ComponentContainer
{

    /**
     * Init
     *
     * @return    void
     * @access    public
     */
    public function init()
    {
        $this->defineAttribute('src', false, '', COMPONENT_TYPE_STRING);

        // call the superclass for validate the attributes
        parent::init();
    }


    /**
     * @throws Exception
     */
    public function process()
    {
        $this->addComponentsToEdit($this->getAttribute('src'));
        parent::process();
    }


    /**
     * @param $src
     * @throws Exception
     */
    protected function addComponentsToEdit($src)
    {
        $originalRootComponent = &$this->_application->getRootComponent();
        $this->childComponents = [];

        $pageTypeObj = pinax_ObjectFactory::createPage($this->_application,
            $src,
            pinax_Paths::get('APPLICATION_TO_ADMIN_PAGETYPE'),
                    [ 'skipImport' => true,
                      'mode' => 'edit']);

        $rootComponent = &$this->_application->getRootComponent();
        $rootComponent->init();
        $this->_application->_rootComponent = &$originalRootComponent;
        for ($i = 0; $i < count($rootComponent->childComponents); $i++) {
            $this->addChild($rootComponent->childComponents[$i]);
            $rootComponent->childComponents[$i]->_parent = &$this;
        }
    }
}
