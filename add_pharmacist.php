<?php
session_start();

// Check if user is logged in as an admin
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin') {
    $user_id = $_SESSION['user_id'];

    // Include the database connection file
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
        $user_id= $_POST['user_id'];
        $username = $_POST['username'];
        $fullName = $_POST['full_name'];
        $dateOfBirth = $_POST['date_of_birth'];
        $password = $_POST['password']; // You should use a secure hashing algorithm to store the password

        // Function to calculate age based on date of birth
        function calculateAge($dateOfBirth) {
            $dateOfBirth = new DateTime($dateOfBirth);
            $currentDate = new DateTime();
            $age = $currentDate->diff($dateOfBirth)->y;
            return $age;
        }

        // Calculate age based on date of birth
        $age = calculateAge($dateOfBirth);

        $email = $user_id . '@example.com'; // Generate email using user_id (national ID)
        $contactNumber = $_POST['contact_number'];
        $role = 'Pharmacist';

        // Insert into the users table
        $insertUserStmt = $conn->prepare("INSERT INTO users (user_id, username, full_name, password, email, contact_number, date_of_birth, age, role) 
                                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insertUserStmt->bind_param('sssssssis', $user_id, $username, $fullName, $password, $email, $contactNumber, $dateOfBirth, $age, $role);
        $insertUserResult = $insertUserStmt->execute();

        if ($insertUserResult) {

            $stmt_pharmacists = $conn->prepare("INSERT INTO Pharmacists (user_id) VALUES (?)");
            $stmt_pharmacists->bind_param('i', $user_id);
            
            $stmt_pharmacists->execute();
            // Get the auto-generated pharmacist_number
            $pharmacist_number = $insertUserStmt->insert_id;
            // Generate the pharmacist_id with the format "PH0001"
            $pharmacist_id = 'Ph' . str_pad($pharmacist_number, 4, '0', STR_PAD_LEFT);

            // Insert into the pharmacists table
            $insertPharmacistStmt = $conn->prepare("INSERT INTO pharmacists (pharmacist_id, pharmacist_number) VALUES (?, ?)");
            $insertPharmacistStmt->bind_param('si', $pharmacist_id, $pharmacist_number);
            $insertPharmacistResult = $insertPharmacistStmt->execute();

            if ($insertPharmacistResult) {
                echo "<p>New pharmacist added successfully! Pharmacist ID: $pharmacist_id</p>";
            } else {
                echo "Error inserting pharmacist data: " . $conn->error;
            }
        } else {
            echo "Error inserting user data: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add New Pharmacist</title>
    <link rel="stylesheet" type="text/css" href="add_pharmacist.css">
</head>
<body>
<?php include "header.html";?>
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin') : ?>
    <h2>Add New Pharmacist</h2>
    <div class="add-pharmacist-form">
        <form method="post" action="add_pharmacist.php">
            <label for="user_id">National ID:</label>
            <input type="number" id="user_id" name="user_id" required>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="date_of_birth">Date of Birth:</label>
            <input type="date" id="date_of_birth" name="date_of_birth" required>
            <label for="contact_number">Contact Number:</label>
            <input type="text" id="contact_number" name="contact_number" required>
            <input type="submit" value="Add Pharmacist">
        </form>
    </div>
    <?php else : ?>
        <p>You must be logged in as an admin to access this page.</p>
    <?php endif; ?>
    <?php include "footer.html";?>
</body>
</html>
