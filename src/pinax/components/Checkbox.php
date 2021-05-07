<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_Checkbox extends pinax_components_HtmlFormElement
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
		$this->defineAttribute('bindTo',			false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClass',			false, 	__Config::get('pinax.formElement.checkbox.cssClass'),		COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClassLabel',			false, 	__Config::get('pinax.formElement.cssClassLabel'),		COMPONENT_TYPE_STRING);
		$this->defineAttribute('label',				false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('required',			false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('value',				false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('defaultValue',		false, 	'0',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('adm:defaultValue',	false, 	'0',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('wrapLabel',			false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('title',				false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('checkedValue',		false, 	'1',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('data',		false, 	'type=checkbox',	COMPONENT_TYPE_STRING);

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
			// richiede il contenuto al padre
			$this->_content = $this->_parent->loadContent($this->getId(), $this->getAttribute('bindTo'));
		}

		if (is_null($this->_content) || $this->_content=='')
		{
			$this->_content = $this->_application->isAdmin() ?
								$this->getAttribute('adm:defaultValue') :
								$this->getAttribute('defaultValue');
		}
	}

	function render_html()
	{
		$output = '';
		$attributes = $this->getContent();
		unset($attributes['label']);
		if ($this->getAttribute('readOnly')) {
			$attributesHidden = array_merge(array(), $attributes);
			$attributesHidden['type'] = 'hidden';
			unset($attributesHidden['disabled']);
			unset($attributesHidden['checked']);
			unset($attributesHidden['class']);
			$output = '<input '.$this->_renderAttributes($attributesHidden, array()).'/>';

			$attributes['id'] .= '_hidden_disabled';
		}
		$output  .= '<input '.$this->_renderAttributes($attributes).'/>';

		$cssClassLabel = $this->getAttribute( 'cssClassLabel' );
		$cssClassLabel .= ( $cssClassLabel ? ' ' : '' ).($this->getAttribute('required') ? 'required' : '');
		if ($this->getAttribute('wrapLabel')) {
			$label = pinax_helpers_Html::label($this->getAttributeString('label'), $this->getId(), true, $output, array('class' => $cssClassLabel ), false);
			$output = '';
		} else {
			$label = pinax_helpers_Html::label($this->getAttributeString('label'), $this->getId(), false, '', array('class' => $cssClassLabel ), false);
		}
		$this->addOutputCode($this->applyItemTemplate($label, $output));
	}

	/**
	 * @return mixed
	 */
	public function getContent()
	{
		$attributes 				= array();
		$attributes['id'] 			= $this->getId();
		$attributes['name'] 		= $this->getOriginalId();
		$attributes['class'] 		= $this->getAttribute('required') ? 'required' : '';
		$attributes['class'] 		.= $this->getAttribute( 'cssClass' ) != '' ? ( $attributes['class'] != '' ? ' ' : '' ).$this->getAttribute( 'cssClass' ) : '';
		$attributes['type'] 		= 'checkbox';
		$attributes['value'] 		= $this->getAttribute( 'checkedValue' );
		$attributes['checked'] 		= $this->_content == $attributes['value'] ? 'checked' : '';
		$attributes['disabled'] 	= $this->getAttribute('disabled') || $this->getAttribute('readOnly') ? 'disabled' : '';
		$attributes['label'] = $this->encodeOuput($this->getAttribute('label'));
		$attributes['title'] = $this->getAttributeString('title');
		return $attributes;
	}

	function resetContent($childrensReset=false)
	{
		$this->_content = $this->getAttribute('defaultValue');
	}
}
