<?php
include 'header.php';
include 'db.php';

// --- DATA FETCHING ---
$classrooms_result = $conn->query("SELECT classroom_id, room_name FROM classrooms ORDER BY room_name");
$selected_room_id = null;
if (isset($_GET['room_id'])) {
    $selected_room_id = intval($_GET['room_id']);
}

$allocations = [];
$classroom_details = null;
if ($selected_room_id) {
    // Fetch classroom layout details
    $stmt = $conn->prepare("SELECT * FROM classrooms WHERE classroom_id = ?");
    $stmt->bind_param("i", $selected_room_id);
    $stmt->execute();
    $classroom_details = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Fetch allocated students for this classroom
    $stmt = $conn->prepare("
        SELECT sa.row_index, sa.col_index, sa.seat_pos, s.name, s.roll_no
        FROM seat_allocation sa
        JOIN students s ON sa.student_id = s.student_id
        WHERE sa.classroom_id = ?
        ORDER BY sa.row_index, sa.col_index, sa.seat_pos
    ");
    $stmt->bind_param("i", $selected_room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $allocations[$row['row_index']][$row['col_index']][$row['seat_pos']] = [
            'name' => $row['name'],
            'roll_no' => $row['roll_no']
        ];
    }
    $stmt->close();
}
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-eye-fill me-2"></i>View Seating Plan</h5>
            <!-- NEW: Export Button appears only when there's something to export -->
            <?php if ($selected_room_id && !empty($allocations)): ?>
                <a href="export_csv.php?room_id=<?= $selected_room_id ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-download me-1"></i> Export as CSV
                </a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-4">
                <div class="input-group">
                    <label class="input-group-text" for="room_id">Choose Classroom to View:</label>
                    <select name="room_id" id="room_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Please Select a Classroom --</option>
                        <?php while ($room = $classrooms_result->fetch_assoc()) { ?>
                            <option value="<?= $room['classroom_id'] ?>" <?= ($room['classroom_id'] == $selected_room_id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($room['room_name']) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </form>

            <?php if ($classroom_details): ?>
                <div class="seating-chart">
                    <div class="front-screen">SCREEN / FRONT OF CLASS</div>
                    <?php
                    $positions_base = ['L', 'M', 'R', 'S4', 'S5', 'S6'];
                    for ($r = 1; $r <= $classroom_details['total_rows']; $r++) {
                        echo '<div class="class-row">';
                        for ($c = 1; $c <= $classroom_details['total_cols']; $c++) {
                            echo '<div class="desk">';
                            $positions_to_show = array_slice($positions_base, 0, $classroom_details['seats_per_desk']);
                            foreach ($positions_to_show as $pos) {
                                if (isset($allocations[$r][$c][$pos])) {
                                    $student = $allocations[$r][$c][$pos];
                                    echo '<div class="seat occupied">';
                                    echo '<div class="seat-name">' . htmlspecialchars($student['name']) . '</div>';
                                    echo '<div class="seat-roll">' . htmlspecialchars($student['roll_no']) . '</div>';
                                    echo '</div>';
                                } else {
                                    echo '<div class="seat empty">(Empty)</div>';
                                }
                            }
                            echo '</div>'; 
                        }
                        echo '</div>';
                    }
                    ?>
                </div>
            <?php elseif(isset($_GET['room_id']) && $_GET['room_id'] != ''): ?>
                 <div class="alert alert-warning text-center">No allocation found for this classroom.</div>
            <?php else: ?>
                <div class="alert alert-info text-center">Please select a classroom to view its seating plan.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Custom CSS for the attractive seating chart -->
<style>
    .seating-chart { border: 1px solid var(--border-color); padding: 2rem; background: var(--card-bg-color); border-radius: 0.5rem; }
    .front-screen { text-align: center; margin-bottom: 2.5rem; padding: 0.75rem; background: #343a40; color: white; border-radius: 0.25rem; font-weight: 600; letter-spacing: 2px; font-size: 1.1rem;}
    .class-row { display: flex; justify-content-center; gap: 2rem; margin-bottom: 2rem; }
    .desk { display: flex; gap: 0.5rem; padding: 0.5rem; background: #e9ecef; border-radius: 0.5rem; border: 1px solid #ced4da;}
    .seat {
        border: 1px solid #adb5bd; border-radius: 0.375rem; padding: 0.5rem;
        width: 150px; height: 80px; display: flex; flex-direction: column;
        justify-content: center; align-items: center; text-align: center; 
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.075);
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        cursor: default;
    }
    .seat:hover { transform: translateY(-3px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    .seat-name { font-weight: 600; font-size: 0.95em; color: var(--heading-color); }
    .seat-roll { font-size: 0.8em; color: var(--text-color); }
    .seat.occupied { background-color: var(--primary-light); border-color: var(--primary-color);}
    .seat.empty { background: #f8f9fa; color: #adb5bd; font-style: italic; }
</style>

<?php include 'footer.php'; ?>

