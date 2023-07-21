<?php
session_start();

// Check if user is logged in as a doctor
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Doctor') {
    $user_id = $_SESSION['user_id'];

    // Include the database connection file
    $host = 'localhost';
    $db_name = 'drug-dispensing-system';
    $user = 'root';
    $password = '';

    $conn = new mysqli($host, $user, $password, $db_name);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and execute the query to fetch doctor's details from both users and doctors tables
    $stmt = $conn->prepare("SELECT u.*, d.* FROM users u JOIN doctors d ON u.user_id = d.user_id WHERE u.user_id = ?");
    $stmt->bind_param('s', $user_id);
    $stmt->execute();

    // Fetch the doctor's details
    $result = $stmt->get_result();
    $doctor = $result->fetch_assoc();

    // Handle delete process
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_doctor'])) {
        try {
            // Begin a transaction for the deletion process
            $conn->begin_transaction();

            // Delete doctor's details from the doctors table
            $delete_doctor_stmt = $conn->prepare("DELETE FROM doctors WHERE user_id = ?");
            $delete_doctor_stmt->bind_param('s', $user_id);
            $delete_doctor_stmt->execute();

            // Delete doctor's details from the users table
            $delete_user_stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            $delete_user_stmt->bind_param('s', $user_id);
            $delete_user_stmt->execute();

            // Commit the transaction, as both deletions are successful
            $conn->commit();

            // Redirect the user to the logout page after successful deletion
            header("Location: logout.php");
            exit();
        } catch (Exception $e) {
            // If there is any error during the transaction, rollback and display an error message
            $conn->rollback();
            echo "Error deleting doctor: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Doctor Account</title>
</head>
<body>
    <?php if (isset($doctor)) : ?>
    <h2>Delete Account</h2>
    <p>By clicking the "Delete Account" button, your account will be permanently deleted, and all data will be lost. This action cannot be undone.</p>
    <form method="post" action="delete_doctor.php">
        <input type="submit" name="delete_doctor" value="Delete Account">
    </form>
    <?php else : ?>
        <p>You must be logged in as a doctor to access this page.</p>
    <?php endif; ?>
</body>
</html>
