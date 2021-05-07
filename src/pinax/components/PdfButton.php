<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


class pinax_components_PdfButton extends pinax_components_Component
{
	var $isEnabled;

	/**
	 * Init
	 *
	 * @return	void
	 * @access	public
	 */
	function init()
	{
		// define the custom attributes
		$this->defineAttribute('label', false, 	__T( 'PNX_PRINT_PDF' ),	COMPONENT_TYPE_STRING);

		// call the superclass for validate the attributes
		parent::init();
	}

	function render_html()
	{
		if( $this->_application->getCurrentMenu()->printPdf && 	!pinax_ObjectValues::get( 'pinax.application', 'pdfMode' ) )
		{
			$url = PNX_HOST."/index.php?".__Request::get( '__url__' )."&printPdf=1";
			$output = __Link::makeSimpleLink( $this->getAttribute( 'label' ), $url, '', 'printPdf' );
			$this->addOutputCode( $output );
		}
	}

}
