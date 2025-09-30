<?php

namespace App\Core;

use Exception;

class Router
{
   public $response;
   public $container;
   public function __construct()
   {

      global $response, $container;
      $this->response = $response;
      $this->container = $container;
   }

   private $routes = [];

   public function add($controller, $action, $tokenReqired, $method)
   {
      $this->routes[] = [
         "controller" => $controller,
         "action" => $action,
         "method" => $method,
         "tokenRequired" => $tokenReqired
      ];
   }


   public function get($controller, $action, $tokenReqired = false)
   {
      return $this->add($controller, $action, $tokenReqired, "GET");
   }

   public function post($controller, $action, $tokenReqired = false)
   {
      return $this->add($controller, $action, $tokenReqired, "POST");
   }

   public function delete($controller, $action, $tokenReqired = false)
   {
      return $this->add($controller, $action, $tokenReqired, "DELETE");
   }
   public function put($controller, $action, $tokenReqired = false)
   {
      return $this->add($controller, $action, $tokenReqired, "PUT");
   }



   public function reslove($module, $action, $method)
   {

      foreach ($this->routes as $route) {

         if ($route["method"] === $method && $route["action"] === $action) {

            if ($route["tokenRequired"]) {

               $token = getBearerToken();

               if (!$token || !verifyToken($token) || !isBearerTokenValid($token)) {
                  $this->response->status = 401;
                  returnJson();
               }
            }
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

            } catch (Exception $e) {
               ddd($e->getMessage());
               // $this->response->data = $e->getMessage();
               return $this->response->status = 500;
            }

         } else {
            $this->response->status = 400;
         }
      }

   }
}


?>