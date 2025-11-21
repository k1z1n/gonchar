<?php

if (!isset($_SESSION['user_id'])) {
    echo "<script>document.location.href='./?page=login';</script>";
    exit;
}

$userId = (int)$_SESSION['user_id'];

$sqlOrders = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmtOrders = $database->prepare($sqlOrders);
$stmtOrders->execute([$userId]);
$orders = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);

$orderStatusMap = [
    'pending' => 'В обработке',
    'processing' => 'Собираем заказ',
    'paid' => 'Оплачен',
    'shipped' => 'Передан в доставку',
    'delivered' => 'Доставлен',
    'cancelled' => 'Отменён',
];

$orderItemsMap = [];

if (!empty($orders)) {
    $orderIds = array_column($orders, 'id');
    $placeholders = implode(',', array_fill(0, count($orderIds), '?'));

    $sqlItems = "
        SELECT 
            oi.order_id,
            oi.count,
            oi.price,
            p.title,
            p.id AS product_id,
            (SELECT path FROM images WHERE product_id = p.id ORDER BY id ASC LIMIT 1) AS image_path
        FROM orders_items oi
        JOIN products p ON p.id = oi.product_id
        WHERE oi.order_id IN ($placeholders)
    ";

    $stmtItems = $database->prepare($sqlItems);
    $stmtItems->execute($orderIds);
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as $item) {
        $orderId = $item['order_id'];
        if (!isset($orderItemsMap[$orderId])) {
            $orderItemsMap[$orderId] = [
                'items' => [],
                'images' => []
            ];
        }
        $orderItemsMap[$orderId]['items'][] = $item;

        $imagePath = $item['image_path'] ?? '';
        if (!empty($imagePath)) {
            if (strpos($imagePath, '/uploads/') === 0) {
                $imagePath = 'uploads/' . substr($imagePath, 8);
            }
        } else {
            $imagePath = 'assets/media/images/index/product1-1e853d.png';
        }
        $orderItemsMap[$orderId]['images'][] = $imagePath;
    }
}

?>

<!-- ORDERS HERO START -->
<section class="catalog_hero">
    <h1>МОИ ЗАКАЗЫ</h1>
</section>
<!-- ORDERS HERO END -->

<!-- PROFILE CONTENT START -->
<div class="profile_content container">
    <div class="profile_layout">
        <!-- PROFILE NAVIGATION START -->
        <?php include('./includes/left_menu.php'); ?>
        <!-- PROFILE NAVIGATION END -->

        <!-- ORDERS LIST START -->
        <div class="orders_section">
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $index => $order): ?>
                    <div class="order_item">
                        <div class="order_sku">№ <?=htmlspecialchars($order['order_number'] ?? $order['id'])?></div>
                        <div class="order_header">
                            <?php
                            $statusRaw = $order['status'] ?? null;
                            if (is_numeric($statusRaw)) {
                                $statusRaw = (string)$statusRaw;
                            }
                            $statusLabel = $orderStatusMap[$statusRaw] ?? ($statusRaw ?: 'Статус не указан');
                            ?>
                            <?php
                            $orderDateStr = '';
                            if (!empty($order['created_at'])) {
                                $orderDate = DateTime::createFromFormat('Y-m-d H:i:s', $order['created_at']);
                                if (!$orderDate) {
                                    $timestamp = strtotime($order['created_at']);
                                    if ($timestamp !== false) {
                                        $orderDate = new DateTime();
                                        $orderDate->setTimestamp($timestamp);
                                    }
                                }
                                if (!empty($orderDate)) {
                                    $orderDateStr = $orderDate->format('d.m.Y H:i');
                                }
                            }
                            ?>
                            <div class="order_date">
                                <?= $orderDateStr ?>
                            </div>
                            <div class="order_status">
                                <?= htmlspecialchars($statusLabel) ?>
                            </div>
                            <div class="order_price">
                                <?= number_format((float)$order['total_amount'], 0, ',', ' ') ?> ₽
                            </div>
                        </div>

                        <div class="order_content">
                            <div class="order_images">
                                <?php
                                $images = $orderItemsMap[$order['id']]['images'] ?? [];
                                $images = array_slice(array_unique($images), 0, 3);
                                ?>
                                <?php if (!empty($images)): ?>
                                    <?php foreach ($images as $imagePath): ?>
                                        <img src="<?=$imagePath?>" alt="Изображение товара">
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <img src="assets/media/images/index/product1-1e853d.png" alt="Изображение товара">
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="order_address">
                            Адрес доставки: <?=htmlspecialchars($order['adress'] ?? 'Не указан')?>
                        </div>
                    </div>

                    <?php if ($index < count($orders) - 1): ?>
                        <div class="order_separator"></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p>У вас пока нет оформленных заказов.</p>
            <?php endif; ?>
        </div>
        <!-- ORDERS LIST END -->
    </div>
</div>
<!-- PROFILE CONTENT END -->