<?php
mysqli_report(MYSQLI_REPORT_OFF);
$con = mysqli_connect("localhost", "root", "");
if (!$con)
{ die('Could not connect: ' . mysqli_connect_error()); }

if (mysqli_query($con, "CREATE DATABASE IF NOT EXISTS room_reservation_db"))
{ echo "Database ready<br />"; }
else
{ die("Error creating database: " . mysqli_error($con)); }

mysqli_select_db($con, "room_reservation_db");

$rooms = "CREATE TABLE IF NOT EXISTS rooms (
  room_id INT AUTO_INCREMENT PRIMARY KEY,
  room_name VARCHAR(100) NOT NULL,
  room_number VARCHAR(20) NOT NULL,
  room_type VARCHAR(50) NOT NULL,
  building VARCHAR(100) NOT NULL,
  capacity INT NOT NULL,
  status ENUM('Available','Under Maintenance','Inactive') NOT NULL DEFAULT 'Available',
  UNIQUE KEY uq_room (room_number, building)
)";
echo mysqli_query($con, $rooms) ? "Table rooms ready<br />" : "Error creating rooms: " . mysqli_error($con) . "<br />";

$res = "CREATE TABLE IF NOT EXISTS reservations (
  reservation_id INT AUTO_INCREMENT PRIMARY KEY,
  teacher_name VARCHAR(100) NOT NULL,
  department VARCHAR(100) NOT NULL,
  room_id INT NOT NULL,
  purpose VARCHAR(255) NOT NULL,
  reservation_date DATE NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  participants INT NOT NULL,
  status ENUM('Pending','Approved','Rejected','Cancelled') NOT NULL DEFAULT 'Pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE RESTRICT
)";
echo mysqli_query($con, $res) ? "Table reservations ready<br />" : "Error creating reservations: " . mysqli_error($con) . "<br />";

/* Sample data from the activity example (inserted only once) */
$count = mysqli_fetch_row(mysqli_query($con, "SELECT COUNT(*) FROM rooms"))[0];
if ($count == 0) {
  mysqli_query($con, "INSERT INTO rooms (room_name, room_number, room_type, building, capacity, status) VALUES
    ('Computer Lab 1', 'CL-101', 'Computer Laboratory', 'IT Building', 40, 'Available'),
    ('Science Lab 2',  'SL-202', 'Science Laboratory',  'Science Building', 30, 'Available'),
    ('Room 301',       'R-301',  'Classroom',           'Main Building', 50, 'Available'),
    ('Conference Room A', 'CR-A', 'Conference Room',    'Admin Building', 20, 'Under Maintenance')");
  mysqli_query($con, "INSERT INTO reservations (teacher_name, department, room_id, purpose, reservation_date, start_time, end_time, participants, status) VALUES
    ('Ms. Reyes', 'IT', 1, 'Programming class', '2026-11-20', '08:00', '10:00', 35, 'Approved'),
    ('Mr. Cruz',  'IT', 1, 'Database class',    '2026-11-20', '10:00', '12:00', 35, 'Approved'),
    ('Ms. Lopez', 'Science', 2, 'Chemistry lab', '2026-11-20', '08:00', '11:00', 25, 'Approved')");
  echo "Sample rooms and reservations added<br />";
}
mysqli_close($con);
?>