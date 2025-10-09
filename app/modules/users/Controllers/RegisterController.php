<?php
namespace App\Modules\Users\Controllers;

use App\Modules\Users\Mails\VerifyAccountMail;
use App\Modules\Users\UserService;

use function App\Modules\Users\bcrypt;
use function App\Modules\Users\getUserTableName;
use function App\Modules\Users\validateRegister;

class RegisterController
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
    * VISITORS -  Handle the request to create visitor user in the database
    */
   function register()
   {
      $validateData = validateRegister();

      if (isset($validateData["errors"])) {
         $this->response->status = 422;
         return $this->response->data = $validateData;
      }


      if (isset($validateData["queen_id"])) {
         $queen = $this->user->repo->checkIfExist($validateData["queen_id"], "object_queen");

         if (!$queen) {
            $this->response->status = 422;
            return $this->response->data = ["errors" => ["user_type" => "Queen does not exist"]];
         }
      }


      $userTableName = getUserTableName((int) $validateData["user_type"]);


      if ($this->user->repo->emailExists($validateData["email"], $userTableName)) {
         $this->response->status = 422;
         return $this->response->data = ["errors" => ["email" => "Email already exists"]];
      }


      $user = $this->user->repo->checkIfUserExists($validateData["email"], $userTableName);

      if ($user) {
         $this->response->status = 422;
         return $this->response->data = ["errors" => ["email" => "Email already exists"]];
      }


      $validateData["password"] = bcrypt($validateData["password"]);
      $validateData["guid"] = create_guid();
      $validateData["is_active"] = 1;
      $res = dbCreate($userTableName, $validateData);


      if ($res) {

         $user = [
            "id" => (int) $this->db->insert_id,
            "first_name" => $validateData["first_name"],
            "last_name" => $validateData["last_name"],
            "email" => $validateData["email"],
            "user_type" => (int) $validateData["user_type"]

         ];


         // TODO hendlati kada email ne prode
         $this->user->sendVerifyToken($validateData["email"], $userTableName);


         $this->response->status = 201;
         return $this->response->data = [
            "user" => $user,
            "message" => "Please check your email to verify your account."
         ];
      }

      $this->response->status = 500;
      return $this->response->data = [];

   }



   function verify_account()
   {

      $token = $_GET["token"] ?? null;

      if (!$token) {
         $this->response->status = 400;
         return $this->response->data = ["message" => "Invalid or expired token"];
      }

      $res = $this->user->repo->findToken($token, "log_users_verify_tokens");

      if (!$res) {
         $this->response->status = 400;
         return $this->response->data = ["message" => "Invalid or expired token"];
      }

      $userTableName = getUserTableName((int) $res["user_type"]);


      $user = $this->user->repo->findByEmail($res["email"], $userTableName);

      $data = [
         "is_active" => 1,
         "verified_at" => getNowDatetime()
      ];

      DBupdate($userTableName, $data, $user["id"]);


      $this->user->repo->deactivateAllVerifyTokens($res["email"], $res["user_type"], "log_users_verify_tokens");

      $this->response->status = 200;
      return $this->response->data = ["message" => "Account successfully verified"];
   }


}
?>