<?php
// All PHP logic that might redirect MUST come before any HTML output.
include 'db.php';

// --- ACTION HANDLER ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    // Action: Create a new exam session
    if ($action == 'create_session') {
        $session_name = trim($_POST['session_name']);
        if (!empty($session_name)) {
            $stmt = $conn->prepare("INSERT INTO exam_sessions (session_name) VALUES (?)");
            $stmt->bind_param("s", $session_name);
            $stmt->execute();
            $stmt->close();
            header("Location: exam_sessions.php");
            exit();
        }
    }

    // Action: Delete an exam session
    if ($action == 'delete_session') {
        $session_id_to_delete = intval($_POST['session_id']);
        $conn->query("TRUNCATE TABLE seat_allocation");
        $stmt = $conn->prepare("DELETE FROM exam_sessions WHERE session_id = ?");
        $stmt->bind_param("i", $session_id_to_delete);
        $stmt->execute();
        $stmt->close();
        header("Location: exam_sessions.php");
        exit();
    }
}

// Now that all logic is done, we can safely include the header.
include 'header.php';

// Fetch all existing exam sessions for display
$sessions_result = $conn->query("SELECT * FROM exam_sessions ORDER BY creation_date DESC");
?>

<div class="container-fluid">
    <!-- Page Title -->
    <div class="mb-4">
        <h1 class="display-6">Exam Sessions</h1>
        <p class="text-muted">Create and manage exam events here. This is the starting point for any new allocation.</p>
    </div>

    <div class="row g-4">
        <!-- Left Side: Form to Create New Session -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-plus-circle-fill"></i> Create New Session</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="create_session">
                        <div class="mb-3">
                            <label for="session_name" class="form-label">Session Name</label>
                            <input type="text" class="form-control" id="session_name" name="session_name" placeholder="e.g., Final Exams - Autumn 2025" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-lg"></i> Create Session</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Side: List of Existing Sessions -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-list-ul"></i> Existing Sessions</h5>
                </div>
                <div class="list-group list-group-flush">
                    <?php if ($sessions_result->num_rows > 0): ?>
                        <?php while ($session = $sessions_result->fetch_assoc()): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                                <a href="manage_session_students.php?session_id=<?= $session['session_id'] ?>" class="text-decoration-none text-dark flex-grow-1">
                                    <strong><?= htmlspecialchars($session['session_name']) ?></strong>
                                    <br>
                                    <small class="text-muted">Created: <?= date('d M Y', strtotime($session['creation_date'])) ?></small>
                                </a>
                                <div>
                                    <a href="manage_session_students.php?session_id=<?= $session['session_id'] ?>" class="btn btn-outline-primary btn-sm">Manage Students</a>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this session and clear the current seating plan?');" class="d-inline">
                                        <input type="hidden" name="action" value="delete_session">
                                        <input type="hidden" name="session_id" value="<?= $session['session_id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete Session">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="list-group-item text-muted p-5 text-center">
                            <p class="mb-1"><i class="bi bi-folder-x fs-2"></i></p>
                            No exam sessions found. <br> Please create one to begin.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

