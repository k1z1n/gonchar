<?php

$sql = "SELECT * FROM category";
$stmt = $database->prepare($sql);
$stmt->execute();
$categories = $stmt->fetchAll(2);


?>

<div class="admin-container container">
    <!-- Сайдбар -->
    <?php include('./includes/admin_menu.php');?> ?>

    <!-- Основной контент -->
    <main class="admin-main-content">
        <div class="admin-header">
            <h2 class="admin-page-title">Управление категориями</h2>
            <div>
                <a href="./?page=admin_add_category" class="btn_bg">Добавить категорию</a>
                <a href="?exit">Выйти</a>
            </div>
        </div>

        <div id="categories-list" class="tab-content active">
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Название</th>
<!--                        <th>Описание</th>-->
                        <th>Количество товаров</th>
                        <th>Действия</th>
                    </tr>
                    </thead>
                    <tbody>

                        <?php foreach($categories as $category): ?>
                        <tr>
                        <td><?=$category['id'] ?></td>
                        <td><?=$category['title'] ?></td>
<!--                        <td>Керамические тарелки различных размеров</td>-->
                        <td><?=$category['count'] ?></td>
                        <td>
                            <a href="./?page=admin_edit_category&&id=<?=$category['id'] ?>" class="admin-btn admin-btn-secondary edit-category">Редактировать</a>
                            <a href="./?page=admin_delete_category&&id=<?=$category['id'] ?>" class="admin-btn admin-btn-danger">Удалить</a>
                        </td>
                        </tr>
                        <?php endforeach; ?>

                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
