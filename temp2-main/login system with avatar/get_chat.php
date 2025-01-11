<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$chat_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// Fetch chat history between the current user and the selected user
$query = "
    SELECT cm.message, cm.sender_id, cm.timestamp 
    FROM chat_messages cm 
    WHERE (cm.user1_id = '$user_id' AND cm.user2_id = '$chat_user_id') 
    OR (cm.user1_id = '$chat_user_id' AND cm.user2_id = '$user_id')
    ORDER BY cm.timestamp ASC
";

$result = mysqli_query($conn, $query);

if (!$result) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit();
}

$messages = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Return the messages as JSON
echo json_encode($messages);
?>
