<?php
// All PHP logic that might redirect MUST come before any HTML output.
include 'db.php';

// --- INITIAL SETUP ---
$session_id = isset($_GET['session_id']) ? intval($_GET['session_id']) : 0;
if ($session_id == 0) {
    // We can't show a pretty error if the header hasn't been included yet,
    // so we just die with a simple message. A more advanced router would handle this.
    die("Error: No exam session selected.");
}

// --- ACTION HANDLER ---
// This block now runs before the header is included.
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_batch') {
    $dept_id = $_POST['dept_id'];
    $year_id = $_POST['year_id'];

    // 1. Get all students from the selected batch
    $student_stmt = $conn->prepare("SELECT student_id FROM students WHERE dept_id = ? AND year_id = ?");
    $student_stmt->bind_param("ii", $dept_id, $year_id);
    $student_stmt->execute();
    $students_to_add = $student_stmt->get_result();

    // 2. Get students ALREADY in this session to prevent duplicates
    $existing_students_stmt = $conn->prepare("SELECT student_id FROM session_students WHERE session_id = ?");
    $existing_students_stmt->bind_param("i", $session_id);
    $existing_students_stmt->execute();
    $existing_student_ids = array_column($existing_students_stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'student_id');

    // 3. Insert only the new students
    $insert_stmt = $conn->prepare("INSERT INTO session_students (session_id, student_id) VALUES (?, ?)");
    while ($student = $students_to_add->fetch_assoc()) {
        if (!in_array($student['student_id'], $existing_student_ids)) {
            $insert_stmt->bind_param("ii", $session_id, $student['student_id']);
            $insert_stmt->execute();
        }
    }
    $insert_stmt->close();
    
    // This header call will now work because no HTML has been sent yet.
    header("Location: manage_session_students.php?session_id=" . $session_id);
    exit();
}

// Now that all logic is done, we can safely include the header and start the page.
include 'header.php';

// --- DATA FOR DISPLAY ---
// Fetch the details of the current exam session
$session_stmt = $conn->prepare("SELECT session_name FROM exam_sessions WHERE session_id = ?");
$session_stmt->bind_param("i", $session_id);
$session_stmt->execute();
$session = $session_stmt->get_result()->fetch_assoc();
$session_name = $session['session_name'] ?? 'Unknown Session';
$session_stmt->close();

// Fetch all students currently added to this session for display
$query = "SELECT s.name, s.roll_no, d.dept_name, y.year_name 
          FROM session_students ss
          JOIN students s ON ss.student_id = s.student_id
          JOIN departments d ON s.dept_id = d.dept_id
          JOIN years y ON s.year_id = y.year_id
          WHERE ss.session_id = ?
          ORDER BY d.dept_name, y.year_name, s.roll_no";

$students_in_session_stmt = $conn->prepare($query);
$students_in_session_stmt->bind_param("i", $session_id);
$students_in_session_stmt->execute();
$students_in_session = $students_in_session_stmt->get_result();

$departments = $conn->query("SELECT * FROM departments ORDER BY dept_name");
$years = $conn->query("SELECT * FROM years ORDER BY year_name");
?>

<h3 class="mb-3">Manage Students for: <span class="text-primary"><?= htmlspecialchars($session_name) ?></span></h3>

<div class="row">
    <!-- Left Side: Add Batches & Main Action Button -->
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header"><h5><i class="bi bi-plus-circle-fill"></i> Add Student Batch</h5></div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add_batch">
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <select name="dept_id" class="form-select" required>
                            <option value="">-- Select --</option>
                            <?php while($dept = $departments->fetch_assoc()): ?>
                                <option value="<?= $dept['dept_id'] ?>"><?= htmlspecialchars($dept['dept_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Year</label>
                        <select name="year_id" class="form-select" required>
                            <option value="">-- Select --</option>
                            <?php while($year = $years->fetch_assoc()): ?>
                                <option value="<?= $year['year_id'] ?>"><?= htmlspecialchars($year['year_name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success w-100"><i class="bi bi-plus-lg"></i> Add Entire Batch</button>
                </form>
            </div>
        </div>
        <div class="d-grid gap-2">
             <a href="allocate_session.php?session_id=<?= $session_id ?>" class="btn btn-primary btn-lg">Proceed to Allocation <i class="bi bi-arrow-right-circle-fill"></i></a>
        </div>
    </div>

    <!-- Right Side: List of Added Students -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="bi bi-people-fill"></i> Students in this Session</h5>
                <span class="badge bg-primary rounded-pill"><?= $students_in_session->num_rows ?> students</span>
            </div>
            <div class="card-body p-0" style="max-height: 75vh; overflow-y: auto;">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Roll No</th>
                            <th scope="col">Batch</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($students_in_session->num_rows > 0): ?>
                            <?php while ($student = $students_in_session->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($student['name']) ?></td>
                                    <td><?= htmlspecialchars($student['roll_no']) ?></td>
                                    <td><?= htmlspecialchars($student['dept_name']) ?>, <?= htmlspecialchars($student['year_name']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted p-5">
                                    <p class="mb-1"><i class="bi bi-person-x-fill fs-2"></i></p>
                                    No students added yet. <br> Use the form on the left to add a batch.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="mt-3">
    <a href="exam_sessions.php" class="btn btn-secondary"><i class="bi bi-arrow-left-short"></i> Back to All Sessions</a>
</div>

<?php include 'footer.php'; ?>

