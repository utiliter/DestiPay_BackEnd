<?php


declare(strict_types=1);


namespace App\Core;

use DateTime;
use PHPMailer\PHPMailer\PHPMailer;



class Mailer
{
   protected $mail;
   protected $content;
   protected $mailConfig;

   public function __construct(public $queenId, public bool $shouldQueue = true)
   {

      global $mailConfig, $DB;

      $this->mailConfig = $DB->query("SELECT smtp_from_name , smtp_from_email, smtp_port,smtp_encryption,smtp_host,smtp_username,smtp_password FROM object_queen_settings WHERE queen_id = $queenId")->fetch_assoc();

      $this->mail = new PHPMailer(true);

      $this->mail->isSMTP();

      // $mailConfig = $config->get("MAIL");

      $this->mail->Host = $this->mailConfig["smtp_host"];
      // $this->mail->SMTPAuth = $this->mailConfig["SMTPAuth"];
      $this->mail->SMTPAuth = true;
      $this->mail->Username = $this->mailConfig["smtp_username"];
      $this->mail->Password = $this->mailConfig["smtp_password"];
      $this->mail->SMTPSecure = $this->mailConfig["smtp_encryption"];
      // $this->mail->SMTPSecure = "ssl";
      $this->mail->Port = $this->mailConfig["smtp_port"];

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

      // return $this->mail->send();


      !$this->mail->send();
      // echo 'Message could not be sent. Mailer Error: ' . $this->mail->ErrorInfo
   }


}
?>