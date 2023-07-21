<?php
session_start();

// Check if user is logged in as a patient
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Patient') {
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

    // Prepare and execute the query to fetch patient's details from both users and patients tables
    $stmt = $conn->prepare("SELECT u.*, p.* FROM users u JOIN patients p ON u.user_id = p.user_id WHERE u.user_id = ?");
    $stmt->bind_param('s', $user_id);
    $stmt->execute();

    // Fetch the patient's details
    $result = $stmt->get_result();
    $patient = $result->fetch_assoc();

    // Handle delete process
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_patient'])) {
        try {
            // Begin a transaction for the deletion process
            $conn->begin_transaction();

            // Delete patient's details from the patients table
            $delete_patient_stmt = $conn->prepare("DELETE FROM patients WHERE user_id = ?");
            $delete_patient_stmt->bind_param('s', $user_id);
            $delete_patient_stmt->execute();

            // Delete patient's details from the users table
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
            echo "Error deleting patient: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Patient Account</title>
</head>
<body>
    <?php if (isset($patient)) : ?>
    <h2>Delete Account</h2>
    <p>By clicking the "Delete Account" button, your account will be permanently deleted, and all data will be lost. This action cannot be undone.</p>
    <form method="post" action="delete_patient.php">
        <input type="submit" name="delete_patient" value="Delete Account">
    </form>
    <?php else : ?>
        <p>You must be logged in as a patient to access this page.</p>
    <?php endif; ?>
</body>
</html>
