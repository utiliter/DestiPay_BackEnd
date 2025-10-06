<?php

use App\Modules\Users\Controllers\LoginController;
use App\Modules\Users\Controllers\RegisterController;
use App\Modules\Users\Controllers\UserController;



// USER
$router->get(UserController::class, "list", false);


$router->get(UserController::class, "get_user_roles", true);
$router->get(UserController::class, "get_user_types", true);


$router->post(UserController::class, "change_password", true);


$router->post(UserController::class, "edit_account", true);

$router->post(UserController::class, "create_account", false);

$router->get(UserController::class, "delete_account", false);
$router->get(UserController::class, "send_verify_delete_token", true);

// REGISTER
$router->post(RegisterController::class, "register", false);

$router->get(RegisterController::class, "verify_account", false);


// LOGIN
$router->post(LoginController::class, "login", false);

$router->post(LoginController::class, "logout", true);

?>