<?php
namespace App\Modules\Users;

use Valitron\Validator;



function validate_login()
{

   $inputData = decodeJson();

   $v = new Validator($inputData);


   $v->rule("required", ["email", "password", "device_id", "mobile_type"]);


   $v->rule("email", "email");
   $v->rule("lengthMin", "password", 6);

   $v->rule('in', 'mobile_type', [0, 1, 2]);


   if (!empty($inputData["mobile_type"] && $inputData["mobile_type"] !== 0)) {
      $v->rule("required", ["fcm_token"]);
   }

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


function validateChangePassword()
{
   global $DB;

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

   // $userTableName = getUserTableName((int) $data["user_type"]);



   if (!checkIfExist($data["user_type"], "users_types")) {

      return ["errors" => ["user_type" => "User type does not exist"]];
   }


   $data["user_type"] = $userType;

   return $data;
}



function validateRegister()
{
   $inputData = decodeJson();
   $v = new Validator($inputData);

   $v->rule("required", ["email", "password", "confirm_password", "first_name", "last_name", "address", "postal_code", "region", "state", "country_id", "phone"]);

   $v->rule("email", "email");

   $v->rule("equals", "confirm_password", "password")->label("Confirm password");
   $v->rule("lengthMin", "password", 6);

   $VISITOR_TYPE_ID = 3;
   $data["user_type"] = empty($inputData["user_type"]) ? $VISITOR_TYPE_ID : $inputData["user_type"];


   if ((int) $data["user_type"] === 2) {
      $v->rule("required", fields: "partner_id");

   }


   if (!$v->validate()) {
      return ["errors" => $v->errors()];
   }


   if (!checkIfExist($data["user_type"], "users_types")) {

      return ["errors" => ["user_type" => "User type does not exist"]];
   }

   $data =
      array_diff_key($v->data(), ["confirm_password" => 0]);


   // TODO izmijeniti
   $queenId = 1;

   if ($data["user_type"] !== 2) {
      $data["queen_id"] = $queenId;

      unset($data["partner_id"]);
   } else {

      if (!checkIfExist($data["partner_id"], "object_partner")) {
         return ["errors" => ["partner_id" => "Partner does not exist"]];
      }

   }

   return $data;
}


function validateEdit()
{

   $inputData = decodeJson();
   $v = new Validator($inputData);


   $v->rule("required", ["user_id", "role_id", "first_name", "last_name", "address", "postal_code", "region", "state", "country_id", "phone", "user_type"]);



   if (!$v->validate()) {
      return ["errors" => $v->errors()];
   }


   if (!checkIfExist($inputData["role_id"], "users_roles")) {
      return ["errors" => ["role_id" => "Role id does not exist"]];

   }


   if (isset($inputData["queen_id"])) {

      if (!checkIfExist($inputData["queen_id"], "object_queen")) {
         return ["errors" => ["queen_id" => "Queen does not exist"]];
      }
   }



   return [
      "user_id" => $inputData["user_id"] ?? "",
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





function validateCreateAccount()
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


   if (!checkIfExist($inputData["role_id"], "users_roles")) {
      return ["errors" => ["role_id" => "Role id does not exist"]];

   }

   if (!checkIfExist($inputData["user_type"], "users_types")) {
      return ["errors" => ["user_type" => "User type does not exist"]];
   }


   if (isset($inputData["queen_id"])) {
      $queen = checkIfExist($inputData["queen_id"], "object_queen");

      if (!$queen) {
         return ["errors" => ["queen_id" => "Queen does not exist"]];
      }


   }

   $data =
      array_diff_key($v->data(), ["confirm_password" => 0]);

   $data["user_type"] = $userType;

   if ($userType == 2) {
      unset($data["queen_id"]);
   } else {
      unset($data["partner_id"]);

   }


   return $data;
}




function validateDelete()
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



function validateSendDeleteTokenMail()
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