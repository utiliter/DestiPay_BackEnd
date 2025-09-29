<?php

use App\Modules\Users\Controllers\LoginController;
use App\Modules\Users\Controllers\RegisterController;
use App\Modules\Users\Controllers\UserController;



// USER
$router->get(UserController::class, "list", false);
$router->get(UserController::class, "get_user_roles", true);
$router->get(UserController::class, "get_user_types", false);
$router->post(UserController::class, "change_password", true);
$router->post(UserController::class, "edit_account", true);
$router->post(UserController::class, "delete_account", true);

// REGISTER
$router->post(RegisterController::class, "register", false);


// LOGIN
$router->post(LoginController::class, "login", false);

$router->post(LoginController::class, "logout", true);

?>