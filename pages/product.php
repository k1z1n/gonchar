<?php

if(isset($_GET['id']) && !empty($_GET['id'])){
    $id = $_GET['id'];
} else {
    die ('Товар с данным ID не найден');
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


if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $sql_cart = "SELECT * FROM carts WHERE product_id = ? AND user_id = ?";
    $stmt_cart = $database->prepare($sql_cart);
    $stmt_cart->execute([$product['id'], $_SESSION['user_id']]);
    $cart = $stmt_cart->fetch(2);

    if(empty($cart)) {
        $sql_add_cart = "INSERT INTO carts (product_id, user_id) VALUES (?, ?)";
        $stmt_add_cart = $database->prepare($sql_add_cart);
        $stmt_add_cart->execute([$product['id'], $_SESSION['user_id']]);
    } else {
        $sql_add_cart_count = "UPDATE carts SET count = count + 1 WHERE product_id = ? AND user_id = ?";
        $stmt_add_cart_count = $database->prepare($sql_add_cart_count);
        $stmt_add_cart_count->execute([$product['id'], $_SESSION['user_id']]);
    }

    header('Location: ./?page=cart');

}


?>

<!-- PRODUCT PAGE START -->
<div class="product_page container mt-105">
    <div class="product_layout">
        <!-- PRODUCT IMAGES START -->
        <div class="product_images">
            <div class="main_image">
                <img src="<?=$images[0]['path'] ?>" alt="Васильковое поле" id="mainProductImage">
            </div>

            <div class="thumbnail_images">
                <?php foreach ($images as $image): ?>
                <img src="<?=$image['path'] ?>" alt="Ваза вид 1" class="thumbnail active" data-main="assets/media/images/index/product_thumb1.png">
                <?php endforeach; ?>
            </div>

            <div class="image_navigation">
                <button class="nav_btn">
                    <img src="assets/media/images/catalog/str.svg" alt="">
                </button>
                <button class="nav_btn">
                    <img src="assets/media/images/catalog/str.svg" alt="">
                </button>
            </div>
        </div>
        <!-- PRODUCT IMAGES END -->

        <!-- PRODUCT INFO START -->
        <div class="product_info">
            <h1><?=$product['title'] ?></h1>
            <p class="product_sku">Артикул: <?=$product['article'] ?></p>
            <p class="product_price"><?=$product['price'] ?> ₽</p>

            <div class="product_description">
                <p><?=$product['content'] ?></p>
            </div>

            <!-- COLOR SELECTION START -->
            <div class="color_selection">

                    <?php if(isset($_SESSION['user_id'])): ?>
                        <form class="add_to_cart_section" action="" method="post">
                            <input type="number" class="quantity_input" value="2" min="1" max="99">
                            <button class="btn_bg" type="submit">В КОРЗИНУ</button>
                        </form>
                    <?php endif; ?>

            </div>
            <!-- COLOR SELECTION END -->

            <!-- PRODUCT SPECIFICATIONS START -->
            <div class="product_specs">
                <?php foreach ($characteristics as $characteristic): ?>
                <div class="spec_item">
                    <span class="spec_label"><?=$characteristic['name'] ?>:</span>
                    <span class="spec_value"><?=$characteristic['value'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <!-- PRODUCT SPECIFICATIONS END -->
        </div>
        <!-- PRODUCT INFO END -->
    </div>
</div>
<!-- PRODUCT PAGE END -->
