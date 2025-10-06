<?php
require_once __DIR__ . '/vendor/autoload.php';

require_once 'config.php';

require __DIR__ . "/app/Core/Config/pathConstants.php";
require_once 'functions/functions.php';

require_once 'functions/connection.php';

$container = require_once "app/Core/" . "container.php";


require_once 'load.php';
$DB->close();