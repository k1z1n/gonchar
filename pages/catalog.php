<?php

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id']) && isset($_SESSION['user_id'])) {
    $product_id = (int)$_POST['product_id'];
    
    $sql_cart = "SELECT * FROM carts WHERE product_id = ? AND user_id = ?";
    $stmt_cart = $database->prepare($sql_cart);
    $stmt_cart->execute([$product_id, $_SESSION['user_id']]);
    $cart = $stmt_cart->fetch(2);

    if(empty($cart)) {
        $sql_add_cart = "INSERT INTO carts (product_id, user_id) VALUES (?, ?)";
        $stmt_add_cart = $database->prepare($sql_add_cart);
        $stmt_add_cart->execute([$product_id, $_SESSION['user_id']]);
    } else {
        $sql_add_cart_count = "UPDATE carts SET count = count + 1 WHERE product_id = ? AND user_id = ?";
        $stmt_add_cart_count = $database->prepare($sql_add_cart_count);
        $stmt_add_cart_count->execute([$product_id, $_SESSION['user_id']]);
    }
    
    header('Location: ./?page=cart');
    exit;
}

$sql_products = "SELECT * FROM products";
$stmt_products = $database->prepare($sql_products);
$stmt_products->execute();
$products = $stmt_products->fetchAll(2);

?>

<!-- HERO BANNER START -->
<section class="catalog_hero">
    <h1>Создано с любовью. Для вашего уюта.</h1>
</section>
<!-- HERO BANNER END -->

<!-- CATALOG CONTENT START -->
<div class="catalog_content container">
    <div class="catalog_layout">
        <!-- FILTERS SIDEBAR START -->
        <div class="filters_sidebar">
            <div class="filter_categories">
                <div class="filter_category active">ВСЕ ТОВАРЫ</div>
                <div class="filter_category">ВАЗА</div>
                <div class="filter_category">КРУЖКА</div>
                <div class="filter_category">ТАРЕЛКИ</div>
                <div class="filter_category">ДРУГОЕ</div>
            </div>
        </div>
        <!-- FILTERS SIDEBAR END -->

        <!-- PRODUCTS SECTION START -->
        <div class="products_section">
            <div class="sort_dropdown">
                <div class="sort_trigger">
                    <span class="sort_text">По умолчанию</span>
                    <img class="sort_arrow" src="assets/media/images/catalog/filtr.svg" alt="">
                </div>
                <div class="sort_options">
                    <div class="sort_option" data-value="default">По умолчанию</div>
                    <div class="sort_option" data-value="price-asc">По возрастанию цены</div>
                    <div class="sort_option" data-value="price-desc">По убыванию цены</div>
                    <div class="sort_option" data-value="popularity">По популярности</div>
                    <div class="sort_option" data-value="newest">Сначала новые</div>
                </div>
            </div>

            <!-- PRODUCTS GRID START -->
            <div class="products_grid">
                <!-- Product 1 -->
                <?php foreach ($products as $product) : ?>
                <?php
                    $sql_image = "SELECT path FROM images WHERE product_id = ? ORDER BY id ASC LIMIT 1";
                    $stmt_image = $database->prepare($sql_image);
                    $stmt_image->execute([$product['id']]);
                    $image = $stmt_image->fetch(2);
                ?>
                <div class="product_card">
                    <img src="<?=$image['path'] ?>" alt="Васильковое поле">
                    <h3><?=$product['title'] ?></h3>
                    <p class="price"><?=$product['price'] ?> ₽</p>
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <form action="" method="post" class="product_actions">
                        <input type="hidden" name="product_id" value="<?=$product['id'] ?>">
                        <a href="./?page=product&&id=<?=$product['id'] ?>" class="btn">ПОДРОБНЕЕ</a>
                        <button class="btn_bg" type="submit">В КОРЗИНУ</button>
                    </form>
                    <?php else: ?>
                    <div class="product_actions">
                        <a href="./?page=product&&id=<?=$product['id'] ?>" class="btn">ПОДРОБНЕЕ</a>
                        <a href="./?page=login" class="btn_bg">В КОРЗИНУ</a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>


            </div>
            <!-- PRODUCTS GRID END -->

            <!-- PAGINATION START -->
            <div class="pagination">
                <button class="pagination_btn">
                    <img src="assets/media/images/catalog/str.svg" alt="">
                </button>
                <span class="pagination_number active">1</span>
                <span class="pagination_number">2</span>
                <span class="pagination_dots">...</span>
                <span class="pagination_number">5</span>
                <button class="pagination_btn">
                    <img src="assets/media/images/catalog/str.svg" alt="">
                </button>
            </div>
            <!-- PAGINATION END -->
        </div>
        <!-- PRODUCTS SECTION END -->
    </div>
</div>
<!-- CATALOG CONTENT END -->
