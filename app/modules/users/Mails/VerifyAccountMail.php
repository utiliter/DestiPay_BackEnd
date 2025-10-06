<?php


namespace App\Modules\Users\Mails;

use App\Core\Mailer;

// class VerifyAccountMail extends Mailer
class VerifyAccountMail
{




   public function sendMail($data)
   {





   }


   private function emailContent($data): string
   {
      global $URL;

      return "
      To: {$data['email']}
      From: test@test.com
      Subject: Verify Account
      Content: Please verify your account, <a href=\"{$URL}/DestiPay_BackEnd/?module=users&action=verify_account&token={$data['token']}\" target=\"_blank\">Verify account</a>
";

   }

   public function send($data): void
   {

      $filename = EMAIL_LOGS . "-verify mail" . "-" . date('d-m-Y-H-i-s') . ".txt";

      file_put_contents($filename, $this->emailContent($data));

   }









}

?>