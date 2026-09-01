<?php

$request_path = parse_url(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/', PHP_URL_PATH);
$request_path = is_string($request_path) ? $request_path : '/';
$candidate = (isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : __DIR__) . $request_path;

if (PHP_SAPI === 'cli-server' && $request_path !== '/' && is_file($candidate)) {
    return false;
}

require __DIR__ . '/index.php';
