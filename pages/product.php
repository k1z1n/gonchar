<?php

// Обработка POST запроса должна быть в самом начале
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id']) && isset($_SESSION['user_id'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    if ($quantity < 1) {
        $quantity = 1;
    }

    // Получаем информацию о товаре из БД
    $sql_product_check = "SELECT count FROM products WHERE id = ?";
    $stmt_product_check = $database->prepare($sql_product_check);
    $stmt_product_check->execute([$product_id]);
    $productData = $stmt_product_check->fetch(PDO::FETCH_ASSOC);

    if (!$productData) {
        echo "<script>document.location.href='./?page=product&id=" . $product_id . "';</script>";
        exit;
    }

    $availableStock = (int)$productData['count'];
    
    // Проверяем наличие товара
    if ($availableStock <= 0) {
        // Товара нет в наличии - перенаправляем обратно с сообщением
        $_SESSION['cart_error'] = 'Товар временно отсутствует на складе. Доступное количество: 0 шт.';
        echo "<script>document.location.href='./?page=product&id=" . $product_id . "';</script>";
        exit;
    }
    
    // Ограничиваем количество доступным на складе
    if ($quantity > $availableStock) {
        $_SESSION['cart_error'] = 'В таком количестве товара нет. Доступное количество: ' . $availableStock . ' шт.';
        echo "<script>document.location.href='./?page=product&id=" . $product_id . "';</script>";
        exit;
    }

    $sql_cart = "SELECT * FROM carts WHERE product_id = ? AND user_id = ?";
    $stmt_cart = $database->prepare($sql_cart);
    $stmt_cart->execute([$product_id, $_SESSION['user_id']]);
    $cart = $stmt_cart->fetch(PDO::FETCH_ASSOC);

    if (empty($cart)) {
        $sql_add_cart = "INSERT INTO carts (product_id, user_id, count) VALUES (?, ?, ?)";
        $stmt_add_cart = $database->prepare($sql_add_cart);
        $stmt_add_cart->execute([$product_id, $_SESSION['user_id'], $quantity]);
    } else {
        $newCount = (int)$cart['count'] + $quantity;
        // Ограничиваем количеством на складе
        if ($newCount > $availableStock) {
            $_SESSION['cart_error'] = 'В таком количестве товара нет. Доступное количество: ' . $availableStock . ' шт.';
            echo "<script>document.location.href='./?page=product&id=" . $product_id . "';</script>";
            exit;
        }

        $sql_add_cart_count = "UPDATE carts SET count = ? WHERE product_id = ? AND user_id = ?";
        $stmt_add_cart_count = $database->prepare($sql_add_cart_count);
        $stmt_add_cart_count->execute([$newCount, $product_id, $_SESSION['user_id']]);
    }

    echo "<script>document.location.href='./?page=cart';</script>";
    exit;
}

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
} else {
    die('Товар с данным ID не найден');
}

$sql_product = "SELECT * FROM products WHERE id = ?";
$stmt_product = $database->prepare($sql_product);
$stmt_product->execute([$id]);
$product = $stmt_product->fetch();

$sql_characteristics = "SELECT 
            pc.value, c.name FROM product_characteristics pc 
             JOIN characteristics c ON pc.characteristic_id = c.id
             WHERE product_id = ?";
$stmt_characteristics = $database->prepare($sql_characteristics);
$stmt_characteristics->execute([$product['id']]);
$characteristics = $stmt_characteristics->fetchAll(2);

$sql_images = "SELECT * FROM images WHERE product_id = ?";
$stmt_images = $database->prepare($sql_images);
$stmt_images->execute([$product['id']]);
$images = $stmt_images->fetchAll(2);


?>

