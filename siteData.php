<?php

# Start loading data from server

$httpHost = $_SERVER['HTTP_HOST'];

$serverName = $_SERVER['SERVER_NAME'];

$requestMethod = $_SERVER['REQUEST_METHOD'];

$requestURI = $_SERVER['REQUEST_URI'];

$pathInfo = isset($_SERVER["PATH_INFO"]) ?? $_SERVER["PATH_INFO"];

$redirectURL = isset($_SERVER["REDIRECT_URL"]) ?? $_SERVER["REDIRECT_URL"];

# End loading data from server

$URLs = [];

$NamedRoutes = [];

$viewPath = __DIR__ . "/views";

$envPath = __DIR__ . "/.env";
$routesPath = __DIR__ . "/routes/web.pap";
