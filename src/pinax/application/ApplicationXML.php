<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinax_application_ApplicationXML extends pinax_application_Application
{
	/**
	 * @return void
	 */
	function createSiteMap($forceReload=false)
	{
		$this->siteMap = &pinax_ObjectFactory::createObject('pinax.application.SiteMapXML');
		$this->siteMap->getSiteArray($forceReload);
	}
}
