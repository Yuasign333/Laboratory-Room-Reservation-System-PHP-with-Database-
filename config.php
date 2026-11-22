<?php
mysqli_report(MYSQLI_REPORT_OFF);   /* let mysqli return false on errors, like the lesson */
session_start();

$con = mysqli_connect("localhost", "root", "", "room_reservation_db");
if (!$con)
{ die('Could not connect: ' . mysqli_connect_error() . ' (run setup.php first)'); }
mysqli_set_charset($con, "utf8mb4");

/* Escape text before printing it in HTML */
function e($value)
{ return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }

/* Save a message to show on the next page */
function flash($type, $message)
{ $_SESSION['flash'] = ['type' => $type, 'msg' => $message]; }

/* Shared top of every PHP-generated page */
function page_start($title)
{
  $page = basename($_SERVER['PHP_SELF']);
  /* Reuse the <style> block of index.html so the CSS lives in one place */
  preg_match('/<style>(.*?)<\/style>/s', file_get_contents(__DIR__ . '/index.html'), $m);
  $css = $m[1] ?? '';
  echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">'
     . '<meta name="viewport" content="width=device-width, initial-scale=1">'
     . '<title>' . e($title) . '</title><style>' . $css . '</style></head><body>'
     . '<header class="topbar"><h1>Room Reservation System</h1><nav>'
     . '<a href="index.html">Home / Forms</a>'
     . '<a href="rooms.php" class="' . (in_array($page, ['rooms.php','room_edit.php']) ? 'active' : '') . '">Rooms</a>'
     . '<a href="reservations.php" class="' . (in_array($page, ['reservations.php','reservation_edit.php']) ? 'active' : '') . '">Reservations</a>'
     . '</nav></header><main class="container">';
  if (!empty($_SESSION['flash'])) {
    $f = $_SESSION['flash']; unset($_SESSION['flash']);
    echo '<div class="alert ' . e($f['type']) . '">' . e($f['msg']) . '</div>';
  }
}
function page_end()
{ echo '</main><footer class="footer">Laboratory/Room Reservation System &middot; Yuan Miguel V. Mendoza of BSIT 3-AIML</footer></body></html>'; }

/* Show a result message (used when a form is rejected) and stop */
function message_page($type, $message)
{
  page_start('Result');
  echo '<div class="alert ' . e($type) . '">' . e($message) . '</div>';
  echo '<p class="back"><a class="btn gray" href="javascript:history.back()">&laquo; Go back and fix the form</a></p>';
  page_end();
  exit;
}

require_once __DIR__ . '/functions.php';
?>