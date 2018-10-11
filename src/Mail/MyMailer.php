<?php

namespace App\Mail;

use PHPMailer\PHPMailer\PHPMailer;

/**
 * 
 */ 
class MyMailer
{
    private $fromEmail;
    
    private $fromName;

    public function getFromEmail()
    {
        return $this->fromEmail;
    }

    public function setFromEmail($fromEmail)
    {
        $this->fromEmail = $fromEmail;

        return $this;
    }

    public function getFromName()
    {
        return $this->fromName;
    }

    public function setFromName($fromName)
    {
        $this->fromName = $fromName;

        return $this;
    }

    /**
     * envoyer un mail avec PHPMailer
     * ATTENTION AJOUTER CES 2 LIGNES DANS .env POUR ACTIVER PHPMAILER
     * # POUR UTILISER SMTP
     * PHPMAILER_GMAIL_FROM_EMAIL=c4mars@gmail.com
     * PHPMAILER_GMAIL_PASSWORD=weekend2018
     * PHPMAILER_GMAIL_FROM_NAME=c4mars
     * # SUR 1AND1
     * # PHPMAILER_SENDMAIL_FROM_EMAIL=contact@code4marseille.fr
     * # PHPMAILER_SENDMAIL_FROM_NAME=code4marseille
     */
    public function sendMail ($to, $subject, $content)
    {
        
        // ATTENTION AJOUTER CES 2 LIGNES DANS .env POUR ACTIVER PHPMAILER
        /*
PHPMAILER_GMAIL_FROM_EMAIL=c4mars@gmail.com
PHPMAILER_GMAIL_PASSWORD=weekend2018
PHPMAILER_GMAIL_FROM_NAME=c4mars
# PHPMAILER_SENDMAIL_FROM_EMAIL=contact@code4marseille.fr
# PHPMAILER_SENDMAIL_FROM_NAME=code4marseille
        */

        // https://github.com/PHPMailer/PHPMailer/blob/master/examples/gmail.phps
        $mail = new PHPMailer;
        
        $fromEmailSendmail  = getEnv("PHPMAILER_SENDMAIL_FROM_EMAIL");
        $fromNameSendmail   = getEnv("PHPMAILER_SENDMAIL_FROM_NAME");

        $fromEmailGmail     = getEnv("PHPMAILER_GMAIL_FROM_EMAIL");
        $fromPasswordGmail  = getEnv("PHPMAILER_GMAIL_PASSWORD");
        $fromNameGmail      = getEnv("PHPMAILER_GMAIL_FROM_NAME");
        
        if ($fromEmailGmail != "") {
            $mail->isSMTP();
            $mail->SMTPDebug = 0;
            $mail->Host = 'smtp.gmail.com';
            $mail->Port = 587;
            $mail->SMTPSecure = 'tls';
            $mail->SMTPAuth = true;
            // FIXME: A DEPLACER DANS parameters.yml
            $mail->Username = $fromEmailGmail;
            $mail->Password = "weekend2018";
            $mail->setFrom($fromEmailGmail, $fromNameGmail);
            $mail->addReplyTo($fromEmailGmail, $fromNameGmail);
            $this->setFromEmail($fromEmailGmail);
            $this->setFromName($fromNameGmail);
        }
        if ($fromEmailSendmail != "") {
            // https://github.com/PHPMailer/PHPMailer/issues/816
            $mail->isSendMail();
            $mail->setFrom($fromEmailSendmail, $fromNameSendmail);
            $mail->addReplyTo($fromEmailSendmail, $fromNameSendmail);
            $this->setFromEmail($fromEmailSendmail);
        }
        
        $mail->addAddress($to, $to);
        $mail->Subject = $subject;
        $mail->msgHTML($content, __DIR__);
        
        if (!$mail->send()) {
            return "Mailer Error: " . $mail->ErrorInfo;
        } else {
            return "";
        }
    }
}