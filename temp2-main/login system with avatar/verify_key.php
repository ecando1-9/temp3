<?php
include 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $entered_key = mysqli_real_escape_string($conn, $_POST['security_key']);

    // Fetch the security key from the database
    $query = "SELECT security_key FROM user_form WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['security_key'] === $entered_key) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'failure', 'message' => 'Invalid security key']);
        }
    } else {
        echo json_encode(['status' => 'failure', 'message' => 'User not found']);
    }

    $stmt->close();
    $conn->close();
}
?>
