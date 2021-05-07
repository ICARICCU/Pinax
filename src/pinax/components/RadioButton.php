<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_RadioButton extends pinax_components_HtmlFormElement
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
		$this->defineAttribute('cssClass',			false, 	__Config::get('pinax.formElement.radio.cssClass'),		COMPONENT_TYPE_STRING);
		$this->defineAttribute('cssClassLabel',			false, 	__Config::get('pinax.formElement.cssClassLabel'),		COMPONENT_TYPE_STRING);
		$this->defineAttribute('disabled',			false, 	NULL,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('label',				false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('labelPosition',		false, 	'left',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('required',			false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('value',				false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('defaultValue',		false, 	'0',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('wrapLabel',			false, 	false,	COMPONENT_TYPE_BOOLEAN);
		$this->defineAttribute('name',				false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('title',				false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('checkedValue',		false, 	'1',	COMPONENT_TYPE_STRING);

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
			// imposta il valore di default
			$this->_content = $this->getAttribute('defaultValue');
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
			$output  .= '<input '.$this->_renderAttributes($attributesHidden, array()).'/>';
			$attributes['id'] .= '_hidden_disabled';
		}
		$output  .= '<input '.$this->_renderAttributes($attributes).'/>';

		$this->_rightLabel = $this->getAttribute('labelPosition')=='right';
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
		$name = $this->getAttribute( 'name' );
		$attributes 				= array();
		$attributes['id'] 			= $this->getId();
		$attributes['name'] 		= empty( $name ) ? $this->getOriginalId() : $name ;
		$attributes['class'] 		= $this->getAttribute('required') ? 'required' : '';
		$attributes['class'] 		.= $this->getAttribute( 'cssClass' ) != '' ? ( $attributes['class'] != '' ? ' ' : '' ).$this->getAttribute( 'cssClass' ) : '';
		$attributes['type'] 		= 'radio';
		$attributes['disabled'] 	= $this->getAttribute('disabled') || $this->getAttribute('readOnly') ? 'disabled' : '';

		$attributes['value'] 		= $this->getAttribute( 'checkedValue' );
		if ( $attributes['value'] == "" ) $attributes['value'] = $attributes['id'];
		$attributes['checked'] 		= $this->_content == $attributes['value'] ? 'checked' : '';
		$attributes['label'] = $this->encodeOuput($this->getAttribute('label'));
		$attributes['title'] = $this->getAttributeString('title');
		return $attributes;
	}
}
