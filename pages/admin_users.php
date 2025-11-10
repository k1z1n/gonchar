<?php

$sql = "SELECT * FROM users";
$stmt = $database->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll(2);


?>

<div class="admin-container container mt-105">

    <?php include('./includes/admin_menu.php'); ?>

    <!-- Основной контент -->
    <main class="admin-main-content">
        <div class="admin-header">
            <h2 class="admin-page-title">Управление пользователями</h2>
            <a href="?exit">Выйти</a>
        </div>

        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Имя</th>
                    <th>Email</th>
                    <th>Телефон</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?=$user['id'] ?></td>
                    <td><?=$user['surname'] . " " . $user['username'] ?></td>
                    <td><?=$user['email'] ?></td>
                    <td><?=$user['phone'] ?></td>
                    <td><span class="admin-status-badge admin-status-new">Активен</span></td>
                    <td>
                        <button class="admin-btn admin-btn-danger">Заблокировать</button>
                    </td>
                </tr>
                <?php endforeach; ?>

                </tbody>
            </table>
        </div>

        <!-- Mobile user cards -->
        <div class="admin-user-cards">
            <?php foreach ($users as $user): ?>
            <div class="admin-user-card">
                <div class="admin-user-card-header">
                    <span class="admin-user-id">ID: <?=$user['id'] ?></span>
                    <span class="admin-user-status admin-status-badge admin-status-new">Активен</span>
                </div>
                <div class="admin-user-info">
                    <div class="admin-user-info-item">
                        <span class="admin-user-info-label">Имя:</span>
                        <?=$user['surname'] . " " . $user['username'] ?>
                    </div>
                    <div class="admin-user-info-item">
                        <span class="admin-user-info-label">Email:</span>
                        <?=$user['email'] ?>
                    </div>
                    <div class="admin-user-info-item">
                        <span class="admin-user-info-label">Телефон:</span>
                        <?=$user['phone'] ?>
                    </div>
                </div>
                <div class="admin-user-actions">
                    <button class="admin-btn admin-btn-danger">Заблокировать</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>
