<?php

$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3306';
$dbname = getenv('DB_NAME') ?: 'full_stack_task';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
];


// Enable SSL only for cloud database
if (getenv('DB_HOST')) {

  $caPath = __DIR__ . '/ca.pem';

  $options[PDO::MYSQL_ATTR_SSL_CA] = $caPath;

  $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
}


try {

  $pdo = new PDO(
    "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
    $username,
    $password,
    $options
  );
} catch (PDOException $e) {

  die('Database connection failed. ');
    
}
