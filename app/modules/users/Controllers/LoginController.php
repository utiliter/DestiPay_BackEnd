<?php
namespace App\Modules\Users\Controllers;


use App\Modules\Users\UserService;
use function App\Modules\Users\validate_login;

class LoginController
{
   public $db;
   public $response;

   public function __construct(private UserService $user)
   {
      global $DB, $response;
      $this->db = $DB;

      $this->response = $response;

   }

   public function login()
   {
      $data = validate_login();

      if (isset($data["errors"])) {
         $this->response->status = 422;
         return $this->response->data = $data;
      }

      $data = $this->user->attempToLogin($data);

      if (!$data) {
         $this->response->status = 401;
         return $this->response->data = ["errors" => ["email" => "Email or password is incorrect"]];
      }


      $this->response->status = 200;
      return $this->response->data = $data;

   }




   public function logout()
   {
      $this->user->invalidateUserToken();
      $this->response->status = 200;
      $this->response->data = [
         "message" => "Logged out successfully"
      ];
   }

}
?>