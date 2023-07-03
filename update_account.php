<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the user type and entered information
    $userType = $_POST['user_type'];
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $contactNumber = $_POST['contact_number'];
    $address = $_POST['address'];

    // Update the user's account details
    $host = 'localhost';
    $db = 'drug-dispensing-system';
    $user = 'root';
    $pass = '';
    $mysqli = new mysqli($host, $user, $pass, $db);
    if ($mysqli->connect_error) {
        die('Connection Error: ' . $mysqli->connect_error);
    }

    $updateQuery = "UPDATE $userType SET name = ?, username = ?, password = ?, email = ?, contact_number = ?, address = ? WHERE username = ?";
    $stmt = $mysqli->prepare($updateQuery);
    $stmt->bind_param("sssssss", $name, $username, $password, $email, $contactNumber, $address, $_SESSION['username']);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();

    // Notify the admin of the account update
    $adminEmail = "admin@example.com";
    $subject = "Account Change Notification";
    $message = "User: $_SESSION[username], UserType: $userType, Action: Update";
    mail($adminEmail, $subject, $message);

    // Redirect the user to the appropriate page based on the user type
    if ($userType === 'admin') {
        header("Location: admin_page.php");
    } elseif ($userType === 'doctor') {
        header("Location: doctor_page.php");
    } elseif ($userType === 'pharmacist') {
        header("Location: pharmacist_page.php");
    }
    exit();
}
?>
