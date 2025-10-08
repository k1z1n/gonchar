<?php

$host = 'localhost';
$dbname = 'goncharok';
$username = 'root';
$password = '';


try {
    $database = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
} catch (PDOException $e) {
    die("Ошибка при подключении к БД: " . $e->getMessage());
}

?>