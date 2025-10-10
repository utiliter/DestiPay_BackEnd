<?php
namespace App\Core\Enums;



enum UserPermissions: string
{
   case VIEW = "user_view";
   case CREATE = "user_create";
   case EDIT = "user_edit";
   case DELETE = "user_delete";


}

?>