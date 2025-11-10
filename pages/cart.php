<?php

if (!isset($_SESSION['user_id'])) {
    header('Location: ./?page=login');
    exit;
}

$sql = "SELECT 
    p.id as product_id,
    c.id as cart_id,
    p.title,
    p.price,
    c.count
    FROM carts c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?";
$stmt = $database->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$cart = $stmt->fetchAll(2);

$totalPrice = 0;
$pickupAddress = 'Казань, ул. Новаторов 8Б';
$orderError = null;
$addressInvalid = false;

$action = $_POST["action"] ?? null;

if($_SERVER["REQUEST_METHOD"] == "POST" && $action !== 'order') {

    $cart_id = $_POST["cart_id"] ?? null;

    if($action === "minus") {
        $sql = "SELECT * FROM carts WHERE id = ?";
        $stmt = $database->prepare($sql);
        $stmt->execute([$cart_id]);
        $cartItem = $stmt->fetch(2);

        if($cartItem && $cartItem['count'] > 1) {
            $sql = "UPDATE carts SET count = count - 1 WHERE id = ?";
            $stmt = $database->prepare($sql);
            $stmt->execute([$cart_id]);
        } elseif($cartItem) {
            $sql = "DELETE FROM carts WHERE id = ?";
            $stmt = $database->prepare($sql);
            $stmt->execute([$cart_id]);
        }
    } elseif($action === "plus") {
        $sql = "UPDATE carts SET count = count + 1 WHERE id = ?";
        $stmt = $database->prepare($sql);
        $stmt->execute([$cart_id]);
    } elseif($action === "set_delivery") {
        $deliveryMethod = $_POST['delivery_method'] === 'pickup' ? 'pickup' : 'courier';
        $_SESSION['delivery_method'] = $deliveryMethod;

        $address = trim($_POST['delivery_address'] ?? '');
        if($deliveryMethod === 'courier') {
            $_SESSION['delivery_address'] = $address;
        } else {
            $_SESSION['delivery_address'] = '';
        }
    }

    header('Location: ./?page=cart');
    exit;
}

foreach ($cart as $item) {
    $totalPrice += (float)$item['price'] * (int)$item['count'];
}

$deliveryMethod = $_SESSION['delivery_method'] ?? 'courier';
$deliveryAddressRaw = $_SESSION['delivery_address'] ?? '';
$deliveryAddress = trim($deliveryAddressRaw);
$deliveryPrice = empty($cart) ? 0 : ($deliveryMethod === 'pickup' ? 0 : 490);
$deliveryLabel = $deliveryMethod === 'pickup' ? 'Самовывоз' : 'Курьерская доставка';
$deliveryAddressEsc = htmlspecialchars($deliveryAddressRaw, ENT_QUOTES, 'UTF-8');

$orderAddress = $deliveryMethod === 'pickup' ? $pickupAddress : $deliveryAddress;
$orderAddressDisplay = $orderAddress !== '' ? $orderAddress : '—';
$orderAddressDisplayEsc = htmlspecialchars($orderAddressDisplay, ENT_QUOTES, 'UTF-8');

$allPrice = $totalPrice + $deliveryPrice;

if($_SERVER['REQUEST_METHOD'] == 'POST' && $action === 'order') {

    if (empty($cart)) {
        $orderError = 'Добавьте товары в корзину, чтобы оформить заказ.';
    } elseif ($deliveryMethod === 'courier' && $orderAddress === '') {
        $orderError = 'Укажите адрес для курьерской доставки.';
        $addressInvalid = true;
    } else {
        $sql = "SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders'";
        $orderNumber = (string)$database->query($sql)->fetchColumn();

        $sql = "INSERT INTO orders (user_id, total_amount, order_number, adress) VALUES (?, ?, ?, ?)";
        $stmt = $database->prepare($sql);
        $stmt->execute([$_SESSION['user_id'], $allPrice, $orderNumber, $orderAddress]);

        $order_id = $database->lastInsertId();

        foreach ($cart as $item) {
            $sql = "INSERT INTO orders_items (order_id, product_id, count, price) VALUES (?, ?, ?, ?)";
            $stmt = $database->prepare($sql);
            $stmt->execute([$order_id, $item['product_id'], $item['count'], $allPrice]);
        }
        $idempotentKey = uniqid('', true);

        $paymentData = [
            'amount' => [
                'value' => number_format($allPrice, 2, '.', ''),
                'currency' => 'RUB',
            ],
            'capture' => true,
            'confirmation' => [
                'type' => 'redirect',
                'return_url' => './?page=cart',
            ],
            'description' => 'Заказ №' . $orderNumber,
        ];

        $url = 'https://api.yookassa.ru/v3/payments';

        $option = [
            'http' => [
                'header' => "Content-Type: application/json\r\n" .
                    "Idempotence-Key: " . $idempotentKey . "\r\n" .
                    "Authorization: Basic " . base64_encode("1178569:test_gGqTKdTnhxY50boT2OG7BWTTRgFerh_Y669BiH5x1Jg"),
                'method' => 'POST',
                'content' => json_encode($paymentData, JSON_UNESCAPED_UNICODE)
            ]
        ];

        $context = stream_context_create($option);
        $result = file_get_contents($url, false, $context);

        if($result === false) {
            $orderError = 'Ошибка при создании платежа. Попробуйте позже.';
        } else {
            $response = json_decode($result, true);

            foreach($cart as $cartItem) {
                $database->query("DELETE FROM carts WHERE id = " . (int)$cartItem['cart_id']);
            }
            $_SESSION['delivery_address'] = '';
            $_SESSION['delivery_method'] = 'courier';
            header('Location: ' . $response['confirmation']['confirmation_url']);
            exit;
        }
    }
}

