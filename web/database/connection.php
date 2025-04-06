<?php
define('AUTH_TOKEN', 'secret-token-123');

$host = 'postgres';
$db = 'hellofresh';
$user = 'hellofresh';
$pass = 'hellofresh';

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$db", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS recipes (
            id SERIAL PRIMARY KEY,
            name TEXT NOT NULL,
            prep_time INT NOT NULL,
            difficulty INT CHECK (difficulty BETWEEN 1 AND 3),
            vegetarian BOOLEAN NOT NULL,
            ratings_count INT DEFAULT 0,
            ratings_sum INT DEFAULT 0
        );
    ");

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

?>
