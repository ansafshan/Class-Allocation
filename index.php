<?php 
include 'header.php'; 
include 'db.php';

// Fetch stats for the dashboard cards to make it feel alive
$total_students = $conn->query("SELECT COUNT(*) AS count FROM students")->fetch_assoc()['count'];
$total_classrooms = $conn->query("SELECT COUNT(*) AS count FROM classrooms")->fetch_assoc()['count'];
$total_sessions = $conn->query("SELECT COUNT(*) AS count FROM exam_sessions")->fetch_assoc()['count'];

?>

<div class="container-fluid">
    <!-- Page Title -->
    <div class="mb-4">
        <h1 class="display-6">Dashboard</h1>
        <p class="text-muted">Welcome, Admin. Here is a summary of your system.</p>
    </div>

    <!-- Stat Cards -->
    <div class="row g-4">
        <div class="col-lg-4 col-md-6">
            <div class="card text-white bg-primary">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="card-title mb-0"><?= $total_students ?></h3>
                        <p class="card-text">Total Students</p>
                    </div>
                    <i class="bi bi-people-fill" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="card text-white" style="background-color: #A27B5C;">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="card-title mb-0"><?= $total_classrooms ?></h3>
                        <p class="card-text">Total Classrooms</p>
                    </div>
                    <i class="bi bi-door-open-fill" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12">
            <div class="card text-white" style="background-color: #aa01ecff;">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="card-title mb-0"><?= $total_sessions ?></h3>
                        <p class="card-text">Exam Sessions</p>
                    </div>
                    <i class="bi bi-calendar2-check-fill" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card mt-4">
        <div class="card-header">
            <h5><i class="bi bi-lightning-charge-fill"></i> Quick Actions</h5>
        </div>
        <div class="card-body text-center py-5">
            <h4 class="mb-3">Ready to set up a new exam?</h4>
            <p class="text-muted">Start by creating a new session. You can then add students and run the auto-allocator.</p>
            <a href="exam_sessions.php" class="btn btn-primary btn-lg mt-2">
                <i class="bi bi-plus-circle-fill"></i> Create New Exam Session
            </a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

