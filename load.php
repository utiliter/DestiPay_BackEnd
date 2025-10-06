<?php

use App\Core\Router;

session_start();
$response = new stdClass();
$response->status = 200;
$response->data = [];
checkRequests();
define('REQUEST_METHOD', $_SERVER['REQUEST_METHOD']);
checkMethod();
if (!empty($_REQUEST['api_data'])) {
    $data = base64_decode(json_decode($_REQUEST['api_data'] ?? [], true));
} else {
    $data = [];
    foreach ($_REQUEST as $key => $value) {
        if ($key != 'module' && $key != 'action') {
            $data[$key] = $value;
        }
    }
    $data["module"] = $_REQUEST["module"] ?? "DEFAULT";
    $data["action"] = $_REQUEST["action"] ?? "DEFAULT";




}


$router = new Router();

// $filePath = "app/Modules/" . $data["module"] . "/routes.php";


// if (file_exists($filePath)) {
//     ddd("POSTOJI");

//     require_once $path;

//     $router->reslove($data[$module], $action, );


// } else {

//     $response->status = 400;

// }


switch (strtolower($data["module"]) ?? "DEFAULT") {
    case 'users':
        require_once 'app/modules/Users/routes.php';
        break;

    case 'settings':
        require_once 'app/modules/Core/routes.php';
        break;
    default:
        $response->status = 400;
        returnJson();
        break;
}


$router->reslove($data["module"], $data["action"], REQUEST_METHOD);
returnJson();