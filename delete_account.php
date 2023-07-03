<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the user type and username
    $userType = $_POST['user_type'];
    $username = $_SESSION['username'];

    // Update the user's account status to "disabled" or "deleted" based on the selected option
    $host = 'localhost';
    $db = 'drug-dispensing-system';
    $user = 'root';
    $pass = '';
    $mysqli = new mysqli($host, $user, $pass, $db);
    if ($mysqli->connect_error) {
        die('Connection Error: ' . $mysqli->connect_error);
    }

    if (isset($_POST['disable'])) {
        // Disable the account
        $updateQuery = "UPDATE $userType SET status = 'disabled' WHERE username = ?";
        $message = "Your account has been disabled. You can contact the administrator to reactivate it.";
    } else {
        // Delete the account
        $updateQuery = "UPDATE $userType SET status = 'deleted' WHERE username = ?";
        $message = "Your account has been deleted. We're sorry to see you go.";
    }

    $stmt = $mysqli->prepare($updateQuery);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();

    // Notify the admin of the account deletion or disablement
    $adminEmail = "ogato@gmail.com";
    $subject = "Account Change Notification";
    $message = "User: $username, UserType: $userType, Action: " . ($message === "Your account has been disabled. You can contact the administrator to reactivate it." ? "Disable" : "Delete");
    mail($adminEmail, $subject, $message);

    // Redirect the user to the login page
    header("Location: login.html");
    exit();
}
?>
