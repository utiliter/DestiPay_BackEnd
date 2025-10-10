<?php

use App\Core\App;
use App\Core\FileManager;

session_start();

date_default_timezone_set('Europe/Zagreb');

$response = new stdClass();
$response->status = 200;
$response->data = [];

$logResponse = new stdClass();
$logResponse->user_id = null;
$logResponse->user_type = null;
$logResponse->log_types = [];

$fileManger = new FileManager();

if ($fileManger->exists(OPERATION_TYPES_CACHE)) {

    $logResponse->log_types = $fileManger->getPhpContent(OPERATION_TYPES_CACHE);

} else {
    $data = $DB->query(query: "SELECT id,log_operation_key FROM log_operations_types")->fetch_all(MYSQLI_ASSOC);
    $mapped = [];

    foreach ($data as $item) {

        $mapped[$item["log_operation_key"]] = $item["id"];

    }


    $logResponse->log_types = $data;
    $fileManger->putPhpConent(OPERATION_TYPES_CACHE, $mapped);

}

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

$app = new App();
$router = $app->getRouter();






// $router = new Router();

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
    case 'roles':
        require_once 'app/modules/Roles/routes.php';
        break;

    default:
        $response->status = 400;
        returnJson();
        break;
}


$router->reslove($data["module"], $data["action"], REQUEST_METHOD);
returnJson();