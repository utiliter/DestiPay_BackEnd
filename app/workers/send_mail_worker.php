<?php
namespace App\Workers;
// file_put_contents(EMAIL_ERROR_LOGS . "/aaa", "error");
use App\Core\Mailer;
global $DB;
$now = getNowDatetime();


$emails = $DB->query("SELECT * FROM log_email_queue WHERE send_at <= '$now'")->fetch_all(MYSQLI_ASSOC);

$mailer = new Mailer(false);
foreach ($emails as $email) {

   if ($mailer->sendMail($email)) {

      $formatData = [
         "email_from" => $email["email_from"],
         "email_to" => $email["email_to"],
         "email_content" => $email["email_content"],
         "sent_at" => getNowDatetime()
      ];

      dbCreate("log_email_sent", $formatData);
      $DB->query("DELETE FROM log_email_queue WHERE id = {$email['id']}");

   } else {
      if (!file_exists(EMAIL_ERROR_LOGS)) {
         mkdir(EMAIL_ERROR_LOGS);
      }

      $errorContent = "Failed to send: Email ID : {$email['id']} ";
      file_put_contents(EMAIL_ERROR_LOGS . "email-id-" . $email["id"] . " .txt", $errorContent);
   }
}



?>