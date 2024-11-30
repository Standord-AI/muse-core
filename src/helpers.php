<?php

function uriPattern(string $uri): string
{
    $pattern = preg_replace('/\{([^\/]+)\}/', '([^/]+)', $uri); // Replace {param} with regex
    $pattern = "#^" . $pattern . "$#"; // Add start and end delimiters
    return $pattern;
}