<?php

namespace Kaviru\MuseCore;

use Exception;

class Route
{
    public static function get(string $uri, $callback, $classMethod = null)
    {
        return self::register('GET', $uri, $callback, $classMethod);
    }

    public static function post(string $uri, $callback, $classMethod = null)
    {
        return self::register('POST', $uri, $callback, $classMethod);
    }

    private static function register(string $methodAllowed, string $uri, $callback, $classMethod = null)
    {
        global $URLs, $requestMethod, $requestURI;

        if (substr($uri, 0, 1) != '/') {
            $uri = '/' . $uri;
        }

        $URLsKey = $classMethod."@".$uri;

        $URLs[$URLsKey] = ['uri' => $uri, 'callback' => $callback, 'classMethod' => $classMethod, 'methodAllowed' => $methodAllowed];
    }

    public static function load()
    {
        global $URLs, $requestMethod, $requestURI;

        foreach ($URLs ?? [] as $URLsKey => $URL) {

            # Convert route pattern (e.g., 'posts/{title}/{id}') into regex
            $pattern = self::uriPattern($URL['uri']);

            # Check for request match
            if ($requestMethod === $URL['methodAllowed'] && preg_match($pattern, $requestURI, $matches)) {
                ErrorHandling::check_405($requestMethod, $URL['methodAllowed']);

                # Remove the first match (full match) from $matches
                array_shift($matches);

                try {
                    if (is_callable($URL['callback'])) {
                        return call_user_func($URL['callback'], ...$matches);
                    }

                    $controller = new $URL['callback']();
                    $classMethod = $URL['classMethod'];

                    return $controller->$classMethod(...array_values($matches));
                    
                    # return call_user_func_array([$controller, $URL['classMethod']], $matches); // Use if above method didnt work.
                } catch (\Throwable $th) {

                    ErrorHandling::handleException($th);
                }
            }
        }
        
        # If no match was found, trigger 404 error
        ErrorHandling::check_404();
    }

    // Add name to a route
    public static function name(string $uri, string $name)
    {
        global $namedRoutes;

        if (substr($uri, 0, 1) != '/') {
            $uri = '/' . $uri;
        }

        $namedRoutes[$name] = $uri;
    }

    // Generate URL by route name
    public static function route(string $name, array|string $params = [])
    {
        global $namedRoutes;

        if (!isset($namedRoutes[$name])) {
            //throw new Exception("Route name '$name' not found.");
        }

        $uri = $namedRoutes[$name];

        // Replace placeholders with params
        if (is_array($params)) {
            foreach ($params as $key => $value) {
                $uri = str_replace("{" . $key . "}", $value, $uri);
            }
        } elseif (is_string($params)) {
            $uri = preg_replace('/\{[^}]+\}/', $params, $uri, 1);
        }

        return self::currentDomain() . $uri;
    }

    // Get the current domain
    private static function currentDomain()
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
        return $protocol . "://" . $_SERVER['HTTP_HOST'];
    }

    // Convert URI to regex pattern
    private static function uriPattern(string $uri)
    {
        return "#^" . preg_replace('/\{([^\/]+)\}/', '([^/]+)', $uri) . "$#";
    }
}
