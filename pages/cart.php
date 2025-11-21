<?php

function generateUniqueOrderNumber(PDO $database, int $min = 100000, int $max = 999999, int $maxAttempts = 20): int
{
    $stmt = $database->prepare("SELECT 1 FROM orders WHERE order_number = ? LIMIT 1");

    for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
        $candidate = random_int($min, $max);
        $stmt->execute([$candidate]);
        if (!$stmt->fetchColumn()) {
            return $candidate;
        }
    }

    $fallback = $database->query("SELECT COALESCE(MAX(order_number), 0) + 1 FROM orders");
    return (int)$fallback->fetchColumn();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ./?page=login');
    exit;
}

$sql = "SELECT 
    p.id as product_id,
    c.id as cart_id,
    p.title,
    p.price,
    p.count AS stock,
    c.count
    FROM carts c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?";
$stmt = $database->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$cart = $stmt->fetchAll(2);

$cartMessage = $_SESSION['cart_message'] ?? null;
unset($_SESSION['cart_message']);

$promoMessage = $_SESSION['promo_message'] ?? null;
$promoError = $_SESSION['promo_error'] ?? null;
$promoInputValue = $_SESSION['promo_input'] ?? '';
unset($_SESSION['promo_message'], $_SESSION['promo_error'], $_SESSION['promo_input']);
$appliedPromoId = $_SESSION['applied_promo_id'] ?? null;
$appliedPromoCode = $_SESSION['applied_promo_code'] ?? null;

$totalPrice = 0;
$pickupAddress = 'Казань, ул. Новаторов 8Б';
$orderError = null;
$addressInvalid = false;

