<?php

if(isset($_SESSION['user_id'])) {
    if($USER['role'] !== 'admin') {
        echo "<script>document.location.href='./?page=login';</script>";
    }
}

$productId = $_GET['id'] ?? 0;

if (!$productId) {
    echo "<script>document.location.href='./?page=admin_products';</script>";
    exit;
}

// Загружаем данные товара
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $database->prepare($sql);
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    echo "<script>document.location.href='./?page=admin_products';</script>";
    exit;
}

// Загружаем категории
$sql_categories = "SELECT * FROM category";
$stmt_categories = $database->prepare($sql_categories);
$stmt_categories->execute();
$categories = $stmt_categories->fetchAll();

// Загружаем существующие изображения товара
$sql = "SELECT path FROM images WHERE product_id = ? ORDER BY id ASC";
$stmt = $database->prepare($sql);
$stmt->execute([$productId]);
$existingImages = $stmt->fetchAll(PDO::FETCH_COLUMN);

$currentCategoryId = $product['category_id'];
?>

<div class="admin-container container">
    <?php include('./includes/admin_menu.php');?>

    <main class="admin-main-content">
        <div class="admin-header">
            <h2 class="admin-page-title">Редактирование товара</h2>
            <a href="?exit">Выйти</a>
        </div>

        <div class="admin-form-container">
            <form id="productForm" method="post" action="?page=update_product" enctype="multipart/form-data">
                <input type="hidden" name="product_id" value="<?=$productId?>">

                <!-- Сообщения -->
                <div id="errorContainer" class="errors-container" style="display: none;">
                    <strong>Ошибки:</strong>
                    <ul id="errorList"></ul>
                </div>

                <div id="successContainer" style="display: none; padding: 15px; margin-bottom: 20px; background-color: #efe; border: 1px solid #cfc; border-radius: 4px; color: #3c3;">
                    <strong id="successMessage"></strong>
                </div>

                <!-- Основные поля -->
                <div class="admin-form-group">
                    <label for="productName">Название товара *</label>
                    <input type="text" id="productName" name="title" value="<?=htmlspecialchars($product['title'])?>" required>
                    <div class="field-error" style="display: none; color: #d00; font-size: 12px; margin-top: 5px;"></div>
                </div>

                <div class="admin-form-group">
                    <label for="productCategory">Категория *</label>
                    <select id="productCategory" name="category_id" required>
                        <option value="">Выберите категорию</option>
                        <?php foreach ($categories as $category) : ?>
                            <option value="<?=$category['id']?>" <?= $category['id'] == $currentCategoryId ? 'selected' : '' ?>>
                                <?=htmlspecialchars($category['title'])?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="field-error" style="display: none; color: #d00; font-size: 12px; margin-top: 5px;"></div>
                </div>

                <div class="admin-form-group">
                    <label for="productPrice">Цена *</label>
                    <input type="number" id="productPrice" name="price" value="<?=$product['price']?>" required min="0" step="0.01">
                    <div class="field-error" style="display: none; color: #d00; font-size: 12px; margin-top: 5px;"></div>
                </div>

                <div class="admin-form-group">
                    <label for="productStock">Количество на складе *</label>
                    <input type="number" id="productStock" name="count" value="<?=$product['count']?>" required min="0">
                    <div class="field-error" style="display: none; color: #d00; font-size: 12px; margin-top: 5px;"></div>
                </div>

                <div class="admin-form-group">
                    <label for="productDescription">Описание *</label>
                    <textarea id="productDescription" name="content" required><?=htmlspecialchars($product['content'] ?? '')?></textarea>
                    <div class="field-error" style="display: none; color: #d00; font-size: 12px; margin-top: 5px;"></div>
                </div>

                <!-- Изображения -->
                <div class="admin-form-group">
                    <label for="productImages">Изображения товара</label>

                    <!-- Существующие изображения -->
                    <?php if (!empty($existingImages)): ?>
                        <div style="margin-bottom: 15px;">
                            <label style="font-size: 14px; color: #666;">Текущие изображения:</label>
                            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 10px;">
                                <?php foreach ($existingImages as $imagePath): ?>
                                    <div style="border: 2px solid #ddd; border-radius: 8px; overflow: hidden;">
                                        <img src="<?=$imagePath?>" alt="Изображение товара" style="width: 100%; height: 150px; object-fit: cover;">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Новые изображения -->
                    <label style="font-size: 14px; color: #666;">Новые изображения (заменят текущие):</label>
                    <input type="file" id="productImages" name="images[]" accept="image/*" multiple>
                    <small style="display: block; color: #666; margin-top: 5px;">Если загружаете новые - необходимо выбрать ровно 4 изображения</small>
                    <div class="field-error" style="display: none; color: #d00; font-size: 12px; margin-top: 5px;"></div>
                    <div id="imagePreviews" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 15px;"></div>
                </div>

                <!-- Характеристики -->
                <div id="characteristicsContainer">
                    <div id="characteristicsLoading">Загрузка характеристик...</div>
                </div>

                <div class="admin-form-actions">
                    <a href="./?page=admin_products" class="admin-btn admin-btn-secondary">Отмена</a>
                    <button type="submit" class="admin-btn admin-btn-primary">Сохранить изменения</button>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
    // Элементы
    const productCategory = document.getElementById('productCategory');
    const productImages = document.getElementById('productImages');
    const imagePreviews = document.getElementById('imagePreviews');
    const characteristicsContainer = document.getElementById('characteristicsContainer');
    const characteristicsLoading = document.getElementById('characteristicsLoading');
    const errorContainer = document.getElementById('errorContainer');
    const errorList = document.getElementById('errorList');
    const successContainer = document.getElementById('successContainer');
    const successMessage = document.getElementById('successMessage');
    const currentCategoryId = <?=$currentCategoryId?>;
    const productId = <?=$productId?>;

    // Показать ошибку у поля (только визуальное выделение)
    function showFieldError(message, field) {
        const fieldError = field.parentNode.querySelector('.field-error');
        if (fieldError) {
            fieldError.textContent = message;
            fieldError.style.display = 'block';
            field.style.borderColor = '#d00';
        }
    }

    // Показать список всех ошибок в верхнем блоке
    function showErrorsList(errors) {
        errorList.innerHTML = '';
        errors.forEach(error => {
            const li = document.createElement('li');
            li.textContent = error;
            errorList.appendChild(li);
        });
        errorContainer.style.display = 'block';
        successContainer.style.display = 'none';
        errorContainer.scrollIntoView({ behavior: 'smooth' });
    }

    // Показать одну ошибку (для бэкенда)
    function showError(message) {
        errorList.innerHTML = '<li>' + message + '</li>';
        errorContainer.style.display = 'block';
        successContainer.style.display = 'none';
        errorContainer.scrollIntoView({ behavior: 'smooth' });
    }

    // Очистить ошибки поля
    function clearFieldError(field) {
        const fieldError = field.parentNode.querySelector('.field-error');
        if (fieldError) {
            fieldError.style.display = 'none';
            field.style.borderColor = '';
        }
    }

    // Показать успех
    function showSuccess(message) {
        successMessage.textContent = message;
        successContainer.style.display = 'block';
        errorContainer.style.display = 'none';
        successContainer.scrollIntoView({ behavior: 'smooth' });
    }

    // Скрыть сообщения
    function hideMessages() {
        errorContainer.style.display = 'none';
        successContainer.style.display = 'none';
        // Очищаем все ошибки полей
        document.querySelectorAll('.field-error').forEach(error => {
            error.style.display = 'none';
        });
        document.querySelectorAll('input, select, textarea').forEach(field => {
            field.style.borderColor = '';
        });
    }

    // Валидация формы
    function validateForm() {
        let isValid = true;
        const errors = [];

        // Проверяем обязательные поля
        const title = document.getElementById('productName');
        const category = document.getElementById('productCategory');
        const price = document.getElementById('productPrice');
        const stock = document.getElementById('productStock');
        const description = document.getElementById('productDescription');

        // Очищаем предыдущие ошибки
        [title, category, price, stock, description].forEach(field => {
            clearFieldError(field);
        });

        // Проверка названия
        if (!title.value.trim()) {
            showFieldError('Введите название товара', title);
            errors.push('Название товара обязательно для заполнения');
            isValid = false;
        }

        // Проверка категории
        if (!category.value) {
            showFieldError('Выберите категорию', category);
            errors.push('Категория обязательна для выбора');
            isValid = false;
        }

        // Проверка цены
        if (!price.value || parseFloat(price.value) <= 0) {
            showFieldError('Цена должна быть больше 0', price);
            errors.push('Укажите корректную цену');
            isValid = false;
        }

        // Проверка количества
        if (!stock.value || parseInt(stock.value) < 0) {
            showFieldError('Количество не может быть отрицательным', stock);
            errors.push('Укажите корректное количество');
            isValid = false;
        }

        // Проверка описания
        if (!description.value.trim()) {
            showFieldError('Введите описание товара', description);
            errors.push('Описание товара обязательно для заполнения');
            isValid = false;
        }

        // Проверка изображений (если загружаются новые)
        if (productImages.files.length > 0 && productImages.files.length !== 4) {
            showFieldError('Необходимо выбрать ровно 4 изображения', productImages);
            errors.push('Необходимо выбрать ровно 4 изображения');
            isValid = false;
        }

        // Проверка характеристик (все поля должны быть заполнены)
        const characteristicInputs = characteristicsContainer.querySelectorAll('input[type="text"]');
        let characteristicsValid = true;

        characteristicInputs.forEach((input, index) => {
            if (!input.value.trim()) {
                const fieldName = input.previousElementSibling?.textContent || `Характеристика ${index + 1}`;
                showFieldError('Заполните это поле', input);
                if (characteristicsValid) {
                    errors.push('Все характеристики должны быть заполнены');
                    characteristicsValid = false;
                }
                isValid = false;
            } else {
                clearFieldError(input);
            }
        });

        // Показываем все ошибки в верхнем блоке
        if (errors.length > 0) {
            showErrorsList(errors);
        }

        return { isValid, errors };
    }

    // Загрузка характеристик
    function loadCharacteristics(categoryId, productId = null) {
        if (characteristicsLoading) {
            characteristicsLoading.style.display = 'block';
        }

        const url = productId
            ? `?page=get_characteristics&category_id=${categoryId}&product_id=${productId}`
            : `?page=get_characteristics&category_id=${categoryId}`;

        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Ошибка загрузки');
                }
                return response.text();
            })
            .then(html => {
                characteristicsContainer.innerHTML = html;

                // Добавляем обработчики событий для новых полей характеристик
                const characteristicInputs = characteristicsContainer.querySelectorAll('input[type="text"]');
                characteristicInputs.forEach(input => {
                    input.addEventListener('input', function() {
                        clearFieldError(this);
                        errorContainer.style.display = 'none';
                    });
                });
            })
            .catch(error => {
                console.error('Ошибка загрузки характеристик:', error);
                characteristicsContainer.innerHTML = '<p class="text-muted">Ошибка загрузки характеристик</p>';
            });
    }

    // Загружаем характеристики сразу при загрузке страницы
    document.addEventListener('DOMContentLoaded', function() {
        if (currentCategoryId > 0) {
            loadCharacteristics(currentCategoryId, productId);
        }
    });

    // Обработчик категории
    productCategory.addEventListener('change', function() {
        hideMessages();
        const categoryId = this.value;

        if (!categoryId) {
            characteristicsContainer.innerHTML = '<p class="text-muted">Выберите категорию для загрузки характеристик</p>';
            return;
        }

        loadCharacteristics(categoryId, productId);
    });

    // Обработчик изображений
    productImages.addEventListener('change', function(e) {
        hideMessages();
        const files = Array.from(e.target.files);

        if (files.length > 0 && files.length !== 4) {
            showFieldError('Необходимо выбрать ровно 4 изображения!', this);
            imagePreviews.innerHTML = '';
            return;
        }

        // Показываем превью
        imagePreviews.innerHTML = '';
        files.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.style.position = 'relative';
                div.style.border = '2px solid #4CAF50';
                div.style.borderRadius = '8px';
                div.style.overflow = 'hidden';
                div.innerHTML = `
                <img src="${e.target.result}" alt="Новое изображение ${index + 1}"
                     style="width: 100%; height: 150px; object-fit: cover; display: block;">
                <div style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(76, 175, 80, 0.9); color: white; text-align: center; padding: 5px; font-size: 12px;">
                    Новое ${index + 1}
                </div>
            `;
                imagePreviews.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    });

    // Обработчик формы
    document.getElementById('productForm').addEventListener('submit', function(e) {
        e.preventDefault();
        hideMessages();

        // Валидируем форму
        const validation = validateForm();
        if (!validation.isValid) {
            return;
        }

        // Отправляем форму
        const formData = new FormData(this);

        fetch('?page=update_product', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess('Товар успешно обновлен! Перенаправление...');
                    setTimeout(() => {
                        window.location.href = './?page=admin_products';
                    }, 1500);
                } else {
                    showError(data.error || 'Неизвестная ошибка');
                }
            })
            .catch(error => {
                showError('Ошибка сети: ' + error.message);
            });
    });

    // Валидация при вводе (очищаем ошибки когда пользователь начинает исправлять)
    document.querySelectorAll('input, select, textarea').forEach(field => {
        field.addEventListener('input', function() {
            clearFieldError(this);
            errorContainer.style.display = 'none';
        });
    });
</script>