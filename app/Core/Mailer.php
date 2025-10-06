<?php


declare(strict_types=1);


namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;



class Mailer
{
   protected $mail;

   public function __construct(protected Config $config)
   {
      $this->mail = new PHPMailer(true);

      $this->mail->isSMTP();

      $mailConfig = $config->get("MAIL");

      $this->mail->Host = $mailConfig["host"];
      $this->mail->SMTPAuth = $mailConfig["SMTPAuth"];
      $this->mail->Username = $mailConfig["username"];
      $this->mail->Password = $mailConfig["password"];
      $this->mail->SMTPSecure = $mailConfig["SMTPsecure"];
      $this->mail->Port = $mailConfig["port"];


   }


   public function sendMail($data)
   {
      $fromAddress = $data["fromAddress"] ?? $this->config->get("MAIL")["mailFromAddress"];
      $fromName = $data["fromName"] ?? $this->config->get("MAIL")["mailFromName"];
      $isHtml = $data["isHTML"] ?? $this->config->get("MAIL")["isHtml"];

      $this->mail->setFrom($fromAddress, $fromName);
      $this->mail->addAddress('test@com', 'Test Name');

      $this->mail->isHTML($isHtml);
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