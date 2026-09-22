<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode([]);
    exit();
}
include '../config/db.php';

// Fetch approved bookings with room and guest information
$query = "
    SELECT b.id, b.guest_name, b.check_in, b.check_out, r.room_number, r.room_type 
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    WHERE b.status = 'Approved'
";
$result = $conn->query($query);

$events = [];

while ($row = $result->fetch_assoc()) {
    // Add one day to check_out because FullCalendar treats the end date as exclusive
    $end_date = date('Y-m-d', strtotime($row['check_out'] . ' +1 day'));
    
    $events[] = [
        'id'    => $row['id'],
        'title' => 'Room ' . $row['room_number'] . ' - ' . $row['guest_name'],
        'start' => $row['check_in'],
        'end'   => $end_date,
        // Color code based on room type
        'color' => ($row['room_type'] === 'Suite') ? '#3b82f6' : (($row['room_type'] === 'Executive') ? '#10b981' : '#f59e0b')
    ];
}

header('Content-Type: application/json');
echo json_encode($events);