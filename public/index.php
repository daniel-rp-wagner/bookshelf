<?php
require_once "../app/config/config.php";
require_once "../app/core/Database.php";
require_once "../app/core/Controller.php";
require_once "../app/core/App.php";

set_exception_handler(function (Throwable $exception): void {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => $exception->getMessage()]);
    exit;
});

// Create a new instance of the App class
$app = new App();
