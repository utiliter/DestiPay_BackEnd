<?php

$URL = "http://localhost";

$db_connection = [



    "default" => [
        "host" => "localhost",
        "username" => "root",
        "password" => "",
        "database" => "dbbb",
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

$mailerConfig = [

    "host" => "sandbox.smtp.mailtrap.io",
    "SMTPAuth" => true,
    "username" => "75d087ee15be0d",
    "password" => "5e4909633e044e",
    "SMTPsecure" => "tls",
    "port" => 587,
    "mailFromAddress" => "hello@example.com",
    "mailFromName" => "Utiliter d.o.o.",

    "isHtml" => false
];


define('ALLOWED_REQUESTS', ['POST', 'GET', "PUT", "DELETE"]);
define('JWT_SECRET', "fYkv7CDHRexY/uDaTLLIh8gh4RpCoyYHqapbFiWmCRA=");