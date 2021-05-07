<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_mvc_core_Command extends pinax_application_AbstractCommand
{
	/**
	 * @var pinax_components_Component
	 */
	protected $view = NULL;

	/**
	 * @var pinax_interfaces_Session
	 */
	protected $session;

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
		$this->session = $this->container->get('pinax_interfaces_Session');
	}


	function changePage( $routingName, $option=array(), $addParam=array() )
	{
		$url = __Link::makeUrl( $routingName, $option, $addParam );
		pinax_helpers_Navigation::gotoUrl( $url );
	}

	function changeAction( $action )
	{
		$url = __Link::makeUrl( 'linkChangeAction', array( 'action' => $action ) );
		pinax_helpers_Navigation::gotoUrl( $url );
	}

	function goHere($params=null, $hash='')
	{
		pinax_helpers_Navigation::gotoUrl( __Routing::scriptUrl(), $params, $hash);
	}

	function changeBackPage()
	{
		$url = $this->session->get( '__backUrl__', '' );
		pinax_helpers_Navigation::gotoUrl( $url );
	}


	function setComponentsVisibility( $components, $state )
	{
		$this->setComponentsAttribute( $components, 'visible', $state );
	}

	function setComponentsAttribute( $components, $attribute, $state, $merge = false )
	{
		$components = is_array( $components ) ? $components : array( $components );
		foreach( $components as $v )
		{
			$c = $this->view->getComponentById( $v );
			if ( is_object( $c ) )
			{
				$c->setAttribute( $attribute, $state, $merge);
			}
		}
	}

	/**
	 * @param string|array $components
	 * @param mixed $content
	 */
	function setComponentsContent( $components, $content )
	{
		$components = is_array( $components ) ? $components : array( $components );
		foreach( $components as $v )
		{
			$c = $this->view->getComponentById( $v );
			if ( is_object( $c ) )
			{
				$c->setContent( $content);
			}
		}
	}
}
