<?php
include 'config.php';

$query = $_GET['query'];

$sql = "SELECT id, name FROM user_form WHERE name LIKE '%$query%' LIMIT 5";
$result = mysqli_query($conn, $sql);

$users = [];
while($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

echo json_encode($users);
?>
