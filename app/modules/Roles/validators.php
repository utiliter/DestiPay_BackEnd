<?php
namespace App\Modules\Users;

use Valitron\Validator;



function validateCreateCustomRoles($authUser)
{

   $inputData = decodeJson();


   $v = new Validator($inputData);
   $v->rule("required", ["name", "permissions", "queen_id", "user_type"]);

   $v->rule("array", "permissions");


   if (!$v->validate()) {
      return ["errors" => $v->errors()];
   }

   if (customCheckIfExist("SELECT id FROM users_roles WHERE role_name = '{$inputData['name']}'")) {
      return ["errors" => ["name" => "Role name already exists"]];
   }

   if (!checkIfExist($inputData["user_type"], "users_types")) {
      return ["errors" => ["user_type" => "User type does not exist"]];
   }

   if (!checkIfExist($inputData["queen_id"], "object_queen")) {
      return ["errors" => ["queen_id" => "Queen does not exist"]];
   }

   $superadminId = 1;
   if ($authUser["role_id"] !== $superadminId) {
      if ($authUser["queen_id"] !== (int) $inputData["queen_id"]) {
         return ["errors" => ["queen_id" => "Invalid queen id"]];
      }
   }


   return [
      "name" => $inputData["name"],
      "permissions" => validatePermissions($inputData["permissions"]),
      "queen_id" => $inputData["queen_id"],
      "user_type" => $inputData["user_type"]
   ];

}


function validateGetRole()
{
   $data = [
      "queen_id" => isset($_GET["queen_id"]) ? $_GET["queen_id"] : "",
      "role_id" => isset($_GET["role_id"]) ? $_GET["role_id"] : "",
   ];


   $v = new Validator($data);
   $v->rule("required", ["queen_id", "role_id"]);


   if (!$v->validate()) {
      return ["errors" => $v->errors()];


   }

   if (!checkIfExist($data["role_id"], "users_roles")) {
      return ["errors" => ["queen_id" => "Role does not exist"]];
   }

   if (!checkIfExist($data["queen_id"], "object_queen")) {
      return ["errors" => ["queen_id" => "Queen does not exist"]];
   }


   return [
      "role_id" => $data["role_id"],
      "queen_id" => $data["queen_id"]
   ];

}

function validateEditRolePermissions()
{
   $inputData = decodeJson();
   $v = new Validator($inputData);
   $v->rule("required", ["role_id", "permissions"]);
   $v->rule("array", "permissions");



   if (!$v->validate()) {
      return ["errors" => $v->errors()];
   }


   return [
      "role_id" => $inputData["role_id"],
      "permissions" => $inputData["permissions"]
   ];

}

function validateDeleteRole()
{
   $inputData = decodeJson();

   $v = new Validator($inputData);
   $v->rule("required", ["role_id", "queen_id"]);

   if (!$v->validate()) {
      return ["errors" => $v->errors()];
   }


   if (!customCheckIfExist("SELECT id FROM users_roles WHERE id = '{$inputData['role_id']}' AND queen_id = {$inputData['queen_id']} AND is_active = 1")) {
      return ["errors" => ["role_id" => "Role does not exists"]];
   }


   if (checkIfRoleIsAssignedToAnyUser($inputData["role_id"])) {
      return ["errors" => ["role_id" => "Cannot delete this role because it is assigned to one or more users."]];
   }

   return [
      "queen_id" => $inputData["queen_id"],
      "role_id" => $inputData["role_id"],

   ];
}