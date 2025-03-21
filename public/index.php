<?php
require_once "../app/config/config.php";
require_once "../app/core/ApiException.php";
require_once "../app/core/Database.php";
require_once "../app/core/Controller.php";
require_once "../app/core/App.php";

set_exception_handler(function (Throwable $exception): void {
    // Default values for unkown errors
    $status = 500;
    $codeName = 'INTERNAL_ERROR';
    $message = 'An unexpected error occurred.';

    // Use specific values
    if ($exception instanceof ApiException) {
        $status = $exception->getStatus();
        $codeName = $exception->getCodeName();
        $message = $exception->getMessage();
    }

    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        "status"   => $status,
        "code"     => $codeName,
        "message"  => $message,
        "help_url" => 'https://api.example.com/docs/errors#' . $codeName
    ]);
    exit;
});

// Create a new instance of the App class
$app = new App();
