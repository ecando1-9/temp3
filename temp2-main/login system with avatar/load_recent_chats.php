<?php


// Fetch recent chats query
$recent_chats_query = "
    SELECT DISTINCT 
        CASE 
            WHEN cm.user1_id = '$user_id' THEN cm.user2_id
            ELSE cm.user1_id
        END AS chat_user_id,
        u.name,
        u.image,
        MAX(cm.timestamp) as last_message_time
    FROM chat_messages cm
    JOIN user_form u ON (u.id = cm.user1_id OR u.id = cm.user2_id)
    WHERE (cm.user1_id = '$user_id' OR cm.user2_id = '$user_id')
      AND u.id != '$user_id'
    GROUP BY chat_user_id, u.name, u.image
    ORDER BY last_message_time DESC
    LIMIT 10
";
$recent_chats_result = mysqli_query($conn, $recent_chats_query);

while ($chat = mysqli_fetch_assoc($recent_chats_result)): ?>
    <div class="recent-chat-item">
        <a href="user_chat.php?user_id=<?php echo $chat['chat_user_id']; ?>">
            <?php
            // Display user chat image or default avatar
            if($chat['image'] == '') {
                echo '<img src="images/default-avatar.png" class="chat-avatar">';
            } else {
                echo '<img src="uploaded_img/'.$chat['image'].'" class="chat-avatar">';
            }
            ?>
            <span class="chat-name"><?php echo htmlspecialchars($chat['name']); ?></span>
            <span class="last-message-time"><?php echo date('M d, H:i', strtotime($chat['last_message_time'])); ?></span>
        </a>
        <button class="delete-chat" data-chat-id="<?php echo $chat['chat_user_id']; ?>">Delete</button>
    </div>
<?php endwhile; ?>
