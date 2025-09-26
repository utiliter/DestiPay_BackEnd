<?php


include 'functions.php';
include 'validations.php';
include 'UserRepo.php';
include 'Mailer.php';


$users = new Users();
$action = $data['action'] ?? '';
$users->data = $data;
checkMethod(["POST", "GET", "PUT", "DELETE"]);


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
        checkMethod(["POST"]);
        $token =
            getBearerToken();


        if (!$token || !verifyToken($token) || !$users->isTokenValid($token)) {
            $response->status = 401;
            returnJson();
        }

        $users->logout($token);
        break;
    case 'change_password':
        checkMethod(["POST"]);

        $token =
            getBearerToken();



        if (!$token || !verifyToken($token) || !$users->isTokenValid($token)) {
            $response->status = 401;
            returnJson();
        }
        $users->change_password();
        break;


    case 'register':
        checkMethod(["POST"]);
        $users->register();
        break;
    case 'verify_account':
        checkMethod(["GET"]);
        $users->verify_account();
        break;

    // case 'reset_password':
    //     checkMethod(["POST"]);
    //     checkUser();
    //     $users->reset_password();
    //     break;
    case 'create_account':
        checkMethod(["POST"]);
        $users->create_account();
        break;
    case 'edit_account':
        checkMethod(["POST"]);
        $token =
            getBearerToken();



        if (!$token || !verifyToken($token) || !$users->isTokenValid($token)) {
            $response->status = 401;
            returnJson();
        }
        $users->edit_account();
        break;
    case 'delete_account':
        checkMethod(["DELETE"]);

        $token =
            getBearerToken();



        if (!$token || !verifyToken($token) || !$users->isTokenValid($token)) {
            $response->status = 401;
            returnJson();
        }

        $users->delete_account();
        break;
    case 'verify_delete_account':
        checkMethod(["GET"]);
        $users->verify_delete_account();
        break;

    case 'send_mail_verify_delete_account':
        checkMethod(["GET"]);
        $users->sendVerifyDeleteToken();
        break;

    case 'get_user_types':
        checkMethod(allowedMethods: ["GET"]);
        $users->get_user_types();
        break;


    case 'get_user_roles':
        checkMethod(["GET"]);
        $users->get_user_roles();
        break;


    case 'load_user':
        checkMethod(["GET"]);

        $token =
            getBearerToken();

        if (!$token || !verifyToken($token) || !$users->isTokenValid($token)) {
            $response->status = 401;
            returnJson();
        }


        $users->load_user();
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
    public $response;

    private $userRepo;

    function __construct()
    {
        global $DB, $response;
        $this->db = $DB;

        $this->userRepo = new UserRepo();

        $this->response = $response;

    }

    /**
     *  Handle the request get user information
     * params userId, user_type
     */
    public function load_user()
    {
        $data = validate_load_user();

        if (isset($data["errors"])) {
            $this->response->status = 422;
            return $this->response->data = $data;
        }

        $userTableName = $this->get_user_table_name((int) $data["user_type"]);


        $userExists = $this->userRepo->findById($data['user_id'], $userTableName);


        if (!$userExists) {
            $this->response->status = 404;
            return $this->response->data = [];
        }

        $this->response->status = 200;

        return $this->response->data = [
            "user" => $userExists
        ];

    }


    function list_users()
    {

        // $data = $this->db->query("SELECT * FROM users_roles")->fetch_all(MYSQLI_ASSOC);


    }


    /**
     *  Handle the request to login user
     */
    function login()
    {
        $data = validate_login();



        if (isset($data["errors"])) {
            $this->response->status = 422;
            return $this->response->data = $data;
        }
        $tableName = $this->get_user_table_name($data["user_type"]);

        $userExists = $this->userRepo->findByEmail($data['email'], $tableName);

        if ($userExists && $userExists["password"] === bcrypt($data['password'])) {

            $user = [
                "id" => $userExists["id"],
                "first_name" => $userExists["first_name"],
                "last_name" => $userExists["last_name"],
                "email" => $userExists["email"],
            ];

            $token = generateToken($user);


            $this->insertUserToken($userExists["id"], $token, $data["user_type"]);



            $this->response->status = 200;
            return $this->response->data = [
                "user" => $user,
                "token" => $token
            ];

        }


        $this->response->status = 401;
        return $this->response->data = ["errors" => ["email" => "Email or password is incorrect"]];
    }





    function logout($token)
    {

        $this->invalidUserToken($token);
        $this->response->status = 200;
        $this->response->data = [
            "message" => "Logged out successfully"
        ];
    }


    /**
     *  Handle the request to update user's password in the database
     */
    function change_password()
    {

        $data = validate_change_password();

        if (isset($data["errors"])) {
            $this->response->status = 422;
            return $this->response->data = $data;
        }


        $userTableName = $this->get_user_table_name((int) $data["user_type"]);



        $userType = $this->userRepo->check_if_exist($data["user_type"], "users_types");


        if (!$userType) {
            $this->response->status = 422;
            return $this->response->data = ["errors" => ["user_type" => "User type does not exist"]];
        }


        $userExists = $this->userRepo->findById($data["user_id"], $userTableName);


        if (!$userExists) {
            $this->response->status = 404;
            return $this->response->data = [];
        }

        $formatedData = ["password" => bcrypt($data["password"])];

        $res = DBupdate($userTableName, $formatedData, $data["user_id"]);


        if ($res) {
            // TODO message

            $this->response->status = 201;
            return $this->response->data = [
                "message" => "Password successfully changed"
            ];
        }

        return $this->response->data = [];


    }

    /**
     * VISITORS -  Handle the request to create visitor user in the database
     */
    function register()
    {
        $validateData = validate_register();

        if (isset($validateData["queen_id"])) {
            $queen = $this->userRepo->check_if_exist($validateData["queen_id"], "object_queen");

            if (!$queen) {
                $this->response->status = 422;
                return $this->response->data = ["errors" => ["user_type" => "Queen does not exist"]];
            }
        }

        $userTableName = $this->get_user_table_name((int) $validateData["user_type"]);



        if ($this->userRepo->emailExists($validateData["email"], $userTableName)) {
            $this->response->status = 422;
            return $this->response->data = ["errors" => ["email" => "Email already exists"]];

        }


        $user = $this->checkIfUserExists($validateData["email"], $userTableName);

        if ($user) {
            $this->response->status = 422;
            return $this->response->data = ["errors" => ["email" => "Email already exists"]];
        }


        $validateData["password"] = bcrypt($validateData["password"]);
        $validateData["uuid"] = create_guid();
        $validateData["is_active"] = 1;
        $res = dbCreate($userTableName, $validateData);


        if ($res) {

            $user = [
                "id" => (string) $this->db->insert_id,
                "first_name" => $validateData["first_name"],
                "last_name" => $validateData["last_name"],
                "email" => $validateData["email"],

            ];

            $token = generateToken($user);

            $this->insertUserToken($this->db->insert_id, $token, $validateData["user_type"]);



            $this->sendVerifyToken($validateData["email"], $userTableName);




            $this->response->status = 201;
            return $this->response->data = [
                "user" => $user,
                "token" => $token
            ];
        }

        $this->response->status = 500;
        return $this->response->data = [];



    }

    function verify_account()
    {

        $token = $_GET["token"] ?? null;

        if (!$token) {
            $this->response->status = 400;
            return $this->response->data = ["message" => "Invalid or expired token"];
        }

        $res = $this->findByToken($token);

        if (!$res) {
            $this->response->status = 400;
            return $this->response->data = ["message" => "Invalid or expired token"];
        }

        $userTableName = $this->get_user_table_name((int) $res["user_type"]);
        $user = $this->userRepo->findByEmail($res["email"], $userTableName);

        $data = ["is_active" => 1];
        DBupdate($userTableName, $data, $user["id"]);

        $this->deactivateAllVerifyTokens($res["email"], $res["user_type"]);


        $this->response->status = 200;
        return $this->response->data = ["message" => "Account successfully verified"];
    }

    public function findByToken($token)
    {

        $date = new DateTime();
        $now = $date->format('Y-m-d H:i:s');

        return $this->db->query("SELECT * FROM verify_tokens WHERE token = '$token' AND is_active = 1 AND expiration > '$now'")->fetch_assoc();
    }


    public function findByTokenVerifyDelete($token)
    {

        $date = new DateTime();
        $now = $date->format('Y-m-d H:i:s');

        return $this->db->query("SELECT * FROM verify_delete_tokens WHERE token = '$token' AND is_active = 1 AND expiration > '$now'")->fetch_assoc();
    }

    function reset_password()
    {

    }

    /**
     *  Handle the request to create a new user from the mobile app
     */
    function create_account()
    {
        $validateData = validate_create_account();

        if (isset($validateData["errors"])) {
            $this->response->status = 422;
            return $this->response->data = $validateData;
        }

        $roleId = $this->userRepo->check_if_exist($validateData["role_id"], "users_roles");

        $userType = $this->userRepo->check_if_exist($validateData["user_type"], "users_types");

        if (!$roleId) {
            $this->response->status = 422;
            return $this->response->data = ["errors" => ["role_id" => "Role id does not exist"]];

        }

        if (!$userType) {
            $this->response->status = 422;
            return $this->response->data = ["errors" => ["user_type" => "User type does not exist"]];
        }


        if (isset($validateData["queen_id"])) {
            $queen = $this->userRepo->check_if_exist($validateData["queen_id"], "object_queen");

            if (!$queen) {
                $this->response->status = 422;
                return $this->response->data = ["errors" => ["user_type" => "Queen does not exist"]];
            }
        }

        if (isset($validateData["partner_id"])) {
            $partner = $this->userRepo->check_if_exist($validateData["partner_id"], "object_partner");

            if (!$partner) {
                $this->response->status = 422;
                return $this->response->data = ["errors" => ["user_type" => "Partner does not exist"]];
            }
        }

        $userTableName = $this->get_user_table_name((int) $validateData["user_type"]);


        if ($this->userRepo->emailExists($validateData["email"], $userTableName)) {
            $this->response->status = 422;
            return $this->response->data = ["errors" => ["email" => "Email already exists"]];

        }


        $user = $this->checkIfUserExists($validateData["email"], $userTableName);

        if ($user) {
            $this->response->status = 422;
            return $this->response->data = ["errors" => ["email" => "Email already exists"]];

        }

        $validateData["password"] = bcrypt($validateData["password"]);
        $validateData["uuid"] = create_guid();
        $validateData["is_active"] = 1;

        $res = dbCreate($userTableName, $validateData);



        if ($res) {

            $user = [
                "id" => (string) $this->db->insert_id,
                "first_name" => $validateData["first_name"],
                "last_name" => $validateData["last_name"],
                "email" => $validateData["email"],

            ];

            $token = generateToken($user);


            $this->insertUserToken($this->db->insert_id, $token, $validateData["user_type"]);

            $this->sendVerifyToken($validateData["email"], $userTableName);



            $this->response->status = 201;
            return $this->response->data = [
                "user" => $user,
                "token" => $token
            ];
        }

        $this->response->status = 500;
        return $this->response->data = [];
    }



    function get_user_table_name($userTypeId)
    {
        return match ($userTypeId) {
            1 => 'users_queen',
            2 => 'users_partners',
            3 => 'users_visitors', default => 'users_visitors',
        };


    }

    function insert_user_to_db()
    {
    }





    function edit_account()
    {
        $validateData = validate_edit();

        if (isset($validateData["errors"])) {
            $this->response->status = 422;
            return $this->response->data = $validateData;
        }
        $userTableName = $this->get_user_table_name((int) $validateData["user_type"]);


        $user = $this->checkIfUserExists($validateData["email"], $userTableName);

        if (!$user) {
            $this->response->status = 404;
            return $this->response->data = [
            ];
        }


        $roleId = $this->userRepo->check_if_exist($validateData["role_id"], "users_roles");

        $userType = $this->userRepo->check_if_exist($validateData["user_type"], "users_types");

        if (!$roleId) {
            $this->response->status = 422;
            return $this->response->data = ["errors" => ["role_id" => "Role id does not exist"]];

        }

        if (!$userType) {
            $this->response->status = 422;
            return $this->response->data = ["errors" => ["user_type" => "User type does not exist"]];
        }


        if (isset($validateData["queen_id"])) {
            $queen = $this->userRepo->check_if_exist($validateData["queen_id"], "object_queen");

            if (!$queen) {
                $this->response->status = 422;
                return $this->response->data = ["errors" => ["user_type" => "Queen does not exist"]];
            }
        }

        if (isset($validateData["partner_id"])) {
            $partner = $this->userRepo->check_if_exist($validateData["partner_id"], "object_partner");

            if (!$partner) {
                $this->response->status = 422;
                return $this->response->data = ["errors" => ["user_type" => "Partner does not exist"]];
            }
        }





        $formatedData =
            array_diff_key($validateData, ["user_id" => 0]);


        $res = DBupdate($userTableName, $formatedData, $validateData["user_id"]);


        if ($res) {

            $user = [
                "id" => $validateData["user_id"],
                "first_name" => $validateData["first_name"],
                "last_name" => $validateData["last_name"],
                "email" => $validateData["email"],

            ];

            $this->response->status = 200;
            return $this->response->data = [
                "user" => $user,

            ];
        }

        $this->response->status = 500;
        return $this->response->data = [];

    }

    /**
     * DElete user
     * @return array|array{errors: array|bool|array{user_id: mixed, user_type: mixed}}
     */
    function delete_account()
    {

        $validatedData = validate_delete();

        if (isset($validateData["errors"])) {
            $this->response->status = 422;
            return $this->response->data = $validatedData;
        }


        $userTableName = $this->get_user_table_name((int) $validatedData["user_type"]);

        $user = $this->userRepo->check_if_user_exist($validatedData["user_id"], $userTableName);

        if (!$user) {
            $this->response->status = 404;
            return $this->response->data = [
            ];
        }

        $data = [

            "deleted_at" => date("Y-m-d H:i:s"),
            "is_active" => 0
        ];

        $res = DBupdate($userTableName, $data, $validatedData["user_id"]);

        if ($res) {

            $this->response->status = 200;
            return $this->response->data = [
                "message" => "User deleted successfully",

            ];
        }

        $this->response->status = 500;
        return $this->response->data = [];
    }



    /**
     * DElete user
     * @return array|array{errors: array|bool|array{user_id: mixed, user_type: mixed}}
     */
    function verify_delete_account()
    {


        $token = $_GET["token"] ?? null;

        if (!$token) {
            $this->response->status = 400;
            return $this->response->data = ["message" => "Invalid or expired token"];
        }

        $res = $this->findByTokenVerifyDelete($token);

        if (!$res) {
            $this->response->status = 400;
            return $this->response->data = ["message" => "Invalid or expired token"];
        }

        $userTableName = $this->get_user_table_name((int) $res["user_type"]);
        $user = $this->userRepo->findByEmail($res["email"], $userTableName);


        // ddd($user);  

        $data = [

            "deleted_at" => date("Y-m-d H:i:s"),
            "is_active" => 0
        ];

        DBupdate($userTableName, $data, $user["id"]);

        if ($res) {


            $this->deactivateAllDeleteVerifyTokens($res["email"], $res["user_type"]);

            $this->response->status = 200;
            return $this->response->data = [
                "message" => "User successfully deleted",

            ];


        }


    }



    function handle_verify_delete_account()
    {

        $token = $_GET["token"] ?? null;

        if (!$token) {
            $this->response->status = 400;
            return $this->response->data = ["message" => "Invalid or expired token"];
        }

        $res = $this->findByTokenVerifyDelete($token);

        if (!$res) {
            $this->response->status = 400;
            return $this->response->data = ["message" => "Invalid or expired token"];
        }

        $userTableName = $this->get_user_table_name((int) $res["user_type"]);
        $user = $this->userRepo->findByEmail($res["email"], $userTableName);

        $data = ["is_active" => 1];
        DBupdate($userTableName, $data, $user["id"]);

        $this->deactivateAllDeleteVerifyTokens($res["email"], $res["user_type"]);


        $this->response->status = 200;
        return $this->response->data = ["message" => "Account successfully verified"];
    }








    /**
     * Get list of all user types
     */
    function get_user_types()
    {
        $data = $this->db->query("SELECT * FROM users_types")->fetch_all(MYSQLI_ASSOC);

        return $this->response->data = $data;
    }


    /**
     * Get list of all user roles
     */
    function get_user_roles()
    {

        $data = $this->db->query("SELECT * FROM users_roles")->fetch_all(MYSQLI_ASSOC);

        return $this->response->data = $data;
    }


    function isTokenValid($token)
    {

        return $this->db->query("SELECT is_valid FROM tokens_blacklist WHERE token = '$token'")->fetch_assoc()["is_valid"];

    }

    function getTokenId($token)
    {

        return $this->db->query("SELECT id FROM tokens_blacklist WHERE token = '$token'")->fetch_assoc();

    }

    function insertUserToken($userId, $token, $userType)
    {

        $data = [
            "user_id" => $userId,
            "token" => $token,
            "user_type" => $userType

        ];

        return dbCreate("tokens_blacklist", $data);

    }






    function invalidUserToken($token)
    {

        $data = [
            "is_valid" => 0
        ];

        $id = $this->getTokenId($token)["id"];
        return DBupdate("tokens_blacklist", $data, $id);

    }


    function checkIfUserExists($email, $table)
    {
        return $this->db->query("SELECT id FROM $table WHERE email = '$email' AND is_active = 1 AND deleted_at IS NULL")->num_rows;
    }


    // function findByEmail()
    // {

    //     return $this->db->query("SELECT email, password FROM  WHERE email = '$email'")->num_rows;


    // }



    function sendMail($data)
    {
        global $URL;


        $mailer = new Mailer();

        $mailData = [
            "fromMail" => "queen@queen.com",
            "fromName" => "queen",
            "userAddress" => $data["email"]
        ];

        $mailer->mail->setFrom($mailData['fromMail']);
        $mailer->mail->addAddress($mailData["userAddress"]);
        $mailer->mail->isHTML(true);
        $mailer->mail->Subject = 'Your Subject Here';
        $mailer->mail->Body = 'Verify: <a href="' . $URL . '/DestiPay_BackEnd' . '/?module=users&action=verify_account&token='
            . $data["token"] . '" target="_blank">Verify account</a>';

        $mailer->mail->send();


    }

    function sendVerifyDeleteMail($data)
    {
        global $URL;


        $mailer = new Mailer();

        $mailData = [
            "fromMail" => "queen@queen.com",
            "fromName" => "queen",
            "userAddress" => $data["email"]
        ];

        $mailer->mail->setFrom($mailData['fromMail']);
        $mailer->mail->addAddress($mailData["userAddress"]);
        $mailer->mail->isHTML(true);
        $mailer->mail->Subject = 'Your Subject Here';
        $mailer->mail->Body = 'Verify Delete: <a href="' . $URL . '/DestiPay_BackEnd' . '/?module=users&action=verify_delete_account&token='
            . $data["token"] . '" target="_blank">Verify Deletee</a>';

        $mailer->mail->send();


    }


    function generateToken(string $email, $userType)
    {

        $token = bin2hex(random_bytes(32));
        $date = new DateTime("+30minutes");

        $expiration = $date->format('Y-m-d H:i:s');

        return ["token" => $token, "expiration" => $expiration, "email" => $email, "user_type" => $userType];
    }

    function deactivateAllPasswordResets(string $email)
    {
        return $this->db->query("UPDATE password_resets SET is_active = 0 WHERE email = '$email' AND is_active = 1");
    }

    function insertToken($data)
    {
        return dbCreate("verify_tokens", $data);


    }

    function insertDeleteToken($data)
    {
        return dbCreate("verify_delete_tokens", $data);


    }

    /**
     * Handle the request for verify token.
     * Generates a password reset token and sends a reset link to the user's email.
     */
    function sendVerifyDeleteToken()
    {

        $validatedData = validate_send_delete_token();

        if (isset($validateData["errors"])) {
            $this->response->status = 422;
            return $this->response->data = $validatedData;
        }



        $userTableName = $this->get_user_table_name((int) $validatedData["user_type"]);
        $userExists = $this->userRepo->findByEmail($validatedData["email"], $userTableName);
        // ddd($userTableName);
        if ($userExists) {
            $this->deactivateAllDeleteVerifyTokens($validatedData["email"], $validatedData["user_type"]);
            $data = $this->generateToken($validatedData["email"], $userExists["user_type"]);

            $res = $this->insertDeleteToken($data);

            if ($res) {
                $this->sendVerifyDeleteMail($data);

                $this->response->status = 200;
                return $this->response->data = [
                    "message" => "verification email for account deletion has been sent",

                ];
            }
        } else {

            return $this->response->status = 404;



        }

    }
    function sendVerifyToken($userEmail, $table)
    {



        $userExists = $this->userRepo->findByEmail($userEmail, $table);

        if ($userExists) {
            $this->deactivateAllVerifyTokens($userEmail, $userExists["user_type"]);
            $data = $this->generateToken($userEmail, $userExists["user_type"]);

            $res = $this->insertToken($data);

            if ($res) {
                $this->sendMail($data);
            }
        }

    }


    function deactivateAllVerifyTokens(string $email, $userType)
    {
        return $this->db->query("UPDATE verify_tokens SET is_active = 0 WHERE email = '$email' AND is_active = 1 AND user_type = $userType");
    }

    function deactivateAllDeleteVerifyTokens(string $email, $userType)
    {
        return $this->db->query("UPDATE verify_delete_tokens SET is_active = 0 WHERE email = '$email' AND is_active = 1 AND user_type = $userType");
    }


}