<?php

?>


<div class="admin-container container">
    <!-- Сайдбар -->
    <?php include_once('./includes/admin_menu.php'); ?>

    <!-- Основной контент -->
    <main class="admin-main-content">
        <div class="admin-header">
            <h2 class="admin-page-title">Добавить категорию</h2>
            <a href="?exit">Выйти</a>
        </div>

        <div class="admin-form-container">
            <form id="categoryForm">
                <div class="admin-form-group">
                    <label for="categoryName">Название категории</label>
                    <input type="text" id="categoryName" required>
                </div>
                <div class="admin-form-group">
                    <label for="categoryDescription">Описание</label>
                    <textarea id="categoryDescription"></textarea>
                </div>
                <div class="admin-form-actions">
                    <a href="./?page=admin_categories" class="admin-btn admin-btn-secondary">Отмена</a>
                    <button type="submit" class="admin-btn admin-btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </main>
</div>
