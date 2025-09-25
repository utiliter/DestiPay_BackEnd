<?php

$db_connection = [



    "default" => [
        "host" => "localhost",
        "username" => "root",
        "password" => "",
        "database" => "spin4",
        "charset" => "utf8mb4",
        "port" => 3307
    ],


    "dev" => [
        "host" => "dev.greendestipay.com",
        "username" => "petar",
        "password" => "8z5cU8Du_",
        "database" => "devgreendestipay",
        "charset" => "utf8mb4",
        "port" => 11111
    ],


    "production" => [
        "host" => "localhost",
        "username" => "root",
        "password" => "",
        "database" => "dbname",
        "charset" => "utf8mb4",
        "port" => 3306
    ]

    ,

];
define('ALLOWED_REQUESTS', ['POST', 'GET', "PUT", "DELETE"]);
define('JWT_SECRET', "fYkv7CDHRexY/uDaTLLIh8gh4RpCoyYHqapbFiWmCRA=");