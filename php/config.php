<?php

// Configurazione database
$dbHost = '127.0.0.1';
$dbName = 'cinespecs';
$dbUser = 'root';
$dbPass = '';

// DSN
$dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    error_log("Errore connessione DB: " . $e->getMessage());
    exit("Errore di connessione al database.");
}