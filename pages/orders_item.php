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

$items = [];
if ($order) {
    $sql_item = "SELECT 
        p.id as product_id,
        oi.id as order_item_id,
        p.title,
        oi.count,
        oi.price
        FROM orders_items oi
        JOIN products p ON p.id = oi.product_id
        WHERE oi.order_id = ?";
    $stmt_item = $database->prepare($sql_item);
    $stmt_item->execute([$order['order_id']]);
    $items = $stmt_item->fetchAll(2);
}



?>

<div class="admin-container container">

    <?php include('./includes/admin_menu.php'); ?>

    <main class="admin-main-content">
        <div class="admin-header">
            <h2 class="admin-page-title">Заказ #<?=$order['order_id'] ?? 'Не найден'?></h2>
            <a href="?page=admin_orders">← Назад к заказам</a>
        </div>

        <?php if ($order): ?>
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
                            <?php if (!empty($items)): ?>
                                <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?=$item['title'] ?? '' ?></td>
                                    <td><?=$item['count'] ?? '0' ?></td>
                                    <td><?= number_format($item['price'] ?? 0, 0, ',', ' ') ?> ₽</td>
                                    <td><?= number_format(($item['price'] ?? 0) * ($item['count'] ?? 0), 0, ',', ' ') ?> ₽</td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center;">Товары не найдены</td>
                                </tr>
                            <?php endif; ?>




                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php else: ?>
            <div style="padding: 40px; text-align: center;">
                <p>Заказ не найден</p>
            </div>
        <?php endif; ?>

    </main>

</div>
