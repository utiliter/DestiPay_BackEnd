<?php

use PHPMailer\PHPMailer\PHPMailer;
class Mailer
{
   public $mail;
   protected $config;

   public function __construct()
   {

      global $mailerConfig;

      $this->mail = new PHPMailer(true);

      $this->mail->isSMTP();


      $this->mail->Host = $mailerConfig["host"];
      $this->mail->SMTPAuth = $mailerConfig["SMTPAuth"];
      $this->mail->Username = $mailerConfig["username"];
      $this->mail->Password = $mailerConfig["password"];
      $this->mail->SMTPSecure = $mailerConfig["SMTPsecure"];
      $this->mail->Port = $mailerConfig["port"];


   }


   public function sendMail($data)
   {
      $fromAddress = $data["fromAddress"];
      $fromName = $data["fromName"];


      $this->mail->setFrom($fromAddress, $fromName);
      $this->mail->addAddress('test@com', 'Test Name');

      // $this->mail->isHTML($isHtml);
      $this->mail->Subject = 'Your Subject Here';
      $this->mail->Body = 'This is the plain text message body';

      if (!$this->mail->send()) {
         echo 'Message could not be sent. Mailer Error: ' . $this->mail->ErrorInfo;
      } else {
         echo 'Message has been sent';
      }


   }
}


?>