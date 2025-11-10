<?php

$errors = [];

if(isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
} else {
    $errors[] = 'Товар с таким id не найден';
}

$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $database->prepare($sql);
$stmt->execute([$id]);
$product = $stmt->fetch(2);

$sql_images = "SELECT * FROM images WHERE product_id = ?";
$stmt_images = $database->prepare($sql_images);
$stmt_images->execute([$id]);
$images = $stmt_images->fetchAll(2);

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach($images as $image) {
        unlink('./' . $image['path']);
    }
    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$id]);
    header("Location: ./?page=admin_products");
}


?>

<div class="admin-container container">

    <?php include_once('./includes/admin_menu.php'); ?>

    <main class="admin-main-content">
        <div class="admin-header">
            <h2 class="admin-page-title">Удалить товар</h2>
            <a href="?exit">Выйти</a>
        </div>

        <div class="admin-form-container">
            <form action="" method="post">

                <!-- Превью удаляемого товара (пример данных) -->
                <div class="delete-product-card">
                    <img class="delete-product-thumb" src="assets/media/images/index/product1-1e853d.png" alt="Изображение товара">
                    <div class="delete-product-meta">
                        <div class="delete-field">
                            <?php
                            $sql_category = "SELECT * FROM category WHERE id = " . $product['category_id'];
                            $stmt_category = $database->prepare($sql_category);
                            $stmt_category->execute();
                            $category = $stmt_category->fetch(2);
                            ?>
                            <span class="delete-field-label">Категория</span>
                            <div><strong><?=$category['title'] ?></strong></div>
                        </div>
                        <div class="delete-field">
                            <span class="delete-field-label">Наименование</span>
                            <h3><?=$product['title'] ?></h3>
                        </div>
                        <div class="delete-field">
                            <span class="delete-field-label">Стоимость</span>
                            <div><strong><?=$product['price'] ?> ₽</strong></div>
                        </div>
                        <div class="delete-field">
                            <span class="delete-field-label">Описание</span>
                            <div><?=$product['content'] ?></div>
                        </div>
                        <div class="delete-field">
                            <span class="delete-field-label">Артикул</span>
                            <div><strong><?=$product['article'] ?></strong></div>
                        </div>
                        <div class="delete-field">
                            <span class="delete-field-label">Количество на складе</span>
                            <div><strong><?=$product['count'] ?> шт.</strong></div>
                        </div>
                    </div>
                </div>

                <div class="delete-warning">
                    <p><strong>Вы уверены, что хотите удалить этот товар?</strong></p>
                    <p>Действие необратимо. Товар будет удалён из каталога и больше не будет доступен пользователям.</p>
                </div>

                <div class="delete-actions">
                    <a href="./?page=admin_products" class="admin-btn admin-btn-secondary">Отмена</a>
                    <button type="submit" class="admin-btn admin-btn-danger">Удалить</button>
                </div>
            </form>

        </div>
    </main>

</div>


