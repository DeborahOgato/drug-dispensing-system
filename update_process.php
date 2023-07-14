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

    // Update the user's details in the appropriate table in the database
    $username = $_POST['username'];
    $full_name = $_POST['full_name'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $contact_number = $_POST['contact_number'];

    $stmt = $conn->prepare("UPDATE $table_name SET username = ?, full_name = ?, password = ?, email = ?, contact_number = ? WHERE $id_column = ?");
    $stmt->bind_param('ssssss', $username, $full_name, $password, $email, $contact_number, $user_id);
    $stmt->execute();

    // Redirect to the user's page
    header("Location: view.php");
    exit();
} else {
    header('Location: login.html');
    exit();
}

// Close the database connection
$conn->close();
?>
