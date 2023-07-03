<?php
session_start();

// Check if the user is logged in as a doctor
if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'doctor') {
    header("Location: login.html");
    exit();
}

// Display the doctor's username
echo "Logged in as doctor: " . $_SESSION['username'];

// Rest of the doctor's page content goes here

// View Account Details
echo "<h3>Account Details</h3>";
// Retrieve the doctor's information from the database and display it
$host = 'localhost';
$db = 'drug-dispensing-system';
$user = 'root';
$pass = '';
$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_error) {
    die('Connection Error: ' . $mysqli->connect_error);
}
$username = $_SESSION['username'];
$query = "SELECT * FROM doctors WHERE username = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "Name: " . $row['name'] . "<br>";
    echo "Username: " . $row['username'] . "<br>";
    echo "Email: " . $row['email'] . "<br>";
    echo "Contact Number: " . $row['contact_number'] . "<br>";
    echo "Address: " . $row['address'] . "<br>";
}
$stmt->close();
$mysqli->close();

// Delete Account
echo "<h3>Delete Account</h3>";
echo "<form action='delete_account.php' method='POST'>";
echo "<input type='hidden' name='user_type' value='doctor'>";
echo "<input type='submit' value='Delete Account'>";
echo "</form>";

// Update Account Details
echo "<h3>Update Account Details</h3>";
echo "<form action='update_account.php' method='POST'>";
echo "<input type='hidden' name='user_type' value='doctor'>";
echo "<input type='text' name='name' placeholder='Name' required><br>";
echo "<input type='text' name='username' placeholder='Username' required><br>";
echo "<input type='password' name='password' placeholder='Password' required><br>";
echo "<input type='email' name='email' placeholder='Email' required><br>";
echo "<input type='text' name='contact_number' placeholder='Contact Number' required><br>";
echo "<input type='text' name='address' placeholder='Address' required><br>";
echo "<input type='submit' value='Update'>";
echo "</form>";
?>
