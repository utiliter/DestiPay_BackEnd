<?php
namespace App\Core\Enums;



enum RolePermissions: string
{
   case VIEW = "roles_view";
   case CREATE = "roles_create";
   case EDIT = "roles_edit";
   case DELETE = "roles_delete";


}

?>