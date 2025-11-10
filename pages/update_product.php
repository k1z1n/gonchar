<?php
header('Content-Type: application/json');

// Подключаемся к БД
$host = 'localhost';
$dbname = 'goncharok';
$username = 'root';
$password = '';

try {
    $database = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Ошибка подключения к БД']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$productId = (int)($_POST['product_id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$categoryId = (int)($_POST['category_id'] ?? 0);
$price = (float)($_POST['price'] ?? 0);
$description = trim($_POST['content'] ?? '');
$stock = (int)($_POST['count'] ?? 0);

// Валидация на сервере
$errors = [];

if ($productId <= 0) {
    $errors[] = 'Неверный ID товара';
}

if (empty($title)) {
    $errors[] = 'Название товара обязательно для заполнения';
}

if ($categoryId <= 0) {
    $errors[] = 'Категория обязательна для выбора';
}

if ($price <= 0) {
    $errors[] = 'Цена должна быть больше 0';
}

if ($stock < 0) {
    $errors[] = 'Количество не может быть отрицательным';
}

if (empty($description)) {
    $errors[] = 'Описание товара обязательно для заполнения';
}

// Валидация характеристик
if (isset($_POST['characteristic']) && is_array($_POST['characteristic'])) {
    foreach ($_POST['characteristic'] as $charId => $value) {
        if (empty(trim($value))) {
            $errors[] = 'Все характеристики должны быть заполнены';
            break; // Достаточно одной ошибки
        }
    }
}

// Если есть ошибки - возвращаем их
if (!empty($errors)) {
    echo json_encode(['error' => implode(', ', $errors)]);
    exit;
}

try {
    $sql = "SELECT id FROM products WHERE id = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$productId]);
    $existingProduct = $stmt->fetch();

    if (!$existingProduct) {
        echo json_encode(['error' => 'Товар не найден']);
        exit;
    }

    $database->beginTransaction();

    // 1. Обновляем основной товар
    $sql = "UPDATE products SET title = ?, category_id = ?, price = ?, content = ?, count = ? WHERE id = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$title, $categoryId, $price, $description, $stock, $productId]);

    // 2. Обновляем характеристики товара
    // Сначала удаляем старые
    $sql = "DELETE FROM product_characteristics WHERE product_id = ?";
    $stmt = $database->prepare($sql);
    $stmt->execute([$productId]);

    // Затем добавляем новые
    if (isset($_POST['characteristic']) && is_array($_POST['characteristic'])) {
        $sql = "INSERT INTO product_characteristics (product_id, characteristic_id, value) VALUES (?, ?, ?)";
        $stmt = $database->prepare($sql);

        foreach ($_POST['characteristic'] as $charId => $value) {
            $value = trim($value);
            if (!empty($value)) {
                $stmt->execute([$productId, (int)$charId, $value]);
            }
        }
    }

    // 3. Обновляем изображения (если загружены новые)
    if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
        $uploadedCount = 0;
        $validFiles = [];

        foreach ($_FILES['images']['name'] as $i => $name) {
            if (!empty($name) && $_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (in_array($ext, $allowed)) {
                    $uploadedCount++;
                    $validFiles[] = $i;
                }
            }
        }

        // Если загружаются новые изображения, должно быть ровно 4
        if ($uploadedCount > 0 && $uploadedCount !== 4) {
            throw new Exception('Необходимо загрузить ровно 4 изображения');
        }

        // Если есть валидные файлы - обрабатываем
        if (!empty($validFiles)) {
            // Удаляем старые изображения
            $sql = "SELECT path FROM images WHERE product_id = ?";
            $stmt = $database->prepare($sql);
            $stmt->execute([$productId]);
            $oldImages = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Удаляем файлы с сервера
            foreach ($oldImages as $oldImagePath) {
                if (!empty($oldImagePath)) {
                    $fullPath = $_SERVER['DOCUMENT_ROOT'] . $oldImagePath;
                    if (file_exists($fullPath)) {
                        unlink($fullPath);
                    }
                }
            }

            // Удаляем записи из БД
            $sql = "DELETE FROM images WHERE product_id = ?";
            $stmt = $database->prepare($sql);
            $stmt->execute([$productId]);

            // Загружаем новые изображения
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
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
                    $stmt->execute(['/uploads/' . $filename, $productId]);
                } else {
                    throw new Exception('Ошибка загрузки файла: ' . $_FILES['images']['name'][$i]);
                }
            }
        }
    }

    $database->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Товар успешно обновлен',
        'product_id' => $productId
    ]);

} catch (Exception $e) {
    if (isset($database) && $database->inTransaction()) {
        $database->rollBack();
    }
    echo json_encode(['error' => 'Ошибка: ' . $e->getMessage()]);
}

exit;
?>