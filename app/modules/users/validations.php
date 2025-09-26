<?php
use Valitron\Validator;



function validate_login()
{
   $inputData = decodeJson();

   $v = new Validator($inputData);

   $v->rule("required", ["email", "password"]);

   $v->rule("lengthMin", "password", 6);
   $v->rule("email", "email");

   if (!$v->validate()) {
      return ["errors" => $v->errors()];
   }

   $data = $v->data();

   $VISITOR_TYPE_ID = 3;
   $data["user_type"] = empty($inputData["user_type"]) ? $VISITOR_TYPE_ID : $inputData["user_type"];
   return $data;



}

function validate_load_user()
{
   $inputData = $_GET;

   $v = new Validator($inputData);

   $VISITOR_TYPE_ID = 3;

   $userType = empty($inputData["user_type"]) ? $VISITOR_TYPE_ID : $inputData["user_type"];


   $v->rule("required", ["user_id"]);


   if (!$v->validate()) {
      return ["errors" => $v->errors()];
   }


   return [
      "user_id" => $inputData["user_id"],
      "user_type" => $userType
   ];


}


function validate_change_password()
{
   $inputData = decodeJson();
   $v = new Validator($inputData);

   $VISITOR_TYPE_ID = 3;

   $userType = empty($inputData["user_type"]) ? $VISITOR_TYPE_ID : $inputData["user_type"];

   $v->rule("required", ["user_id", "password", "confirm_password"]);
   $v->rule("lengthMin", "password", 6);
   $v->rule("equals", "confirm_password", "password")->label("Confirm password");

   if (!$v->validate()) {
      return ["errors" => $v->errors()];
   }

   $data = $v->data();

   $data["user_type"] = $userType;

   return $data;
}



function validate_register()
{
   $inputData = decodeJson();
   // ddd($inputData)
   $v = new Validator($inputData);

   $v->rule("required", ["email", "password", "confirm_password", "first_name", "last_name", "address", "postal_code", "region", "state", "country_id", "phone"]);

   $v->rule("email", "email");

   $v->rule("equals", "confirm_password", "password")->label("Confirm password");
   $v->rule("lengthMin", "password", 6);

   $userTypeIdVisitor = 3;



   if (!$v->validate()) {
      return ["errors" => $v->errors()];
   }


   $data =
      array_diff_key($v->data(), ["confirm_password" => 0]);

   $data["user_type"] = $userTypeIdVisitor;

   // TODO izmijeniti
   $queenId = 1;

   $data["queen_id"] = $queenId;
   return $data;

}


function validate_edit()
{

   $inputData = decodeJson();
   $v = new Validator($inputData);


   $v->rule("required", ["user_id", "email", "role_id", "first_name", "last_name", "address", "postal_code", "region", "state", "country_id", "phone", "user_type"]);



   if (!$v->validate()) {
      return ["errors" => $v->errors()];
   }


   return [
      "user_id" => $inputData["user_id"] ?? "",
      "email" => $inputData["email"] ?? "",
      "role_id" => $inputData["role_id"] ?? "",
      "first_name" => $inputData["first_name"] ?? "",
      "last_name" => $inputData["last_name"] ?? "",
      "address" => $inputData["address"] ?? "",
      "postal_code" => $inputData["postal_code"] ?? "",
      "region" => $inputData["region"] ?? "",
      "state" => $inputData["state"] ?? "",
      "country_id" => $inputData["country_id"] ?? "",
      "phone" => $inputData["phone"] ?? "",
      "user_type" => $inputData["user_type"] ?? "",
   ];


}


function validate_delete()
{

   $inputData = decodeJson();
   $v = new Validator($inputData);


   $v->rule("required", ["user_id", "user_type"]);

   if (!$v->validate()) {
      return ["errors" => $v->errors()];
   }


   return [
      "user_id" => $inputData["user_id"],
      "user_type" => $inputData["user_type"],
   ];

}



function validate_create_account()
{
   $inputData = decodeJson();

   $v = new Validator($inputData);

   $v->rule("required", ["email", "password", "confirm_password", "role_id", "first_name", "last_name", "address", "postal_code", "region", "state", "country_id", "phone"]);

   $v->rule("required", ["email", "role_id", "first_name"]);
   $v->rule("email", "email");

   $v->rule("equals", "confirm_password", "password")->label("Confirm password");
   $v->rule("lengthMin", "password", 6);

   $VISITOR_TYPE_ID = 3;

   $userType = empty($inputData["user_type"]) ? $VISITOR_TYPE_ID : $inputData["user_type"];


   if ($userType == 1 || $userType == 3) {
      $v->rule("required", ["queen_id"]);

   }
   if ($userType == 2) {
      $v->rule("required", ["partner_id"]);

   }



   if (!$v->validate()) {
      return ["errors" => $v->errors()];
   }

   $data =
      array_diff_key($v->data(), ["confirm_password" => 0]);

   $data["user_type"] = $userType;

   return $data;



}


function validate_send_delete_token()
{


   $v = new Validator($_GET);


   $v->rule("required", ["email", "user_type"]);

   if (!$v->validate()) {
      return ["errors" => $v->errors()];
   }


   return [
      "email" => $_GET["email"],
      "user_type" => $_GET["user_type"],
   ];

}


?>