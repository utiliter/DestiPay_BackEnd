<?php

use App\Modules\Users\Controllers\LoginController;
use App\Modules\Users\Controllers\RegisterController;
use App\Modules\Users\Controllers\UserController;



// USER
$router->get(UserController::class, "list", "user_list", false);

$router->get(UserController::class, "get_user_roles", "user_roles_get", true);
$router->get(UserController::class, "get_user_types", "user_types_get", true);


$router->post(UserController::class, "change_password", "user_change_password", true);

$router->post(UserController::class, "edit_account", "user_edit", true);

$router->post(UserController::class, "create_account", "user_create", true);

$router->get(UserController::class, "delete_account", "user_deletion", false);
$router->get(UserController::class, "send_verify_delete_token", "user_send_delete_token", true);

// REGISTER
$router->post(RegisterController::class, "register", "user_registration", false);
$router->get(RegisterController::class, "verify_account", "user_verify_account", false);

// LOGIN
$router->post(LoginController::class, "login", "user_login", false);

$router->post(LoginController::class, "logout", "user_logout", true);

?>