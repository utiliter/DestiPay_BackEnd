<?php
namespace App\Modules\Users;


class UserService
{


   public function __construct(public UserRepo $repo)
   {
   }




   public function insertUserToken($userId, $token, $userType)
   {

      $data = [
         "user_id" => $userId,
         "token" => $token,
         "user_type" => $userType

      ];

      return dbCreate("tokens_blacklist", $data);

   }


   public function attempToLogin($data)
   {

      $tableName = getUserTableName($data["user_type"]);

      $userExists = $this->repo->findByEmail($data['email'], $tableName);

      if ($userExists && $userExists["password"] === bcrypt($data['password'])) {

         $user = [
            "id" => $userExists["id"],
            "first_name" => $userExists["first_name"],
            "last_name" => $userExists["last_name"],
            "email" => $userExists["email"],
         ];

         $token = generateToken($user);


         $this->insertUserToken($userExists["id"], $token, $data["user_type"]);

         return ["user" => $user, "token" => $token];



      }

      return null;


   }


   public function invalidateUserToken()
   {

      $token =
         getBearerToken();

      $data = [
         "is_valid" => 0
      ];

      $id = $this->repo->getBearerTokenId($token)["id"];

      return DBupdate("tokens_blacklist", $data, $id);
   }


}
?>