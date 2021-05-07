<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

trait pinax_mvc_core_AuthenticatedCommandTrait
{
	/** @var pinax_application_User $user */
	protected $user = NULL;

    /**
     * Check if the user is logged
     */
    protected function checkIsLogged($service=null, $action=null)
    {
        if (!$this->user->isLogged()) {
            pinax_helpers_Navigation::accessDenied();
        }
    }


	/**
	 * Check the user permission
	 * @param  string $service
	 * @param  string $action [description]
	 */
	protected function checkPermission($service=null, $action=null)
	{
		$canAccess = $this->user->isLogged();

        if ($canAccess && $service && $action) {
        	$canAccess = $this->user->acl($service, $action);
        }

        if (!$canAccess) {
            pinax_helpers_Navigation::accessDenied();
        }
	}

        /**
     * Check the user permission
     * @param  string $service
     * @param  string $action [description]
     */
    protected function checkPermissionForBackend($service=null, $action=null)
    {
        if (!$this->user->backEndAccess) {
            pinax_helpers_Navigation::accessDenied($this->user->isLogged());
        }

        $this->checkPermission($service, $action);
    }
}
