<?php

declare(strict_types=1);

require_once __DIR__ . '/../database/connect.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['action'] ?? '') !== 'masterclass_request') {
    echo json_encode([
        'success' => false,
        'message' => 'Неверный тип запроса.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$fullName = trim($_POST['full_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$callTime = trim($_POST['call_time'] ?? '');

$allowedCallTimes = [
    '10:00-12:00',
    '12:00-15:00',
    '15:00-18:00',
    '18:00-21:00',
    'В любое время'
];

$errors = [];

if ($fullName === '') {
    $errors['full_name'] = 'Укажите ФИО';
}

if ($phone === '' || !preg_match('/^\+?[0-9\s\-\(\)]{7,20}$/u', $phone)) {
    $errors['phone'] = 'Введите корректный телефон';
}

if ($callTime === '' || !in_array($callTime, $allowedCallTimes, true)) {
    $errors['call_time'] = 'Выберите удобное время для звонка';
}

if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'errors' => $errors,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt = $database->prepare("INSERT INTO masterclass_requests (full_name, phone, call_time, status) VALUES (?, ?, ?, 'new')");
$stmt->execute([$fullName, $phone, $callTime]);

echo json_encode([
    'success' => true,
    'message' => 'Заявка отправлена! Мы свяжемся с вами в ближайшее время.',
], JSON_UNESCAPED_UNICODE);

