<?php

use Kaviru\MuseCore\ErrorHandling;
use Kaviru\MuseCore\Route;

# Set the custom error handler
register_shutdown_function(function () {
    ErrorHandling::customError();
});

# Hide default php errors
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
if ((config('APP_DEBUG') == true ? true: false )) {
    error_reporting(E_ALL);
}

ob_start();

global $routesPath;

require_once $routesPath;

Route::load();
