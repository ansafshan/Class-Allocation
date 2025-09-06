<?php 
include 'header.php';
include 'db.php';

$error_message = null;
$success_message = null;

// --- ACTION HANDLER ---

// ACTION: Import Students from CSV
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'import_csv') {
    if (isset($_FILES['student_csv']) && $_FILES['student_csv']['error'] == 0) {
        $file = $_FILES['student_csv']['tmp_name'];
        
        $depts_map = array_column($conn->query("SELECT dept_id, dept_name FROM departments")->fetch_all(MYSQLI_ASSOC), 'dept_id', 'dept_name');
        $years_map = array_column($conn->query("SELECT year_id, year_name FROM years")->fetch_all(MYSQLI_ASSOC), 'year_id', 'year_name');
        $existing_rolls = array_column($conn->query("SELECT roll_no FROM students")->fetch_all(MYSQLI_ASSOC), 'roll_no');

        $handle = fopen($file, "r");
        fgetcsv($handle); // Skip header

        $imported_count = 0;
        $skipped_count = 0;
        
        $insert_stmt = $conn->prepare("INSERT INTO students (name, roll_no, dept_id, year_id, subject) VALUES (?, ?, ?, ?, ?)");

        while (($data = fgetcsv($handle)) !== FALSE) {
            // Ensure we have the correct number of columns
            if (count($data) < 5) continue;

            $name = trim($data[0]);
            $roll = trim($data[1]);
            $dept_name = trim($data[2]);
            $year_name = trim($data[3]);
            $subject = trim($data[4]);

            if (!in_array($roll, $existing_rolls) && isset($depts_map[$dept_name]) && isset($years_map[$year_name])) {
                $dept_id = $depts_map[$dept_name];
                $year_id = $years_map[$year_name];
                
                $insert_stmt->bind_param("ssiis", $name, $roll, $dept_id, $year_id, $subject);
                $insert_stmt->execute();
                $existing_rolls[] = $roll;
                $imported_count++;
            } else {
                $skipped_count++;
            }
        }
        fclose($handle);
        $insert_stmt->close();

        $success_message = "Import complete! Successfully added $imported_count students. Skipped $skipped_count rows (due to duplicates or invalid data).";
    } else {
        $error_message = "Error: File upload failed. Please try again.";
    }
}

// ACTION: Add a single student manually
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_student') {
    $name = trim($_POST['student_name']);
    $roll = trim($_POST['roll_no']);
    $dept = $_POST['dept_id'];
    $year = $_POST['year_id'];
    $subject = trim($_POST['subject']);

    $check_stmt = $conn->prepare("SELECT student_id FROM students WHERE roll_no = ?");
    $check_stmt->bind_param("s", $roll);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        $error_message = "Error: A student with Roll Number '" . htmlspecialchars($roll) . "' already exists.";
    } else {
        $sql = "INSERT INTO students (name, roll_no, dept_id, year_id, subject) VALUES (?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($sql);
        $insert_stmt->bind_param("ssiis", $name, $roll, $dept, $year, $subject);
        $insert_stmt->execute();
        $insert_stmt->close();
        header("Location: students.php");
        exit();
    }
    $check_stmt->close();
}

// --- DATA FETCHING FOR DISPLAY ---

// --- NEW: ADVANCED SORTING LOGIC ---
$sortable_columns = ['name', 'roll_no', 'dept_name', 'year_name'];
$column_map = [ // Maps friendly names to actual DB columns for the query
    'name' => 's.name',
    'roll_no' => 's.roll_no',
    'dept_name' => 'd.dept_name',
    'year_name' => 'y.year_name'
];

// THIS IS THE CHANGED LINE: Default sort is now by department
$current_sort_str = $_GET['sort'] ?? 'dept_name';
$sort_columns_raw = explode(',', $current_sort_str);
$order_by_parts = [];

foreach ($sort_columns_raw as $column) {
    if (in_array($column, $sortable_columns)) {
        $order_by_parts[] = $column_map[$column] . " ASC";
    }
}

if (empty($order_by_parts)) {
    // Fallback to a default sort if the sort parameter is invalid
    $order_by_clause = "ORDER BY d.dept_name ASC, s.roll_no ASC";
} else {
    $order_by_clause = "ORDER BY " . implode(', ', $order_by_parts);
}


// Helper function to generate the next sort URL
function generateSortUrl($new_column, $current_sort_str) {
    $current_columns = explode(',', $current_sort_str);
    if (!in_array($new_column, $current_columns)) {
        $current_columns[] = $new_column;
    }
    return "students.php?sort=" . implode(',', $current_columns);
}
// --- END OF SORTING LOGIC ---


// Get dropdown values
$depts_result = $conn->query("SELECT * FROM departments ORDER BY dept_name");
$years_result = $conn->query("SELECT * FROM years ORDER BY year_name");

