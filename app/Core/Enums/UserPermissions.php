<?php
namespace App\Core\Enums;



enum UserPermissions: string
{
   case VIEW = "users_view";
   case CREATE = "users_create";
   case EDIT = "users_edit";
   case DELETE = "users_delete";


}

?>