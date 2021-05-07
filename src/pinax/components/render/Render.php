<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_render_Render extends PinaxObject
{
	var $_parent;
	var $_application;
	var $_rootComponent;
	var $_outputMode;
	var $_skipChilds;

	function __construct(&$parent, $outputMode, $skipChilds=false)
	{
		$this->_parent = &$parent;
		$this->_application = &$parent->_application;
		$this->_rootComponent = &$this->_application->getRootComponent();
		$this->_outputMode = $outputMode;
		$this->_skipChilds = $skipChilds;

	}

	function render()
	{
		if (method_exists($this, 'render_'.$this->_outputMode))
		{
			$this->{'render_'.$this->_outputMode}();
		}
		else
		{
			$content = $this->_parent->getContent();
			if (is_array( $content ) && isset($content['id']))
			{
				$content['id'] = str_replace('@', '___', $content['id']);
			}
			$skinClass = &$this->getSkinClass();

			$skinClass->set($this->_parent->getTagName(), $content);
			$skinClass->set('Component', $content);
			$skinClass->set('id', $this->_parent->getId());
			$skinClass->set('ajaxMode', $this->_application->_ajaxMode);

			if (!$this->_skipChilds && isset($this->_parent->acceptOutput))
			{
				if ($this->_parent->acceptOutput && $this->_parent->overrideEditableRegion)
				{
					$this->_parent->renderChilds($this->_outputMode);
					$childOutput = '';
					for ($i=0; $i<count($this->_parent->_output); $i++)
					{
						$childOutput .= $this->_parent->_output[$i]['code'];
					}
					$skinClass->set('childOutput', $childOutput);
					$output = $skinClass->execute();
					$this->_parent->addParentOutputCode($output);
					return;
				}
			}

			$output = $skinClass->execute();
			$this->_parent->addOutputCode($output);
		}
	}

	// gestione skins

	function getSkin()
	{
		return $this->_parent->skin();
	}


	function getDefaultSkin()
	{
		return '';
	}

	function &getSkinClass()
	{
		$skin 				= $this->getSkin();
		$skinFileName 		= NULL;
		$skinDefaultHtml 	= NULL;
		$skinType 			= __Config::get( 'DEFAULT_SKIN_TYPE' );
		$skinFolders = [];

		if (empty($skin))
		{
			$skinFileName 		= str_replace(":", "", $this->_parent->_tagname);
			$skinType			= "PHPTAL";
			$skinDefaultHtml 	= $this->getDefaultSkin();
		}
		else if (is_object($skin))
		{
			$skinType			= $skin->getAttribute('skinType');
			if ( empty( $skinType ) ) $skinType = __Config::get( 'DEFAULT_SKIN_TYPE' );
			$skinFileName 		= $this->_application->getPageId().'_'.$skin->getId();
			$skinDefaultHtml 	= $skin->getTemplateString();
		}
		else
		{
			$skin = explode(':', $skin);
			if (count($skin)==2) {
				$skinComponentFolder = realpath(pinax_findClassPath($skin[0])).'/skins';
				$skinFileName = $skin[1];
			} else if (count($skin)==1) {
				$skinComponentFolder = null;
				$skinFileName = $skin[0];
			} else {
				throw new Exception('Wrong skin value: '.$this->getSkin());
			}

			$skinExtension = pathinfo($skinFileName, PATHINFO_EXTENSION);
			$extensionToSkinType = [
				'html' => 'PHPTAL',
				'htm' => 'PHPTAL',
				'phptal' => 'PHPTAL',
				'twig' => 'twig',
				'tw' => 'twig',
			];

			if (!isset($extensionToSkinType[$skinExtension])) {
				throw new Exception('Wrong skin type: '.$skinExtension);
			}
			$skinType = $extensionToSkinType[$skinExtension];

			if ( !pinax_ObjectValues::get( 'pinax.application', 'pdfMode' ) ) {
				$skinFolders[] = pinax_Paths::getRealPath('APPLICATION_TEMPLATE', 'skins');
				$skinFolders[] = pinax_Paths::getRealPath('APPLICATION_TEMPLATE_DEFAULT', 'skins');
			} else {
				$skinFolders[] = pinax_Paths::getRealPath('APPLICATION_TEMPLATE', 'skins-pdf');
				$skinFolders[] = pinax_Paths::getRealPath('APPLICATION_TEMPLATE_DEFAULT', 'skins-pdf');
			}

			if ($skinComponentFolder) {
				$skinFolders[] = $skinComponentFolder;
			}
		}

		$skinClass = pinax_ObjectFactory::createObject('pinax.template.skin.'.strtoupper($skinType), $skinFileName, $skinFolders, $skinDefaultHtml, $this->_application->getLanguage());
		return $skinClass;
	}
}
