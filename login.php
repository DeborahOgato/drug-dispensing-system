<?php
$host = 'localhost';
$db = 'drug-dispensing-system';
$user = 'root';
$pass = '';

// Create a database connection
$con = mysqli_connect($host, $user, $pass, $db);
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the submitted username and password
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if the user exists in the Doctors table
    $query = "SELECT * FROM doctors WHERE username = ? AND password = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Doctor is logged in
        $userType = 'doctor';
        $userRow = $result->fetch_assoc();
    } else {
        // Check if the user exists in the Pharmacists table
        $query = "SELECT * FROM pharmacists WHERE username = ? AND password = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Pharmacist is logged in
            $userType = 'pharmacist';
            $userRow = $result->fetch_assoc();
        } else {
            // Check if the user exists in the Admins table
            $query = "SELECT * FROM admins WHERE username = ? AND password = ?";
            $stmt = $con->prepare($query);
            $stmt->bind_param("ss", $username, $password);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Admin is logged in
                $userType = 'admin';
                $userRow = $result->fetch_assoc();
            } else {
                // Invalid username or password
                echo "Invalid username or password.";
                $stmt->close();
                $con->close();
                exit();
            }
        }
    }

    // Start the session and store user information
    session_start();
    $_SESSION['userType'] = $userType;
    $_SESSION['username'] = $userRow['username'];

    // Redirect to appropriate page based on user type
    if ($userType === 'doctor') {
        header("Location: doctor_page.php");
    } else if ($userType === 'pharmacist') {
        header("Location: pharmacist_page.php");
    } else if ($userType === 'admin') {
        header("Location: admin_page.php");
    }
    exit();
}

$con->close(); // Close the database connection
?>
