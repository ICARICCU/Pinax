<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_mvc_scaffold_controllers_AbstractCommand extends pinax_mvc_core_Command
{
	protected $modelName = '';
	protected $pageId = '';
	protected $submit = false;
	protected $refreshPage = false;
	protected $show = false;
	protected $id;

	/**
	 * @param pinax_components_Component $view
	 * @param pinax_mvc_core_Application $application
	 */
	function __construct( $view=NULL, $application=NULL )
	{
		parent::__construct( $view, $application );

		$this->submit = strtolower( __Request::get( 'submit', '' ) ) == 'submit' || strtolower( __Request::get( 'submit', '' ) ) == 'submitclose';
		$this->show = strtolower( __Request::get( 'action', '' ) ) == 'show';
		$this->refreshPage = strtolower( __Request::get( 'action', '' ) ) == 'close' || strtolower( __Request::get( 'submit', '' ) ) == 'submitclose';
		$this->id = intval( __Request::get( 'id', '' ) );
		$this->pageId = $this->application->getPageId();
	}
}
