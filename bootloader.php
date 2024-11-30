<?php

use Kaviru\MuseCore\ErrorHandling;
use Kaviru\MuseCore\Route;

# Set the custom error handler
register_shutdown_function(function () {
    ErrorHandling::customError();
});

# Hide default php errors
if (config('APP_DEBUG')) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
}

ob_start();

include_once $routesPath;

Route::load();
