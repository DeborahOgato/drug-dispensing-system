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

    // Retrieve the user's details from the appropriate table in the database
    $stmt = $conn->prepare("SELECT * FROM $table_name WHERE $id_column = ?");
    $stmt->bind_param('s', $user_id);
    $stmt->execute();

    // Fetch the user's details
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Display the update form
    echo "<h2>Update {$role} Details:</h2>";
    echo "<form action='update_process.php' method='POST'>";
    echo "<label for='username'>Username</label>";
    echo "<input type='text' id='username' name='username' value='{$user['username']}' required><br>";
    echo "<label for='full_name'>Name:</label>";
    echo "<input type='text' id='full_name' name='full_name' value='{$user['full_name']}' required><br>";
    echo "<label for='password'>Password:</label>";
    echo "<input type='password' id='password' name='password' required><br>";
    echo "<label for='email'>Email:</label>";
    echo "<input type='email' id='email' name='email' value='{$user['email']}' required><br>";
    echo "<label for='contact_number'>Contact Number:</label>";
    echo "<input type='text' id='contact_number' name='contact_number' value='{$user['contact_number']}' required><br>";
    echo "<input type='hidden' name='user_id' value='{$user_id}'>";
    echo "<input type='submit' value='Update'>";
    echo "</form>";
} else {
    header('Location: login.html');
    exit();
}

// Close the database connection
$conn->close();
?>
