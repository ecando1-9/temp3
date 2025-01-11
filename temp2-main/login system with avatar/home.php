<?php

include 'config.php';
session_start();
$user_id = $_SESSION['user_id'];

// Check if user is logged in
if (!isset($user_id)) {
    header('location:login.php');
}

if (isset($_GET['logout'])) {
    unset($user_id);
    session_destroy();
    header('location:login.php');
}

// Initialize error message variable
$error_msg = '';

// Process account deletion when form is submitted
if (isset($_POST['delete_account'])) {
    $password = mysqli_real_escape_string($conn, md5($_POST['password'])); // Hash entered password
    $checkbox = isset($_POST['confirm_deletion']) ? 1 : 0; // Check if checkbox is ticked

    // Check if the entered password matches the stored password
    $select_user = mysqli_query($conn, "SELECT * FROM `user_form` WHERE id = '$user_id'") or die('query failed');
    $fetch_user = mysqli_fetch_assoc($select_user);

    // If password matches, proceed with deletion
    if (mysqli_num_rows($select_user) > 0 && $fetch_user['password'] == $password) {
        if ($checkbox) {
            // Store deleted account information in deleted_accounts table
            $delete_account = mysqli_query($conn, "INSERT INTO `deleted_accounts` (user_id, name, email, image) 
                                                   VALUES ('".$fetch_user['id']."', '".$fetch_user['name']."', '".$fetch_user['email']."', '".$fetch_user['image']."')") or die('query failed');

            // Move chat history to deleted_chat_history table
            $chat_history = mysqli_query($conn, "INSERT INTO `deleted_chat_history` (deleted_user_id, sender_id, receiver_id, message)
                                                 SELECT '".$fetch_user['id']."', sender_id, receiver_id, message
                                                 FROM `chat_messages`
                                                 WHERE sender_id = '".$fetch_user['id']."' OR receiver_id = '".$fetch_user['id']."'") or die('query failed');

            // Now delete the user's chat history
            $delete_chat = mysqli_query($conn, "DELETE FROM `chat_messages` WHERE sender_id = '".$fetch_user['id']."' OR receiver_id = '".$fetch_user['id']."'") or die('query failed');

            // Finally, delete the user account
            $delete_user = mysqli_query($conn, "DELETE FROM `user_form` WHERE id = '$user_id'") or die('query failed');
            
            if ($delete_user) {
                session_destroy();
                header('location:login.php'); // Redirect to login page after account deletion
            }
        } else {
            $error_msg = 'You must confirm the deletion by checking the box.';
        }
    } else {
        $error_msg = 'Incorrect password. Please try again.'; // Show this message if password is incorrect
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Home</title>

   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="css/style.css">

   <style>
      .message {
         background-color: rebeccapurple;
         color: white;
         padding: 10px;
         margin-bottom: 10px;
         border-radius: 5px;
         text-align: center;
      }

      .btn {
         background-color: green;
         color: white;
         padding: 10px;
         border: none;
         border-radius: 5px;
         cursor: pointer;
      }

      .delete-btn {
         background-color: red;
         color: white;
         padding: 10px;
         border: none;
         border-radius: 5px;
         cursor: pointer;
      }

      .container {
         padding: 20px;
         text-align: center;
      }

      .profile img {
         width: 100px;
         height: 100px;
         border-radius: 50%;
         object-fit: cover;
      }

      .popup-container {
         display: none;
         position: fixed;
         top: 50%;
         left: 50%;
         transform: translate(-50%, -50%);
         background: white;
         padding: 20px;
         box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
         border-radius: 10px;
         z-index: 1000;
      }

      .popup-container input,
      .popup-container button {
         margin-top: 10px;
         padding: 10px;
         width: 100%;
         border-radius: 5px;
      }

      .popup-container .actions {
         display: flex;
         justify-content: space-between;
      }

      .popup-container button {
         width: 48%;
         cursor: pointer;
      }

      .overlay {
         display: none;
         position: fixed;
         top: 0;
         left: 0;
         width: 100%;
         height: 100%;
         background: rgba(0, 0, 0, 0.5);
         z-index: 999;
      }

      .error-msg {
         color: red;
         margin-top: 10px;
         font-size: 14px;
      }
   </style>
</head>
<body>

<div class="container">
   <div class="profile">
      <?php
         $select = mysqli_query($conn, "SELECT * FROM `user_form` WHERE id = '$user_id'") or die('query failed');
         if (mysqli_num_rows($select) > 0) {
            $fetch = mysqli_fetch_assoc($select);
         }
         if ($fetch['image'] == '') {
            echo '<img src="images/default-avatar.png">';
         } else {
            echo '<img src="uploaded_img/'.$fetch['image'].'">';
         }
      ?>
      <h3><?php echo $fetch['name']; ?></h3>
      <a href="update_profile.php" class="btn">Update Profile</a>
      <a href="chat.php" class="btn">Go to Chat</a>
      <a href="home.php?logout=<?php echo $user_id; ?>" class="delete-btn">Logout</a>
      
      <p>New? <a href="login.php">Login</a> or <a href="register.php">Register</a></p>

      <!-- Delete Account Button -->
      <button class="delete-btn" onclick="showPopup()">Delete Account</button>
   </div>
</div>

<!-- Overlay for popup -->
<div class="overlay" id="overlay"></div>

<!-- Popup for account deletion -->
<div class="popup-container" id="popup">
   <h3>Enter Password to Confirm Deletion</h3>
   <form action="" method="post" id="delete-form">
      <input type="password" name="password" placeholder="Enter Password" required>
      <input type="checkbox" name="confirm_deletion"> I confirm my account and data will be erased.
      <?php if (!empty($error_msg)): ?>
         <p class="error-msg"><?php echo $error_msg; ?></p>
      <?php endif; ?>
      <div class="actions">
         <button type="button" onclick="closePopup()">Cancel</button>
         <button type="submit" name="delete_account">Delete Account</button>
      </div>
   </form>
</div>

<script>
   // Show popup
   function showPopup() {
      document.getElementById('popup').style.display = 'block';
      document.getElementById('overlay').style.display = 'block';
   }

   // Close popup
   function closePopup() {
      document.getElementById('popup').style.display = 'none';
      document.getElementById('overlay').style.display = 'none';
   }
</script>

</body>
</html>
