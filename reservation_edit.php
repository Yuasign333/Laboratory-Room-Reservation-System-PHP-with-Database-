<?php require_once 'config.php';
$statuses = ['Pending', 'Approved', 'Rejected', 'Cancelled'];
$id = (int)($_GET['id'] ?? $_POST['reservation_id'] ?? 0);

$st = mysqli_prepare($con, "SELECT * FROM reservations WHERE reservation_id = ?");
mysqli_stmt_bind_param($st, "i", $id);
mysqli_stmt_execute($st);
$res = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
if (!$res) { flash('error', 'Reservation not found.'); header('Location: reservations.php'); exit; }
$res['start_time'] = substr($res['start_time'], 0, 5);
$res['end_time']   = substr($res['end_time'], 0, 5);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $res = array_merge($res, post_fields(RES_FIELDS));
  [$ok, $msg] = save_reservation($con, $res, $id);
  if ($ok) { flash('success', $msg); header('Location: reservations.php'); exit; }
  $error = $msg;
}
$rooms = mysqli_query($con, "SELECT room_id, room_name, room_number, capacity, status FROM rooms ORDER BY room_name");
page_start('Edit Reservation');
if ($error) { echo '<div class="alert error">' . e($error) . '</div>'; }
?>
<div class="card">
  <h2>Edit Reservation</h2>
  <form method="post" action="reservation_edit.php">
    <input type="hidden" name="reservation_id" value="<?= $id ?>">
    <div class="grid">
      <div><label>Name of Requesting Teacher</label><input name="teacher_name" value="<?= e($res['teacher_name']) ?>" required></div>
      <div><label>Department</label><input name="department" value="<?= e($res['department']) ?>" required></div>
      <div><label>Room/Laboratory</label><select name="room_id" required>
        <?php while ($r = mysqli_fetch_assoc($rooms)): ?>
          <option value="<?= (int)$r['room_id'] ?>" <?= $res['room_id'] == $r['room_id'] ? 'selected' : '' ?>>
            <?= e($r['room_name'] . ' (' . $r['room_number'] . ', cap. ' . $r['capacity'] . ($r['status'] != 'Available' ? ', ' . $r['status'] : '') . ')') ?>
          </option>
        <?php endwhile; ?></select></div>
      <div><label>Purpose of Reservation</label><input name="purpose" value="<?= e($res['purpose']) ?>" required></div>
      <div><label>Reservation Date</label><input type="date" name="reservation_date" value="<?= e($res['reservation_date']) ?>" required></div>
      <div><label>Start Time</label><input type="time" name="start_time" value="<?= e($res['start_time']) ?>" required></div>
      <div><label>End Time</label><input type="time" name="end_time" value="<?= e($res['end_time']) ?>" required></div>
      <div><label>Number of Participants</label><input type="number" min="1" name="participants" value="<?= e($res['participants']) ?>" required></div>
      <div><label>Reservation Status</label><select name="status">
        <?php foreach ($statuses as $s): ?><option <?= $res['status'] == $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
      </select></div>
    </div>
    <p><button class="btn" type="submit">Save Changes</button> <a class="btn gray" href="reservations.php">Cancel</a></p>
  </form>
</div>
<?php page_end(); ?>