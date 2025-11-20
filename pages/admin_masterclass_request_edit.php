<?php

if(isset($_SESSION['user_id'])) {
    if($USER['role'] !== 'admin') {
        header('Location: ./?page=login');
    }
}

$statusOptions = [
    'new' => 'Новая',
    'booked' => 'Записали',
];

$requestId = (int)($_GET['id'] ?? 0);

if ($requestId <= 0) {
    header('Location: ./?page=admin_masterclass_requests');
    exit;
}

$stmt = $database->prepare("SELECT * FROM masterclass_requests WHERE id = ?");
$stmt->execute([$requestId]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    header('Location: ./?page=admin_masterclass_requests');
    exit;
}

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'update_status';

    if ($action === 'delete_request') {
        $deleteStmt = $database->prepare("DELETE FROM masterclass_requests WHERE id = ?");
        $deleteStmt->execute([$requestId]);
        header('Location: ./?page=admin_masterclass_requests&deleted=1');
        exit;
    }

    $newStatus = $_POST['status'] ?? 'new';

    if (!array_key_exists($newStatus, $statusOptions)) {
        $errorMessage = 'Недопустимый статус';
    } else {
        $updateStmt = $database->prepare("UPDATE masterclass_requests SET status = ? WHERE id = ?");
        $updateStmt->execute([$newStatus, $requestId]);
        $request['status'] = $newStatus;
        $successMessage = 'Статус заявки обновлён';
    }
}

?>

<div class="admin-container container mt-105">
    <?php include('./includes/admin_menu.php'); ?>

    <main class="admin-main-content">
        <div class="admin-header">
            <h2 class="admin-page-title">Заявка №<?= (int)$request['id'] ?></h2>
            <div class="admin-header-actions">
                <a class="admin-btn admin-btn-secondary" href="./?page=admin_masterclass_requests">Вернуться к списку</a>
                <a href="?exit">Выйти</a>
            </div>
        </div>

        <?php if ($successMessage): ?>
            <div class="admin-alert admin-alert-success"><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if ($errorMessage): ?>
            <div class="admin-alert admin-alert-danger"><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="admin-card">
            <h3>Информация о заявке</h3>
            <div class="admin-info-grid">
                <div>
                    <div class="admin-info-label">ФИО</div>
                    <div class="admin-info-value"><?= htmlspecialchars($request['full_name'], ENT_QUOTES, 'UTF-8') ?></div>
                </div>
                <div>
                    <div class="admin-info-label">Телефон</div>
                    <div class="admin-info-value">
                        <?= $request['phone'] ?>
                    </div>
                </div>
                <div>
                    <div class="admin-info-label">Когда звонить</div>
                    <div class="admin-info-value"><?= htmlspecialchars($request['call_time'], ENT_QUOTES, 'UTF-8') ?></div>
                </div>
                <div>
                    <div class="admin-info-label">Оставлена</div>
                    <div class="admin-info-value">
                        <?php
                        $createdAt = $request['created_at'] ?? '';
                        $createdAtFormatted = $createdAt;
                        if (!empty($createdAt)) {
                            $date = DateTime::createFromFormat('Y-m-d H:i:s', $createdAt);
                            if ($date) {
                                $createdAtFormatted = $date->format('d.m.Y H:i');
                            }
                        }
                        ?>
                        <?= htmlspecialchars($createdAtFormatted, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <h3>Изменить статус</h3>
            <form method="post" class="admin-status-form admin-status-form--inline">

                <select id="requestStatus" name="status" class="admin-search-input" required>
                    <?php foreach ($statusOptions as $value => $label): ?>
                        <option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" <?=$request['status'] === $value ? 'selected' : ''?>>
                            <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="admin-status-actions">
                    <button type="submit" name="action" value="update_status" class="admin-btn admin-btn-primary">Сохранить</button>
                    <button type="submit" name="action" value="delete_request" class="admin-btn admin-btn-danger">Удалить заявку</button>
                </div>
            </form>
        </div>
    </main>
</div>

