<?php

use App\Modules\Core\Controllers\SettingsController;

$router->get(SettingsController::class, "get_languages", true);

?>