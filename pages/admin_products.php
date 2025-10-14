<?php


?>


<div class="admin-container container">

    <?php include('./includes/admin_menu.php'); ?>

    <!-- Основной контент -->
    <main class="admin-main-content">
        <div class="admin-header">
            <h2 class="admin-page-title">Управление товарами</h2>
            <div>
                <button class="btn_bg" onclick="window.location.href='admin_add_product.html'">Добавить товар</button>
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
                            <button class="admin-btn admin-btn-secondary edit-product" data-id="1">Редактировать</button>
                            <button class="admin-btn admin-btn-danger">Удалить</button>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td><img src="assets/media/images/index/product2-1dad5f.png" alt="Товар" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;"></td>
                        <td>Ваза "Осенний лес"</td>
                        <td>Вазы</td>
                        <td>2,500₽</td>
                        <td>5 шт.</td>
                        <td>
                            <button class="admin-btn admin-btn-secondary edit-product" data-id="2">Редактировать</button>
                            <button class="admin-btn admin-btn-danger">Удалить</button>
                        </td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td><img src="assets/media/images/index/product3.png" alt="Товар" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;"></td>
                        <td>Чашка "Утро"</td>
                        <td>Чашки</td>
                        <td>800₽</td>
                        <td>20 шт.</td>
                        <td>
                            <button class="admin-btn admin-btn-secondary edit-product" data-id="3">Редактировать</button>
                            <button class="admin-btn admin-btn-danger">Удалить</button>
                        </td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td><img src="assets/media/images/index/product1-1e853d.png" alt="Товар" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;"></td>
                        <td>Блюдо "Розы"</td>
                        <td>Тарелки</td>
                        <td>1,500₽</td>
                        <td>8 шт.</td>
                        <td>
                            <button class="admin-btn admin-btn-secondary edit-product" data-id="4">Редактировать</button>
                            <button class="admin-btn admin-btn-danger">Удалить</button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
