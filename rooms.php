<?php require_once 'config.php';
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
  $like = "%$q%";
  $st = mysqli_prepare($con, "SELECT * FROM rooms WHERE room_name LIKE ? OR room_number LIKE ? OR room_type LIKE ? OR building LIKE ? OR status LIKE ? ORDER BY room_name");
  mysqli_stmt_bind_param($st, "sssss", $like, $like, $like, $like, $like);
  mysqli_stmt_execute($st);
  $result = mysqli_stmt_get_result($st);
} else {
  $result = mysqli_query($con, "SELECT * FROM rooms ORDER BY room_name");
}
page_start('Rooms');
?>
<div class="card">
  <h2>Rooms / Laboratories</h2>
  <form class="search" method="get" action="rooms.php">
    <div style="flex:1;min-width:220px"><label>Search rooms</label>
      <input type="text" name="q" value="<?= e($q) ?>" placeholder="Name, number, type, building or status"></div>
    <button class="btn" type="submit">Search</button>
    <a class="btn gray" href="rooms.php">Reset</a>
    <a class="btn green" href="index.html#add-room">+ Add Room</a>
  </form>
  <div class="table-wrap">
  <table>
    <tr><th>Name</th><th>Number</th><th>Type</th><th>Building</th><th>Capacity</th><th>Status</th><th>Actions</th></tr>
    <?php if (mysqli_num_rows($result) == 0): ?>
      <tr><td colspan="7">No rooms found.</td></tr>
    <?php endif; ?>
    <?php while ($r = mysqli_fetch_assoc($result)): ?>
    <tr>
      <td><?= e($r['room_name']) ?></td>
      <td><?= e($r['room_number']) ?></td>
      <td><?= e($r['room_type']) ?></td>
      <td><?= e($r['building']) ?></td>
      <td><?= e($r['capacity']) ?></td>
      <td><span class="badge <?= e(explode(' ', $r['status'])[0]) ?>"><?= e($r['status']) ?></span></td>
      <td>
        <a class="btn small" href="room_edit.php?id=<?= (int)$r['room_id'] ?>">Edit</a>
        <form method="post" action="room_delete.php" style="display:inline" onsubmit="return confirm('Delete this room?');">
          <input type="hidden" name="room_id" value="<?= (int)$r['room_id'] ?>">
          <button class="btn small red" type="submit">Delete</button>
        </form>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
  </div>
</div>
<?php page_end(); ?>