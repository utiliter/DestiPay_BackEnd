<?php
namespace App\Modules\Roles\Controllers;
use App\Core\BaseController;



class RoleController extends BaseController
{
   public $token;
   public $authUser;
   public function __construct()
   {
      parent::__construct();
      $this->token = getBearerToken();

      $this->authUser = getUserDataFromBearerToken($this->token);
      // ddd($this->authUser);
   }


   /**
    * Get all roles for the authenticated user’s Queen
    */
   public function get_queen_roles()
   {
      $query = "SELECT id, role_name, 'global' AS type FROM users_roles WHERE is_active = 1 AND `group` LIKE 'queen' 
      UNION ALL
      SELECT id, role_name , 'custom'  AS type FROM users_roles_custom WHERE is_active = 1 AND queen_id = {$this->authUser["queen_id"]}";

      $res = $this->db->query($query)->fetch_all(MYSQLI_ASSOC);

      $this->response->status = 200;
      return $this->response->data = $res;

   }

   /**
    * Get role info is_custom_role 0
    */
   public function get_role()
   {

      $inputData = $_GET;
      ddd($inputData);

      $query = "SELECT id, role_name, 'global' AS type FROM users_roles WHERE is_active = 1 AND `group` LIKE 'queen' 
      UNION ALL
      SELECT id, role_name , 'custom'  AS type FROM users_roles_custom WHERE is_active = 1 AND queen_id = {$this->authUser["queen_id"]}";

      $res = $this->db->query($query)->fetch_all(MYSQLI_ASSOC);

      $this->response->status = 200;
      return $this->response->data = $res;

   }

   /**
    * Get permissions for queen roles
    */
   public function a()
   {
      $query = "SELECT id, role_name, 'global' AS type FROM users_roles WHERE is_active = 1 AND `group` LIKE 'queen' 
      UNION ALL
      SELECT id, role_name , 'custom'  AS type FROM users_roles_custom WHERE is_active = 1 AND queen_id = {$this->authUser["queen_id"]}";

      $res = $this->db->query($query)->fetch_all(MYSQLI_ASSOC);

      $this->response->status = 200;
      return $this->response->data = $res;

   }



}
?>