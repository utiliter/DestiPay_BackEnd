<?php
namespace App\Modules\Roles;


class RoleRepo
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
      return (bool) $this->db->query($query)->num_rows;
   }

   public function getRole($id)
   {
      $id = (int) $id;
      $query = "SELECT * FROM users_roles WHERE id = $id AND is_active = 1";
      return $this->db->query($query)->fetch_assoc();
   }

   public function getPermissionsByRoleId($roleId)
   {
      $query = "SELECT rp.permission_id , p.name FROM users_roles_permissions rp INNER JOIN users_permissions p ON p.id = rp.permission_id
      INNER JOIN users_roles us ON rp.role_id = us.id
       WHERE rp.role_id = $roleId AND us.is_active = 1";


      return $this->db->query($query)->fetch_all(MYSQLI_ASSOC);


   }


   public function deletePermissionForRole($roleId)
   {

      return $this->db->query("DELETE FROM users_roles_permissions WHERE role_id = $roleId");


   }

   public function insertPermissions($data)
   {
      $q = "INSERT INTO users_roles_permissions (role_id, permission_id) VALUES ";

      foreach ($data["permissionIds"] as $id) {

         $q .= "(" . $data["roleId"] . "," . $id . "),";

      }
      $query = substr_replace($q, ";", -1);


      return $this->db->query($query);

   }



   public function createRole($data)
   {

      $formatedData = [

         "role_name" => $data["name"],
         "queen_id" => $data["queen_id"],
         "user_type" => $data["user_type"]

      ];

      $res = dbCreate("users_roles", $formatedData);

      if ($res) {

         return $this->db->insert_id;

      }

      return false;
   }


   public function deleteRole($roleId, $queenId)
   {

      $roleName = $this->db->query("SELECT role_name FROM users_roles WHERE id = $roleId")->fetch_assoc();
      $now = getNowDatetime();

      $formatedData = [
         "deleted_at" => $now,
         "is_active" => 0,
         "role_name" => $roleName["role_name"] . "-deleted-" . $now

      ];

      return DBupdate("users_roles", $formatedData, $roleId);

   }





   public function getAllPermissionsByGroup()
   {



      $query = "SELECT group_name, name ,id FROM users_permissions";
      $rows = $this->db->query($query)->fetch_all(MYSQLI_ASSOC);
      $permissionsByGroup = [];
      foreach ($rows as $row) {
         $group = $row['group_name'];
         $permission = $row;

         if (!isset($permissionsByGroup[$group])) {
            $permissionsByGroup[$group] = [];
         }

         $permissionsByGroup[$group][] = $permission;
      }
      return $permissionsByGroup;
   }

}


?>