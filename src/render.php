<?php

use Kaviru\MuseCore\Route;
use Kaviru\MuseCore\DataHandling;
use Kaviru\MuseCore\ErrorHandling;

function view(string $location, array $variables = null, array $error = null)
{
    global $requestURI, $namedRoutes; // load all the public variables

    if (!isset($error)) {
        $error = ['code' => 404, 'message' => 'Page not found'];
    }

    $views = "/../../../../views";
    echo __DIR__;
    $error = (object) $error;
    $view_missing_error = $error->code . ' : ' . $error->message . ",\n\n View file for error is missing.";

    try {
        if (isset($variables)) {
            extract($variables);
        }

        if (substr($location, 0, 1) != '/') {
            $location = '/' . $location;
        }

        return require_once __DIR__ . $views . $location;
    } catch (\Throwable $th) {
        if (!$error) {
            ErrorHandling::_404("View file not found");
        } else {
            ErrorHandling::_404($view_missing_error);
        }
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
    global $httpHost;

    $protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';

    if (substr($route, 0, 1) != '/') {
        $route = '/' . $route;
    }

    return $protocol . '://' . $httpHost . $route;
}

function asset(string $path)
{
    if (substr($path, 0, 1) != '/') {
        $path = '/' . $path;
    }

    return route('public' . $path);
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

    return $data->env->$parameter;
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