<?php
// Отключаем вывод ошибок
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Устанавливаем заголовок JSON
header('Content-Type: application/json');

// Подключаемся к БД
include_once(__DIR__ . '/../database/connect.php');

// Проверяем POST запрос
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Получаем данные формы
$title = trim($_POST['title'] ?? '');
$categoryId = (int)($_POST['category_id'] ?? 0);
$price = (float)($_POST['price'] ?? 0);
$description = trim($_POST['content'] ?? '');
$stock = (int)($_POST['count'] ?? 0);

// Валидация обязательных полей
if (empty($title) || $categoryId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Не заполнены обязательные поля: название и категория']);
    exit;
}

// Проверяем что загружено ровно 4 изображения
if (!isset($_FILES['images']) || count($_FILES['images']['name']) !== 4) {
    http_response_code(400);
    echo json_encode(['error' => 'Необходимо загрузить ровно 4 изображения']);
    exit;
}

// Проверяем каждое изображение
$validFiles = [];
foreach ($_FILES['images']['name'] as $i => $name) {
    if (empty($name) || $_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'Все 4 изображения должны быть корректными файлами']);
        exit;
    }

    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($ext, $allowed)) {
        http_response_code(400);
        echo json_encode(['error' => 'Допустимые форматы: JPG, PNG, GIF, WebP']);
        exit;
    }

    $validFiles[] = $i;
}

try {
    $database->beginTransaction();

    // 1. Сохраняем основной товар
    $article = rand(100000, 999999);
    $sql = "INSERT INTO products (title, category_id, price, content, count, article) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $database->prepare($sql);
    $stmt->execute([$title, $categoryId, $price, $description, $stock, $article]);
    $productId = $database->lastInsertId();

    // 2. Сохраняем характеристики товара
    if (isset($_POST['characteristic']) && is_array($_POST['characteristic'])) {
        $sql = "INSERT INTO product_characteristics (product_id, characteristic_id, value) VALUES (?, ?, ?)";
        $stmt = $database->prepare($sql);

        foreach ($_POST['characteristic'] as $charId => $value) {
            $value = trim($value);
            if (!empty($value)) {
                $stmt->execute([$productId, $charId, $value]);
            }
        }
    }

    // 3. Сохраняем изображения
    $uploadDir = __DIR__ . '/../uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $sql = "INSERT INTO images (path, product_id) VALUES (?, ?)";
    $stmt = $database->prepare($sql);

    foreach ($validFiles as $i) {
        $ext = strtolower(pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION));
        $filename = 'product_' . $productId . '_' . $i . '_' . time() . '.' . $ext;
        $uploadPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['images']['tmp_name'][$i], $uploadPath)) {
            $stmt->execute(['uploads/' . $filename, $productId]);
        } else {
            throw new Exception('Ошибка загрузки файла: ' . $_FILES['images']['name'][$i]);
        }
    }

    $database->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Товар успешно добавлен',
        'product_id' => $productId
    ]);

} catch (Exception $e) {
    if (isset($database) && $database->inTransaction()) {
        $database->rollBack();
    }
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка: ' . $e->getMessage()]);
}
?>