$action = $_POST["action"] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'apply_promo') {
        $enteredCode = strtoupper(trim($_POST['promo_code'] ?? ''));
        $_SESSION['promo_input'] = $enteredCode;

        if ($enteredCode === '') {
            $_SESSION['promo_error'] = 'Введите промокод.';
        } else {
            $stmtPromo = $database->prepare("SELECT * FROM promocodes WHERE code = ?");
            $stmtPromo->execute([$enteredCode]);
            $promoData = $stmtPromo->fetch(PDO::FETCH_ASSOC);

            if (!$promoData) {
                $_SESSION['promo_error'] = 'Промокод не найден.';
            } elseif ((int)$promoData['used_count'] >= (int)$promoData['usage_limit']) {
                $_SESSION['promo_error'] = 'Лимит использований промокода исчерпан.';
            } else {
                $_SESSION['applied_promo_id'] = (int)$promoData['id'];
                $_SESSION['applied_promo_code'] = $promoData['code'];
                $_SESSION['promo_message'] = 'Промокод применён. Скидка ' . number_format($promoData['amount'], 0, ',', ' ') . ' ₽.';
                unset($_SESSION['promo_error'], $_SESSION['promo_input']);
            }
        }

        header('Location: ./?page=cart');
        exit;
    }

    if ($action === 'remove_promo') {
        unset($_SESSION['applied_promo_id'], $_SESSION['applied_promo_code'], $_SESSION['promo_input']);
        $_SESSION['promo_message'] = 'Промокод удалён.';
        header('Location: ./?page=cart');
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $action !== 'order') {

    $cart_id = $_POST["cart_id"] ?? null;

    if ($action === "minus") {
        $sql = "SELECT * FROM carts WHERE id = ?";
        $stmt = $database->prepare($sql);
        $stmt->execute([$cart_id]);
        $cartItem = $stmt->fetch(2);

        if ($cartItem && $cartItem['count'] > 1) {
            $sql = "UPDATE carts SET count = count - 1 WHERE id = ?";
            $stmt = $database->prepare($sql);
            $stmt->execute([$cart_id]);
        } elseif ($cartItem) {
            $sql = "DELETE FROM carts WHERE id = ?";
            $stmt = $database->prepare($sql);
            $stmt->execute([$cart_id]);
        }
    } elseif ($action === "plus") {
        $sql = "SELECT c.count, p.count AS stock FROM carts c JOIN products p ON c.product_id = p.id WHERE c.id = ?";
        $stmt = $database->prepare($sql);
        $stmt->execute([$cart_id]);
        $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cartItem) {
            $currentCount = (int)$cartItem['count'];
            $availableStock = (int)$cartItem['stock'];

            if ($currentCount < $availableStock) {
                $sql = "UPDATE carts SET count = count + 1 WHERE id = ?";
                $stmt = $database->prepare($sql);
                $stmt->execute([$cart_id]);
            }
        }
    } elseif ($action === "set_delivery") {
        $deliveryMethod = $_POST['delivery_method'] === 'pickup' ? 'pickup' : 'courier';
        $_SESSION['delivery_method'] = $deliveryMethod;

        $address = trim($_POST['delivery_address'] ?? '');
        if ($deliveryMethod === 'courier') {
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
$appliedPromo = null;
$promoDiscount = 0.0;

if ($appliedPromoId) {
    $stmtAppliedPromo = $database->prepare("SELECT * FROM promocodes WHERE id = ?");
    $stmtAppliedPromo->execute([$appliedPromoId]);
    $promoCandidate = $stmtAppliedPromo->fetch(PDO::FETCH_ASSOC);

    if ($promoCandidate && (int)$promoCandidate['used_count'] < (int)$promoCandidate['usage_limit']) {
        $appliedPromo = $promoCandidate;
        $appliedPromoCode = $promoCandidate['code'];
        $promoDiscount = min((float)$promoCandidate['amount'], max(0, $totalPrice + $deliveryPrice));
        $allPrice = max(0, $allPrice - $promoDiscount);
    } else {
        unset($_SESSION['applied_promo_id'], $_SESSION['applied_promo_code']);
        if (!$promoError) {
            $promoError = 'Промокод больше недоступен.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action === 'order') {

    if (empty($cart)) {
        $orderError = 'Добавьте товары в корзину, чтобы оформить заказ.';
    } elseif ($deliveryMethod === 'courier' && $orderAddress === '') {
        $orderError = 'Укажите адрес для курьерской доставки.';
        $addressInvalid = true;
    } else {
        $insufficientStockItems = [];
        foreach ($cart as $item) {
            $availableStock = (int)($item['stock'] ?? 0);
            $requestedCount = (int)$item['count'];
            if ($availableStock <= 0 || $requestedCount > $availableStock) {
                $insufficientStockItems[] = [
                    'cart_id' => $item['cart_id'],
                    'available' => $availableStock
                ];
            }
        }

        if (!empty($insufficientStockItems)) {
            foreach ($insufficientStockItems as $insufficient) {
                if ($insufficient['available'] <= 0) {
                    $sql = "DELETE FROM carts WHERE id = ?";
                    $stmt = $database->prepare($sql);
                    $stmt->execute([$insufficient['cart_id']]);
                } else {
                    $sql = "UPDATE carts SET count = ? WHERE id = ?";
                    $stmt = $database->prepare($sql);
                    $stmt->execute([$insufficient['available'], $insufficient['cart_id']]);
                }
            }
            $_SESSION['cart_message'] = 'Количество некоторых товаров было скорректировано из-за ограниченного остатка.';
            header('Location: ./?page=cart');
            exit;
        }

        $orderNumber = generateUniqueOrderNumber($database);
        $order_id = null;

        try {
            $database->beginTransaction();

            $sql = "INSERT INTO orders (user_id, total_amount, order_number, adress) VALUES (?, ?, ?, ?)";
            $stmt = $database->prepare($sql);
            $stmt->execute([$_SESSION['user_id'], $allPrice, $orderNumber, $orderAddress]);

            $order_id = (int)$database->lastInsertId();

            $stmtInsertOrderItem = $database->prepare("INSERT INTO orders_items (order_id, product_id, count, price) VALUES (?, ?, ?, ?)");
            $stmtUpdateProduct = $database->prepare("UPDATE products SET count = CASE WHEN count >= ? THEN count - ? ELSE 0 END WHERE id = ?");
            $stmtGetCategory = $database->prepare("SELECT category_id FROM products WHERE id = ?");
            $stmtRecalcCategoryTotal = $database->prepare("SELECT COALESCE(SUM(count), 0) FROM products WHERE category_id = ?");
            $stmtUpdateCategoryCount = $database->prepare("UPDATE category SET count = ? WHERE id = ?");

            $affectedCategories = [];

            foreach ($cart as $item) {
                $stmtInsertOrderItem->execute([$order_id, $item['product_id'], $item['count'], $allPrice]);

                $stmtGetCategory->execute([$item['product_id']]);
                $categoryId = (int)$stmtGetCategory->fetchColumn();
                if ($categoryId) {
                    $affectedCategories[] = $categoryId;
                }

                $stmtUpdateProduct->execute([$item['count'], $item['count'], $item['product_id']]);
            }

            $affectedCategories = array_unique($affectedCategories);
            foreach ($affectedCategories as $categoryId) {
                $stmtRecalcCategoryTotal->execute([$categoryId]);
                $categoryTotal = (int)$stmtRecalcCategoryTotal->fetchColumn();
                $stmtUpdateCategoryCount->execute([$categoryTotal, $categoryId]);
            }

            if ($appliedPromo && $promoDiscount > 0) {
                $stmtIncrementPromo = $database->prepare("UPDATE promocodes SET used_count = used_count + 1 WHERE id = ? AND used_count < usage_limit");
                $stmtIncrementPromo->execute([$appliedPromo['id']]);

                if ($stmtIncrementPromo->rowCount() === 0) {
                    throw new RuntimeException('Промокод больше недоступен.');
                }

                $stmtPromoUsage = $database->prepare("INSERT INTO promocode_usages (promocode_id, order_id, user_id, discount_amount) VALUES (?, ?, ?, ?)");
                $stmtPromoUsage->execute([$appliedPromo['id'], $order_id, $_SESSION['user_id'], $promoDiscount]);
            }

            $database->commit();
        } catch (Throwable $exception) {
            $database->rollBack();
            $orderError = 'Не удалось оформить заказ. ' . $exception->getMessage();
        }

        if (!$orderError) {
            $idempotentKey = uniqid('', true);

            $paymentData = [
                'amount' => [
                    'value' => number_format($allPrice, 2, '.', ''),
                    'currency' => 'RUB',
                ],
                'capture' => true,
                'confirmation' => [
                    'type' => 'redirect',
                    'return_url' => 'http://goncharok/?page=user_orders',
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

            if ($result === false) {
                $orderError = 'Ошибка при создании платежа. Попробуйте позже.';
            } else {
                $response = json_decode($result, true);

                foreach ($cart as $cartItem) {
                    $database->query("DELETE FROM carts WHERE id = " . (int)$cartItem['cart_id']);
                }
                $_SESSION['delivery_address'] = '';
                $_SESSION['delivery_method'] = 'courier';
                unset($_SESSION['applied_promo_id'], $_SESSION['applied_promo_code']);
                header('Location: ' . $response['confirmation']['confirmation_url']);
                exit;
            }
        }
    }
}

?>

<!-- BASKET CONTENT START -->
<div class="basket_content container mt-105">
    <div class="basket_layout">
        <!-- BASKET ITEMS START -->
        <div class="basket_items">
            <?php if ($cartMessage): ?>
                <div class="order_error"><?= htmlspecialchars($cartMessage, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <!-- ITEM 1 START -->
            <?php if (empty($cart)): ?>
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
                            <img src="<?= $image['path'] ?>" alt="ВАСИЛЬКОВОЕ ПОЛЕ">
                        </div>
                        <div class="item_info">
                            <h3><?= $item['title'] ?></h3>
                        </div>
                        <div class="item_controls">
                            <form action="" method="post">
                                <input type="hidden" name="action" value="minus">
                                <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                <button class="quantity_btn minus" type="submit">
                                    <img src="assets/media/images/index/minus_icon.svg" alt="">
                                </button>
                            </form>

                            <span class="quantity"><?= $item['count'] ?></span>

                            <form action="" method="post">
                                <input type="hidden" name="action" value="plus">
                                <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                <button class="quantity_btn plus" type="submit">
                                    <img src="assets/media/images/index/plus_icon.svg" alt="">
                                </button>
                            </form>

                        </div>
                        <div class="item_price"><?= number_format((float)$item['price'] * (int)$item['count'], 0, ',', ' ') ?> ₽</div>
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
                <?php if (empty($cart)): ?>
                    <p>Добавьте товары в корзину, чтобы выбрать способ доставки.</p>
                <?php else: ?>
                    <form class="delivery_form" method="post">
                        <input type="hidden" name="action" value="set_delivery">
                        <label class="delivery_option" for="delivery_courier">
                            <input type="radio" id="delivery_courier" name="delivery_method" value="courier" <?= $deliveryMethod === 'courier' ? 'checked' : '' ?>>
                            <span>Курьерская доставка (490 ₽)</span>
                        </label>
                        <div class="delivery_address_field" <?= $deliveryMethod === 'courier' ? '' : 'style="display:none;"' ?>>
                            <label for="delivery_address">Адрес доставки</label>
                            <input type="text" id="delivery_address" name="delivery_address" value="<?= $deliveryAddressEsc ?>" placeholder="Город, улица, дом, квартира" class="delivery_address_input<?= $addressInvalid ? ' input_error' : '' ?>">
                            <?php if ($addressInvalid): ?>
                                <span class="field_error">Укажите адрес для доставки.</span>
                            <?php endif; ?>
                        </div>
                        <label class="delivery_option" for="delivery_pickup">
                            <input type="radio" id="delivery_pickup" name="delivery_method" value="pickup" <?= $deliveryMethod === 'pickup' ? 'checked' : '' ?>>
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
                        <span><?= number_format($totalPrice, 0, ',', ' ') ?> ₽</span>
                    </div>
                    <div class="summary_row">
                        <span>доставка (<?= $deliveryLabel ?>)</span>
                        <span><?= number_format($deliveryPrice, 0, ',', ' ') ?> ₽</span>
                    </div>
                    <div class="summary_row">
                        <span>скидка</span>
                    <span><?= $promoDiscount > 0 ? '- ' . number_format($promoDiscount, 0, ',', ' ') : '0' ?> ₽</span>
                    </div>
                </div>

                <div class="promo_section">
                <?php if ($promoMessage): ?>
                    <div class="promo_status success"><?= htmlspecialchars($promoMessage, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <?php if ($promoError): ?>
                    <div class="promo_status error"><?= htmlspecialchars($promoError, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <?php if ($appliedPromo): ?>
                    <form method="post">
                        <input type="hidden" name="action" value="remove_promo">
                        <div class="promo_applied_tag">
                            <span><?= htmlspecialchars($appliedPromoCode ?? $appliedPromo['code'], ENT_QUOTES, 'UTF-8') ?></span>
                            <strong>-<?= number_format($promoDiscount, 0, ',', ' ') ?> ₽</strong>
                        </div>
                        <button type="submit" class="promo_remove_btn">Удалить</button>
                    </form>
                <?php else: ?>
                    <form method="post">
                        <input type="hidden" name="action" value="apply_promo">
                        <input type="text" name="promo_code" placeholder="введите промокод" value="<?= htmlspecialchars($promoInputValue, ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit">Применить</button>
                    </form>
                <?php endif; ?>
                </div>

                <div class="summary_total">
                    <span>итого</span>
                    <span><?= number_format($allPrice, 0, ',', ' ') ?> ₽</span>
                </div>
                <?php if ($orderError): ?>
                    <div class="order_error"><?= htmlspecialchars($orderError, ENT_QUOTES, 'UTF-8') ?></div>
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

<?php if (!empty($cart)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var deliveryForm = document.querySelector('.delivery_form');
            if (!deliveryForm) return;
            var addressField = deliveryForm.querySelector('.delivery_address_field');
            var addressInput = deliveryForm.querySelector('#delivery_address');
            var radios = deliveryForm.querySelectorAll('input[name="delivery_method"]');

            var submitForm = function() {
                if (typeof deliveryForm.requestSubmit === 'function') {
                    deliveryForm.requestSubmit();
                } else {
                    deliveryForm.submit();
                }
            };

            var toggleAddress = function() {
                if (!addressField) return;
                var courierSelected = deliveryForm.querySelector('input[name="delivery_method"][value="courier"]').checked;
                addressField.style.display = courierSelected ? '' : 'none';
            };

            var debounceTimer;
            var handleAddressInput = function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(submitForm, 600);
            };

            radios.forEach(function(radio) {
                radio.addEventListener('change', function() {
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
                addressInput.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
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