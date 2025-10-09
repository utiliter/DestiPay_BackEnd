<?php

use App\Modules\Core\Controllers\SettingsController;

$router->get(SettingsController::class, "get_languages", "settings_get_language", true);

?>