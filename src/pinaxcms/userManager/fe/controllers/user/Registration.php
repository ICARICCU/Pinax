<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_userManager_fe_controllers_user_Registration extends pinax_mvc_core_Command
{
    protected $submit;

    function __construct( $view=NULL, $application=NULL )
    {
        parent::__construct( $view, $application );
        $this->submit = strtolower( __Request::get( 'submit', '' ) ) == 'submit';
    }

    public function executeLater()
    {
       if ($this->submit && $this->view->validate()) {
            $email = pinax_Request::get('email', '');
            $ar = pinax_ObjectFactory::createModel('pinax.models.User');
            if ($ar->find(array('user_loginId' => $email))) {
                $this->view->validateAddError(__T('MW_REGISTRATION_EMAIL_ALREADY_EXISTS'));
                return;
            }

            $fields = $ar->getFields();
            foreach($fields as $k=>$v) {
                if (__Request::exists($k)) {
                    $ar->$k = __Request::get($k);
                }
            }

            $ar->user_FK_usergroup_id = __Config::get('USER_DEFAULT_USERGROUP');
            $ar->user_isActive = __Config::get('USER_DEFAULT_ACTIVE_STATE');
            $ar->user_password = pinax_password(__Request::get('psw'));
            $ar->user_loginId = $email;
            $ar->user_email = $email;
            $ar->user_dateCreation = new pinax_types_DateTime();
            $ar->save();
            $this->changeAction('registrationConfirm');
        }
    }
}
