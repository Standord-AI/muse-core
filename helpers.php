<?php

use Kaviru\MuseCore\Route;
use Kaviru\MuseCore\DataHandling;
use Kaviru\MuseCore\ErrorHandling;

function view(string $location, array $variables = null, array $error = ['code' => 404, 'message' => 'Page not found'])
{
    global $requestURI, $namedRoutes, $viewPath; // load all the public variables

    try {
        if (isset($variables)) {
            extract($variables);
        }

        if (substr($location, 0, 1) != '/') {
            $location = '/' . $location;
        }
        
        return require_once  $viewPath . $location;
    } catch (\Throwable $th) {
            ErrorHandling::_404($error['code'] . ' : ' . $error['message'] . ",\n\n View file for error is missing.");
    }
}

function redirect(string $url)
{
    try {
        header("Location: $url");
        exit();
    } catch (\Throwable $th) {
        ErrorHandling::_404("View file not found");
    }
}

function route(string $name, array $params = [])
{
    return Route::route($name, params: $params);
}

function routePath(string $route)
{
    global $httpHost, $requestURI;

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    
    if (substr($route, 0, 1) == '/') {
        $route = '/' . $route;
    }

    return $protocol . '://' . $httpHost. $requestURI . $route;
}

function asset(string $path)
{   
    if (substr($path, 0, 1) != '/') {
        $path = '/' . $path;
    }

    return routePath('public' . $path);
}

function request($parameter)
{

    $data = new DataHandling;

    return $data->request->$parameter;
}

function config($envName)
{
    $data = new DataHandling;

    return $data->env->$envName;
}

function get($parameter)
{
    $data = new DataHandling;

    return $data->get->$parameter;
}

function post($parameter)
{
    $data = new DataHandling;

    return $data->post->$parameter;
}

function cookie($parameter)
{
    $data = new DataHandling;

    return $data->cookie->$parameter;
}

function files($parameter)
{
    $data = new DataHandling;

    return $data->files->$parameter;
}

function session($parameter)
{
    $data = new DataHandling;

    if (isset($_SESSION)) {
        $data->session = (object) $_SESSION;
    } else {
        $data->session = null;
    }
    return $data->session->$parameter;
}

function uriPattern(string $uri): string
{
    $pattern = preg_replace('/\{([^\/]+)\}/', '([^/]+)', $uri); // Replace {param} with regex
    $pattern = "#^" . $pattern . "$#"; // Add start and end delimiters
    return $pattern;
}