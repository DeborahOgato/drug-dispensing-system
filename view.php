<?php
//  Database connection 
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

    if ($role === 'Doctor') {
        $table_name = 'doctors';
        $id_column = 'doctor_id';
    } elseif ($role === 'Pharmacist') {
        $table_name = 'pharmacists';
        $id_column = 'pharmacist_id';
    } elseif ($role === 'Patient') {
        $table_name = 'patients';
        $id_column = 'patient_id';
    } elseif ($role === 'Admin') {
        $table_name = 'admins';
        $id_column = 'admin_id';
    }

    // Retrieve the user's details from the appropriate table in the database
    $stmt = $conn->prepare("SELECT * FROM $table_name WHERE $id_column = ?");
    $stmt->bind_param('s', $user_id);
    $stmt->execute();

    // Fetch the user's details
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Display the user's details
    echo "<h2>{$role} Details:</h2>";
    echo "<p>{$role} ID: {$user[$id_column]}</p>";
    echo "<p>Name: {$user['full_name']}</p>";
    echo "<p>Email: {$user['email']}</p>";
    echo "<p>Contact Number: {$user['contact_number']}</p>";
} else {
    header('Location: login.html');
    exit();
}

// Close the database connection
$conn->close();
?>
