<?php

namespace App\Core;

use Exception;

class Router
{
   public $response;
   public $logResponse;
   public $container;
   public function __construct()
   {
      // $logger = new Logger();

      global $response, $container, $logResponse;
      $this->response = $response;
      $this->container = $container;
      $this->logResponse = $logResponse;
   }

   private $routes = [];

   public function add($controller, $action, $logOperationName, $tokenReqired, $method)
   {
      $this->routes[] = [
         "controller" => $controller,
         "action" => $action,
         "logOperationName" => $logOperationName,
         "method" => $method,
         "tokenRequired" => $tokenReqired
      ];
   }


   public function get($controller, $action, $logOperationName, $tokenReqired = false)
   {
      return $this->add($controller, $action, $logOperationName, $tokenReqired, "GET");
   }

   public function post($controller, $action, $logOperationName, $tokenReqired = false)
   {
      return $this->add($controller, $action, $logOperationName, $tokenReqired, "POST");
   }

   public function delete($controller, $action, $logOperationName, $tokenReqired = false)
   {
      return $this->add($controller, $action, $logOperationName, $tokenReqired, "DELETE");
   }
   public function put($controller, $action, $logOperationName, $tokenReqired = false)
   {
      return $this->add($controller, $action, $logOperationName, $tokenReqired, "PUT");
   }



   public function reslove($module, $action, $method)
   {

      // $log_operation_data = [
      //    "user_login" => 1 
      // ]

      foreach ($this->routes as $route) {

         if ($route["method"] === $method && $route["action"] === $action) {

            if ($route["tokenRequired"]) {

               $token = getBearerToken();

               if (!$token || !verifyToken($token) || !isBearerTokenValid($token)) {
                  $operationId = $this->logResponse->log_types[$route["logOperationName"]];
                  $requestLogData = [
                     "operation_id" => $operationId,
                     "user_type" => $this->logResponse->user_type ?? 0,
                     "operation_data" => file_get_contents("php://input") ?? "",
                     "ip_address" => $_SERVER['REMOTE_ADDR'],
                     "device_data" => "",
                     "user_id" => $this->logResponse->user_id ?? 0,
                     "log_operation_type" => 0
                  ];
                  $logRes = logOperation($requestLogData);
                  $this->logResponse->logId = $logRes;
                  $this->logResponse->operationId = $operationId;

                  $this->response->status = 401;
                  returnJson();
               }

               $tokenData = getUserDataFromBearerToken($token);
               $this->logResponse->user_id = $tokenData["id"];
               $this->logResponse->user_type = $tokenData["user_type"];
            }



            $operationId = $this->logResponse->log_types[$route["logOperationName"]];
            $requestLogData = [
               "operation_id" => $operationId,
               "user_type" => $this->logResponse->user_type ?? 0,
               "operation_data" => file_get_contents("php://input") ?? "",
               "ip_address" => $_SERVER['REMOTE_ADDR'],
               "device_data" => "",
               "user_id" => $this->logResponse->user_id ?? 0,
               "log_operation_type" => 0
            ];

            $logRes = logOperation($requestLogData);
            $this->logResponse->logId = $logRes;
            $this->logResponse->operationId = $operationId;

            try {

               if ($this->container->has($route["controller"])) {
                  $controllerClass = $this->container->get($route["controller"]);
               } else {
                  throw new \Exception("Class" . $route["controller"] . " does not exists");
               }

               if (!method_exists($controllerClass, $action)) {
                  throw new \Exception("Method $action does not exist in controller $controllerClass");
               }

               // return call_user_func([$controllerClass, $action]);
               return $controllerClass->$action();

            }
            //  catch (\mysqli_sql_exception $e) {

            //    ddd($e);
            // } 
            catch (Exception $e) {

               $this->response->data = $e->getMessage();
               return $this->response->status = 500;
            }

         } else {
            $this->response->status = 400;
         }
      }

   }
}


?>