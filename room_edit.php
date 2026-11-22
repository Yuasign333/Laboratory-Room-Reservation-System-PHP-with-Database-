<?php require_once 'config.php';
$types    = ['Computer Laboratory', 'Science Laboratory', 'Classroom', 'Conference Room', 'Other'];
$statuses = ['Available', 'Under Maintenance', 'Inactive'];
$id = (int)($_GET['id'] ?? $_POST['room_id'] ?? 0);

$st = mysqli_prepare($con, "SELECT * FROM rooms WHERE room_id = ?");
mysqli_stmt_bind_param($st, "i", $id);
mysqli_stmt_execute($st);
$room = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
if (!$room) { flash('error', 'Room not found.'); header('Location: rooms.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $room = array_merge($room, post_fields(ROOM_FIELDS));
  [$ok, $msg] = save_room($con, $room, $id);
  if ($ok) { flash('success', $msg); header('Location: rooms.php'); exit; }
  $error = $msg;
}
page_start('Edit Room');
if ($error) { echo '<div class="alert error">' . e($error) . '</div>'; }
?>
<div class="card">
  <h2>Edit Room</h2>
  <form method="post" action="room_edit.php">
    <input type="hidden" name="room_id" value="<?= $id ?>">
    <div class="grid">
      <div><label>Room/Laboratory Name</label><input name="room_name" value="<?= e($room['room_name']) ?>" required></div>
      <div><label>Room Number</label><input name="room_number" value="<?= e($room['room_number']) ?>" required></div>
      <div><label>Room Type</label><select name="room_type">
        <?php foreach ($types as $t): ?><option <?= $room['room_type'] == $t ? 'selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?>
      </select></div>
      <div><label>Building</label><input name="building" value="<?= e($room['building']) ?>" required></div>
      <div><label>Room Capacity</label><input type="number" min="1" max="500" name="capacity" value="<?= e($room['capacity']) ?>" required></div>
      <div><label>Availability Status</label><select name="status">
        <?php foreach ($statuses as $s): ?><option <?= $room['status'] == $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
      </select></div>
    </div>
    <p><button class="btn" type="submit">Save Changes</button> <a class="btn gray" href="rooms.php">Cancel</a></p>
  </form>
</div>
<?php page_end(); ?>