<!-- PRODUCT PAGE START -->
<div class="product_page container mt-105">
    <div class="product_layout">
        <!-- PRODUCT IMAGES START -->
        <div class="product_images">
            <div class="main_image">
                <?php 
                $mainImagePath = !empty($images) ? $images[0]['path'] : 'assets/media/images/index/product_main-69809c.png';
                ?>
                <img src="<?= $mainImagePath ?>" alt="<?= htmlspecialchars($product['title']) ?>" id="mainProductImage">
            </div>

            <?php if (!empty($images)): ?>
            <div class="thumbnail_images">
                <?php foreach ($images as $index => $image): ?>
                    <div class="thumbnail <?= $index === 0 ? 'active' : '' ?>" data-image="<?= htmlspecialchars($image['path']) ?>">
                        <img src="<?= $image['path'] ?>" alt="<?= htmlspecialchars($product['title']) ?> вид <?= $index + 1 ?>">
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (count($images) > 1): ?>
            <div class="image_navigation">
                <button class="nav_btn nav_btn_prev">
                    <img src="assets/media/images/catalog/str.svg" alt="Предыдущее">
                </button>
                <button class="nav_btn nav_btn_next">
                    <img src="assets/media/images/catalog/str.svg" alt="Следующее">
                </button>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
        <!-- PRODUCT IMAGES END -->

        <!-- PRODUCT INFO START -->
        <div class="product_info">
            <h2><?= $product['title'] ?></h2>
            <p class="product_sku">Артикул: <?= $product['article'] ?></p>
            <p class="product_price"><?= number_format($product['price'], 0, ',', ' ') ?> ₽</p>

            <div class="product_description">
                <p><?= $product['content'] ?></p>
            </div>

            <?php if (isset($_SESSION['cart_error'])): ?>
                <div class="product_notification product_notification_error">
                    <p> <?= htmlspecialchars($_SESSION['cart_error']) ?></p>
                </div>
                <?php unset($_SESSION['cart_error']); ?>
            <?php endif; ?>

            <!-- COLOR SELECTION START -->
            <div class="color_selection">

                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php 
                    $productCount = (int)$product['count'];
                    $maxQty = max(1, $productCount);
                    $isAvailable = $productCount > 0;
                    ?>
                    <?php if (!$isAvailable): ?>
                        <div class="product_notification product_notification_warning">
                            <p>Товар временно отсутствует на складе</p>
                            <p>Доступное количество: <strong>0 шт.</strong></p>
                        </div>
                    <?php endif; ?>
                    <form class="add_to_cart_section" action="./?page=product&id=<?= $product['id'] ?>" method="post" id="addToCartForm">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <input type="number"
                            class="quantity_input"
                            name="quantity"
                            value="1"
                            min="1"
                            max="<?= $maxQty ?>"
                            id="quantityInput"
                            <?= !$isAvailable ? 'disabled' : '' ?>>
                        <button class="btn_bg" type="submit" id="addToCartBtn" <?= !$isAvailable ? 'disabled' : '' ?>>
                            В КОРЗИНУ
                        </button>
                    </form>
                    <?php if ($isAvailable): ?>
                        <div class="product_stock_info">
                            Доступно на складе: <strong><?= $productCount ?> шт.</strong>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="product_login_required">
                        <p>Для добавления товара в корзину необходимо <a href="./?page=login">войти</a> в систему.</p>
                    </div>
                <?php endif; ?>

            </div>
            <!-- COLOR SELECTION END -->

            <!-- PRODUCT SPECIFICATIONS START -->
            <div class="product_specs">
                <?php foreach ($characteristics as $characteristic): ?>
                    <div class="spec_item">
                        <span class="spec_label"><?= $characteristic['name'] ?>:</span>
                        <span class="spec_value"><?= $characteristic['value'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- PRODUCT SPECIFICATIONS END -->
        </div>
        <!-- PRODUCT INFO END -->
    </div>
</div>
<!-- PRODUCT PAGE END -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mainImage = document.getElementById('mainProductImage');
    const thumbnails = document.querySelectorAll('.thumbnail');
    const navBtnPrev = document.querySelector('.nav_btn_prev');
    const navBtnNext = document.querySelector('.nav_btn_next');
    const thumbnailContainer = document.querySelector('.thumbnail_images');
    
    let currentIndex = 0;
    const visibleThumbnails = 4;
    
    // Обработка кликов по миниатюрам
    thumbnails.forEach((thumbnail, index) => {
        thumbnail.addEventListener('click', function() {
            // Убираем активный класс у всех миниатюр
            thumbnails.forEach(t => t.classList.remove('active'));
            // Добавляем активный класс текущей миниатюре
            this.classList.add('active');
            
            // Обновляем главное изображение
            const imagePath = this.getAttribute('data-image');
            if (imagePath && mainImage) {
                mainImage.src = imagePath;
            }
            
            currentIndex = index;
            
            // Прокручиваем миниатюры, если их больше 4
            if (thumbnails.length > visibleThumbnails) {
                updateThumbnailScroll();
            }
        });
    });
    
    // Функция для обновления прокрутки миниатюр
    function updateThumbnailScroll() {
        if (!thumbnailContainer) return;
        
        const thumbnailWidth = thumbnails[0] ? thumbnails[0].offsetWidth + 15 : 95; // 80px + 15px gap
        const scrollPosition = currentIndex * thumbnailWidth;
        thumbnailContainer.scrollTo({
            left: scrollPosition,
            behavior: 'smooth'
        });
    }
    
    // Навигация влево
    if (navBtnPrev) {
        navBtnPrev.addEventListener('click', function() {
            if (currentIndex > 0) {
                currentIndex--;
                thumbnails[currentIndex].click();
            }
        });
    }
    
    // Навигация вправо
    if (navBtnNext) {
        navBtnNext.addEventListener('click', function() {
            if (currentIndex < thumbnails.length - 1) {
                currentIndex++;
                thumbnails[currentIndex].click();
            }
        });
    }
    
    // Инициализация: устанавливаем первое изображение как активное
    if (thumbnails.length > 0) {
        currentIndex = 0;
    }
    
});
</script>