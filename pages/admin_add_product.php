<?php

if(isset($_SESSION['user_id'])) {
    if($USER['role'] !== 'admin') {
        echo "<script>document.location.href='./?page=login';</script>";
    }
}

$sql_categories = "SELECT * FROM category";
$stmt_categories = $database->prepare($sql_categories);
$stmt_categories->execute();
$categories = $stmt_categories->fetchAll();
?>

<div class="admin-container container">
    <?php include('./includes/admin_menu.php'); ?>

    <main class="admin-main-content">
        <div class="admin-header">
            <h2 class="admin-page-title">Добавить товар</h2>
            <a href="?exit">Выйти</a>
        </div>

        <div class="admin-form-container">
            <form id="productForm" method="post" action="?page=save_product" enctype="multipart/form-data">
                <!-- Сообщения об ошибках -->
                <div id="errorContainer" class="errors-container" style="display: none;">
                    <strong>Ошибка:</strong>
                    <span id="errorText"></span>
                </div>

                <!-- Сообщения об успехе -->
                <div id="successContainer" style="display: none; padding: 15px; margin-bottom: 20px; background-color: #efe; border: 1px solid #cfc; border-radius: 4px; color: #3c3;">
                    <strong id="successMessage"></strong>
                </div>

                <!-- Основные поля -->
                <div class="admin-form-group">
                    <label for="productName">Название товара *</label>
                    <input type="text" id="productName" name="title" required>
                </div>

                <div class="admin-form-group">
                    <label for="productCategory">Категория *</label>
                    <select id="productCategory" name="category_id" required>
                        <option value="">Выберите категорию</option>
                        <?php foreach ($categories as $category) : ?>
                            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="admin-form-group">
                    <label for="productPrice">Цена *</label>
                    <input type="number" id="productPrice" name="price" required min="0" step="0.01">
                </div>

                <div class="admin-form-group">
                    <label for="productStock">Количество на складе *</label>
                    <input type="number" id="productStock" name="count" required min="0">
                </div>

                <div class="admin-form-group">
                    <label for="productDescription">Описание</label>
                    <textarea id="productDescription" name="content"></textarea>
                </div>

                <!-- Изображения -->
                <div class="admin-form-group">
                    <label for="productImages">Изображения товара *</label>
                    <input type="file" id="productImages" name="images[]" accept="image/*" multiple required>
                    <small style="display: block; color: #666; margin-top: 5px;">Необходимо выбрать ровно 4 изображения</small>
                    <div id="imagePreviews" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 15px;"></div>
                </div>

                <!-- Характеристики -->
                <div id="characteristicsContainer"></div>

                <div class="admin-form-actions">
                    <a href="./?page=admin_products" class="admin-btn admin-btn-secondary">Отмена</a>
                    <button type="submit" class="admin-btn admin-btn-primary">Сохранить</button>
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
    const errorContainer = document.getElementById('errorContainer');
    const errorText = document.getElementById('errorText');
    const successContainer = document.getElementById('successContainer');
    const successMessage = document.getElementById('successMessage');

    // Показать ошибку
    function showError(message) {
        errorText.textContent = message;
        errorContainer.style.display = 'block';
        successContainer.style.display = 'none';
        errorContainer.scrollIntoView({
            behavior: 'smooth'
        });
    }

    // Показать успех
    function showSuccess(message) {
        successMessage.textContent = message;
        successContainer.style.display = 'block';
        errorContainer.style.display = 'none';
        successContainer.scrollIntoView({
            behavior: 'smooth'
        });
    }

    // Скрыть сообщения
    function hideMessages() {
        errorContainer.style.display = 'none';
        successContainer.style.display = 'none';
    }

    // Загрузка характеристик
    function loadCharacteristics(categoryId) {
        characteristicsContainer.innerHTML = '<p>Загрузка характеристик...</p>';

        fetch(`?page=get_characteristics&category_id=${categoryId}`)
            .then(response => response.text())
            .then(html => {
                characteristicsContainer.innerHTML = html;
            })
            .catch(error => {
                characteristicsContainer.innerHTML = '<p class="text-muted">Ошибка загрузки характеристик</p>';
            });
    }

    // Обработчик категории
    productCategory.addEventListener('change', function() {
        hideMessages();
        const categoryId = this.value;

        if (!categoryId) {
            characteristicsContainer.innerHTML = '';
            return;
        }

        loadCharacteristics(categoryId);
    });

    // Обработчик изображений
    productImages.addEventListener('change', function(e) {
        hideMessages();
        const files = Array.from(e.target.files);

        // Проверяем 4 файла
        if (files.length !== 4) {
            showError('Необходимо выбрать ровно 4 изображения!');
            this.value = '';
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
                div.style.border = '2px solid #ddd';
                div.style.borderRadius = '8px';
                div.style.overflow = 'hidden';
                div.innerHTML = `
                <img src="${e.target.result}" alt="Превью ${index + 1}"
                     style="width: 100%; height: 150px; object-fit: cover; display: block;">
                <div style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.7); color: white; text-align: center; padding: 5px; font-size: 12px;">
                    Изображение ${index + 1}
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

        // Проверяем изображения
        if (productImages.files.length !== 4) {
            showError('Необходимо выбрать ровно 4 изображения!');
            return;
        }

        // Отправляем форму
        const formData = new FormData(this);

        fetch('?page=save_product', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess('Товар успешно добавлен! Перенаправление...');
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
</script>