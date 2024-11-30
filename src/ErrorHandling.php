<?php

namespace Core;

use Throwable;

class ErrorHandling
{
    public static string $view_404 = '/errors/404.php';
    public static string $message_404 = "Sorry, we can't find that page.";

    public static string $view_405 = '/errors/405.php';
    public static string $message_405 = "Sorry, your method is not allowed to proceed.";

    public static string $view_500 = '/errors/500.php';
    public static string $message_500 = "Internal server error";

    public static string $view_custom_error_handler = '/errors/error-handler.php';

    public static function _404($message = null)
    {
        self::sendHeader(404, "Not Found");
        $message = $message ?? self::$message_404;
        self::renderError(404, $message, self::$view_404);
        exit();
    }

    public static function _405($message = null)
    {
        self::sendHeader(405, "Method Not Allowed");
        $message = $message ?? self::$message_405;
        self::renderError(405, $message, self::$view_405);
        exit();
    }

    public static function _500($message = null)
    {
        self::sendHeader(500, "Internal Server Error");
        $message = $message ?? self::$message_500;
        self::renderError(500, $message, self::$view_500);
        exit();
    }

    public static function check_404()
    {
        global $requestURI, $URLs;

        foreach ($URLs as $uri => $callback) {
            // Convert route pattern (e.g., 'posts/{title}/{id}') into regex
            $pattern = uriPattern($uri);

            // Check if the current requestURI matches any registered pattern
            if (preg_match($pattern, $requestURI)) {
                return; // If a match is found, no need to proceed further
            }
        }

        // If no match was found, trigger 404 error
        self::cleanOutputBuffer();
        self::_404();
    }

    public static function check_405(string $requestMethod, string $methodAllowed)
    {
        if ($requestMethod !== $methodAllowed) {
            self::cleanOutputBuffer();
            self::_405();
        }
    }

    private static function renderError(int $code, string $message, string $view)
    {
        try {
            return view($view, [], ['code' => $code, 'message' => $message]);
        } catch (Throwable $th) {
            echo "Error {$code}: {$message}";
        }
    }

    private static function cleanOutputBuffer()
    {
        if (ob_get_contents()) {
            ob_end_clean();
        }
    }

    private static function sendHeader(int $statusCode, string $statusMessage)
    {
        header("HTTP/1.1 {$statusCode} {$statusMessage}");
    }

    public static function customError()
    {
        $err = error_get_last();

        return print_r($err);

        if (!is_null($err)) {

            self::cleanOutputBuffer();

            $errorDetails = [
                'errno' => $error['type'] ?? E_ERROR,
                'errtype' => self::getErrorType($error['type'] ?? E_ERROR),
                'errstr' => $error['message'] ?? "Unknown error",
                'errfile' => $error['file'] ?? "N/A",
                'errline' => $error['line'] ?? "N/A",
            ];

            self::processError($errorDetails);
        }
    }

    public static function handleException(Throwable $exception)
    {
        self::cleanOutputBuffer();

        $errorDetails = [
            'errno' => $exception->getCode(),
            'errtype' => "Uncaught Exception",
            'errstr' => $exception->getMessage(),
            'errfile' => $exception->getFile(),
            'errline' => $exception->getLine(),
        ];

        self::processError($errorDetails);
    }

    private static function processError(array $errorDetails)
    {
        if (self::isDebugMode()) {
            try {
                return view(self::$view_custom_error_handler, $errorDetails);
            } catch (Throwable $th) {
                self::_500("Error rendering custom error view.");
            }
        } else {
            self::_500("An unexpected error occurred. Please try again later.");
        }
    }

    private static function getErrorType(int $type): string
    {
        return match ($type) {
            E_ERROR => "Fatal Error",
            E_WARNING => "Warning",
            E_NOTICE => "Notice",
            default => "Unknown Error",
        };
    }

    private static function isDebugMode(): bool
    {
        return function_exists('config') ? config('APP_DEBUG') : false;
    }
}
