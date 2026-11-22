<?php require_once 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = (int)($_POST['room_id'] ?? 0);
  $st = mysqli_prepare($con, "SELECT COUNT(*) FROM reservations WHERE room_id = ?");
  mysqli_stmt_bind_param($st, "i", $id);
  mysqli_stmt_execute($st);
  $used = mysqli_fetch_row(mysqli_stmt_get_result($st))[0];

  if ($used > 0) {
    flash('error', "This room has $used reservation(s) and cannot be deleted. Set its status to Inactive instead.");
  } else {
    $st = mysqli_prepare($con, "DELETE FROM rooms WHERE room_id = ?");
    mysqli_stmt_bind_param($st, "i", $id);
    mysqli_stmt_execute($st);
    flash('success', 'Room deleted successfully.');
  }
}
header('Location: rooms.php');
exit;
?>