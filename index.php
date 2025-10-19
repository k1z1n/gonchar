<?php

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

    $allPages = ['main', 'register', 'login', 'profile', 'catalog', 'product', 'admin_users', 'admin_products', 'admin_add_product', 'admin_edit_product', 'admin_categories', 'admin_add_category', 'admin_edit_category'];

    $page = $_GET['page'] ?? 'main';

    // $mass = [
    //     'main' => 'dsda',
    //     'login' => 'dsda',
    // ];
    // if (in_array($page, $mass)) {
    //     die($mass[$page]);
    // }
    // if (isset($mass[$page])) {
    //     echo $mass[$page];
    // }


    if (in_array($page, $allPages)) {
        include("pages/" . "$page" . ".php");
    } else {
        include("404.php");
    }

    include("includes/footer.php");

    ?>
</body>

</html>