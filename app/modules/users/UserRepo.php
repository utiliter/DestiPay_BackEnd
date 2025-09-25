<?php

class UserRepo
{

   private $db;

   public function __construct()
   {

      global $DB;
      $this->db = $DB;
   }



   public function check_if_exist($id, $table)
   {
      return $this->db->query("SELECT id FROM $table WHERE id = $id")->num_rows;

   }


   public function findByEmail($email, $table)
   {

      return $this->db->query("SELECT id,first_name,last_name,email, password FROM $table  WHERE email = '$email'")->fetch_assoc();


   }


   public function findById($id, $table)
   {

      return $this->db->query("SELECT id,first_name,last_name,email FROM $table  WHERE id = $id")->fetch_assoc();


   }

}


?>