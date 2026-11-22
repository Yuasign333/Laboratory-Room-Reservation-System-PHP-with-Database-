<?php require_once 'config.php';
$date    = trim($_GET['date'] ?? '');
$room    = (int)($_GET['room_id'] ?? 0);
$teacher = trim($_GET['teacher'] ?? '');

$where = []; $types = ''; $params = [];
if ($date !== '')    { $where[] = "r.reservation_date = ?";  $types .= 's'; $params[] = $date; }
if ($room > 0)       { $where[] = "r.room_id = ?";           $types .= 'i'; $params[] = $room; }
if ($teacher !== '') { $where[] = "r.teacher_name LIKE ?";   $types .= 's'; $params[] = "%$teacher%"; }

$sql = "SELECT r.*, rm.room_name FROM reservations r JOIN rooms rm ON rm.room_id = r.room_id"
     . ($where ? " WHERE " . implode(" AND ", $where) : "")
     . " ORDER BY r.reservation_date DESC, r.start_time";
$st = mysqli_prepare($con, $sql);
if ($params) { mysqli_stmt_bind_param($st, $types, ...$params); }
mysqli_stmt_execute($st);
$result = mysqli_stmt_get_result($st);
$allRooms = mysqli_query($con, "SELECT room_id, room_name FROM rooms ORDER BY room_name");
page_start('Reservations');
?>
<div class="card">
  <h2>Reservations</h2>
  <form class="search" method="get" action="reservations.php">
    <div><label>Date</label><input type="date" name="date" value="<?= e($date) ?>"></div>
    <div><label>Room</label><select name="room_id"><option value="0">All rooms</option>
      <?php while ($r = mysqli_fetch_assoc($allRooms)): ?>
        <option value="<?= (int)$r['room_id'] ?>" <?= $room == $r['room_id'] ? 'selected' : '' ?>><?= e($r['room_name']) ?></option>
      <?php endwhile; ?></select></div>
    <div><label>Teacher</label><input type="text" name="teacher" value="<?= e($teacher) ?>" placeholder="Teacher name"></div>
    <button class="btn" type="submit">Search</button>
    <a class="btn gray" href="reservations.php">Reset</a>
    <a class="btn green" href="index.html#new-reservation">+ New Reservation</a>
  </form>
  <div class="table-wrap">
  <table>
    <tr><th>Teacher</th><th>Department</th><th>Room</th><th>Purpose</th><th>Date</th><th>Time</th><th>Participants</th><th>Status</th><th>Actions</th></tr>
    <?php if (mysqli_num_rows($result) == 0): ?>
      <tr><td colspan="9">No reservations found.</td></tr>
    <?php endif; ?>
    <?php while ($r = mysqli_fetch_assoc($result)): ?>
    <tr>
      <td><?= e($r['teacher_name']) ?></td>
      <td><?= e($r['department']) ?></td>
      <td><?= e($r['room_name']) ?></td>
      <td><?= e($r['purpose']) ?></td>
      <td><?= e($r['reservation_date']) ?></td>
      <td><?= date('g:i A', strtotime($r['start_time'])) ?> - <?= date('g:i A', strtotime($r['end_time'])) ?></td>
      <td><?= e($r['participants']) ?></td>
      <td><span class="badge <?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
      <td>
        <a class="btn small" href="reservation_edit.php?id=<?= (int)$r['reservation_id'] ?>">Edit</a>
        <?php if ($r['status'] !== 'Cancelled'): ?>
        <form method="post" action="reservation_cancel.php" style="display:inline" onsubmit="return confirm('Cancel this reservation?');">
          <input type="hidden" name="reservation_id" value="<?= (int)$r['reservation_id'] ?>">
          <button class="btn small red" type="submit">Cancel</button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
  </div>
</div>
<?php page_end(); ?>