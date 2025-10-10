<?php
namespace App\Modules\Roles;
use App\Modules\Core\Repositories\PermissionRepository;
use App\Modules\Roles\RoleRepo;
use Error;

class RoleService
{



   public function __construct(public RoleRepo $roleRepo)
   {
   }

   public function update(array $data)
   {

      if (!$this->roleRepo->checkIfExist($data["role_id"], "users_roles")) {
         throw new Error("Role id does not exist");
      }
      $this->permissionRepo->deletePermissionForRole($data["role_id"]);



      $permissionsIds = array_keys($data["permissions"]);


      return $this->permissionRepo->insertPermissions(["roleId" => $data["role_id"], "permissionIds" => $permissionsIds]);


   }


}




?>