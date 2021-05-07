<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_mvc_core_CommandAjax extends pinax_application_AbstractCommand
{
	/** @var pinax_components_Component $view */
	protected $view = NULL;

	public $directOutput = false;

	/**
	 * @param pinax_mvc_core_Application $application
	 * @param pinax_components_Component $view
	 */
	function __construct($application=NULL, $view=NULL)
	{
		// backward compatibility
        // because we have swapped the arguments
        if (!is_a($application, 'pinax_application_Application')) {
            $tempAppication = $view;
            $view = $application;
            $application = is_null( $tempAppication ) && !is_null( $view ) ? $view->_application : $tempAppication;
        }

        parent::__construct($application);

		$this->view = $view;
	}


	function changePage( $routingName, $option=array() )
	{
		$url = __Link::makeUrl( $routingName, $option );
		$url = str_replace( "ajax", "index", $url );
		return $url;
	}

	function changeAction( $action )
	{
		$url = __Link::makeUrl( 'linkChangeAction', array( 'action' => $action ) );
		$url = str_replace( "ajax", "index", $url );
		return $url;
	}

}
