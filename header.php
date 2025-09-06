<?php
// --- ACTIVE PAGE LOGIC ---
// This gets the filename of the current page (e.g., "index.php", "students.php")
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AllocationPro | Seating Management System</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS for the new aesthetic -->
    <style>
        :root {
            --sidebar-width: 280px;
            --primary-color: #4A55A2; /* A richer, deeper purple */
            --background-color: #fdfdfdff; /* A soft, clean background */
            --sidebar-bg: #111827; /* A very dark, almost black blue */
            --sidebar-link-color: #9ca3af;
            --sidebar-link-hover-bg: rgba(74, 85, 162, 0.2); /* Faded primary color */
            --sidebar-link-hover-color: #ffffff;
            --sidebar-link-active-bg: var(--primary-color);
            --sidebar-link-active-color: #ffffff;
            --text-color: #334155;
            --heading-color: #1e293b;
            --border-color: #e5e7eb;
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }

        body {
            background-color: var(--background-color);
            font-family: 'Poppins', sans-serif;
            color: var(--text-color);
            display: flex;
        }

        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: var(--sidebar-bg);
            padding: 1.5rem 1rem;
            display: flex;
            flex-direction: column;
        }

        .sidebar .navbar-brand {
            color: #fff;
            font-weight: 600;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1.5rem;
        }
        .sidebar .nav-link {
            color: var(--sidebar-link-color);
            font-weight: 500;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            transition: all 0.2s ease-in-out;
        }
        .sidebar .nav-link:hover {
            background-color: var(--sidebar-link-hover-bg);
            color: var(--sidebar-link-hover-color);
        }
        /* This is the new active state style */
        .sidebar .nav-link.active {
            background-color: var(--sidebar-link-active-bg);
            color: var(--sidebar-link-active-color);
            font-weight: 600;
            box-shadow: var(--shadow);
        }
        .sidebar .nav-link i {
            margin-right: 0.75rem;
        }
        
        .sidebar .sidebar-footer {
            margin-top: auto; /* Pushes footer to the bottom */
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            padding: 2rem;
            height: 100vh;
            overflow-y: auto;
        }
        
        .card {
            border: none;
            box-shadow: var(--shadow);
            border-radius: 0.75rem;
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
            color: var(--heading-color);
            padding: 1rem 1.5rem;
        }
        .card-body {
            padding: 1.5rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: 500;
        }
        .btn-primary:hover {
            background-color: #3e478a;
            border-color: #3e478a;
        }
        
        h1, h2, h3, h4, h5, h6 {
            color: var(--heading-color);
            font-weight: 600;
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div>
        <a class="navbar-brand" href="index.php">
            <i class="bi bi-bounding-box-circles"></i>
            <strong>AllocationPro</strong>
        </a>

        <ul class="nav flex-column">
            <!-- The 'active' class is now added dynamically with PHP -->
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'index.php') ? 'active' : '' ?>" href="index.php"><i class="bi bi-house-door-fill"></i> Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'exam_sessions.php' || $current_page == 'manage_session_students.php' || $current_page == 'allocate_session.php') ? 'active' : '' ?>" href="exam_sessions.php"><i class="bi bi-calendar2-check-fill"></i> Exam Sessions</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'view_allocation.php') ? 'active' : '' ?>" href="view_allocation.php"><i class="bi bi-eye-fill"></i> View Seating</a>
            </li>
            <li class="nav-item mt-3">
                <h6 class="text-secondary text-uppercase px-3" style="font-size: 0.75rem;">Core Data</h6>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'students.php') ? 'active' : '' ?>" href="students.php"><i class="bi bi-people-fill"></i> Students</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'classrooms.php') ? 'active' : '' ?>" href="classrooms.php"><i class="bi bi-door-open-fill"></i> Classrooms</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'departments.php') ? 'active' : '' ?>" href="departments.php"><i class="bi bi-building"></i> Departments</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'years.php') ? 'active' : '' ?>" href="years.php"><i class="bi bi-calendar3"></i> Years</a>
            </li>
        </ul>
    </div>

    <div class="sidebar-footer">
        <ul class="nav flex-column">
             <li class="nav-item">
                <a class="nav-link" href="#"><i class="bi bi-gear-fill"></i> Settings</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#"><i class="bi bi-box-arrow-left"></i> Log out</a>
            </li>
        </ul>
    </div>
</aside>

<main class="main-content">

