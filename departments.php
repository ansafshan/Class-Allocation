<?php 
include 'header.php'; 
include 'db.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dept_name = trim($_POST['dept_name']);
    if (!empty($dept_name)) {
        $sql = "INSERT INTO departments (dept_name) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $dept_name);
        $stmt->execute();
        $stmt->close();
        header("Location: departments.php");
        exit();
    }
}

// Fetch all departments
$result = $conn->query("SELECT * FROM departments ORDER BY dept_name");
?>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-building"></i> Manage Departments</h5>
        </div>
        <div class="card-body">
            <!-- Add Department Form -->
            <form method="POST" class="mb-4">
              <div class="input-group">
                <input type="text" name="dept_name" class="form-control" placeholder="Enter New Department Name" required>
                <button type="submit" class="btn btn-primary">Add Department</button>
              </div>
            </form>

            <!-- Department List -->
            <table class="table table-striped table-hover">
              <thead class="table-dark">
                <tr>
                  <th>ID</th>
                  <th>Department Name</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                  <tr>
                    <td><?= htmlspecialchars($row['dept_id']) ?></td>
                    <td><?= htmlspecialchars($row['dept_name']) ?></td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
