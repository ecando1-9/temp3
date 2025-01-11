<?php

include 'config.php';
session_start();

if(isset($_POST['submit'])){

   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $pass = mysqli_real_escape_string($conn, md5($_POST['password']));

   $select = mysqli_query($conn, "SELECT * FROM `user_form` WHERE email = '$email' AND password = '$pass'") or die('query failed');

   if(mysqli_num_rows($select) > 0){
      $row = mysqli_fetch_assoc($select);
      $_SESSION['user_id'] = $row['id'];
      header('location:home.php');
   }else{
      $message[] = 'incorrect email or password!';
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Login/Register</title>

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <style>
      /* Hide forms initially */
      .form-container {
         display: none;
         position: absolute;
         top: 50%;
         left: 50%;
         transform: translate(-50%, -50%);
         background-color: white;
         padding: 20px;
         box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
         border-radius: 10px;
         z-index: 1;
      }

      /* Full-screen black background */
      body {
         height: 100vh;
         margin: 0;
         background-color: black;
         display: flex;
         justify-content: center;
         align-items: center;
         cursor: pointer;
         color: white;
         position: relative;
      }

      /* Style for message container */
      .message {
         background-color: #ff4d4d;
         color: white;
         padding: 10px;
         margin-bottom: 10px;
         border-radius: 5px;
         text-align: center;
      }

      .box {
         width: 100%;
         margin-bottom: 10px;
         padding: 10px;
         border: 1px solid #ccc;
         border-radius: 5px;
      }

      .btn {
         background-color: #333;
         color: white;
         padding: 10px;
         border: none;
         border-radius: 5px;
         cursor: pointer;
      }

      a {
         color: blue;
         text-decoration: underline;
      }

   </style>
</head>
<body>

<!-- Login Form -->
<div class="form-container" id="loginForm">
   <form action="" method="post" enctype="multipart/form-data">
      <h3>Login Now</h3>
      <?php
      if(isset($message)){
         foreach($message as $message){
            echo '<div class="message">'.$message.'</div>';
         }
      }
      ?>
      <input type="email" name="email" placeholder="Enter email" class="box" required>
      <input type="password" name="password" placeholder="Enter password" class="box" required>
      <input type="submit" name="submit" value="Login Now" class="btn">
      <p>Don't have an account? <a href="register.php">Register now</a></p>
   </form>
</div>

<script>
   let clickCount = 0;

   document.body.addEventListener('click', function(event) {
      // Check if the click is outside the login form
      const loginForm = document.getElementById('loginForm');
      
      if (!loginForm.contains(event.target)) {
         clickCount++;

         if (clickCount === 2) {
            // Show login form after 2 clicks
            loginForm.style.display = 'block';
         } else if (clickCount === 3) {
            // Redirect to register.php after 3 clicks
            window.location.href = 'register.php';
         }
      }
   });
</script>

</body>
</html>
