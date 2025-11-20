<?php

$host = 'localhost';
$dbname = 'k1z1nksb_gonchar';
$username = 'k1z1nksb_gonchar';
$password = 'DoCCbyE%UD0n';


try {
    $database = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
} catch (PDOException $e) {
    die("Ошибка при подключении к БД: " . $e->getMessage());
}

?>