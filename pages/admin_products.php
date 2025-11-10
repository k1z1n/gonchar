<?php

$sql_products = "SELECT * FROM products";
$stmt_products = $database->prepare($sql_products);
$stmt_products->execute();
$products = $stmt_products->fetchAll(2);





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
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?=$product['id'] ?></td>
                        <td>
                            <?php
                            // Получаем первое изображение товара
                            $sql_image = "SELECT path FROM images WHERE product_id = ? ORDER BY id ASC LIMIT 1";
                            $stmt_image = $database->prepare($sql_image);
                            $stmt_image->execute([$product['id']]);
                            $image = $stmt_image->fetch(2);
                            
                            if ($image && !empty($image['path'])) {
                                $imagePath = $image['path'];
                            } else {
                                $imagePath = 'assets/media/images/index/product1-1e853d.png'; // Изображение по умолчанию
                            }
                            ?>
                            <img src="<?=$imagePath?>" alt="<?=htmlspecialchars($product['title'])?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                        </td>
                        <td><?=$product['title'] ?></td>
                        <?php
                            $sql_category = "SELECT * FROM category WHERE id = {$product['category_id']}";
                            $stmt_category = $database->prepare($sql_category);
                            $stmt_category->execute();
                            $category = $stmt_category->fetch(2);
                        ?>

                        <td><?=$category['title'] ?></td>
                        <td><?=$product['price'] ?>₽</td>
                        <td><?=$product['count'] ?> шт.</td>
                        <td>
                            <a href="./?page=admin_edit_product&id=<?=$product['id']?>" class="admin-btn admin-btn-secondary edit-product">Редактировать</a>
                            <a href="./?page=admin_delete_product&&id=<?=$product['id'] ?>" class="admin-btn admin-btn-danger">Удалить</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
