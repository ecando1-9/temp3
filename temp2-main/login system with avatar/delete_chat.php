<?php
include 'config.php';
session_start();

// Check if user is logged in
if(!isset($_SESSION['user_id'])){
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$chat_user_id = isset($_POST['chat_user_id']) ? $_POST['chat_user_id'] : '';

if(empty($chat_user_id)){
    echo json_encode(['status' => 'error', 'message' => 'Chat user ID is missing.']);
    exit();
}

// Delete chat messages between logged-in user and chat_user_id
$query = "
    DELETE FROM chat_messages 
    WHERE (user1_id = '$user_id' AND user2_id = '$chat_user_id') 
       OR (user1_id = '$chat_user_id' AND user2_id = '$user_id')
";

if(mysqli_query($conn, $query)){
    echo json_encode(['status' => 'success', 'message' => 'Chat deleted successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete chat.']);
}
?>
