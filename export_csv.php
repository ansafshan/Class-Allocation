<?php
include 'db.php';

// --- VALIDATION ---
$room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;
if ($room_id == 0) {
    die("Error: No classroom specified for export.");
}

// --- DATA FETCHING ---
// Fetch classroom details to build the grid and name the file
$stmt = $conn->prepare("SELECT * FROM classrooms WHERE classroom_id = ?");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$classroom_details = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$classroom_details) {
    die("Error: Classroom not found.");
}

// Fetch allocated students for this classroom
$stmt = $conn->prepare("
    SELECT sa.row_index, sa.col_index, sa.seat_pos, s.roll_no
    FROM seat_allocation sa
    JOIN students s ON sa.student_id = s.student_id
    WHERE sa.classroom_id = ?
");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();
$allocations = [];
while ($row = $result->fetch_assoc()) {
    $allocations[$row['row_index']][$row['col_index']][$row['seat_pos']] = $row['roll_no'];
}
$stmt->close();

// --- CSV GENERATION ---
$filename = "seating_plan_" . str_replace(' ', '_', $classroom_details['room_name']) . ".csv";

// Set HTTP headers to trigger a file download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Open the output stream
$output = fopen('php://output', 'w');

// Create and write the header row for the CSV
$csv_header = [];
$positions_base = ['L', 'M', 'R', 'S4', 'S5', 'S6'];
for ($c = 1; $c <= $classroom_details['total_cols']; $c++) {
    $positions_to_show = array_slice($positions_base, 0, $classroom_details['seats_per_desk']);
    foreach ($positions_to_show as $pos) {
        $csv_header[] = "Bench {$c} - Seat {$pos}";
    }
}
fputcsv($output, $csv_header);

// Create and write the data rows
for ($r = 1; $r <= $classroom_details['total_rows']; $r++) {
    $csv_row = [];
    for ($c = 1; $c <= $classroom_details['total_cols']; $c++) {
        $positions_to_show = array_slice($positions_base, 0, $classroom_details['seats_per_desk']);
        foreach ($positions_to_show as $pos) {
            // If a student is allocated, add their roll number, otherwise add an empty string
            $csv_row[] = $allocations[$r][$c][$pos] ?? '';
        }
    }
    fputcsv($output, $csv_row);
}

fclose($output);
exit();
