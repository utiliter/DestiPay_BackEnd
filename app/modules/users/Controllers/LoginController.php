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


   /**
    * Login user into app
    * Default user type : 3 - visitor
    */
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


      if (!$data["is_active"]) {
         $this->response->status = 403;
         return $this->response->data = ["errors" => ["email" => "Your account has not been verified yet."]];
      }

      $token = $this->user->handleGenerateAndInsertBearerToken($data);

      $this->response->status = 200;
      return $this->response->data = ["user" => $data, "token" => $token];

   }



   public function logout()
   {
      $this->user->invalidateUserBearerToken();
      $this->response->status = 200;
      $this->response->data = [
         "message" => "Logged out successfully"
      ];
   }

}
?>