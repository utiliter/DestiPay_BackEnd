<?php

namespace App\Modules\Users;

class UserRepo
{

   private $db;

   public function __construct()
   {

      global $DB;
      $this->db = $DB;
   }


   public function checkIfExist($id, $table)
   {
      $id = (int) $id;
      $query = "SELECT id FROM $table WHERE id = $id";
      return $this->db->query($query)->num_rows;
   }




   public function findByEmail($email, $table)
   {

      // ddd($this->db->query("select * from object_partner")->fetch_assoc());
      if (is_array($table)) {
         $q = [];

         foreach ($table as $t) {

            if ($t === "users_partners") {
               $q[] = "SELECT up.id,up.first_name,up.last_name,up.email,up.user_type,up.is_active,up.role_id , up.password , op.queen_id FROM $t up
         INNER JOIN object_partner op ON op.id = up.partner_id
          WHERE email = '$email'";

            } elseif ($t === "users_system") {

               $q[] = "SELECT id,first_name,last_name,email,user_type,is_active, role_id,password , NULL AS queen_id FROM  $t WHERE  email = '$email' ";

            } else {

               $q[] = "SELECT id,first_name,last_name,email,user_type,is_active, role_id,password ,queen_id FROM  $t WHERE  email = '$email' ";
            }
         }

         $query = implode(" UNION ", $q);
         return $this->db->query($query)->fetch_assoc();

      }

      if ($table === "users_partners") {
         return $this->db->query("SELECT up.id,up.first_name,up.last_name,up.email,up.user_type,up.is_active, up.password , op.queen_id ,up.role_id FROM $table up
         INNER JOIN object_partner op ON op.id = up.partner_id
          WHERE email = '$email'")->fetch_assoc();
      }

      return $this->db->query("SELECT id,first_name,last_name,email,user_type,is_active,queen_id, password,role_id FROM $table  WHERE email = '$email'")->fetch_assoc();

   }

   public function emailExists($email, $table)
   {
      return $this->db->query("SELECT id FROM $table  WHERE email = '$email'")->num_rows;
   }



   public function findById($id, $table)
   {

      return $this->db->query("SELECT id,first_name,last_name,email FROM $table  WHERE id = $id AND is_active = 1 AND deleted_at IS NULL")->fetch_assoc();


   }


   function checkIfUserExists($email, $table)
   {



      return $this->db->query("SELECT id FROM $table WHERE email = '$email' AND is_active = 1 AND deleted_at IS NULL")->num_rows;
   }


   function getBearerTokenId($token)
   {

      return $this->db->query("SELECT id FROM users_tokens WHERE bearer_token = '$token'")->fetch_assoc();

   }



   public function findToken(string $token, string $table)
   {

      $now = getNowDatetime();

      return $this->db->query("SELECT * FROM $table WHERE token = '$token' AND is_active = 1 AND expiration > '$now'")->fetch_assoc();
   }


   function deactivateAllVerifyTokens(string $email, $userType, $table)
   {
      return $this->db->query("UPDATE $table SET is_active = 0 WHERE email = '$email' AND is_active = 1 AND user_type = $userType");
   }


   public function findUserBearerTokenId($userId, $userType)
   {

      return $this->db->query("SELECT id FROM users_tokens WHERE user_id = $userId AND user_type = $userType")->fetch_assoc();
   }


   /**
    * Insert new verify token into database
    * @param mixed $data -token data
    * @param mixed $table - log_users_verify_tokens |  log_users_verify_delete_tokens
    */
   public function insertVerifyToken($data, $table)
   {
      return dbCreate($table, $data);

   }


   public function getUserRolePermissions($roleId)
   {
      $query = "SELECT rp.permission_id , p.name FROM users_roles_permissions rp INNER JOIN users_permissions p ON p.id = rp.permission_id WHERE rp.role_id = $roleId";


      return $this->db->query($query)->fetch_all(MYSQLI_ASSOC);


   }

}


?>