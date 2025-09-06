<?php 
include 'header.php';
include 'db.php';

// Add Year
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $year_name = trim($_POST['year_name']);
    if (!empty($year_name)) {
        $sql = "INSERT INTO years (year_name) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $year_name);
        $stmt->execute();
        $stmt->close();
        header("Location: years.php");
        exit();
    }
}

$result = $conn->query("SELECT * FROM years ORDER BY year_id");
?>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-calendar3"></i> Manage Years</h5>
        </div>
        <div class="card-body">
            <form method="POST" class="mb-4">
                <div class="input-group">
                  <input type="text" name="year_name" class="form-control" placeholder="Enter Year (e.g., First Year)" required>
                  <button type="submit" class="btn btn-primary">Add Year</button>
                </div>
            </form>

            <table class="table table-striped table-hover">
                <thead class="table-dark">
                  <tr><th>ID</th><th>Year Name</th></tr>
                </thead>
                <tbody>
                  <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                      <td><?= htmlspecialchars($row['year_id']) ?></td>
                      <td><?= htmlspecialchars($row['year_name']) ?></td>
                    </tr>
                  <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
