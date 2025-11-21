<?php

    $allPages = ['main', 'register', 'login', 'profile', 'catalog', 'product', 'cart', 'user_orders', 'orders_item', 'admin_statistics', 'admin_users', 'admin_products', 'admin_add_product', 'admin_edit_product', 'admin_categories', 'admin_add_category', 'admin_edit_category', 'admin_delete_category', 'admin_orders', 'admin_masterclass_requests', 'admin_masterclass_request_edit', 'admin_promocodes', 'get_characteristics', 'save_product', 'update_product', 'admin_delete_product', 'masterclass_request'];

$page = $_GET['page'] ?? 'main';

// Проверяем, нужен ли только JSON (без HTML обертки)
$jsonOnlyPages = ['get_characteristics', 'save_product', 'update_product'];

if (in_array($page, $jsonOnlyPages)) {
    // Для этих страниц не выводим header/footer - только JSON
    include("pages/" . "$page" . ".php");
    exit;
}

// Для обычных страниц выводим HTML
include_once("database/connect.php");
include_once("includes/head.php");

?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Гончарок - школа керамики и гончарного мастерства</title>
    <link rel="shortcut icon" href="assets/media/images/logo/logo.svg" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <?php

    include("includes/header.php");

    if (in_array($page, $allPages)) {
        include("pages/" . "$page" . ".php");
    } else {
        include("404.php");
    }

    include("includes/footer.php");

    ?>
</body>

</html>