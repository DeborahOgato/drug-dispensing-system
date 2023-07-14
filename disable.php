<?php
// Database connection
$host = 'localhost';
$db = 'drug-dispensing-system';
$user = 'root';
$password = '';

try {
    $conn = new mysqli($host, $user, $password, $db);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

session_start();

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];

    // Retrieve the user's details from the database based on their role
    $table_name = '';
    $id_column = '';
    $status_column = '';

    if ($role === 'Doctor') {
        $table_name = 'doctors';
        $id_column = 'doctor_id';
        $status_column = 'doctor_status';
    } elseif ($role === 'Pharmacist') {
        $table_name = 'pharmacists';
        $id_column = 'pharmacist_id';
        $status_column = 'pharmacist_status';
    } elseif ($role === 'Patient') {
        $table_name = 'patients';
        $id_column = 'patient_id';
        $status_column = 'patient_status';
    } elseif ($role === 'Admin') {
        $table_name = 'admins';
        $id_column = 'admin_id';
        $status_column = 'admin_status';
    }

    // Disable the user's account in the appropriate table in the database
    $stmt = $conn->prepare("UPDATE $table_name SET is_disabled = 1 WHERE $id_column = ?");
    $stmt->bind_param('s', $user_id);
    $stmt->execute();

    // Destroy the session and redirect to the login page
    session_destroy();
    header('Location: login.html');
    exit();
} else {
    header('Location: login.html');
    exit();
}

// Close the database connection
$conn->close();
?>
