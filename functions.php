<?php
define('ROOM_FIELDS', ['room_name','room_number','room_type','building','capacity','status']);
define('RES_FIELDS', ['teacher_name','department','room_id','purpose','reservation_date','start_time','end_time','participants','status']);

/* Read trimmed values from the submitted form */
function post_fields($fields)
{
  $d = [];
  foreach ($fields as $f) { $d[$f] = trim($_POST[$f] ?? ''); }
  return $d;
}

/* ---------- Schedule conflict detection ---------- */
/* Two time ranges overlap when each one starts before the other ends.
   Back-to-back bookings (10:00 end, 10:00 start) are NOT a conflict. */
function find_conflict($con, $room_id, $date, $start, $end, $exclude_id = 0)
{
  $sql = "SELECT teacher_name, start_time, end_time
          FROM reservations
          WHERE room_id = ?
            AND reservation_date = ?
            AND status IN ('Pending','Approved')
            AND reservation_id <> ?
            AND start_time < ?
            AND end_time > ?
          LIMIT 1";
  $st = mysqli_prepare($con, $sql);
  mysqli_stmt_bind_param($st, "isiss", $room_id, $date, $exclude_id, $end, $start);
  mysqli_stmt_execute($st);
  $row = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
  mysqli_stmt_close($st);
  return $row ? $row : false;
}

/* ---------- Validation and notification messages ---------- */
/* Returns [type, message]. type = success means every check passed. */
function validate_reservation($con, $d, $exclude_id = 0)
{
  foreach (['teacher_name','department','room_id','purpose','reservation_date','start_time','end_time','participants'] as $f) {
    if (trim((string)($d[$f] ?? '')) === '')
    { return ['error', "Please fill in all required fields."]; }
  }
  if (!ctype_digit((string)$d['participants']) || (int)$d['participants'] < 1)
  { return ['error', "Number of participants must be a whole number of at least 1."]; }

  $dt = DateTime::createFromFormat('Y-m-d', $d['reservation_date']);
  if (!$dt || $dt->format('Y-m-d') !== $d['reservation_date'])
  { return ['error', "Please enter a valid reservation date."]; }

  if (!in_array($d['status'], ['Pending','Approved','Rejected','Cancelled'], true))
  { return ['error', "Invalid reservation status."]; }

  /* Normalize times to HH:MM:SS so 10:00 and 10:00:00 compare equal */
  $d['start_time'] = date('H:i:s', strtotime($d['start_time']));
  $d['end_time']   = date('H:i:s', strtotime($d['end_time']));

  if ($d['end_time'] <= $d['start_time'])
  { return ['error', "Invalid Schedule: The end time must be later than the start time."]; }

  $rid = (int)$d['room_id'];
  $st = mysqli_prepare($con, "SELECT room_name, capacity, status FROM rooms WHERE room_id = ?");
  mysqli_stmt_bind_param($st, "i", $rid);
  mysqli_stmt_execute($st);
  $room = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
  mysqli_stmt_close($st);
  if (!$room)
  { return ['error', "Please select a valid room."]; }

  /* Only Pending/Approved reservations hold a room, so only they need the room checks */
  if (in_array($d['status'], ['Pending','Approved'], true)) {
    if ($room['status'] !== 'Available')
    { return ['error', "Room Unavailable: " . $room['room_name'] . " is " . strtolower($room['status']) . "."]; }

    if ((int)$d['participants'] > (int)$room['capacity'])
    { return ['error', "Capacity Exceeded: " . $room['room_name'] . " holds only " . $room['capacity'] . " participants."]; }

    $c = find_conflict($con, $rid, $d['reservation_date'], $d['start_time'], $d['end_time'], $exclude_id);
    if ($c) {
      return ['error', "Schedule Conflict: " . $room['room_name'] . " is already reserved on "
        . $d['reservation_date'] . " from " . date('g:i A', strtotime($c['start_time']))
        . " to " . date('g:i A', strtotime($c['end_time'])) . " by " . $c['teacher_name'] . "."];
    }
  }
  return ['success', "Reservation Successful: The selected room is available."];
}

function validate_room($d)
{
  foreach (ROOM_FIELDS as $f) {
    if (trim((string)($d[$f] ?? '')) === '')
    { return "Please fill in all required fields."; }
  }
  if (!ctype_digit((string)$d['capacity']) || (int)$d['capacity'] < 1 || (int)$d['capacity'] > 500)
  { return "Room capacity must be a whole number from 1 to 500."; }
  if (!in_array($d['room_type'], ['Computer Laboratory','Science Laboratory','Classroom','Conference Room','Other'], true))
  { return "Invalid room type."; }
  if (!in_array($d['status'], ['Available','Under Maintenance','Inactive'], true))
  { return "Invalid availability status."; }
  return null;
}

/* ---------- Save helpers (shared by the add and edit pages) ---------- */
/* Return [true, message] on success or [false, error message] */
function save_room($con, $r, $id = 0)
{
  $error = validate_room($r);
  if ($error) { return [false, $error]; }
  $cap = (int)$r['capacity'];
  if ($id) {
    $st = mysqli_prepare($con, "UPDATE rooms SET room_name=?, room_number=?, room_type=?, building=?, capacity=?, status=? WHERE room_id=?");
    mysqli_stmt_bind_param($st, "ssssisi", $r['room_name'], $r['room_number'], $r['room_type'], $r['building'], $cap, $r['status'], $id);
  } else {
    $st = mysqli_prepare($con, "INSERT INTO rooms (room_name, room_number, room_type, building, capacity, status) VALUES (?,?,?,?,?,?)");
    mysqli_stmt_bind_param($st, "ssssis", $r['room_name'], $r['room_number'], $r['room_type'], $r['building'], $cap, $r['status']);
  }
  if (mysqli_stmt_execute($st))
  { return [true, $id ? 'Room updated successfully.' : 'Room added successfully.']; }
  if (mysqli_errno($con) == 1062)
  { return [false, 'A room with this number already exists in that building.']; }
  return [false, 'Database error: ' . mysqli_error($con)];
}

function save_reservation($con, $r, $id = 0)
{
  [$type, $message] = validate_reservation($con, $r, $id);   /* conflict detection runs here */
  if ($type !== 'success') { return [false, $message]; }

  $rid   = (int)$r['room_id'];
  $part  = (int)$r['participants'];
  $start = date('H:i:s', strtotime($r['start_time']));
  $end   = date('H:i:s', strtotime($r['end_time']));
  if ($id) {
    $st = mysqli_prepare($con, "UPDATE reservations SET teacher_name=?, department=?, room_id=?, purpose=?, reservation_date=?, start_time=?, end_time=?, participants=?, status=? WHERE reservation_id=?");
    mysqli_stmt_bind_param($st, "ssissssisi", $r['teacher_name'], $r['department'], $rid, $r['purpose'], $r['reservation_date'], $start, $end, $part, $r['status'], $id);
  } else {
    $st = mysqli_prepare($con, "INSERT INTO reservations (teacher_name, department, room_id, purpose, reservation_date, start_time, end_time, participants, status) VALUES (?,?,?,?,?,?,?,?,?)");
    mysqli_stmt_bind_param($st, "ssissssis", $r['teacher_name'], $r['department'], $rid, $r['purpose'], $r['reservation_date'], $start, $end, $part, $r['status']);
  }
  if (mysqli_stmt_execute($st))
  { return [true, $id ? 'Reservation updated. ' . $message : $message]; }
  return [false, 'Database error: ' . mysqli_error($con)];
}
?>