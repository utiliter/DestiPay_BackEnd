<?php


namespace App\Core;



class App
{
   private Router $router;
   public function __construct()
   {

      $this->router = new Router();
   }


   public function getRouter()
   {
      return $this->router;
   }





}
?>