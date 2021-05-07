<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_mvc_core_Application extends pinax_application_Application
{
	protected $proxyMap = array();
	public $useXmlSiteMap = false;

    /**
     * @param bool $readPageId
     */
	function _startProcess($readPageId=true)
	{
		foreach( $this->proxyMap as $k=>$v )
		{
			$v->onRegister();
		}

		parent::_startProcess($readPageId);
	}

    /**
     * @param string $className
     *
     * @return PinaxObject|mixed
     */
	function registerProxy( $className )
	{
		if ( array_key_exists( $className, $this->proxyMap ) )
		{
			new pinax_Exception( '[mvc:Application] Proxy già registrato' );
		}

		$classObj = __ObjectFactory::createObject( $className, $this );

		if ( is_object( $classObj ) )
		{
			$this->proxyMap[ $className ] = $classObj;
			return $classObj;
		}
		else
		{
			new pinax_Exception( '[mvc:Application] Proxy non trovato '.$className );
		}
	}

    /**
     * @param string $className
     *
     * @return null|
     */
	function retrieveProxy( $className )
	{
		if ( array_key_exists( $className, $this->proxyMap ) )
		{
			return $this->proxyMap[ $className ];
		}

		return null;
	}

    /**
     * @param $className
     *
     * @return PinaxObject|mixed
     */
	function retrieveService( $className )
	{
	    if ($this->container->has($className)) {
            	return $this->container->get($className);
            }
	    $classObj = __ObjectFactory::createObject( $className, $this );
	    return $classObj;
	}

    /**
     * @param bool $forceReload
     */
	function createSiteMap($forceReload=false)
	{
		if ( $this->useXmlSiteMap )
		{
			$this->siteMap = pinax_ObjectFactory::createObject('pinax.application.SiteMapXML');
			$this->siteMap->getSiteArray($forceReload);
		}
		else
		{
			parent::createSiteMap($forceReload);
		}
	}
}
