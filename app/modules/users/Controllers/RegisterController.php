<?php
namespace App\Modules\Users\Controllers;

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

      if (isset($validateData["queen_id"])) {
         $queen = $this->user->repo->check_if_exist($validateData["queen_id"], "object_queen");

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
      $validateData["uuid"] = create_guid();
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

         $token = generateToken($user);

         $this->user->insertUserToken($this->db->insert_id, $token, $validateData["user_type"]);


         // $this->sendVerifyToken($validateData["email"], $userTableName);




         $this->response->status = 201;
         return $this->response->data = [
            "user" => $user,
            "token" => $token
         ];
      }

      $this->response->status = 500;
      return $this->response->data = [];



   }



}
?>