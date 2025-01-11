<?php
// fetch_unread_counts.php
include 'config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

$user_id = $_SESSION['user_id'];

// Prepared statement to prevent SQL injection
$unread_counts_query = "
    SELECT 
        CASE 
            WHEN cm.user1_id = ? THEN cm.user2_id
            ELSE cm.user1_id
        END AS chat_user_id,
        COUNT(*) AS unread_count
    FROM chat_messages cm
    WHERE (cm.user1_id = ? OR cm.user2_id = ?)
      AND cm.is_read = 0
      AND cm.user2_id = ?
    GROUP BY chat_user_id
";

$stmt = $conn->prepare($unread_counts_query);
if ($stmt === false) {
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit();
}
$stmt->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$unread_counts = [];
while ($row = $result->fetch_assoc()) {
    $unread_counts[] = [
        'chat_user_id' => $row['chat_user_id'],
        'unread_count' => $row['unread_count']
    ];
}

$stmt->close();

echo json_encode($unread_counts);
?>
