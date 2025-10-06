<?php



namespace App\Modules\Core\Controllers;



class SettingsController
{


   public $db;
   public $response;


   function __construct()
   {
      global $DB, $response;
      $this->db = $DB;


      $this->response = $response;

   }

   public function get_languages()
   {

      $data = $this->db->query("SELECT * FROM settings_languages  WHERE is_active = 1 ORDER BY sort ASC")->fetch_all(MYSQLI_ASSOC);

      $this->response->status = 200;
      return $this->response->data = $data;

   }

   // function get_countries()
   // {

   //    $data = $this->db->query("SELECT * FROM settings_languages  WHERE is_active = 1")->fetch_all(MYSQLI_ASSOC);


   //    $this->response->status = 200;
   //    return $this->response->data = $data;

   // }


}


?>