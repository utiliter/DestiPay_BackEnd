<?php
namespace App\Modules\Users\Controllers;

use App\Modules\Users\UserService;

use Exception;
use function App\Modules\Users\bcrypt;
use function App\Modules\Users\getUserTableName;
use function App\Modules\Users\validateChangePassword;
use function App\Modules\Users\validateCreateAccount;
use function App\Modules\Users\validateDelete;
use function App\Modules\Users\validateEdit;

class UserController
{
   public $db;
   public $response;

   public function __construct(private UserService $user)
   {
      global $DB, $response;
      $this->db = $DB;

      $this->response = $response;

   }


   public function list()
   {

      ddd("from list");
   }



   /**
    *  Handle the request to create a new user from the app
    */
   function create_account()
   {
      $validateData = validateCreateAccount();
      if (isset($validateData["errors"])) {
         $this->response->status = 422;
         return $this->response->data = $validateData;
      }



      if (isset($validateData["partner_id"])) {
         $partner = $this->user->repo->checkIfExist($validateData["partner_id"], "object_partner");

         if (!$partner) {
            $this->response->status = 422;
            return $this->response->data = ["errors" => ["user_type" => "Partner does not exist"]];
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
            "id" => $this->db->insert_id,
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








   /**
    *  Handle the request to update user's password in the database
    */
   public function change_password()
   {
      $data = validateChangePassword();

      if (isset($data["errors"])) {
         $this->response->status = 422;
         return $this->response->data = $data;
      }


      $userTableName = getUserTableName((int) $data["user_type"]);

      $userType = checkIfExist($data["user_type"], "users_types");


      if (!$userType) {
         $this->response->status = 422;
         return $this->response->data = ["errors" => ["user_type" => "User type does not exist"]];
      }


      $userExists = $this->user->repo->findById($data["user_id"], $userTableName);


      if (!$userExists) {
         $this->response->status = 404;
         return $this->response->data = [];
      }

      $formatedData = ["password" => bcrypt($data["password"])];

      $res = DBupdate($userTableName, $formatedData, $data["user_id"]);


      if ($res) {
         // TODO message

         $this->response->status = 201;
         return $this->response->data = [
            "message" => "Password successfully changed"
         ];
      }

      return $this->response->data = [];
   }




   /**
    * Handle the request to update a user in the database
    */
   function edit_account()
   {

      $validateData = validateEdit();

      if (isset($validateData["errors"])) {
         $this->response->status = 422;
         return $this->response->data = $validateData;
      }


      $userTableName = getUserTableName((int) $validateData["user_type"]);


      $user = $this->user->repo->checkIfUserExists($validateData["email"], $userTableName);


      if (!$user) {
         $this->response->status = 404;
         return $this->response->data = [
         ];
      }



      if (isset($validateData["partner_id"])) {
         $partner = checkIfExist($validateData["partner_id"], "object_partner");

         if (!$partner) {
            $this->response->status = 422;
            return $this->response->data = ["errors" => ["user_type" => "Partner does not exist"]];
         }
      }


      $formatedData =
         array_diff_key($validateData, ["user_id" => 0]);



      $res = DBupdate($userTableName, $formatedData, $validateData["user_id"]);


      if ($res) {

         $user = [
            "id" => $validateData["user_id"],
            "first_name" => $validateData["first_name"],
            "last_name" => $validateData["last_name"],
            "email" => $validateData["email"],
            "user_type" => $validateData["user_type"]

         ];

         $this->response->status = 200;
         return $this->response->data = [
            "user" => $user,

         ];
      }

      $this->response->status = 500;
      return $this->response->data = [];

   }



   /**
    *  Handle the request to delete the user from the database
    */
   public function delete_account()
   {

      $validatedData = validateDelete();

      if (isset($validateData["errors"])) {
         $this->response->status = 422;
         return $this->response->data = $validatedData;
      }


      $userTableName = getUserTableName((int) $validatedData["user_type"]);

      $user = $this->user->repo->checkIfUserExists($validatedData["user_id"], $userTableName);

      if (!$user) {
         $this->response->status = 404;
         return $this->response->data = [
         ];
      }


      $data = [
         "deleted_at" => date("Y-m-d H:i:s"),
         "is_active" => 0
      ];

      $res = DBupdate($userTableName, $data, $validatedData["user_id"]);

      if ($res) {

         $this->response->status = 200;
         return $this->response->data = [
            "message" => "User deleted successfully",

         ];
      }

      $this->response->status = 500;
      return $this->response->data = [];




   }

   /**
    * Get list of all user roles
    */
   public function get_user_roles()
   {

      $data = $this->db->query("SELECT * FROM users_roles")->fetch_all(MYSQLI_ASSOC);


      return $this->response->data = $data;
   }

   /**
    * Get list of all user types
    */
   public function get_user_types()
   {
      $data = $this->db->query("SELECT * FROM users_types")->fetch_all(MYSQLI_ASSOC);

      return $this->response->data = $data;
   }

}


?>