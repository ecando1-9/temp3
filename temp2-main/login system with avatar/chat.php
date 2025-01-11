<?php
include 'config.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location:login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Secure the queries using prepared statements
// Fetch user information
$user_stmt = $conn->prepare("SELECT name, email, image FROM user_form WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();

// Fetch recent chats with unread message counts
$recent_chats_query = "
    SELECT 
        CASE 
            WHEN cm.user1_id = ? THEN cm.user2_id
            ELSE cm.user1_id
        END AS chat_user_id,
        u.name,
        u.image,
        MAX(cm.timestamp) AS last_message_time,
        COUNT(CASE 
                WHEN cm.is_read = 0 AND cm.user2_id = ? THEN 1 
                ELSE NULL 
              END) AS unread_count
    FROM chat_messages cm
    JOIN user_form u ON (u.id = cm.user1_id OR u.id = cm.user2_id)
    WHERE (cm.user1_id = ? OR cm.user2_id = ?)
      AND u.id != ?
    GROUP BY chat_user_id, u.name, u.image
    ORDER BY last_message_time DESC
    LIMIT 10
";

$recent_chats_stmt = $conn->prepare($recent_chats_query);
$recent_chats_stmt->bind_param("iiiii", $user_id, $user_id, $user_id, $user_id, $user_id);
$recent_chats_stmt->execute();
$recent_chats_result = $recent_chats_stmt->get_result();

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="chat-container">
        <!-- Profile Section -->
        <div class="profile-section">
            <div class="profile-icon">
                <a href="home.php">
                <?php
                    // Display user profile image or default avatar
                    if ($user['image'] == '') {
                        echo '<img src="images/default-avatar.png" class="profile-pic">';
                    } else {
                        echo '<img src="uploaded_img/'.$user['image'].'" class="profile-pic">';
                    }
                ?>
                </a>
            </div>
            <h4>Welcome, <?php echo htmlspecialchars($user['name']); ?></h4>
        </div>

        <!-- Chat Area -->
        <div class="chat-area">
            <!-- Search Bar with Search History -->
            <div class="search-bar">
                <input type="text" id="searchUser" placeholder="Search for a user" onfocus="showSearchHistory()">
            </div>
            <div id="searchResults"></div>

            <!-- Recent Searches -->
            <div class="recent-search-history">
                <h3>Recent Searches</h3>
                <div class="searchHistoryList">
                    <ul id="searchHistoryList"></ul>
                </div>
                <button id="clearSearchHistory">Clear Search History</button>
            </div>

            <!-- Recent Chats Section -->
            <div class="recent-chats">
                <h3>Recent Chats</h3>
                <?php while ($chat = $recent_chats_result->fetch_assoc()): ?>
                    <div class="recent-chat-item" data-chat-id="<?php echo $chat['chat_user_id']; ?>">
                        <div class="chat-info">
                            <?php
                                // Display user chat image or default avatar
                                if ($chat['image'] == '') {
                                    echo '<img src="images/default-avatar.png" class="chat-avatar">';
                                } else {
                                    echo '<img src="uploaded_img/'.$chat['image'].'" class="chat-avatar">';
                                }
                            ?>
                            
                            <div class="chat-details">
                                <a href="user_chat.php?user_id=<?php echo $chat['chat_user_id']; ?>">
                                    <span class="chat-name"><?php echo htmlspecialchars($chat['name']); ?></span>
                                    <?php if ($chat['unread_count'] > 0): ?>
                                        <span class="unread-count"><?php echo $chat['unread_count']; ?>New messages</span>
                                    <?php endif; ?>
                                </a>
                                <span class="last-message-time"><?php echo date('M d, H:i', strtotime($chat['last_message_time'])); ?></span>
                            </div>
                        </div>
                        
                        <div class="chat-actions">
                            <button class="delete-chat" data-chat-id="<?php echo $chat['chat_user_id']; ?>">Delete</button>
                            <!-- Optional: Add a profile view button -->
                            
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <!-- Include the chat JavaScript file -->
    <script src="assets/js/chat.js"></script>
  

</body>
</html>


