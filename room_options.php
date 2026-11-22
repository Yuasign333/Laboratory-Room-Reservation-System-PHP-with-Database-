<?php
/* Gives index.html the list of rooms for its dropdowns (JSON) */
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');
$result = mysqli_query($con, "SELECT room_id, room_name, room_number, capacity, status FROM rooms ORDER BY room_name");
echo json_encode(mysqli_fetch_all($result, MYSQLI_ASSOC));
?>