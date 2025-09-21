<?php
include 'functions.php';

$users = new Users();
$action = $data['action'] ?? '';
$users->data = $data;
checkMethod(["POST", "GET"]);


switch ($action) {
    case 'list':
        checkMethod(["GET"]);
        checkUser();
        $users->list_users();
        break;
    case 'login':
        checkMethod(["POST"]);
        $users->login();
        break;
    case 'logout':
        checkMethod(["GET"]);
        checkUser();
        $users->logout();
        break;
    case 'change_password':
        checkMethod(["POST"]);
        checkUser();
        $users->change_password();
        break;
    case 'register':
        checkMethod(["POST"]);
        checkUser();
        $users->register();
        break;
    case 'verify_account':
        checkMethod(["GET"]);
        $users->verify_account();
        break;
    case 'reset_password':
        checkMethod(["POST"]);
        checkUser();
        $users->reset_password();
        break;
    case 'create_account':
        checkMethod(["POST"]);
        $users->create_account();
        break;
    case 'edit_account':
        checkMethod(["POST"]);
        checkUser();
        $users->edit_account();
        break;
    case 'delete_account':
        checkMethod(["GET"]);
        $users->delete_account();
        break;
    case 'verify_delete_account':
        checkMethod(["GET"]);
        $users->verify_delete_account();
        break;

    default:
        $response->status = 400;
        returnJson();
        break;
}



class Users
{
    public $user_id;
    public $username;
    public $email;
    public $first_name;
    public $last_name;
    public $role;
    public $status;
    public $created_at;
    public $updated_at;
    public $data;
    public $methods;
    public $db;

    function __construct()
    {
        global $DB;
        $this->db = $DB;
    }

    private function load_user()
    {

    }


    function list_users()
    {

    }

    function login()
    {


    }

    function logout()
    {

    }

    function change_password()
    {

    }

    function register()
    {

    }

    function verify_account()
    {

    }

    function reset_password()
    {

    }


    function create_account()
    {
    }

    function edit_account()
    {

    }


    function delete_account()
    {

    }

    function verify_delete_account()
    {

    }

}

