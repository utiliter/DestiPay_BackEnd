<?php
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

switch ($data["module"] ?? "DEFAULT") {
    case 'users':
        require_once 'app/modules/users/users.php';
        break;

    default:
        $response->status = 400;
        break;
}

returnJson();