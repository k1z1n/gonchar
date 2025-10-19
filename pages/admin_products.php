<?php


?>


<div class="admin-container container">

    <?php include('./includes/admin_menu.php'); ?>

    <!-- Основной контент -->
    <main class="admin-main-content">
        <div class="admin-header">
            <h2 class="admin-page-title">Управление товарами</h2>
            <div>
                <button class="btn_bg" onclick="window.location.href='./?page=admin_add_product'">Добавить товар</button>
                <a href="?exit">Выйти</a>
            </div>
        </div>

        <div id="products-list" class="tab-content active">
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Изображение</th>
                        <th>Название</th>
                        <th>Категория</th>
                        <th>Цена</th>
                        <th>Наличие</th>
                        <th>Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>1</td>
                        <td><img src="assets/media/images/index/product1-1e853d.png" alt="Товар" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;"></td>
                        <td>Керамическая тарелка "Цветок"</td>
                        <td>Тарелки</td>
                        <td>1,200₽</td>
                        <td>12 шт.</td>
                        <td>
                            <a href="./?page=admin_edit_product&&id" class="admin-btn admin-btn-secondary edit-product">Редактировать</a>
                            <a href="./?page=admin_delete_product&&id" class="admin-btn admin-btn-danger">Удалить</a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
