<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class pinax_application_AbstractCommand extends PinaxObject
{
	/** @var pinax_mvc_core_Application $application */
	protected $application = NULL;
	/** @var pinax_application_User $user */
	protected $user = NULL;
    /** @var pinax_dependencyInjection_Container $container */
    protected $container;

	/**
	 * @param pinax_mvc_core_Application $application
	 */
	function __construct($application=NULL)
	{
		$this->application = $application;
		$this->user = &$this->application->getCurrentUser();
        $this->container = $this->application->getContainer();
	}

}
