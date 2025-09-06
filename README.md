# Class Allocation

📖 Project Overview
Class Allocation is a sophisticated, database-driven web application built with PHP and MySQL, designed for educational institutions to automate the complex process of examination seating arrangement.

The core of this project is its advanced anti-malpractice allocation engine. Unlike simple allocation scripts that place students sequentially, ExamNexus uses an intelligent, configurable algorithm to deliberately distribute students from different departments and years across classrooms, ensuring maximum separation and fairness. It's a powerful tool designed to save administrators hours of manual work, reduce human error, and uphold academic integrity during examinations.

This project was developed from the ground up, starting with basic CRUD operations and evolving into a complete management system with a modern, pleasing user interface inspired by professional dashboards.

✨ Key Features
🧠 Intelligent Anti-Malpractice Algorithm:

Multi-Batch Interleaving: Automatically weaves students from multiple batches together in a round-robin fashion to ensure students of the same group are not seated together.

Single-Batch Separation: When allocating a single large group, it uses a "pass-based" system to fill the ends of benches first, guaranteeing maximum physical distance between students.

Smart Gap Insertion: Intelligently leaves seats empty to separate students when batch sizes are uneven.

📅 Exam Session Management:

Create and manage distinct exam events (e.g., "Mid-Term Exams - Autumn 2025").

Add specific batches of students to each session, preventing student duplication.

Delete sessions and automatically clear associated seating plans.

🚀 Automated Allocation Engine:

With a single click, allocate all students in a session across all available classrooms.

The engine fills one classroom completely before moving to the next, just like in a real-world scenario.

Provides a clear summary of students vs. available seats and warns if capacity is exceeded.

📊 Powerful Data Management:

CSV Import: Bulk-import hundreds of students from a CSV file, with validation to prevent duplicates.

Interactive Multi-Level Sorting: Sort the student list by multiple criteria (e.g., sort by Department, then by Year).

Full CRUD (Create, Read, Update, Delete) functionality for all core data (Students, Classrooms, Departments, Years).

🖥️ Professional & Aesthetic User Interface:

Modern, responsive UI inspired by professional dashboards like Figma.

A fixed sidebar navigation that provides a seamless, single-page application feel.

Clean, "alive" dashboard with live data cards and clear calls to action.

Visually pleasing and intuitive seating plan viewer.

📄 CSV Export:

Export the final seating plan for any classroom as a CSV file, formatted with student roll numbers for easy printing and distribution.

🛠️ Technology Stack
Backend: PHP

Database: MySQL (MariaDB)

Frontend: HTML5, CSS3, Bootstrap 5, Bootstrap Icons

Development Environment: XAMPP

🚀 Setup and Installation
To run this project locally, follow these steps:

Prerequisites:

Make sure you have a local server environment like XAMPP installed and running.

Clone the Repository:

git clone https://github.com/ansafshan/Class-Allocation

Place the cloned folder inside your htdocs directory (e.g., C:\xampp\htdocs\classalloc).

Database Setup:

Open phpMyAdmin by navigating to http://localhost/phpmyadmin.

Create a new database named class_alloc_db.

Select the new database, go to the "Import" tab, and import the provided database_schema.sql file. This will create all the necessary tables.

Database Connection:

The database connection settings are located in db.php. The default settings (username: root, password: "") should work for a standard XAMPP installation. If you have a different setup, modify this file accordingly.

Run the Application:

Open your web browser and navigate to: http://localhost/class_alloc/

🗃️ Database Schema
The database consists of 7 core tables:

departments: Stores department names.

years: Stores year names (e.g., First Year).

classrooms: Stores classroom layouts and capacities.

students: Stores detailed student information.

exam_sessions: Manages the main exam events.

session_students: Links students to a specific exam session.

seat_allocation: Stores the final generated seating plan for a session.

📄 License
This project is licensed under the MIT License. See the LICENSE file for details.
