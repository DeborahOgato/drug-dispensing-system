<?php
session_start();
 // Database connection credentials
 $host = 'localhost';
 $db = 'drug-dispensing-system';
 $user = 'root';
 $password = '';

 // Create a new mysqli connection
 $conn = new mysqli($host, $user, $password, $db);

 // Check if the connection was successful
 if ($conn->connect_error) {
     die("Connection failed: " . $conn->connect_error);
 }


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    
    // Prepare the query
    $stmt = $conn->prepare("SELECT * FROM Users WHERE username = ? AND password = ?");

    // Bind parameters
    $stmt->bind_param('ss', $username, $password);

    // Execute the query
    $stmt->execute();

    // Fetch the user record
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Check if user exists
    if ($user) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Redirect to the user's dashboard or appropriate page
        if ($user['role'] === 'Doctor') {
            header('Location: doctor_dashboard.php');
        } elseif ($user['role'] === 'Pharmacist') {
            header('Location: pharmacist_dashboard.php');
        } elseif ($user['role'] === 'Patient') {
            header('Location: patient_dashboard.php');
        } elseif ($user['role'] === 'Admin') {
            header('Location: admin_dashboard.php');
        }
        exit();
    } else {
        // User not found or invalid credentials
        echo "Invalid username or password.";
    }

    // Close the database connection
    $conn->close();
}
?>

