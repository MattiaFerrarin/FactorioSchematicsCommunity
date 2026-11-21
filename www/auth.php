<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function getDB(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $host = getenv('DB_HOST');
    $dbname = getenv('DB_NAME');
    $dbuser = getenv('DB_USER');
    $dbpass = getenv('DB_PASSWORD');

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $dbuser,
        $dbpass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    return $pdo;
}

function currentUser(): ?array
{
    if (empty($_SESSION['username'])) {
        return null;
    }

    return [
        'username' => $_SESSION['username'],
        'name' => $_SESSION['name'] ?? $_SESSION['username'],
    ];
}