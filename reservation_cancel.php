<?php require_once 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = (int)($_POST['reservation_id'] ?? 0);
  $st = mysqli_prepare($con, "UPDATE reservations SET status = 'Cancelled' WHERE reservation_id = ?");
  mysqli_stmt_bind_param($st, "i", $id);
  mysqli_stmt_execute($st);
  flash('success', 'Reservation cancelled. The room is free for that time again.');
}
header('Location: reservations.php');
exit;
?>