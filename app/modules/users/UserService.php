<?php
namespace App\Modules\Users;

use App\Modules\Users\Mails\VerifyAccountMail;
use DateTime;

class UserService
{


   public function __construct(public UserRepo $repo)
   {
   }




   public function insertUserBearerToken($userId, $token, $userType, $queenId)
   {

      $data = [
         "user_id" => $userId,
         "bearer_token" => $token,
         "user_type" => $userType,
         "queen_id" => $queenId

      ];

      return dbCreate("users_tokens", $data);

   }



   public function updateUserBearerToken($id, $token)
   {
      return DBupdate("users_tokens", ["bearer_token" => $token, "is_valid" => 1], $id);

   }


   /**
    *  Check if the provided user login credentials are valid
    */
   public function attempToLogin($data)
   {

      $tableName = getUserTableName((int) $data["user_type"]);

      $userExists = $this->repo->findByEmail($data['email'], $tableName);


      $queenId = isset($userExists["queen_id"]) ? (int) $userExists["queen_id"] : null;

      if ($userExists && $userExists["password"] === bcrypt($data['password'])) {

         $user = [
            "id" => (int) $userExists["id"],
            "first_name" => $userExists["first_name"],
            "last_name" => $userExists["last_name"],
            "email" => $userExists["email"],
            "is_active" => (int) $userExists["is_active"],
            "user_type" => (int) $userExists["user_type"],

         ];

         if ($queenId) {
            $user["queen_id"] = $queenId;
         }

         return $user;

      }

      return null;
   }



   /**
    * Generate a new bearer token and update the users_token table
    * @return string
    */
   public function handleGenerateAndInsertBearerToken($user)
   {
      $token = generateBearerToken($user);
      $tokenExist = $this->repo->findUserBearerTokenId($user["id"], $user["user_type"]);


      //todo partner table queen id??
      $queenId = $user["queen_id"] ?? null;

      if ($tokenExist) {
         $this->updateUserBearerToken($tokenExist["id"], $token);
      } else {

         $this->insertUserBearerToken($user["id"], $token, $user["user_type"], $queenId);

      }

      return $token;
   }




   /**
    * Invalidate a user's bearer token
    */
   public function invalidateUserBearerToken()
   {
      $token =
         getBearerToken();

      $data = [
         "is_valid" => 0
      ];

      $id = $this->repo->getBearerTokenId($token)["id"];

      return DBupdate("users_tokens", $data, $id);
   }



   /**
    * Send account verification email to the user with a verification token link
    * @param mixed $userEmail
    * @param mixed $table
    * @return void
    */
   function sendVerifyToken($userEmail, $table)
   {
      $userExists = $this->repo->findByEmail($userEmail, $table);


      if ($userExists) {
         $this->repo->deactivateAllVerifyTokens($userEmail, $userExists["user_type"], $table);


         $data = $this->generateVerifyToken($userEmail, $userExists["user_type"]);

         $res = $this->repo->insertVerifyToken($data, "log_users_verify_tokens");


         if ($res) {
            $verifyMail = new VerifyAccountMail();

            $verifyMail->send($data);
            // $this->sendMail($data);
         }
      }

   }


   /**
    * Generate a verification token for a user.
    * This token can be used for account verification or deletion confirmation.
    * @return array{email: string, expiration: string, token: string, user_type: mixed}
    */
   public function generateVerifyToken(string $email, $userType)
   {

      $token = bin2hex(random_bytes(32));
      $date = new DateTime("+30minutes");

      $expiration = $date->format('Y-m-d H:i:s');

      return ["token" => $token, "expiration" => $expiration, "email" => $email, "user_type" => $userType];
   }




   // public function findToken($token, $table)
//    {


   //       $now = getNowDatetime();


   // $this->repo->findToken($token)
//    }

}
?>