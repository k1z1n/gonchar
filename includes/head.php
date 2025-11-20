<?php

session_start();
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) && isset($database)) {
    $id = $_SESSION['user_id'];
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$id]);
    $USER = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($USER && isset($USER['status']) && (int)$USER['status'] === 1) {
        unset($_SESSION['user_id']);
        session_destroy();
        echo "<script>window.location.href='./';</script>";
        exit;
    }
}

if (isset($_GET['exit'])) {
    unset($_SESSION['user_id']);
    session_destroy();
    header('Location: ./');
    exit;
}
