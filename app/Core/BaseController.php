<?php
namespace App\Core;

class BaseController
{


   public $db;
   public $response;

   public function __construct()
   {
      global $DB, $response;
      $this->db = $DB;

      $this->response = $response;

   }
}

?>