<?php
include 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$logged_in_user_id = $_SESSION['user_id'];
$profile_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// Fetch user details
$query = "SELECT id, name, image FROM user_form WHERE id = '$profile_user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    echo '<p>User not found. <a href="profile.php">Go back to profiles</a></p>';
    exit();
}

// Check if the logged-in user has already blocked this user
$block_query = "SELECT * FROM blocked_users WHERE user_id = '$logged_in_user_id' AND blocked_user_id = '$profile_user_id'";
$block_result = mysqli_query($conn, $block_query);
$is_blocked = mysqli_num_rows($block_result) > 0;

// Block or unblock user
if (isset($_POST['action'])) {
    if ($_POST['action'] == 'block') {
        $block_query = "INSERT INTO blocked_users (user_id, blocked_user_id) VALUES ('$logged_in_user_id', '$profile_user_id')";
        mysqli_query($conn, $block_query);
        $is_blocked = true;
    } elseif ($_POST['action'] == 'unblock') {
        $unblock_query = "DELETE FROM blocked_users WHERE user_id = '$logged_in_user_id' AND blocked_user_id = '$profile_user_id'";
        mysqli_query($conn, $unblock_query);
        $is_blocked = false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['name']); ?>'s Profile</title>
    <link rel="stylesheet" href="css/user-d.css">
</head>
<body>
    <div class="profile-container">
        <div class="profile-image">
            <?php
                if ($user['image'] == '') {
                    echo '<img src="images/default-avatar.png" alt="Profile Image">';
                } else {
                    echo '<img src="uploaded_img/'.$user['image'].'" alt="Profile Image">';
                }
            ?>
        </div>
        <div class="profile-details">
            <h2><?php echo htmlspecialchars($user['name']); ?></h2>
        </div>
        <form action="view_profile.php?user_id=<?php echo $profile_user_id; ?>" method="post">
            <?php if ($is_blocked): ?>
                <button type="submit" name="action" value="unblock">Unblock User</button>
            <?php else: ?>
                <button type="submit" name="action" value="block">Block User</button>
            <?php endif; ?>
        </form>
        
    </div>
    <script src="assets/js/profile.js"></script>

</body>
</html>
