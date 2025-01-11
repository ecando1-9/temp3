<?php
include 'config.php';
session_start();

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

// Check if the required input data is present
if (!isset($data['message']) || !isset($data['user_id'])) {
    http_response_code(400); // Bad request
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit();
}

// Sanitize and validate the message and recipient ID
$message = trim($data['message']);
$recipient_id = filter_var($data['user_id'], FILTER_VALIDATE_INT);

// Validate the message length (e.g., 1 to 500 characters)
if (empty($message) || strlen($message) > 9000000) {
    http_response_code(400); // Bad request
    echo json_encode(['success' => false, 'error' => 'Invalid message']);
    exit();
}

// Ensure the recipient ID is valid
if (!$recipient_id || $recipient_id <= 0) {
    http_response_code(400); // Bad request
    echo json_encode(['success' => false, 'error' => 'Invalid recipient']);
    exit();
}

// Use prepared statements to securely insert the message into the database
$query = "INSERT INTO chat_messages (user1_id, user2_id, message, sender_id, timestamp) VALUES (?, ?, ?, ?, NOW())";
$stmt = mysqli_prepare($conn, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'iisi', $user_id, $recipient_id, $message, $user_id);
    $result = mysqli_stmt_execute($stmt);
    
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }

    mysqli_stmt_close($stmt);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'error' => 'Failed to prepare statement']);
}

mysqli_close($conn);
?>
