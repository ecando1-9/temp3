<?php
// delete_search_history.php

header('Content-Type: application/json');

try {
    // Database connection (adjust this to your actual database setup)
    $conn = new mysqli('localhost', 'username', 'password', 'your_database');

    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Clear search history for the user (adjust your logic)
    $userId = $_SESSION['user_id']; // Assuming user_id is stored in session
    
    // Query to delete or clear search history
    $deleteSearchHistoryQuery = "DELETE FROM search_history WHERE user_id = ?";
    $stmt = $conn->prepare($deleteSearchHistoryQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    // Query to delete or clear recent chats
    $deleteRecentChatsQuery = "DELETE FROM recent_chats WHERE user_id = ?";
    $stmt = $conn->prepare($deleteRecentChatsQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    $stmt->close();
    $conn->close();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
