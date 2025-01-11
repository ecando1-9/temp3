<?php
// get_security_status.php
include 'config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['hasSecurityKey' => false]);
    exit();
}

$user_id = $_SESSION['user_id'];

$query = "SELECT safety_key FROM user_form WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($safety_key);
$stmt->fetch();
$stmt->close();

echo json_encode(['hasSecurityKey' => !empty($safety_key)]);
?>
