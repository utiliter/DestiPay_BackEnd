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



   public function check_if_user_exist($id, $table)
   {
      $id = (int) $id;
      $query = "SELECT id FROM $table WHERE id = $id AND is_active = 1 AND deleted_at IS NULL";
      return $this->db->query($query)->num_rows;
   }

   public function check_if_exist($id, $table)
   {
      $id = (int) $id;
      $query = "SELECT id FROM $table WHERE id = $id";
      return $this->db->query($query)->num_rows;
   }

   public function checkIfExist($id, $table)
   {
      $id = (int) $id;
      $query = "SELECT id FROM $table WHERE id = $id";
      return $this->db->query($query)->num_rows;
   }




   public function findByEmail($email, $table)
   {

      return $this->db->query("SELECT id,first_name,last_name,email,user_type, password FROM $table  WHERE email = '$email'")->fetch_assoc();


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

      return $this->db->query("SELECT id FROM tokens_blacklist WHERE token = '$token'")->fetch_assoc();

   }
}


?>