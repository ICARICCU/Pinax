<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_userManager_controllers_ajax_Save extends pinax_mvc_core_CommandAjax
{
    use pinax_mvc_core_AuthenticatedCommandTrait;

    public function execute($data)
    {
        $this->checkPermissionForBackend();

		$this->directOutput = true;

    	$data = json_decode($data);
    	$id = (int)$data->__id;
    	$modelName = $data->__model;

		// controlla se l'email è già nel DB
		$ar = __ObjectFactory::createModel($modelName);
		if ( $ar->find( array( 'user_email' => $data->user_email ) ) ) {
			if ( $id != $ar->user_id ) {
				return array('errors' => array(__T( 'E-mail is already present' )));
			}
		}
        $ar ->emptyRecord();
        if ( $ar->find( array( 'user_loginId' => $data->user_loginId ) ) ) {
            if ( $id != $ar->user_id ) {
                return array('errors' => array(__T( 'Username is already present' )));
            }
        }

        if ($id && $id != $ar->user_id ) {
            if (!$ar->load($id)) {
                return array('errors' => array(__T( 'User not found' )));
            }
        }

		$password = $data->user_password;
		$password = $password ? pinax_password( $password ) : $ar->user_password;
		$data->user_password = $password;
		if ( $id == 0 ) {
			$data->user_dateCreation = new pinax_types_DateTime();
		}

        $proxy = pinax_ObjectFactory::createObject('pinaxcms.contents.models.proxy.ActiveRecordProxy');
        $result = $proxy->save($data);

        if ($result['__id']) {
            return array('set' => $result);
        }
        else {
            return array('errors' => $result);
        }
    }
}
