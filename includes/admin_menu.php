

<?php $currentPage = $_GET['page'] ?? 'main'; ?>

<!-- Сайдбар -->
<aside class="admin-sidebar">
    <div class="admin-logo">
        <h1>Гончарная студия</h1>
        <p>Админ-панель</p>
    </div>
    <ul class="admin-nav-menu">
        <li class="admin-nav-item">
            <a href="./?page=admin_statistics" class="admin-nav-link <?=$currentPage === 'admin_statistics' ? 'active' : ''?>">Статистика</a>
        </li>
        <li class="admin-nav-item">
            <a href="./?page=admin_users" class="admin-nav-link <?=$currentPage === 'admin_users' ? 'active' : ''?>">Пользователи</a>
        </li>
        <li class="admin-nav-item">
            <a href="./?page=admin_products" class="admin-nav-link <?=$currentPage === 'admin_products' ? 'active' : ''?>">Товары</a>
        </li>
        <li class="admin-nav-item">
            <a href="./?page=admin_categories" class="admin-nav-link <?=$currentPage === 'admin_categories' ? 'active' : ''?>">Категории</a>
        </li>
        <li class="admin-nav-item">
            <a href="./?page=admin_orders" class="admin-nav-link <?=$currentPage === 'admin_orders' ? 'active' : ''?>">Заказы</a>
        </li>
        <li class="admin-nav-item">
            <a href="./?page=admin_promocodes" class="admin-nav-link <?=$currentPage === 'admin_promocodes' ? 'active' : ''?>">Промокоды</a>
        </li>
        <li class="admin-nav-item">
            <?php
            $isRequestsPage = in_array($currentPage, ['admin_masterclass_requests', 'admin_masterclass_request_edit'], true);
            ?>
            <a href="./?page=admin_masterclass_requests" class="admin-nav-link <?=$isRequestsPage ? 'active' : ''?>">Записи</a>
        </li>
    </ul>
</aside>
