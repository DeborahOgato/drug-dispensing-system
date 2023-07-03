<?php
$host = 'localhost';
$db = 'drug-dispensing-system';
$user = 'root';
$pass = '';

// Create a database connection
$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) {
    die('Connection Error: ' . $mysqli->connect_error);
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the entered information
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $contactNumber = $_POST['contact_number'];
    $address = $_POST['address'];
    $userType = $_POST['user_type'];

    // Check if the username is already taken
    $query = "SELECT * FROM doctors WHERE username = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "Username already exists. Please choose a different username.";
        $stmt->close();
        $mysqli->close();
        exit();
    }

    // Insert the user's information into the appropriate table based on the user type
    if ($userType === 'doctor') {
        $insertQuery = "INSERT INTO doctors (name, username, password, email, contact_number, address) VALUES (?, ?, ?, ?, ?, ?)";
    } else if ($userType === 'pharmacist') {
        $insertQuery = "INSERT INTO pharmacists (name, username, password, email, contact_number, address) VALUES (?, ?, ?, ?, ?, ?)";
    } else if ($userType === 'admin') { // Insert as admin
        $insertQuery = "INSERT INTO admins (name, username, password, email, contact_number, address) VALUES (?, ?, ?, ?, ?, ?)";
    } else {
        echo "Invalid user type.";
        $stmt->close();
        $mysqli->close();
        exit();
    }

    $stmt = $mysqli->prepare($insertQuery);
    $stmt->bind_param("ssssss", $name, $username, $password, $email, $contactNumber, $address);
    $stmt->execute();

    // Notify the admin of the new user registration
    $adminEmail = "admin@example.com";
    $subject = "New User Registration";
    $message = "A new user has registered. Username: $username, UserType: $userType";
    mail($adminEmail, $subject, $message);

    echo "Registration successful!";

    $stmt->close(); // Close the statement after execution
}

$mysqli->close(); // Close the database connection
?>
