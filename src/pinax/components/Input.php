<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_Input extends pinax_components_HtmlFormElement
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
		$this->defineAttribute('defaultValue',		false, 	'',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('bindTo',			false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('cols',				false, 	'',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClass',			false, 	__Config::get('pinax.formElement.input.cssClass'),		COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClassLabel',			false, 	__Config::get('pinax.formElement.cssClassLabel'),		COMPONENT_TYPE_STRING);
		$this->defineAttribute('disabled',			false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('label',				false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('minLength',			false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('maxLength',			false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('rows',				false, 	'',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('readOnly',			false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('size',				false, 	'',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('value',				false, 	NULL,		COMPONENT_TYPE_STRING);
		$this->defineAttribute('wrapLabel',			false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('wrap',				false, 	'off',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('type',				false, 	'text',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('htmlEditor',		false, 	false,	COMPONENT_TYPE_BOOLEAN);	// TODO
		$this->defineAttribute('required',			false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('title',				false, 	'',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('placeholder',		false, 	'',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('autocomplete',		false, 	'',	COMPONENT_TYPE_STRING);

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
		$this->_content = $this->getAttribute('value');
		if (is_object($this->_content))
		{
			$contentSource = &$this->getAttribute('value');
			$this->_content = $contentSource->loadContent($this->getId(), $this->getAttribute('bindTo'));
		}
		else if (is_null($this->_content))
		{
			$this->_content = $this->_parent->loadContent($this->getId(), $this->getAttribute('bindTo'));
		}
		else
		{
			$this->_content = html_entity_decode( $this->_content );
		}
		if (empty($this->_content))
		{
			$this->_content = $this->getAttribute('defaultValue');

			if ( method_exists($this->_parent, 'setFilterValue') )
			{
				$bindTo = $this->getAttribute('bindTo');
				$this->_parent->setFilterValue(!empty($bindTo) ? $bindTo : $this->getId(), $this->_content);
			}
		}
	}

	/**
	 * Render
	 *
	 * @return	void
	 * @access	public
	 */
	function render_html()
	{
		$attributes 				= array();
		$attributes['id'] 			= $this->getId();
		$attributes['name'] 		= $this->getOriginalId();
		$attributes['disabled'] 	= $this->getAttribute('disabled') ? 'disabled' : '';
		$attributes['readonly'] 	= $this->getAttribute('readOnly') ? 'readonly' : '';
		$attributes['title'] 		= $this->getAttributeString('title');
		$attributes['placeholder'] 		= $this->getAttributeString('placeholder');
		if ( empty( $attributes['title'] ) )
		{
			$attributes['title'] 		= $this->getAttributeString('label');
		}
		$attributes['class'] 		= $this->getAttribute('cssClass');
		$attributes['class'] 		.= (!empty($attributes['class']) ? ' ' : '').($this->getAttribute('required') ? 'required' : '');

		if ($this->getAttribute('type')=='multiline')
		{
			$attributes['cols'] 		= $this->getAttribute('cols');
			$attributes['rows'] 		= $this->getAttribute('rows');
			$attributes['wrap'] 		= $this->getAttribute('wrap');

			$output  = '<textarea '.$this->_renderAttributes($attributes).'>';
			$output .= $this->encodeOuput($this->_content);
			$output .= '</textarea>';

			$this->addTinyMCE( true );
		}
		else
		{
			$attributes['type'] 		= $this->getAttribute('type');
			$attributes['maxLength'] 	= $this->getAttribute('maxLength');
			$attributes['size'] 		= $this->getAttribute('size');
			$attributes['autocomplete'] = $this->getAttributeString('autocomplete');
			$attributes['value'] 		= $this->encodeOuput(is_string($this->_content) ? $this->_content : json_encode($this->_content));

			$output  = '<input '.$this->_renderAttributes($attributes).'/>';
		}

		$label = $this->getAttributeString('label') ? : '';
		if ($label) {
			$cssClassLabel = $this->getAttribute( 'cssClassLabel' );
			$cssClassLabel .= ( $cssClassLabel ? ' ' : '' ).($this->getAttribute('required') ? 'required' : '');
			if ($this->getAttribute('wrapLabel')) {
				$label = pinax_helpers_Html::label($this->getAttributeString('label'), $this->getId(), true, $output, array('class' => $cssClassLabel ), false);
				$output = '';
			} else {
				$label = pinax_helpers_Html::label($this->getAttributeString('label'), $this->getId(), false, '', array('class' => $cssClassLabel ), false);
			}
		}
		$this->addOutputCode($this->applyItemTemplate($label, $output));
	}

	public function render_form()
	{
		$attributes 				= array();
		$attributes['id'] 			= $this->getId();
		$attributes['name'] 		= $this->getOriginalId();
		$attributes['disabled'] 	= $this->getAttribute('disabled') ? 'disabled' : '';
		$attributes['readonly'] 	= $this->getAttribute('readOnly') ? 'readonly' : '';
		$attributes['title'] 		= $this->getAttributeString('title');
		$attributes['placeholder'] 		= $this->getAttributeString('placeholder');
		if ( empty( $attributes['title'] ) )
		{
			$attributes['title'] 		= $this->getAttributeString('label');
		}
		$attributes['class'] 		= $this->getAttribute('cssClass');
		$attributes['class'] 		.= (!empty($attributes['class']) ? ' ' : '').($this->getAttribute('required') ? 'required' : '');

		if ($this->getAttribute('type')=='multiline')
		{
			$attributes['cols'] 		= $this->getAttribute('cols');
			$attributes['rows'] 		= $this->getAttribute('rows');
			$attributes['wrap'] 		= $this->getAttribute('wrap');
		}
		else
		{
			$attributes['type'] 		= $this->getAttribute('type');
			$attributes['maxLength'] 	= $this->getAttribute('maxLength');
			$attributes['size'] 		= $this->getAttribute('size');
			$attributes['autocomplete'] = $this->getAttributeString('autocomplete');
			$attributes['value'] 		= $this->encodeOuput(is_string($this->_content) ? $this->_content : json_encode($this->_content));
		}

		$label = $this->getAttributeString('label') ? : '';
		$attributes['label'] = $this->getAttributeString('label');
		$cssClassLabel = $this->getAttribute( 'cssClassLabel' );
		$cssClassLabel .= ( $cssClassLabel ? ' ' : '' ).($this->getAttribute('required') ? 'required' : '');
		$attributes['cssClassLabel'] = $cssClassLabel;

		$this->addOutputCode($attributes);
	}


	private function addTinyMCE( $attachToElement )
	{
		if ($this->getAttribute('htmlEditor')===true)
		{
			$rootComponent = &$this->getRootComponent();

			if (!pinax_ObjectValues::get('pinax.JS.TinyMCE', 'add', false))
			{
				pinax_ObjectValues::set('pinax.JS.TinyMCE', 'add', true);

				$rootComponent->addOutputCode(pinax_helpers_JS::linkCoreJSfile('tiny_mce.js?v='.PNX_CORE_VERSION, 'tiny_mce/', false), 'head');
				$rootComponent->addOutputCode(pinax_helpers_JS::linkCoreJSfile('Pinax_tiny_mce.js?v='.PNX_CORE_VERSION), 'head', true);

				$imgStyles = __Config::get( 'TINY_MCE_IMG_STYLES' );
				$imgSizes = __Config::get( 'TINY_MCE_IMG_SIZES' );
				$templates = __Config::get( 'TINY_MCE_TEMPLATES' );
				$tableClassList = __Config::get('TINY_MCE_TABLE_CLASS_LIST');
				$imgStyles = $imgStyles ? : '""';
				$imgSizes = $imgSizes ? : '""';
				$templates = $templates ? : '""';
				$tableClassList = $tableClassList ? : '';

				$jsCode = 'Pinax.tinyCSS = "'.__Config::get( 'TINY_MCE_CSS' ).'";';
				$jsCode .= 'Pinax.tinyMCE_plugins = "'.__Config::get( 'TINY_MCE_DEF_PLUGINS' ).'";';
				$jsCode .= 'Pinax.tinyMCE_btn1 = "'.__Config::get( 'TINY_MCE_BUTTONS1' ).'";';
				$jsCode .= 'Pinax.tinyMCE_btn2 = "'.__Config::get( 'TINY_MCE_BUTTONS2' ).'";';
				$jsCode .= 'Pinax.tinyMCE_btn3 = "'.__Config::get( 'TINY_MCE_BUTTONS3' ).'";';
				$jsCode .= 'Pinax.tinyMCE_styles = '.__Config::get( 'TINY_MCE_STYLES' ).';';
				$jsCode .= 'Pinax.tinyMCE_imgStyles = '.$imgStyles.';';
				$jsCode .= 'Pinax.tinyMCE_imgSizes = '.$imgSizes.';';
				$jsCode .= 'Pinax.tinyMCE_templates = '.$templates.';';
				$jsCode .= 'Pinax.tinyMCE_tableClassList = "'.$tableClassList.'";';
				$jsCode .= 'Pinax.tinyMCE_allowLinkTarget = '.(__Config::get( 'TINY_MCE_ALLOW_LINK_TARGET' ) ? 'true' : 'false').';';
				$jsCode .= 'Pinax.tinyMCE_queryStringEnabled = '.(__Config::get( 'pinaxcms.pagePicker.queryStringEnabled' ) ? 'true' : 'false').';';
				$validElements = __Config::get( 'TINY_MCE_VALID_ELEMENTS' );
        		$jsCode .= 'Pinax.tinyMCE_validElements = "'.($validElements ? : '').'";';
				$plugins = __Config::get( 'TINY_MCE_PLUGINS' );
				if ( $plugins ) {
					$jsCode .= 'Pinax.tinyMCE_plugins .= ",'.$plugins.'";';
				}
				$tinyMCEoptions = __Config::get('TINY_MCE_OPTIONS');
				$jsCode .= 'Pinax.tinyMCE_customOptions = '.($tinyMCEoptions ? : '{}').';';

				$rootComponent->addOutputCode(pinax_helpers_JS::JScode( $jsCode ), 'head');
			}

			if (!is_null($this->getAttribute('adm:tinyMCEplugin')))
			{
				$pluginsNames = explode( ',', $this->getAttribute('adm:tinyMCEplugin') );
				$pluginsPaths = array();
				for( $i=0; $i < count( $pluginsNames ); $i++ )
				{
					$pos = strrpos( $pluginsNames[ $i ], "/" );
					if ( $pos !== false )
					{
						$pluginsPaths[] = '../../../../../../'.$pluginsNames[ $i ];
						$pluginsNames[ $i ] = substr( $pluginsNames[ $i ], $pos + 1 );
					}
					else
					{
						$pluginsPaths[] = $pluginsNames[ $i ];
					}
				}
				if ( count( $pluginsPaths ) )
				{
					$jsCode = 'Pinax.tinyMCE_plugins += ",'.implode( ',', $pluginsPaths ).'";';
					$jsCode .= 'Pinax.tinyMCE_pluginsNames += ",'.implode( ',', $pluginsNames ).'";';
					$rootComponent->addOutputCode(pinax_helpers_JS::JScode( $jsCode ), 'head');
				}
			}

			if ( $attachToElement )
			{
				$id = $this->getId();
				$jsCode = <<< EOD
jQuery(function(){
	var options = Pinax.tinyMCE_options;
	options.mode = "exact";
	options.elements = '$id';
	tinyMCE.init( options );
});
EOD;
				//$this->addOutputCode(pinax_helpers_JS::JScode( $jsCode ));
			}
		}
	}
}
