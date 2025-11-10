<?php

if(isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
}

$sql_order = "SELECT
    u.id as user_id,
    o.id as order_id,
    u.surname,
    u.username,
    u.email,
    u.phone,
    o.adress,
    o.total_amount
    FROM orders o
    JOIN users u ON u.id = o.user_id
    WHERE o.id = ?";
$stmt_order = $database->prepare($sql_order);
$stmt_order->execute([$id]);
$order = $stmt_order->fetch(2);

$sql_item = "SELECT 
    o.id as order_id,
    oi.id as item_id,
    
    FROM orders_items oi
    JOIN orders o ON o.id = oi.order_id
    WHERE order_id = ?";
$stmt_item = $database->prepare($sql_item);
$stmt_item->execute([$order['order_id']]);
$item = $stmt_item->fetch(2);



?>

<div class="admin-container container">

    <?php include('./includes/admin_menu.php'); ?>

    <main class="admin-main-content">
        <div class="admin-header">
            <h2 class="admin-page-title">Заказ #<?=$order['order_id']?></h2>
            <a href="?page=admin_orders">← Назад к заказам</a>
        </div>

        <div class="order-details">
            <div class="order-info-section">
                <h3>Информация о заказе</h3>
                <div class="order-info-grid">
                    <div class="info-item">
                        <span class="info-label">Клиент:</span>
                        <span class="info-value"><?=$order['surname'] ?? '' ?> <?=$order['username'] ?? '' ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">E-mail:</span>
                        <span class="info-value"><?=$order['email'] ?? '' ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Телефон:</span>
                        <span class="info-value"><?=$order['phone'] ?? '' ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Адрес:</span>
                        <span class="info-value"><?=$order['adress'] ?? '' ?></span>
                    </div>

                </div>
            </div>

            <div class="order-items-section">
                <h3>Товары в заказе</h3>
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                        <tr>
                            <th>Товар</th>
                            <th>Количество</th>
                            <th>Цена</th>
                            <th>Сумма</th>
                        </tr>
                        </thead>
                        <tbody>


                            <tr>
                                <td><?=$item['title'] ?? '' ?></td>
                                <td><?=$item['count'] ?? '0' ?></td>
                                <td><?=$item['price'] ?? '0' ?>₽</td>
                                <td><?=$item['price'] * $item['count'] ?>₽</td>
                            </tr>




                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>

</div>
