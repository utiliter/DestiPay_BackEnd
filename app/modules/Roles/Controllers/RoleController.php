<?php
namespace App\Modules\Roles\Controllers;
use App\Core\BaseController;
use App\Core\Enums\RolePermissions;
use App\Modules\Roles\RoleRepo;

use function App\Modules\Users\validateCreateCustomRoles;
use function App\Modules\Users\validateDeleteRole;
use function App\Modules\Users\validateEditRolePermissions;
use function App\Modules\Users\validateGetRole;

class RoleController extends BaseController
{
   public $token;
   public $authUser;
   public function __construct(public RoleRepo $roleRepo)
   {
      parent::__construct();
      $this->token = getBearerToken();

      $this->authUser = getUserDataFromBearerToken($this->token);
   }


   /**
    * Retrieve all roles assigned to a specific queen.
    */
   public function get_queen_roles_by_id()
   {

      $queenId = $_GET["queen_id"] ?? $this->authUser["queen_id"] ?? null;



      if (!$queenId || !checkIfExist($queenId, "object_queen")) {
         $this->response->status = 422;
         return $this->response->data = ["errors" => ["queen_id" => "User role does not exist"]];
      }

      $isSuperAdmin = $this->authUser["user_type"] === 5;
      checkPermission(RolePermissions::VIEW, $this->authUser["role_id"] || $isSuperAdmin);


      $q = "SELECT id, role_name 
      FROM users_roles 
      WHERE is_active = 1
      AND (  `group` = 'queen' 
      OR (queen_id = $queenId)
      )";

      $res = $this->db->query($q)->fetch_all(MYSQLI_ASSOC);

      $this->response->status = 200;
      return $this->response->data = $res;

   }

   /**
    * Get all permissions assigned to a role
    */
   public function get_role_permissions()
   {

      $roleId = $_GET["role_id"];


      $queenId = $this->authUser["queen_id"] ?? null;

      $isSuperAdmin = $this->authUser["user_type"] === 5;

      checkPermission(RolePermissions::VIEW, $this->authUser["role_id"] || $isSuperAdmin);

      $role = $this->roleRepo->getRole($roleId);

      if (!$role) {
         $this->response->status = 422;
         return $this->response->data = ["role_id" => "User role does not exist"];
      }

      if (!$isSuperAdmin) {
         authorize($this->authUser["queen_id"] === (int) $role["queen_id"] || ($role["queen_id"] == null && (int) $role["user_type"] === $this->authUser["user_type"]));

      }

      if (!$isSuperAdmin && (!$queenId || !checkIfExist($queenId, "object_queen"))) {
         $this->response->status = 422;
         return $this->response->data = ["queen_id" => "User role does not exist"];
      }


      $data = $this->roleRepo->getPermissionsByRoleId($roleId);

      $this->response->status = 200;
      return $this->response->data = $data;
   }


   /**
    * Handle the request to create a new role for a queen
    */
   public function create_queen_custom_role()
   {

      checkPermission(RolePermissions::CREATE, $this->authUser["role_id"]);

      $validatedData = validateCreateCustomRoles($this->authUser);


      if (isset($validatedData["errors"])) {
         $this->response->status = 422;
         return $this->response->data = $validatedData;
      }

      $roleId = $this->roleRepo->createRole($validatedData);

      if ($roleId) {
         $this->roleRepo->insertPermissions(["roleId" => $roleId, "permissionIds" => $validatedData["permissions"]]);


         $this->response->status = 201;
         return $this->response->data = [
            "message" => "Role has been successfully created"
         ];

      }

      $this->response->status = 500;
      return $this->response->data = [];
   }

   /**
    * Handle the request  to modify the permissions assigned to a specific role
    */
   public function edit_role_permissions()
   {
      $validatedData = validateEditRolePermissions();


      if (isset($validatedData["errors"])) {
         $this->response->status = 422;
         return $this->response->data = $validatedData;
      }

      $role = $this->roleRepo->getRole($validatedData["role_id"]);

      if (!$role) {
         $this->response->status = 422;
         return $this->response->data = ["errors" => ["role_id" => "Role does not exist"]];
      }

      $isSuperAdmin = $this->authUser["user_type"] === 5;


      $canEdit = $isSuperAdmin || (
         checkPermission(RolePermissions::EDIT, $this->authUser["role_id"]) &&
         (int) $role["queen_id"] === (int) $this->authUser["queen_id"]
      );

      authorize($canEdit);

      $this->roleRepo->deletePermissionForRole($validatedData["role_id"]);


      $res = $this->roleRepo->insertPermissions(["roleId" => $validatedData["role_id"], "permissionIds" => $validatedData["permissions"]]);

      if ($res) {

         $this->response->status = 201;
         return $this->response->data = [
            "message" => "Role permission has been successfully updated"
         ];

      }


      $this->response->status = 500;
      return $this->response->data = [];

   }

   /**
    * Handle an incoming request for deleting a role
    */
   public function delete_role()
   {

      $validatedData = validateDeleteRole();

      if (isset($validatedData["errors"])) {
         $this->response->status = 422;
         return $this->response->data = $validatedData;
      }

      $isSuperAdmin = $this->authUser["user_type"] === 5;


      $canDelete = $isSuperAdmin || (
         checkPermission(RolePermissions::DELETE, $this->authUser["role_id"]) &&
         (int) $validatedData["queen_id"] === (int) $this->authUser["queen_id"]
      );

      authorize(
         $canDelete
      );

      $res = $this->roleRepo->deleteRole($validatedData["role_id"], $validatedData["queen_id"]);

      if ($res) {

         $this->response->status = 200;
         return $this->response->data = [
            "message" => "Role has been successfully deleted"
         ];

      }

      $this->response->status = 500;
      return $this->response->data = [];
   }


   /**
    * Get a list of all available permissions
    */
   public function get_all_permissions()
   {
      $isSuperAdmin = $this->authUser["user_type"] === 5;

      authorize(checkPermission(RolePermissions::VIEW, $this->authUser["role_id"] || $isSuperAdmin));


      $res = $this->roleRepo->getAllPermissionsByGroup();

      if ($res) {
         $this->response->status = 200;
         return $this->response->data = $res;
      }

      $this->response->status = 500;
      return $this->response->data = [];
   }



   /**
    *  * Get role information
    * params = queen_id i role_id
    */
   public function get_role()
   {


      $validatedData = validateGetRole();

      if (isset($validatedData["errors"])) {
         $this->response->status = 422;
         return $this->response->data = $validatedData;
      }

      $isSuperAdmin = $this->authUser["user_type"] === 5;


      if (!$isSuperAdmin) {


         $canView = $isSuperAdmin || (
            checkPermission(RolePermissions::VIEW, $this->authUser["role_id"]) &&
            (int) $validatedData["queen_id"] === (int) $this->authUser["queen_id"]
         );

         authorize(
            $canView
         );
      }

      $role = $this->roleRepo->getRole($validatedData["role_id"]);

      if (!$role) {
         $this->response->status = 404;
         return $this->response->data = ["role_id" => "User role does not exist"];
      }

      $this->response->status = 200;
      return $this->response->data = $role;




   }




}
?>