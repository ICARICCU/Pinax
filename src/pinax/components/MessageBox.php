<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_MessageBox extends pinax_components_Component
{
	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		$this->defineAttribute('cssClass',		false, 	'',		COMPONENT_TYPE_STRING);
		$this->defineAttribute('type',			false, 	'ALL',	COMPONENT_TYPE_STRING);
		$this->defineAttribute('message',			false, 	NULL,	COMPONENT_TYPE_STRING);
		$this->defineAttribute('showEmpty',			false, 	false,	COMPONENT_TYPE_BOOLEAN);
		parent::init();
	}



	/**
	 * Render
	 *
	 * @return	void
	 * @access	public
	 */
	function render_html()
	{
		// get the messages quee
		$this->_content = pinax_application_MessageStack::get( $this->getAttribute('type') );
		if ( count($this->_content) || $this->getAttribute( 'showEmpty' ) )
		{
			$attributes			= array();
			$attributes['id'] 	= $this->getId();
			$attributes['class'] 	= $this->getAttribute('cssClass');
			$output  = '<div '.$this->_renderAttributes($attributes).'>';

			if ( count( $this->_content ) )
			{
				if ( !is_null( $this->getAttribute( 'message' ) ) )
				{
					$output  .= '<p>'.$this->getAttribute( 'message' ).'</p>';
				}

				$output  .= '<ul>';
				foreach( $this->_content as $v )
				{
					$output .= '<li>'.$v.'</li>';
				}
				$output  .= '</ul>';
			}

			$output  .= '</div>';
			$this->addOutputCode($output);

			pinax_application_MessageStack::reset( $this->getAttribute('type') );
		}
	}
}
