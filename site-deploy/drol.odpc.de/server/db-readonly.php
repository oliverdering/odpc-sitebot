<?php
declare(strict_types=1);
/**
 * D.R.O.L. private DB adapter — NOT a public endpoint.
 * Requires a private, server-local config file, never checked into Git.
 * This library intentionally contains no credentials and emits no HTTP response.
 */
if (!defined('DROL_INTERNAL')) {
    http_response_code(404);
    exit;
}
function drol_database(): PDO {
    $configPath = getenv('DROL_DB_CONFIG');
    if (!is_string($configPath) || $configPath === '' || !is_file($configPath)) {
        throw new RuntimeException('DROL private database configuration is not provisioned');
    }
    $cfg = require $configPath;
    if (!is_array($cfg)) {
        throw new RuntimeException('Invalid private database configuration');
    }
    foreach (['host', 'database', 'username', 'password'] as $key) {
        if (!isset($cfg[$key]) || !is_string($cfg[$key]) || $cfg[$key] === '') {
            throw new RuntimeException('Incomplete private database configuration');
        }
    }
    $port = (int)($cfg['port'] ?? 3306);
    if ($port < 1 || $port > 65535) {
        throw new RuntimeException('Invalid database port');
    }
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        $cfg['host'], $port, $cfg['database']);
    return new PDO($dsn, $cfg['username'], $cfg['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 8,
    ]);
}
