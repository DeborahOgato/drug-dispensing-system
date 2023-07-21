<?php
session_start();

// Check if user is logged in as a pharmacist
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Pharmacist') {
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

    // Prepare and execute the query to fetch pharmacist's details from both users and pharmacists tables
    $stmt = $conn->prepare("SELECT u.*, ph.* FROM users u JOIN pharmacists ph ON u.user_id = ph.user_id WHERE u.user_id = ?");
    $stmt->bind_param('s', $user_id);
    $stmt->execute();

    // Fetch the pharmacist's details
    $result = $stmt->get_result();
    $pharmacist = $result->fetch_assoc();

    // Handle delete process
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_pharmacist'])) {
        try {
            // Begin a transaction for the deletion process
            $conn->begin_transaction();

            // Delete pharmacist's details from the pharmacists table
            $delete_pharmacist_stmt = $conn->prepare("DELETE FROM pharmacists WHERE user_id = ?");
            $delete_pharmacist_stmt->bind_param('s', $user_id);
            $delete_pharmacist_stmt->execute();

            // Delete pharmacist's details from the users table
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
            echo "Error deleting pharmacist: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Pharmacist Account</title>
</head>
<body>
    <?php if (isset($pharmacist)) : ?>
    <h2>Delete Account</h2>
    <p>By clicking the "Delete Account" button, your account will be permanently deleted, and all data will be lost. This action cannot be undone.</p>
    <form method="post" action="delete_pharmacist.php">
        <input type="submit" name="delete_pharmacist" value="Delete Account">
    </form>
    <?php else : ?>
        <p>You must be logged in as a pharmacist to access this page.</p>
    <?php endif; ?>
</body>
</html>
