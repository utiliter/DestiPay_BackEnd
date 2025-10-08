<?php


declare(strict_types=1);


namespace App\Core;

use DateTime;
use PHPMailer\PHPMailer\PHPMailer;



class Mailer
{
   protected $mail;
   protected $content;

   public function __construct(public bool $shouldQueue = true)
   {

      global $mailConfig;

      $this->mail = new PHPMailer(true);

      $this->mail->isSMTP();

      // $mailConfig = $config->get("MAIL");

      $this->mail->Host = $mailConfig["host"];
      $this->mail->SMTPAuth = $mailConfig["SMTPAuth"];
      $this->mail->Username = $mailConfig["username"];
      $this->mail->Password = $mailConfig["password"];
      $this->mail->SMTPSecure = $mailConfig["SMTPsecure"];
      $this->mail->Port = $mailConfig["port"];


   }

   public function content()
   {
   }
   public function send($data)
   {

      if ($this->shouldQueue) {

         $date = new DateTime("+5minutes");
         $sendAt = $date->format('Y-m-d H:i:s');

         $mailData = [
            "queen_id" => $data["queen_id"],
            "email_from" => $data["email_from"],
            "email_to" => $data["email_to"],
            "email_content" => $this->content(),
            "send_at" => $data["send_at"] ?? $sendAt
         ];

         dbCreate("log_email_queue", $mailData);

      } else {
         $this->sendMail($data);
      }


   }


   public function sendMail($data)
   {
      // $fromAddress = $data["fromAddress"] ?? $this->config->get("MAIL")["mailFromAddress"];
      // $fromName = $data["fromName"] ?? $this->config->get("MAIL")["mailFromName"];
      // $isHtml = $data["isHTML"] ?? $this->config->get("MAIL")["isHtml"];

      $this->mail->setFrom($data["email_from"]);
      $this->mail->addAddress($data["email_to"]);

      // $this->mail->isHTML();
      $this->mail->isHTML(false);
      $this->mail->Subject = 'Your Subject Here';
      $this->mail->Body = $this->content();

      return $this->mail->send();
      // echo 'Message could not be sent. Mailer Error: ' . $this->mail->ErrorInfo
   }


}
?>