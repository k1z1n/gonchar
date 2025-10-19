<?php

?>

<div class="admin-container container">
    <?php include('./includes/admin_menu.php');?>

    <!-- Основной контент -->
    <main class="admin-main-content">
        <div class="admin-header">
            <h2 class="admin-page-title">Добавить товар</h2>
            <a href="?exit">Выйти</a>
        </div>

        <div class="admin-form-container">
            <form id="productForm">
                <div class="admin-form-group">
                    <label for="productName">Название товара</label>
                    <input type="text" id="productName" required>
                </div>
                <div class="admin-form-group">
                    <label for="productCategory">Категория</label>
                    <select id="productCategory" required>
                        <option value="">Выберите категорию</option>
                        <option value="1">Тарелки</option>
                        <option value="2">Вазы</option>
                        <option value="3">Чашки</option>
                        <option value="4">Блюда</option>
                    </select>
                </div>
                <div class="admin-form-group">
                    <label for="productPrice">Цена</label>
                    <input type="number" id="productPrice" required>
                </div>
                <div class="admin-form-group">
                    <label for="productStock">Количество на складе</label>
                    <input type="number" id="productStock" required>
                </div>
                <div class="admin-form-group">
                    <label for="productDescription">Описание</label>
                    <textarea id="productDescription"></textarea>
                </div>
                <div class="admin-form-group">
                    <label for="productImage">Изображение</label>
                    <input type="file" id="productImage" accept="image/*">
                </div>
                <div class="admin-form-actions">
<!--                    <button type="button" class="admin-btn admin-btn-secondary" id="cancelProductBtn">Отмена</button>-->
                    <a href="./?page=admin_products" class="admin-btn admin-btn-secondary">Отмена</a>
                    <button type="submit" class="admin-btn admin-btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </main>
</div>

<!--<script>-->
<!--    // Обработка формы товара-->
<!--    document.getElementById('productForm').addEventListener('submit', function(e) {-->
<!--        e.preventDefault();-->
<!--        alert('Товар добавлен!');-->
<!--        // Перенаправляем обратно на страницу управления товарами-->
<!--        window.location.href = 'admin_products.html';-->
<!--    });-->
<!---->
<!--    // Кнопка отмены-->
<!--    document.getElementById('cancelProductBtn').addEventListener('click', function() {-->
<!--        window.location.href = 'admin_products.html';-->
<!--    });-->
<!--</script>-->