?>

<!-- BASKET CONTENT START -->
<div class="basket_content container mt-105">
    <div class="basket_layout">
        <!-- BASKET ITEMS START -->
        <div class="basket_items">
            <!-- ITEM 1 START -->
            <?php if(empty($cart)): ?>
                <h4>Корзина пуста</h4>
            <?php else: ?>
            <?php foreach ($cart as $item): ?>
            <div class="basket_item">
                <?php
                $sql_images = "SELECT path FROM images WHERE product_id = ? ORDER BY id ASC LIMIT 1";
                $stmt = $database->prepare($sql_images);
                $stmt->execute([$item['product_id']]);
                $image = $stmt->fetch(2);
                ?>

                <div class="item_image">
                    <img src="<?=$image['path'] ?>" alt="ВАСИЛЬКОВОЕ ПОЛЕ">
                </div>
                <div class="item_info">
                    <h3><?=$item['title'] ?></h3>
                </div>
                <div class="item_controls">
                    <form action="" method="post">
                        <input type="hidden" name="action" value="minus">
                        <input type="hidden" name="cart_id" value="<?=$item['cart_id']?>">
                        <button class="quantity_btn minus" type="submit">
                            <img src="assets/media/images/index/minus_icon.svg" alt="">
                        </button>
                    </form>

                    <span class="quantity"><?=$item['count'] ?></span>

                    <form action="" method="post">
                        <input type="hidden" name="action" value="plus">
                        <input type="hidden" name="cart_id" value="<?=$item['cart_id'] ?>">
                        <button class="quantity_btn plus" type="submit">
                            <img src="assets/media/images/index/plus_icon.svg" alt="">
                        </button>
                    </form>

                </div>
                <div class="item_price"><?=number_format((float)$item['price'] * (int)$item['count'], 0, ',', ' ') ?> ₽</div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
            <!-- ITEM 1 END -->

        </div>
        <!-- BASKET ITEMS END -->

        <!-- ORDER FORM START -->
        <div class="order_form">
            <h2>КРАТКОЕ ОПИСАНИЕ ЗАКАЗА</h2>

            <div class="form_section">
                <h3>адрес и способ доставки</h3>
                <?php if(empty($cart)): ?>
                    <p>Добавьте товары в корзину, чтобы выбрать способ доставки.</p>
                <?php else: ?>
                <form class="delivery_form" method="post">
                    <input type="hidden" name="action" value="set_delivery">
                    <label class="delivery_option" for="delivery_courier">
                        <input type="radio" id="delivery_courier" name="delivery_method" value="courier" <?=$deliveryMethod === 'courier' ? 'checked' : '' ?>>
                        <span>Курьерская доставка (490 ₽)</span>
                    </label>
                    <div class="delivery_address_field" <?=$deliveryMethod === 'courier' ? '' : 'style="display:none;"' ?>>
                        <label for="delivery_address">Адрес доставки</label>
                        <input type="text" id="delivery_address" name="delivery_address" value="<?=$deliveryAddressEsc ?>" placeholder="Город, улица, дом, квартира" class="delivery_address_input<?=$addressInvalid ? ' input_error' : '' ?>">
                        <?php if($addressInvalid): ?>
                        <span class="field_error">Укажите адрес для доставки.</span>
                        <?php endif; ?>
                    </div>
                    <label class="delivery_option" for="delivery_pickup">
                        <input type="radio" id="delivery_pickup" name="delivery_method" value="pickup" <?=$deliveryMethod === 'pickup' ? 'checked' : '' ?>>
                        <span>Самовывоз (0 ₽) — Казань, ул. Новаторов 8Б</span>
                    </label>
                </form>
                <?php endif; ?>
            </div>

            <div class="form_section">
                <h3>оплата</h3>
                <p>СБП или банковская карта</p>
            </div>

            <div class="form_section">
                <h3>СУММА ЗАКАЗА</h3>
                <div class="order_summary">
                    <div class="summary_row">
                        <span>стоимость товаров</span>
                        <span><?=number_format($totalPrice, 0, ',', ' ') ?> ₽</span>
                    </div>
                    <div class="summary_row">
                        <span>доставка (<?=$deliveryLabel ?>)</span>
                        <span><?=number_format($deliveryPrice, 0, ',', ' ') ?> ₽</span>
                    </div>
                    <div class="summary_row">
                        <span>скидка</span>
                        <span>0 ₽</span>
                    </div>
                </div>

                <div class="promo_section">
                    <input type="text" placeholder="введите промокод">
                </div>

                <div class="summary_total">
                    <span>итого</span>
                    <span><?=number_format($allPrice, 0, ',', ' ') ?> ₽</span>
                </div>
                <?php if($orderError): ?>
                    <div class="order_error"><?=htmlspecialchars($orderError, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <form action="" method="post">
                    <input type="hidden" name="action" value="order">
                    <button class="btn_bg">ОФОРМИТЬ ЗАКАЗ</button>
                </form>

            </div>

            <div class="contact_section">
                <h3>МОЖЕМ ЛИ МЫ ПОМОЧЬ?</h3>
                <div class="contact_info">
                    <div class="contact_item">
                        <img src="assets/media/images/index/phone.svg" alt="">
                        <span>8 800 777-7-777</span>
                    </div>
                    <div class="contact_item">
                        <img src="assets/media/images/index/email_icon.svg" alt="">
                        <span>goncharok@mail.ru</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- ORDER FORM END -->
    </div>
</div>
<!-- BASKET CONTENT END -->

<?php if(!empty($cart)): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var deliveryForm = document.querySelector('.delivery_form');
  if (!deliveryForm) return;
  var addressField = deliveryForm.querySelector('.delivery_address_field');
  var addressInput = deliveryForm.querySelector('#delivery_address');
  var radios = deliveryForm.querySelectorAll('input[name="delivery_method"]');

  var submitForm = function () {
    if (typeof deliveryForm.requestSubmit === 'function') {
      deliveryForm.requestSubmit();
    } else {
      deliveryForm.submit();
    }
  };

  var toggleAddress = function () {
    if (!addressField) return;
    var courierSelected = deliveryForm.querySelector('input[name="delivery_method"][value="courier"]').checked;
    addressField.style.display = courierSelected ? '' : 'none';
  };

  var debounceTimer;
  var handleAddressInput = function () {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(submitForm, 600);
  };

  radios.forEach(function (radio) {
    radio.addEventListener('change', function () {
      toggleAddress();
      submitForm();
    });
  });

  if (addressInput) {
    addressInput.addEventListener('input', handleAddressInput);
    addressInput.addEventListener('blur', submitForm);
  }

  toggleAddress();
  var addressInvalidFlag = <?php echo $addressInvalid ? 'true' : 'false'; ?>;
  if (addressInvalidFlag && addressInput) {
    addressInput.focus();
    addressInput.scrollIntoView({behavior: 'smooth', block: 'center'});
  }
});
</script>
<?php endif; ?>

<!-- RECOMMENDATIONS START -->
<div class="recommendations container">
    <h2>ВАМ ТАКЖЕ МОЖЕТ ПОНРАВИТЬСЯ</h2>
    <div class="recommendations_grid">
        <div class="product_item">
            <img src="assets/media/images/index/product1-1e853d.png" alt="Васильковое поле">
            <h3>ВАСИЛЬКОВОЕ ПОЛЕ</h3>
            <p>2 700 ₽</p>
        </div>
        <div class="product_item">
            <img src="assets/media/images/index/product2-1dad5f.png" alt="Васильковое поле">
            <h3>ВАСИЛЬКОВОЕ ПОЛЕ</h3>
            <p>2 700 ₽</p>
        </div>
        <div class="product_item">
            <img src="assets/media/images/index/product3.png" alt="Васильковое поле">
            <h3>ВАСИЛЬКОВОЕ ПОЛЕ</h3>
            <p>2 700 ₽</p>
        </div>
        <div class="product_item">
            <img src="assets/media/images/index/product3.png" alt="Васильковое поле">
            <h3>ВАСИЛЬКОВОЕ ПОЛЕ</h3>
            <p>2 700 ₽</p>
        </div>
    </div>
</div>
<!-- RECOMMENDATIONS END -->
