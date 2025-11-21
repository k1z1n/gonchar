<?php

if (isset($_SESSION['user_id'])) {
    if ($USER['role'] !== 'admin') {
        header('Location: ./?page=login');
        exit;
    }
} else {
    header('Location: ./?page=login');
    exit;
}

$errors = [];
$successMessage = null;
$action = $_POST['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {
    if ($action === 'create') {
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $usageLimit = (int)($_POST['usage_limit'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);

        if ($code === '') {
            $errors[] = 'Введите название промокода.';
        } elseif (!preg_match('/^[A-Z0-9_-]{3,40}$/u', $code)) {
            $errors[] = 'Название может содержать только латинские буквы, цифры, дефис и нижнее подчёркивание.';
        }

        if ($usageLimit <= 0) {
            $errors[] = 'Количество использований должно быть больше нуля.';
        }

        if ($amount <= 0) {
            $errors[] = 'Номинал должен быть больше нуля.';
        }

        if (empty($errors)) {
            $stmtCheck = $database->prepare("SELECT COUNT(*) FROM promocodes WHERE code = ?");
            $stmtCheck->execute([$code]);

            if ($stmtCheck->fetchColumn() > 0) {
                $errors[] = 'Такой промокод уже существует.';
            } else {
                $stmtInsert = $database->prepare("INSERT INTO promocodes (code, amount, usage_limit) VALUES (?, ?, ?)");
                $stmtInsert->execute([$code, $amount, $usageLimit]);
                $successMessage = 'Промокод успешно добавлен.';
            }
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['promo_id'] ?? 0);
        if ($id <= 0) {
            $errors[] = 'Не удалось определить промокод.';
        } else {
            $stmtDelete = $database->prepare("DELETE FROM promocodes WHERE id = ?");
            $stmtDelete->execute([$id]);
            $successMessage = 'Промокод удалён.';
        }
    }
}

$stmtPromos = $database->query("SELECT * FROM promocodes ORDER BY created_at DESC");
$promocodes = $stmtPromos->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="admin-container container">
    <?php include('./includes/admin_menu.php'); ?>

    <main class="admin-main-content">
        <div class="admin-header">
            <h2 class="admin-page-title">Промокоды</h2>
            <div>
                <a href="?exit" class="admin-btn admin-btn-secondary">Выйти</a>
            </div>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="admin-alert admin-alert-error">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($successMessage): ?>
            <div class="admin-alert admin-alert-success">
                <?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div class="admin-card">
            <h3>Добавить промокод</h3>
            <form method="post" class="admin-form">
                <input type="hidden" name="action" value="create">
                <div class="admin-form-group">
                    <label for="promo_code">Название</label>
                    <input type="text" id="promo_code" name="code" placeholder="Например, SALE500" required>
                </div>
                <div class="admin-form-row">
                    <div class="admin-form-group">
                        <label for="promo_limit">Кол-во использований</label>
                        <input type="number" id="promo_limit" name="usage_limit" min="1" value="1" required>
                    </div>
                    <div class="admin-form-group">
                        <label for="promo_amount">Номинал (₽)</label>
                        <input type="number" id="promo_amount" name="amount" min="1" step="1" value="500" required>
                    </div>
                </div>
                <button type="submit" class="btn_bg">Добавить</button>
            </form>
        </div>

        <div class="admin-table-container mt-20">
            <table class="admin-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Название</th>
                    <th>Номинал</th>
                    <th>Использовано</th>
                    <th>Всего</th>
                    <th>Создан</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                <?php if (empty($promocodes)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px;">Промокоды не найдены</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($promocodes as $promo): ?>
                        <tr>
                            <td><?= (int)$promo['id'] ?></td>
                            <td><?= htmlspecialchars($promo['code'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= number_format($promo['amount'], 0, ',', ' ') ?> ₽</td>
                            <td><?= (int)$promo['used_count'] ?></td>
                            <td><?= (int)$promo['usage_limit'] ?></td>
                            <td><?= htmlspecialchars(date('d.m.Y H:i', strtotime($promo['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <form method="post" onsubmit="return confirm('Удалить промокод?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="promo_id" value="<?= (int)$promo['id'] ?>">
                                    <button type="submit" class="admin-btn admin-btn-danger">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

