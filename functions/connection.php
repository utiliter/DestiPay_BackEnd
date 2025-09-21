<?php
$DB = connectToDB();
function connectToDB($db = "default")
{
    global $db_connection;

    $charset = $db_connection[$db]["charset"] ?? "";
    $dbhost = $db_connection[$db]["host"] ?? "";
    $dbuser = $db_connection[$db]["username"] ?? "";
    $dbpass = $db_connection[$db]["password"] ?? "";
    $dbname = $db_connection[$db]["database"] ?? "";
    $dbport = (int) $db_connection[$db]["port"] ?? 0;

    $mysqli_connect = new mysqli($dbhost, $dbuser, $dbpass, $dbname, $dbport);

    if ($mysqli_connect->connect_errno) {
        echo "Failed to connect to MySQL: " . $mysqli_connect->connect_error;
        exit();
    }
    $mysqli_connect->set_charset($charset);
    return $mysqli_connect;

}