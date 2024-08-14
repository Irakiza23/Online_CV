<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cv";

// Database connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$table = filter_input(INPUT_GET, 'table', FILTER_SANITIZE_STRING);
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$table || !$id) {
    die("Invalid table or ID.");
}

$sql = "SELECT * FROM $table WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

header('Content-Type: application/json');
echo json_encode($result);

$stmt->close();
$conn->close();
?>