// Fetch existing students, now with dynamic multi-sorting
$query = "SELECT s.student_id, s.name, s.roll_no, d.dept_name, y.year_name, s.subject 
          FROM students s 
          JOIN departments d ON s.dept_id = d.dept_id 
          JOIN years y ON s.year_id = y.year_id
          $order_by_clause";
$students_result = $conn->query($query);

?>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-people-fill"></i> Manage Students</h5>
        </div>
        <div class="card-body">

            <?php if ($error_message): ?><div class="alert alert-danger"><?= $error_message ?></div><?php endif; ?>
            <?php if ($success_message): ?><div class="alert alert-success"><?= $success_message ?></div><?php endif; ?>
            
            <div class="accordion" id="manageStudentsAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                            <i class="bi bi-upload me-2"></i> Import Students from CSV File
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#manageStudentsAccordion">
                        <div class="accordion-body">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="import_csv">
                                <div class="mb-3">
                                    <label for="student_csv" class="form-label">Select CSV File</label>
                                    <input class="form-control" type="file" name="student_csv" id="student_csv" accept=".csv" required>
                                </div>
                                <div class="form-text">
                                    CSV file must have 5 columns in this order: <strong>Name, RollNo, DepartmentName, YearName, Subject</strong><br>
                                    Example: <code>John Doe,CS24-01,Computer Science,Second Year,Data Structures</code>
                                </div>
                                <button type="submit" class="btn btn-primary mt-3">Import Now</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                           <i class="bi bi-person-plus-fill me-2"></i> Add a Single Student Manually
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#manageStudentsAccordion">
                        <div class="accordion-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="add_student">
                                <div class="row g-2">
                                  <div class="col-md-3"><input type="text" name="student_name" class="form-control" placeholder="Student Name" required></div>
                                  <div class="col-md-2"><input type="text" name="roll_no" class="form-control" placeholder="Roll No" required></div>
                                  <div class="col-md-2">
                                    <select name="dept_id" class="form-select" required>
                                      <option value="">-- Dept --</option>
                                      <?php while ($d = $depts_result->fetch_assoc()) { ?>
                                        <option value="<?= $d['dept_id'] ?>"><?= htmlspecialchars($d['dept_name']) ?></option>
                                      <?php } ?>
                                    </select>
                                  </div>
                                  <div class="col-md-2">
                                    <select name="year_id" class="form-select" required>
                                      <option value="">-- Year --</option>
                                      <?php 
                                        // Reset the pointer for the years result set so it can be looped again
                                        $years_result->data_seek(0);
                                        while ($y = $years_result->fetch_assoc()) { 
                                      ?>
                                        <option value="<?= $y['year_id'] ?>"><?= htmlspecialchars($y['year_name']) ?></option>
                                      <?php } ?>
                                    </select>
                                  </div>
                                  <div class="col-md-2"><input type="text" name="subject" class="form-control" placeholder="Subject" required></div>
                                  <div class="col-md-1"><button type="submit" class="btn btn-primary w-100">Add</button></div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- NEW: Active Sort Display -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    <span class="fw-bold">Sorting by:</span>
                    <?php foreach ($sort_columns_raw as $col): ?>
                        <span class="badge bg-primary"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $col))) ?></span>
                    <?php endforeach; ?>
                </div>
                <a href="students.php" class="btn btn-sm btn-outline-secondary">Reset Sort</a>
            </div>

            <div class="table-responsive mt-2">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                      <tr>
                          <th>ID</th>
                          <th><a href="<?= generateSortUrl('name', $current_sort_str) ?>" class="text-white">Name</a></th>
                          <th><a href="<?= generateSortUrl('roll_no', $current_sort_str) ?>" class="text-white">Roll</a></th>
                          <th><a href="<?= generateSortUrl('dept_name', $current_sort_str) ?>" class="text-white">Dept</a></th>
                          <th><a href="<?= generateSortUrl('year_name', $current_sort_str) ?>" class="text-white">Year</a></th>
                          <th>Subject</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php 
                        if ($students_result->num_rows > 0) {
                            while ($row = $students_result->fetch_assoc()) { ?>
                            <tr>
                              <td><?= htmlspecialchars($row['student_id']) ?></td>
                              <td><?= htmlspecialchars($row['name']) ?></td>
                              <td><?= htmlspecialchars($row['roll_no']) ?></td>
                              <td><?= htmlspecialchars($row['dept_name']) ?></td>
                              <td><?= htmlspecialchars($row['year_name']) ?></td>
                              <td><?= htmlspecialchars($row['subject']) ?></td>
                            </tr>
                      <?php }
                        } else {
                            echo "<tr><td colspan='6' class='text-center'>No students found.</td></tr>";
                        }
                      ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

