<?php

if(isset($_SESSION['user_id'])) {
    if($USER['role'] !== 'admin') {
        header('Location: ./?page=login');
    }
}

$sql_orders = "SELECT * FROM orders";
$stmt_orders = $database->prepare($sql_orders);
$stmt_orders->execute();
$orders = $stmt_orders->fetchAll(2);

$stmt_statuses = $database->prepare("SHOW COLUMNS FROM orders LIKE 'status'");
$stmt_statuses->execute();
$status_column = $stmt_statuses->fetch(2);
$statuses = [];

if ($status_column && isset($status_column['Type'])) {
    preg_match_all("/'([^']+)'/", $status_column['Type'], $matches);
    $statuses = $matches[1];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : null;
    $statusValue = $_POST['status'] ?? null;

    if ($orderId && $statusValue && in_array($statusValue, $statuses, true)) {
        $stmt_update = $database->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt_update->execute([$statusValue, $orderId]);
    }

    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

?>

<div class="admin-container container">

    <?php include('./includes/admin_menu.php'); ?>

    <main class="admin-main-content">
        <div class="admin-header">
            <h2 class="admin-page-title">Управление заказами</h2>
            <a href="?exit">Выйти</a>
        </div>

        <div class="admin-tabs">
            <div class="admin-tab active" data-tab="orders-list">Список заказов</div>
        </div>

        <div id="orders-list" class="tab-content active">
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Клиент</th>
                        <th>Дата</th>
                        <th>Сумма</th>
                        <th>Статус</th>
                        <th></th>
                        <th>Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?=$order['id'] ?></td>
                        <?php
                        $sql_user = "SELECT * FROM users WHERE id = ?";
                        $stmt_user = $database->prepare($sql_user);
                        $stmt_user->execute([$order['user_id']]);
                        $user = $stmt_user->fetch(2);
                        ?>
                        <td><?=$user['surname'] . $user['username'] ?></td>
                        <td><?=$order['created_at'] ?></td>
                        <td><?= number_format($order['total_amount'], 0, ',', ' ') ?> ₽</td>
                        <td>
                            <select class="status-select" name="status" form="status-form-<?=$order['id'] ?>">
                                <?php foreach ($statuses as $status_option): ?>
                                    <option value="<?=$status_option ?>" <?=$status_option === $order['status'] ? 'selected' : '' ?>>
                                        <?=$status_option ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <form id="status-form-<?=$order['id'] ?>" method="post">
                                <input type="hidden" name="order_id" value="<?=$order['id'] ?>">
                                <button class="admin-btn admin-btn-primary save-status" type="submit">Сохранить</button>
                            </form>
                        </td>
                        <td>
                            <a class="admin-btn admin-btn-primary view-order" href="./?page=orders_item&&id=<?=$order['id'] ?>">Просмотреть</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    </tbody>
                </table>
            </div>
        </div>


    </main>

</div>
