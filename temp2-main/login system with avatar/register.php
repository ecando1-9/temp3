<?php

include 'config.php';

if(isset($_POST['submit'])){

   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $pass = mysqli_real_escape_string($conn, md5($_POST['password']));
   $cpass = mysqli_real_escape_string($conn, md5($_POST['cpassword']));
   $image = $_FILES['image']['name'];
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/'.$image;

   // Check if name is at least 6 characters long
   if(strlen($name) < 6) {
      $message[] = 'Username must be at least 6 characters long!';
   } else {
      // Check if name is unique (not already taken)
      $name_check = mysqli_query($conn, "SELECT * FROM `user_form` WHERE name = '$name'") or die('query failed');
      if(mysqli_num_rows($name_check) > 0){
         $message[] = 'Username is already taken. Please choose a different name.';
      } else {
         // Check if email already exists in the database
         $select = mysqli_query($conn, "SELECT * FROM `user_form` WHERE email = '$email'") or die('query failed');
         if(mysqli_num_rows($select) > 0){
            $message[] = 'Email is already registered. Please use a different email.';
         } else {
            // Check if passwords match
            if($pass != $cpass){
               $message[] = 'Confirm password does not match!';
            } elseif($image_size > 2000000){
               $message[] = 'Image size is too large! Maximum size is 2MB.';
            } else {
               // Insert the new user into the database
               $insert = mysqli_query($conn, "INSERT INTO `user_form`(name, email, password, image) VALUES('$name', '$email', '$pass', '$image')") or die('query failed');

               if($insert){
                  move_uploaded_file($image_tmp_name, $image_folder);
                  $message[] = 'Registration successful!';
                  header('location:home.php'); // Redirect to the home page after registration
               } else {
                  $message[] = 'Registration failed! Please try again.';
               }
            }
         }
      }
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Register</title>

   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="css/style.css">

   <style>
      /* Hide forms initially */
      .form-container {
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

      /* Full-screen background */
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
   
<div class="form-container" id="registerForm">
   <form action="" method="post" enctype="multipart/form-data">
      <h3>Register Now</h3>
      <?php
      if(isset($message)){
         foreach($message as $message){
            echo '<div class="message">'.$message.'</div>';
         }
      }
      ?>
      <!-- Retaining values in the form fields -->
      <input type="text" name="name" placeholder="Enter username" class="box" value="<?php echo isset($name) ? $name : ''; ?>" required>
      <input type="email" name="email" placeholder="Enter email" class="box" value="<?php echo isset($email) ? $email : ''; ?>" required>
      <input type="password" name="password" placeholder="Enter password" class="box" required>
      <input type="password" name="cpassword" placeholder="Confirm password" class="box" required>
      <input type="file" name="image" class="box" accept="image/jpg, image/jpeg, image/png">
      <input type="submit" name="submit" value="Register Now" class="btn">
      <p>Already have an account? <a href="login.php">Login now</a></p>
   </form>
</div>

<script>
   let clickCount = 0;

   document.body.addEventListener('click', function(event) {
      const registerForm = document.getElementById('registerForm');
      
      // Check if the click is outside the register form
      if (!registerForm.contains(event.target)) {
         clickCount++;

         if (clickCount === 2) {
            // Redirect to login.php after 2 clicks outside the form
            window.location.href = 'login.php';
         }
      }
   });
</script>

</body>
</html>
