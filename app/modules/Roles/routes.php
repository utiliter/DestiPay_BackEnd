<?php

use App\Modules\Roles\Controllers\RoleController;

$router->get(RoleController::class, "get_queen_roles_by_id", "user_roles_get", true);

$router->get(RoleController::class, "get_role_permissions", "user_roles_get", true);
$router->post(RoleController::class, "create_queen_custom_role", "user_create_custom_role", true);

$router->put(RoleController::class, "edit_role_permissions", "user_create_custom_role", true);


$router->delete(RoleController::class, "delete_role", "user_create_custom_role", true);

$router->get(RoleController::class, "get_all_permissions", "user_roles_get", true);





//////

$router->get(RoleController::class, "get_role", "user_roles_get", true);

// $router->get(RoleController::class, "get_auth_user_role_permission", "user_roles_get", true);

?>