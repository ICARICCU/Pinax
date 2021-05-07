<?php
/**
 * This file is part of the PINAX framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPMailer\PHPMailer\PHPMailer;

class pinax_helpers_Mail extends PinaxObject
{
	/**
     * @param       $to
     * @param       $from
     * @param       $subject
     * @param       $body
     * @param array $attach
     * @param array $cc
     * @param array $bcc
     * @param null  $embedDir
     *
     * @return array
     */
    public static function sendEmail($to, $from, $subject, $body, $attach=array(), $cc=array(), $bcc=array(), $embedDir=NULL, $templateHeader='', $templateFooter='')
	{
		try
        {

            $host = __Config::get('SMTP_HOST');

            /** @var PHPMailer $mail */
			$mail = new PHPMailer();
            $mail->CharSet = __Config::get('CHARSET');
			if ($host!='')
			{
				$mail->IsSMTP();
				$mail->Host = $host;

                $port = __Config::get('SMTP_PORT');
                $username = __Config::get('SMTP_USER');
                $smtpSecure = __Config::get('SMTP_SECURE');

				if ($username!='')
				{
                    $mail->SMTPAuth = true;
					$mail->Username = $username;
					$mail->Password = __Config::get('SMTP_PSW');
				}

				if ($port) {
					$mail->Port = $port;
				}
                if ($smtpSecure) {
                    $mail->SMTPSecure = $smtpSecure;
                }
			}

			$mail->From 	= trim($from['email']);
			$mail->FromName = trim($from['name']);
			$mail->AddAddress(trim($to['email']), trim($to['name']));
			$mail->Subject 	= $subject;

            if ($cc)
			{
				if ( !is_array( $cc ) ) $cc = array( $cc );
				foreach( $cc as $v )
				{
                    if ($v) $mail->AddCC($v);
				}
			}

            if ($bcc)
			{
				if ( !is_array( $bcc ) ) $bcc = array( $bcc );
				foreach( $bcc as $v )
				{
                    if ($v) $mail->AddBCC($v);
				}
			}

			$bodyTxt = $body;
			$bodyTxt = str_replace('<br>', "\r\n", $bodyTxt);
			$bodyTxt = str_replace('<br />', "\r\n", $bodyTxt);
			$bodyTxt = str_replace('</p>', "\r\n\r\n", $bodyTxt);
			$bodyTxt = strip_tags($bodyTxt);
			$bodyTxt = html_entity_decode($bodyTxt);

			if (!is_null($attach)){
				foreach ($attach as $a)
				{
					$mail->AddAttachment($a['fileName'], $a['originalFileName']);
				}
			}

			if (!is_null($embedDir))
			{
                $processedImage = array();
                $embImage = 0;
				// controlla se c'è da fare l'embed delle immagini
				preg_match_all('/<img[^>]*src=("|\')([^("|\')]*)("|\')/i', $body, $inlineImages);
				if (count($inlineImages) && count($inlineImages[2]))
				{
					for ($i=0;$i<count($inlineImages[2]);$i++)
					{
						if (in_array($inlineImages[2][$i], $processedImage)) continue;
						$processedImage[] = $inlineImages[2][$i];

						$embImage++;
						$imageType = explode('.', $inlineImages[2][$i]);
						$code = str_pad($embImage, 3, '0', STR_PAD_LEFT);
						$mail->AddEmbeddedImage($embedDir.$inlineImages[2][$i], $code, $inlineImages[2][$i], "base64", "image/".$imageType[count($imageType)-1]);
						$body = str_replace($inlineImages[2][$i], 'cid:'.$code, $body);
					}
				}

				preg_match_all('/<td[^>]*background=("|\')([^("|\')]*)("|\')/i', $body, $inlineImages);
				if (count($inlineImages) && count($inlineImages[2]))
				{
					for ($i=0;$i<count($inlineImages[2]);$i++)
					{
						if (in_array($inlineImages[2][$i], $processedImage)) continue;
						$processedImage[] = $inlineImages[2][$i];

						$embImage++;
						$imageType = explode('.', $inlineImages[2][$i]);
						$code = str_pad($embImage, 3, '0', STR_PAD_LEFT);
						$mail->AddEmbeddedImage($embedDir.$inlineImages[2][$i], $code, $inlineImages[2][$i], "base64", "image/".$imageType[count($imageType)-1]);
						$body = str_replace($inlineImages[2][$i], 'cid:'.$code, $body);
					}
				}
			}

			$mail->Body    = $templateHeader.$body.$templateFooter;
			$mail->AltBody = $bodyTxt;

            $r = array('status' => $mail->Send(),
                       'error' => $mail->ErrorInfo);

        }
        catch (Exception $e)
        {
            $r = array('status' => false,
                       'error' => $e->getMessage());
		}

        if (isset($mail)) {
            $smtp_host = $mail->Host;
            $smtp_port = $mail->Port;
        } else {
            $smtp_host = '';
            $smtp_port = '';
        }

        $eventInfo = array('type' => PNX_LOG_EVENT, 'data' => array(
                                    'level' => $r['status'] ? PNX_LOG_DEBUG : PNX_LOG_ERROR,
                                    'group' => 'pinax.helpers.mail',
            						'message' => array('result' => $r,
                                            'to' => $to,
                                            'from' => $from,
                                            'subject' => $subject,
                                            'body' => $body,
                                            'attach' => $attach,
                                            'cc' => $cc,
                              'bcc' => $bcc,
                              'smtp_host' => $smtp_host,
                              'smtp_port' => $smtp_port
            )));

            $evt = pinax_ObjectFactory::createObject( 'pinax.events.Event', null, $eventInfo );
            pinax_events_EventDispatcher::dispatchEvent( $evt );

        return $r;
	}

    /**
     * @param string $fileName
     * @param array $info
     * @param string $htmlTemplateHeader
     * @param string $htmlTemplateFooter
     * @param string $templatePath
     * @return array
     */
    public static function sendEmailFromTemplate( $fileName, $info, $htmlTemplateHeader = '', $htmlTemplateFooter = '', $templatePath = '')
    {
        $email = self::prepareEmailFromTemplate($fileName, $info, $htmlTemplateHeader, $htmlTemplateFooter, $templatePath);
        $sender = isset($info['SENDER']) ? $info['SENDER'] : array(
                                'email' => __Config::get('SMTP_EMAIL'),
                                'name' => __Config::get('SMTP_SENDER'));

        return pinax_helpers_Mail::sendEmail(
            array('email' => $info['EMAIL'], 'name' => $info['FIRST_NAME'].' '.$info['LAST_NAME'] ),
            $sender,
            $email['title'],
            $email['body'],
            $info['ATTACHS'],
            $info['CC'],
            $info['BCC']
        );
    }


    /**
     * @param string $fileName
     * @param array $info
     * @param string $htmlTemplateHeader
     * @param string $htmlTemplateFooter
     * @param string $templatePath
     * @return array
     */
    public static function prepareEmailFromTemplate( $fileName, $info, $htmlTemplateHeader = '', $htmlTemplateFooter = '', $templatePath = '')
    {
        /** @var pinax_application_Application $application */
        $application  = pinax_ObjectValues::get('org.pinax', 'application' );
        $templatePath = $templatePath ? $templatePath : __Paths::get( 'APPLICATION_STATIC' ) . '/templatesEmail/'. $application->getLanguage() .'/';
        $emailText    = file_get_contents( $templatePath.$fileName.'.txt' );
        $emailText    = explode( "\n", $emailText );
        $emailTitle   = array_shift( $emailText );
        $emailBody    = implode( "\n<br />", $emailText );
        foreach( $info as $k => $v )
        {
            $emailBody  = str_replace('##'.$k.'##', $v, $emailBody);
            $emailTitle = str_replace('##'.$k.'##', $v, $emailTitle);
        }

        if ($htmlTemplateHeader && $htmlTemplateFooter) {
            $emailBody = file_get_contents($templatePath.$htmlTemplateHeader).
                $emailBody.
                file_get_contents($templatePath . $htmlTemplateFooter);
        }
        return [
            'title' => $emailTitle,
            'body' => $emailBody,
        ];
    }

    /**
     * @return array
     */
    public static function getEmailInfoStructure()
	{
		$info = array();
		$info['EMAIL'] = '';
		$info['FIRST_NAME'] = '';
		$info['LAST_NAME'] = '';
		$info['USER'] = '';
		$info['PASSWORD'] = '';
		$info['URL_SITE'] = pinax_helpers_Link::makeSimpleLink(PNX_HOST, PNX_HOST);
		$info['HOST'] = PNX_HOST;
		$info['ATTACHS'] = array();
		$info['BCC'] = array();
		$info['CC'] = array();
		return $info;
	}
}
