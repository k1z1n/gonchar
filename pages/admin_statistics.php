<?php

if(isset($_SESSION['user_id'])) {
    if($USER['role'] !== 'admin') {
        header('Location: ./?page=login');
        exit;
    }
}

// Статистика по заказам
$sql_orders_count = "SELECT COUNT(*) as count FROM orders";
$stmt_orders_count = $database->prepare($sql_orders_count);
$stmt_orders_count->execute();
$orders_count = $stmt_orders_count->fetch(2)['count'];

$sql_total_revenue = "SELECT COALESCE(SUM(total_amount), 0) as total FROM orders";
$stmt_total_revenue = $database->prepare($sql_total_revenue);
$stmt_total_revenue->execute();
$total_revenue = $stmt_total_revenue->fetch(2)['total'];

// Статистика по пользователям
$sql_users_count = "SELECT COUNT(*) as count FROM users";
$stmt_users_count = $database->prepare($sql_users_count);
$stmt_users_count->execute();
$users_count = $stmt_users_count->fetch(2)['count'];

// Статистика по товарам
$sql_products_count = "SELECT COUNT(*) as count FROM products";
$stmt_products_count = $database->prepare($sql_products_count);
$stmt_products_count->execute();
$products_count = $stmt_products_count->fetch(2)['count'];

// Статистика по категориям
$sql_categories_count = "SELECT COUNT(*) as count FROM category";
$stmt_categories_count = $database->prepare($sql_categories_count);
$stmt_categories_count->execute();
$categories_count = $stmt_categories_count->fetch(2)['count'];

?>

<div class="admin-container container mt-105">
    <?php include('./includes/admin_menu.php'); ?>

    <main class="admin-main-content">
        <div class="admin-header">
            <h2 class="admin-page-title">Статистика</h2>
            <a href="?exit">Выйти</a>
        </div>

        <!-- Статистика -->
        <div class="admin-stats-cards">
            <div class="admin-stat-card">
                <div class="admin-stat-value"><?= number_format($orders_count, 0, ',', ' ') ?></div>
                <div class="admin-stat-label">Всего заказов</div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-value"><?= number_format($total_revenue, 0, ',', ' ') ?> ₽</div>
                <div class="admin-stat-label">Общая выручка</div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-value"><?= number_format($users_count, 0, ',', ' ') ?></div>
                <div class="admin-stat-label">Пользователей</div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-value"><?= number_format($products_count, 0, ',', ' ') ?></div>
                <div class="admin-stat-label">Товаров в каталоге</div>
            </div>
            <div class="admin-stat-card">
                <div class="admin-stat-value"><?= number_format($categories_count, 0, ',', ' ') ?></div>
                <div class="admin-stat-label">Категорий</div>
            </div>
        </div>

    </main>
</div>

