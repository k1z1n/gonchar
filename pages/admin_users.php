<?php

$sql = "SELECT * FROM users";
$stmt = $database->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll();


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
                <tr>
                    <?php foreach ($users as $user): ?>
                    <td><?=$user['id'] ?></td>
                    <td><?=$user['surname'] . " " . $user['username'] ?></td>
                    <td><?=$user['email'] ?>/td>
                    <td><?=$user['phone'] ?></td>
                    <td><span class="admin-status-badge admin-status-new">Активен</span></td>
                    <td>
                        <button class="admin-btn admin-btn-danger">Заблокировать</button>
                    </td>
                    <?php endforeach; ?>
                </tr>


                </tbody>
            </table>
        </div>
    </main>
</div>
