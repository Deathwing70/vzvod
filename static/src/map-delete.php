<?php
header('Content-Type: application/json');

// Проверяем, что получен POST-запрос
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit();
}

// Проверяем, что получен JSON-запрос
$input = file_get_contents('php://input');
if (!($data = json_decode($input, true))) {
    http_response_code(400);
    exit();
}

// Подключаемся к базе данных
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "user_db";

$mysqli = new mysqli($servername, $username, $password, $dbname);
if ($mysqli->connect_errno) {
    http_response_code(500);
    exit();
}

// Получаем id маркера из запроса
$id = $data['id'];

// Удаляем маркер из базы данных
if ($stmt = $mysqli->prepare('DELETE FROM markers WHERE id=?')) {
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
} else {
    http_response_code(500);
    exit();
}

// Возвращаем успешный результат
echo json_encode(['status' => 'ok']);
