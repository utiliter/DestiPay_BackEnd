<?php
namespace App\Modules\Users\Controllers;

use App\Modules\Users\Mails\VerifyDeleteMail;
use App\Modules\Users\UserService;

use Exception;
use function App\Modules\Users\bcrypt;
use function App\Modules\Users\getUserTableName;
use function App\Modules\Users\validateChangePassword;
use function App\Modules\Users\validateCreateAccount;
use function App\Modules\Users\validateDelete;
use function App\Modules\Users\validateEdit;
use function App\Modules\Users\validateSendDeleteTokenMail;

class UserController
{
   public $db;
   public $response;
   public $token;

   public function __construct(private UserService $user)
   {
      global $DB, $response;
      $this->db = $DB;

      $this->response = $response;

      $this->token = getBearerToken();

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
      $validateData["guid"] = create_guid();

      $res = dbCreate($userTableName, $validateData);


      if ($res) {

         $user = [
            "id" => $this->db->insert_id,
            "first_name" => $validateData["first_name"],
            "last_name" => $validateData["last_name"],
            "email" => $validateData["email"],
            "user_type" => (int) $validateData["user_type"]

         ];


         // TODO hanfle ako mail faila
         $this->user->sendVerifyToken($validateData["email"], $userTableName);


         $this->response->status = 201;
         return $this->response->data = [
            "user" => $user,
            "message" => "Verification email has been sent to the user"
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
      $currentUser = getUserDataFromBearerToken($this->token);

      $data = validateChangePassword();

      authorize((int) $currentUser["id"] === (int) $data["user_id"] && (int) $currentUser["user_type"] === $data["user_type"]);

      if (isset($data["errors"])) {
         $this->response->status = 422;
         return $this->response->data = $data;
      }


      $userTableName = getUserTableName((int) $currentUser["user_type"]);

      $userType = checkIfExist($currentUser["user_type"], "users_types");


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
      // todo authorize queen
      $currentUser = getUserDataFromBearerToken($this->token);
      $validateData = validateEdit();

      authorize((int) $currentUser["id"] === (int) $validateData["user_id"] && (int) $currentUser["user_type"] === $validateData["user_type"]);


      if (isset($validateData["errors"])) {
         $this->response->status = 422;
         return $this->response->data = $validateData;
      }

      $userTableName = getUserTableName((int) $currentUser["user_type"]);


      $user = $this->user->repo->checkIfUserExists($currentUser["email"], $userTableName);


      if (!$user) {
         $this->response->status = 404;
         return $this->response->data = [
         ];
      }

      // if (isset($validateData["partner_id"])) {
      //    $partner = checkIfExist($validateData["partner_id"], "object_partner");

      //    if (!$partner) {
      //       $this->response->status = 422;
      //       return $this->response->data = ["errors" => ["user_type" => "Partner does not exist"]];
      //    }
      // }

      $formatedData =
         array_diff_key($validateData, ["user_id" => 0]);


      $res = DBupdate($userTableName, $formatedData, $validateData["user_id"]);

      if ($res) {

         $user = [
            "id" => $currentUser["id"],
            "first_name" => $validateData["first_name"],
            "last_name" => $validateData["last_name"],
            "email" => $currentUser["email"],
            "user_type" => $currentUser["user_type"]

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
    * Send an email to the user with a verification token 
    * to confirm account deletion.
    */
   public function send_verify_delete_token()
   {

      $currentUser = getUserDataFromBearerToken($this->token);
      $validatedData = validateSendDeleteTokenMail();

      if (isset($validateData["errors"])) {
         $this->response->status = 422;
         return $this->response->data = $validatedData;
      }

      authorize((int) $currentUser["email"] === (int) $validatedData["email"] && (int) $currentUser["user_type"] === (int) $validatedData["user_type"]);


      $userTableName = getUserTableName((int) $validatedData["user_type"]);

      $userExists = $this->user->repo->findByEmail($validatedData["email"], $userTableName);

      if ($userExists) {


         $this->user->repo->deactivateAllVerifyTokens($userExists["email"], $userExists["user_type"], "log_users_verify_delete_tokens");

         $data = $this->user->generateVerifyToken($userExists["email"], $userExists["user_type"]);

         $res = $this->user->repo->insertVerifyToken($data, "log_users_verify_delete_tokens");

         if ($res) {



            // TODO stavit u queue kad se postavi mail na serveru
            $mailer = new VerifyDeleteMail($userExists["queen_id"], $data["token"], false);
            $mailer->send($data);


            $this->response->status = 200;
            return $this->response->data = [
               "message" => "verification email for account deletion has been sent",

            ];
         }
      } else {

         return $this->response->status = 404;

      }

   }






   /**
    *  Handle the request to delete the user from the database
    */
   public function delete_account()
   {
      $token = $_GET["token"] ?? null;

      if (!$token) {
         $this->response->status = 400;
         return $this->response->data = ["message" => "Invalid or expired token"];
      }

      $res = $this->user->repo->findToken($token, "log_users_verify_delete_tokens");

      if (!$res) {
         $this->response->status = 400;
         return $this->response->data = ["message" => "Invalid or expired token"];
      }

      $userTableName = getUserTableName((int) $res["user_type"]);
      $user = $this->user->repo->findByEmail($res["email"], $userTableName);

      $data = [

         "deleted_at" => date("Y-m-d H:i:s"),
         "is_active" => 0
      ];

      DBupdate($userTableName, $data, $user["id"]);

      if ($res) {

         $this->user->repo->deactivateAllVerifyTokens($res["email"], $res["user_type"], "log_users_verify_delete_tokens");

         $this->response->status = 200;
         return $this->response->data = [
            "message" => "User successfully deleted",

         ];


      }

   }


   /**
    * Get list of all user roles
    */
   public function get_user_roles()
   {
      // todo authorize
      $data = $this->db->query("SELECT * FROM users_roles")->fetch_all(MYSQLI_ASSOC);

      $this->response->status = 200;
      return $this->response->data = $data;
   }

   /**
    * Get list of all user types
    */
   public function get_user_types()
   {

      // todo authorize
      $data = $this->db->query("SELECT * FROM users_types")->fetch_all(MYSQLI_ASSOC);
      $this->response->status = 200;
      return $this->response->data = $data;
   }






}


?>