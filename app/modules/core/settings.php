<?php




$settings = new settings();
$action = $data['action'] ?? '';
// $lang->data = $data;
checkMethod(["POST", "GET", "PUT", "DELETE"]);


switch ($action) {

   case 'get_languages':
      checkMethod(["GET"]);

      $token =
         getBearerToken();

      if (!$token || !verifyToken($token) || !$users->isTokenValid($token)) {
         $response->status = 401;
         returnJson();
      }

      $settings->get_languages();
      break;

   // case 'get_countries':
   //    checkMethod(["GET"]);

   //    $token =
   //       getBearerToken();

   //    if (!$token || !verifyToken($token) || !$users->isTokenValid($token)) {
   //       $response->status = 401;
   //       returnJson();
   //    }

   //    $settings->get_languages();
   //    break;

   default:
      $response->status = 400;
      returnJson();
      break;
}




class settings
{


   public $db;
   public $response;


   function __construct()
   {
      global $DB, $response;
      $this->db = $DB;


      $this->response = $response;

   }

   function get_languages()
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