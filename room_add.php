<?php require_once 'config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.html'); exit; }

[$ok, $msg] = save_room($con, post_fields(ROOM_FIELDS));
if ($ok) { flash('success', $msg); header('Location: rooms.php'); exit; }
message_page('error', $msg);
?>