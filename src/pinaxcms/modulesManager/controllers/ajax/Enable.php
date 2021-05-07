<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_modulesManager_controllers_ajax_Enable extends pinax_mvc_core_CommandAjax
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

	function execute()
	{
		$this->checkPermissionForBackend();

		$id = __Request::get( 'id' );
		$modulesState = pinax_Modules::getModulesState();
		$modulesState[ $id ] = __Request::get( 'action' ) == 'enable';
		pinax_Modules::setModulesState( $modulesState );
		return true;
	}
}
