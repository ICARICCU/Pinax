<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class pinaxcms_userManager_fe_controllers_user_LostPassword extends pinax_mvc_core_Command
{
    protected $submit;

    function __construct( $view=NULL, $application=NULL )
    {
        parent::__construct( $view, $application );
        $this->submit = strtolower( __Request::get( 'submit', '' ) ) == 'submit';
    }

    public function executeLater($email)
    {
        if ($this->submit && $this->view->validate()) {
            $ar = pinax_ObjectFactory::createModel('pinax.models.User');
            if (!$ar->find(array('user_email' => $email))) {
                // utente non trovato
                $this->view->validateAddError(__T('MW_LOSTPASSWORD_ERROR'));
                return false;
            }

            $password = pinax_makeConfirmCode();
            $ar->user_password = pinax_password($password);

            // invia la notifica all'utente
            $subject    = pinax_locale_Locale::get('MW_LOSTPASSWORD_EMAIL_SUBJECT');
            $body       = pinax_locale_Locale::get('MW_LOSTPASSWORD_EMAIL_BODY');
            $body       = str_replace('##USER##', $email, $body);
            $body       = str_replace('##HOST##', pinax_helpers_Link::makeSimpleLink(PNX_HOST, PNX_HOST), $body);
            $body       = str_replace('##PASSWORD##', $password, $body);
            pinax_helpers_Mail::sendEmail(  array('email' => pinax_Request::get('email', ''), 'name' => $ar->user_firstName.' '.$ar->user_lastName),
                                                    array('email' => __Config::get('SMTP_EMAIL'), 'name' => __Config::get('SMTP_SENDER')),
                                                    $subject,
                                                    $body);
            $ar->save();
            $this->changeAction('lostPasswordConfirm');
        }
    }
}
