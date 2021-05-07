<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_components_EmbedPage extends pinax_components_ComponentContainer
{
	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('label',				false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('required',			false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('requiredMessage',	false, 	NULL,	COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();
	}


	/**
	 * Process
	 *
	 * @return	boolean	false if the process is aborted
	 * @access	public
	 */
	function process()
	{
		$tagContent = $this->getText();
		if (empty($tagContent))
		{
			// richiede il contenuto al padre
			$tagContent = $this->_parent->loadContent($this->getId());
			$this->setText($tagContent);
		}

		if ($this->_parent->_tagname=='pnx:Page')
		{
			if (strpos($this->getText(), '.xml')!==false)
			{
				// crea i componenti leggendoli dal pageType specificato
				$fileName = pinax_Paths::getRealPath('APPLICATION_PAGE_TYPE', $this->getText());
				if (!empty($fileName))
				{
					$originalRootComponent 	= &$this->_application->getRootComponent();
					$this->_pageTypeObj  	= &pinax_ObjectFactory::createPage($this->_application, preg_replace('/.xml$/', '', $this->getText()));
					$rootComponent			= &$this->_application->getRootComponent();
					$rootComponent->init();
					$this->_application->_rootComponent = &$originalRootComponent;

					for($i=0; $i<count($rootComponent->childComponents); $i++)
					{
						$rootComponent->childComponents[$i]->remapAttributes($this->getId().'-');
						$this->addChild($rootComponent->childComponents[$i]);
						$rootComponent->childComponents[$i]->_parent = &$this;
					}

					$this->processChilds();
				}
			}
			else
			{
				$newComponent = &pinax_ObjectFactory::createComponent($this->getText(), $this->_application, $this, '', '', '');
				$newComponent->init();
				$this->addChild($newComponent);
				$this->processChilds();
			}
		}
	}
}
