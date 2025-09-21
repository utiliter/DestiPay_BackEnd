<?php

$db_connection = [
    "default" => [
        "host" => "localhost",
        "username" => "root",
        "password" => "root",
        "database" => "1_spin",
        "charset" => "utf8mb4",
        "port" => 3306
    ],
    "production" => [
        "host" => "localhost",
        "username" => "root",
        "password" => "",
        "database" => "dbname",
        "charset" => "utf8mb4",
        "port" => 3306
    ]
];
define('ALLOWED_REQUESTS', ['POST', 'GET']);
