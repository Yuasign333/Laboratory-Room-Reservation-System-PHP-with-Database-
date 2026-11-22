<?php require_once 'config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.html'); exit; }

$data = post_fields(RES_FIELDS);
$data['status'] = 'Pending';      /* every new request starts as Pending */

[$ok, $msg] = save_reservation($con, $data);
if ($ok) { flash('success', $msg); header('Location: reservations.php'); exit; }
message_page('error', $msg);
?>