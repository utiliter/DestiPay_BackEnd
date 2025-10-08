<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function generateBearerToken($user)
{

    $payload = [
        'iss' => 'dev.greendestipay.com',
        'aud' => 'dev.greendestipay.com',
        'iat' => time(),
        'exp' => time() + 3600,        // 1h
        'data' => [
            'id' => $user['id'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            "user_type" => $user["user_type"]
        ]
    ];

    $jwt = JWT::encode($payload, JWT_SECRET, 'HS256');
    return $jwt;
}


function getUserDataFromBearerToken($token)
{
    try {
        $decoded = JWT::decode($token, new Key(JWT_SECRET, 'HS256'));
        return (array) $decoded->data;
    } catch (\Exception $e) {
        return null;
    }
}


function decodeJson()
{
    global $response;

    $data = json_decode(file_get_contents("php://input"), true);

    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        $response->status = 400;
        $response->data = [
            "error" => "Invalid JSON data"
        ];
        returnJson();
    }
    return $data;
}


function verifyToken($jwt = null)
{


    try {
        $res = JWT::decode($jwt, new Key(JWT_SECRET, 'HS256'));

        return true;
    } catch (\Exception $e) {
        return false;
    }
}
//checj
// function getBearerToken()
// {

//     $headers = apache_request_headers();




//     if (isset($headers['Authorization'])) {
//         $matches = [];
//         if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
//             return $matches[1];
//         }
//     }
//     return null;




// }





function getBearerToken()
{

    $headers = apache_request_headers();

    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $auth = $_SERVER['HTTP_AUTHORIZATION'];
    } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $auth = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    } elseif (!empty($headers['Authorization'])) {
        $auth = $headers['Authorization'];
    } else {
        $auth = null;
    }



    if (isset($auth)) {
        $matches = [];
        if (preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
            return $matches[1];
        }
    }
    return null;
}



// function getBearerToken()
// {
//     $headers = [];

//     if (function_exists('apache_request_headers')) {
//         $headers = apache_request_headers();
//     }


//     if (!isset($headers['Authorization']) && isset($_SERVER['HTTP_AUTHORIZATION'])) {
//         $headers['Authorization'] = $_SERVER['HTTP_AUTHORIZATION'];
//     } elseif (!isset($headers['Authorization']) && isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
//         $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
//     }

//     if (isset($headers['Authorization'])) {
//         if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
//             return trim($matches[1]);
//         }
//     }

//     // ddd($headers);


// }

function returnJson()
{
    global $DB, $response;
    $response->message = error($response->status);
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($response->status ?? 500);
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    $DB->close();
    exit();
}





function checkRequests()
{
    if (empty($_REQUEST) || !is_array($_REQUEST) || count($_REQUEST) == 0) {
        return;
    }
    foreach ($_REQUEST as $key => $value) {
        checkInjection(trim($value));
    }
}

function checkMethod($allowedMethods = ALLOWED_REQUESTS)
{

    global $response;
    if (!in_array(REQUEST_METHOD, $allowedMethods)) {
        $response->status = 405;
        return returnJson();


    } else {
        return true;
    }
}

function getAuthorizationHeader()
{
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } else if (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }

    return $headers ?: null;
}

// function getBearerToken()
// {
//     $headers = getAuthorizationHeader();
//     if (!empty($headers)) {
//         if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
//             return $matches[1];
//         }
//     }
//     return null;
// }

function createBearerTokenForUser(int $userId): string
{
    $token = bin2hex(random_bytes(32));
    return $token;
}



function error($code = 404)
{
    $errors = [
        200 => "200 OK",
        201 => "Created",

        400 => "400 Bad Request",
        401 => "401 Unauthorized",
        403 => "403 Forbidden",
        404 => "404 Not Found",
        405 => "405 Method Not Allowed",
        408 => "408 Request Timeout",
        422 => "Unprocessable Entity",
        500 => "500 Internal Server Error",
        502 => "502 Bad Gateway",
        503 => "503 Service Unavailable",
        504 => "504 Gateway Timeout"
    ];
    return $errors[$code] ?? $errors[404];
}

function create_guid()
{
    if (function_exists('com_create_guid') === true) {
        return trim(com_create_guid(), '{}');
    }
    return strtolower(sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535)));
}

function createActivationCode($input)
{
    return randomString(3) . md5($input . date("Y-m-d H:i:s")) . randomString(2);
}


function randomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function randomStringOnly($length = 10)
{
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}



