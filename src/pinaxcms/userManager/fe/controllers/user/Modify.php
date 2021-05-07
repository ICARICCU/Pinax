<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_userManager_fe_controllers_user_Modify extends pinax_mvc_core_Command
{
    protected $submit;

    function __construct( $view=NULL, $application=NULL )
    {
        parent::__construct( $view, $application );
        $this->submit = strtolower( __Request::get( 'submit', '' ) ) == 'submit';
    }

    public function execute()
    {
        if ($this->user->isLogged() && !$this->submit) {
            $ar = pinax_ObjectFactory::createModel('pinax.models.User');
            $ar->load($this->user->id);
            $values = $ar->getValuesAsArray();
            $values['user_password'] = '';
            __Request::setFromArray($values);
        }
    }

    public function executeLater()
    {
        if ($this->user->isLogged() && $this->submit && $this->view->validate()) {
            $ar = pinax_ObjectFactory::createModel('pinax.models.User');
            $ar->load($this->user->id);

            $email = pinax_Request::get('user_email', '');
            if ($email != $ar->user_loginId) {
                $ar2 = pinax_ObjectFactory::createModel('pinax.models.User');
                if ($ar2->find(array('user_loginId' => $email)) && $ar2->user_id!=$ar->user_id) {
                    $this->view->validateAddError(__T('MW_REGISTRATION_EMAIL_ALREADY_EXISTS'));
                    return;
                }
            }

            $fields = $ar->getFields();
            $skipFields = array('user_password', 'user_id', 'user_FK_usergroup_id', 'user_FK_site_id', 'user_dateCreation', 'user_isActive', 'user_loginId', 'user_confirmCode');
            foreach($fields as $k=>$v) {
                if (in_array($k, $skipFields) || !__Request::exists($k)) continue;
                $ar->$k = __Request::get($k);
            }

            $password = __Request::get('user_password');
            if ($password) {
                $ar->user_password = pinax_password($password);
            }
            $ar->user_loginId = $email;
            $ar->user_email = $email;
            $ar->save();
            $this->changeAction('modifyConfirm');
        }
    }
}
