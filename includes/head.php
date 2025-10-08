<?php

session_start();
if(isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $id = $_SESSION['user_id'];
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$id]);
    $USER = $stmt->fetch(2);
}

if(isset($_GET['exit'])) {
    unset($_SESSION['user_id']);
    session_destroy();
}


?>