function randomNumbers($length = 4)
{
    $characters = '0123456789';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


function qr_encryption($text, $sec = 120, $key = "DestipaySpin1")
{
    global $DB;

    $sec = 60 * 60 * 24 * 30;

    $time = str_replace(" ", "T", date("Y-m-d H:i:s", time() + $sec));
    $plaintext = $text . "_" . $time;

    $aes = $DB->query("SELECT HEX(AES_ENCRYPT('$plaintext','DestipaySpin1')) as hex")->fetch_assoc();
    return base64_encode($aes["hex"]);
}

function qr_decryption($encoded, $key = "DestipaySpin1")
{
    global $DB;

    // dekodiraj iz base64 u hex string
    $hex = base64_decode($encoded);
    $hex = strtoupper($hex); // HEX mora biti velikim slovima (nije obavezno, ali sigurnije)

    // OVDJE NEMA UNHEX ako je $hex vec hex string!
    $sql = "SELECT AES_DECRYPT(UNHEX('$hex'), '$key') AS decrypted";
    $result = $DB->query($sql)->fetch_assoc();

    if (!$result || empty($result['decrypted'])) {
        return false;
    }

    $decrypted = $result['decrypted'];

    // Ukloni vremenski dio
    $parts = explode('_', $decrypted);
    array_pop($parts); // makni timestamp
    return implode('_', $parts); // vrati originalni tekst
}

function getUserIP()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function user_agent()
{
    $iPod = strpos($_SERVER['HTTP_USER_AGENT'], "iPod");
    $iPhone = strpos($_SERVER['HTTP_USER_AGENT'], "iPhone");
    $iPad = strpos($_SERVER['HTTP_USER_AGENT'], "iPad");
    $android = strpos($_SERVER['HTTP_USER_AGENT'], "Android");

    if ($iPad || $iPhone || $iPod) {
        return 2;
    } else if ($android) {
        return 1;
    } else {
        return 0;
    }
}

function datetimeDiff($dt1, $dt2)
{
    $t1 = strtotime($dt1);
    $t2 = strtotime($dt2);

    $dtd = new stdClass();
    $dtd->interval = $t2 - $t1;
    $dtd->total_sec = abs($t2 - $t1);
    $dtd->total_min = floor($dtd->total_sec / 60);
    $dtd->total_hour = floor($dtd->total_min / 60);
    $dtd->total_day = floor($dtd->total_hour / 24);

    $dtd->day = $dtd->total_day;
    $dtd->hour = $dtd->total_hour - ($dtd->total_day * 24);
    $dtd->min = $dtd->total_min - ($dtd->total_hour * 60);
    $dtd->sec = $dtd->total_sec - ($dtd->total_min * 60);
    return $dtd;
}




function checkInjection($data, $exclude = [])
{
    global $response;
    $exclude = array_change_key_case($exclude, CASE_UPPER);
    $determine = ["EXEC(", "SYSTEM(", "&& WHOAMI", "; RM -RF", "(CN=*)", ")(|(USER=*))", "CHAR(", "DBMS", "CONVERT(", "_PIPE", "INT(", "INTCHAR(", "CONCAT(", "HAVING", "RLIKE", "INFORMATION_SHEMA", "UNION SELECT", "TABLE_SHEMA", "SLEEP", "IFNULL", "CAST(", "SYSCOLUMNS", "*", "OR 1=1", "DROP TABLE", "OR 1=1--", "SLEEP("];

    if (is_array($data)) {

        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                foreach ($determine as $d => $ddata) {
                    $val = strtoupper($value);
                    $ddat = strtoupper($ddata);
                    if (!in_array($ddata, $exclude)) {
                        if (strstr($val, $ddat) == true) {
                            $data[$key] = "";
                            $response->status = 403;
                            returnJson();
                        }
                    }
                }
            }
        }

    } else {

        foreach ($determine as $d => $ddata) {
            $val = strtoupper($data);
            $ddat = strtoupper($ddata);
            if (!in_array($ddata, $exclude)) {
                if (strstr($val, $ddat) == true) {
                    $response->status = 403;
                    returnJson();
                }
            }
        }

    }

    return $data;
}



function dd($value)
{
    $trace = debug_backtrace();
    $caller = $trace[0];
    echo "FILE: " . $caller['file'] . " LINE: " . $caller['line'] . "\n\n";
    echo "<pre>";
    var_dump($value);
    echo "</pre>";
}

