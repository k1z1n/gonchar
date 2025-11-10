<?php
// Подключаемся к БД
$host = 'localhost';
$dbname = 'goncharok';
$username = 'root';
$password = '';

try {
    $database = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
} catch (PDOException $e) {
    echo '<p class="text-muted">Для этой категории нет характеристик</p>';
    exit;
}

$categoryId = (int)($_GET['category_id'] ?? 0);
$productId = (int)($_GET['product_id'] ?? 0);

if ($categoryId <= 0) {
    echo '<p class="text-muted">Выберите категорию</p>';
    exit;
}

// Запрос характеристик категории
$sql = "SELECT id, name, content FROM characteristics WHERE category_id = ? ORDER BY id ASC";
$stmt = $database->prepare($sql);
$stmt->execute([$categoryId]);
$characteristics = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Загружаем сохраненные значения характеристик (если редактируем товар)
$savedValues = [];
if ($productId > 0) {
    $sql = "SELECT characteristic_id, value FROM product_characteristics WHERE product_id = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$productId]);
    $savedValues = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

if (empty($characteristics)) {
    echo '<p class="text-muted">Для этой категории нет характеристик</p>';
} else {
    foreach ($characteristics as $char) {
        $savedValue = $savedValues[$char['id']] ?? '';
        echo '<div class="admin-form-group">';
        echo '<label for="char_' . $char['id'] . '">' . htmlspecialchars($char['name']) . '</label>';
        echo '<input type="text" id="char_' . $char['id'] . '" 
                     name="characteristic[' . $char['id'] . ']" 
                     value="' . htmlspecialchars($savedValue) . '" 
                     placeholder="' . htmlspecialchars($char['content'] ?? '') . '">';
        echo '</div>';
    }
}
?>