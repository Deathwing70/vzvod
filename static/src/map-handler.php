<?php
// Подключение к базе данных MySQL
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "user_db";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Проверка, была ли отправлена форма с данными маркера
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $x = $_POST["x"];
    $y = $_POST["y"];
    $description = $_POST["description"];

    // Добавление нового маркера в базу данных
    $sql = "INSERT INTO markers (name, x, y, description) VALUES ('$name', '$x', '$y' , '$description')";

    if (mysqli_query($conn, $sql)) {
        //echo "New marker added successfully";
        $id = mysqli_insert_id($conn);
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }

    // Создание массива данных для отправки обратно на клиент
    $responseData = array(
        'id' => $id,
        'name' => $name,
        'x' => $x,
        'y' => $y,
        'description' => $description,
    );
    // Кодирование массива данных в формат JSON
    $jsonData = json_encode($responseData);
    // Установка заголовка для указания формата ответа
    header('Content-Type: application/json');
    // Отправка данных обратно на клиент
    echo $jsonData;
}

function dbMarkers (): array
{
    global $conn;
    // выборка маркеров из базы данных
    $sql = "SELECT * FROM markers";
    $result = mysqli_query($conn, $sql);
    $markers = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $markers[] = array(
            'id' => $row['id'],
            'x' => $row['x'],
            'y' => $row['y'],
            'name' => $row['name'],
            'description' => $row['description'],
        );
    }
    return $markers;
}