function ddd($value)
{
    $trace = debug_backtrace();
    $caller = $trace[0];
    echo "FILE: " . $caller['file'] . " LINE: " . $caller['line'] . "\n\n";
    echo "<pre>";
    var_dump($value);
    echo "</pre>";
    die;
}



function dbCreate($table, $data)
{
    global $DB;
    // $data["id"] = 222222222;

    $formatedFields = "(" . implode(",", array_keys($data)) . ")";
    $count = count(array_keys($data));

    $formatedValuePlaceholder = "(" . implode(",", array_fill(0, $count, "?")) . ")";
    $q = "INSERT INTO $table $formatedFields VALUES $formatedValuePlaceholder;";


    // return $this->query($q, array_values($data));

    // $this->stmt = $this->connection->prepare($query);
    $stmt = $DB->prepare($q);
    if ($stmt === false) {

        // $err = date("Y-m-d H:i:s") . " - " . $query;
        // ddd($err);
        // file_put_contents(LOG_PATH . "errors.txt", $q . PHP_EOL, FILE_APPEND);
        // throw new \Exception("MySQL error: " . $this->connection->error, 500);
    }

    $params = array_values($data);

    if (!empty($params)) {
        $types = "";
        $refs = [];
        foreach ($params as $key => $param) {
            $types .= _gettype($param);
            $refs[$key] = &$params[$key];
        }

        array_unshift($refs, $types);
        call_user_func_array([$stmt, 'bind_param'], $refs);
    }


    return $stmt->execute();

}
function DBupdate($table, $data, $id)
{
    global $DB;

    $str = "";

    foreach ($data as $key => $val) {
        $str .= "$key = ?,";
    }

    $str = substr($str, 0, -1);

    $q = "UPDATE $table SET $str WHERE id = $id ;";


    $params
        = array_values($data);


    $stmt = $DB->prepare($q);



    if ($stmt === false) {

        // $err = date("Y-m-d H:i:s") . " - " . $query;
        // ddd($err);
        // file_put_contents(LOG_PATH . "errors.txt", $query . PHP_EOL, FILE_APPEND);
        // throw new \Exception("MySQL error: " . $this->connection->error, 500);
        // $this->error("Query error: " . $this->connection->error);
    }

    if (!empty($params)) {
        $types = "";
        $refs = [];
        foreach ($params as $key => $param) {
            $types .= _gettype($param);
            $refs[$key] = &$params[$key];
        }

        array_unshift($refs, $types);
        call_user_func_array([$stmt, 'bind_param'], $refs);
    }


    return $stmt->execute();

}






// function query($query, $params = [])
// {

//     $this->lastQuery = $query;


//     $this->stmt = $this->connection->prepare($query);

//     if ($this->stmt === false) {

//         // $err = date("Y-m-d H:i:s") . " - " . $query;
//         // ddd($err);
//         file_put_contents(LOG_PATH . "errors.txt", $query . PHP_EOL, FILE_APPEND);
//         throw new \Exception("MySQL error: " . $this->connection->error, 500);
//         $this->error("Query error: " . $this->connection->error);
//     }

//     if (!empty($params)) {
//         $types = "";
//         $refs = [];
//         foreach ($params as $key => $param) {
//             $types .= $this->_gettype($param);
//             $refs[$key] = &$params[$key];
//         }

//         array_unshift($refs, $types);
//         call_user_func_array([$this->stmt, 'bind_param'], $refs);
//     }

//     $this->stmt->execute();

//     // return $this;
// }


function _gettype($var)
{
    if (is_string($var))
        return 's';
    if (is_float($var))
        return 'd';
    if (is_int($var))
        return 'i';
    return 'b';
}



function isBearerTokenValid($token)
{
    global $DB;
    $data = $DB->query("SELECT is_valid FROM users_tokens WHERE bearer_token = '$token'")->fetch_assoc();

    if (!$data) {
        return false;
    }

    return (bool) $data["is_valid"];
}


function checkIfExist($id, $table)
{
    global $DB;
    $id = (int) $id;
    $query = "SELECT id FROM $table WHERE id = $id";
    return (bool) $DB->query($query)->num_rows;
}


function getNowDatetime()
{


    $date = new DateTime();
    return $date->format('Y-m-d H:i:s');
}


function authorize($condition)
{
    global $response;

    if (!$condition) {
        $response->status = 403;
        $response->data = [];
        return returnJson();


    }
    return true;

}