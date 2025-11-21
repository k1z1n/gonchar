<?php

if(isset($_SESSION['user_id'])) {
    if($USER['role'] !== 'admin') {
        header('Location: ./?page=login');
    }
}

// Обработка пересчета количества товаров во всех категориях
if (isset($_GET['recalculate']) && $_GET['recalculate'] == '1') {
    $sql = "SELECT id FROM category";
    $stmt = $database->prepare($sql);
    $stmt->execute();
    $allCategories = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($allCategories as $categoryId) {
        // Пересчитываем количество товаров в категории
        $sql = "SELECT COALESCE(SUM(count), 0) FROM products WHERE category_id = ?";
        $stmt = $database->prepare($sql);
        $stmt->execute([$categoryId]);
        $categoryTotal = (int)$stmt->fetchColumn();
        
        // Обновляем количество в категории
        $sql = "UPDATE category SET count = ? WHERE id = ?";
        $stmt = $database->prepare($sql);
        $stmt->execute([$categoryTotal, $categoryId]);
    }
    
    header("Location: ./?page=admin_categories");
    exit;
}

$sql = "SELECT * FROM category";
$stmt = $database->prepare($sql);
$stmt->execute();
$categories = $stmt->fetchAll(2);


?>

<div class="admin-container container">
    <!-- Сайдбар -->
    <?php include('./includes/admin_menu.php');?>

    <!-- Основной контент -->
    <main class="admin-main-content">
        <div class="admin-header">
            <h2 class="admin-page-title">Управление категориями</h2>
            <div>
                <a href="./?page=admin_categories&recalculate=1" class="admin-btn admin-btn-secondary" onclick="return confirm('Пересчитать количество товаров во всех категориях?');">Пересчитать количество</a>
                <a href="./?page=admin_add_category" class="admin-btn">Добавить категорию</a>
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
