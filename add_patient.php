<?php
session_start();

// Check if user is logged in as an admin or doctor
if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Doctor')) {
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

    // Function to calculate age based on date of birth
    function calculateAge($dateOfBirth) {
        $dateOfBirth = new DateTime($dateOfBirth);
        $currentDate = new DateTime();
        $age = $currentDate->diff($dateOfBirth)->y;
        return $age;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_id = $_POST['user_id'];
        $username = $_POST['username'];
        $fullName = $_POST['full_name'];
        $dateOfBirth = $_POST['date_of_birth'];
        $password = $_POST['password']; // You should use a secure hashing algorithm to store the password

        // Calculate age based on date of birth
        $age = calculateAge($dateOfBirth);

        $email = $user_id . '@example.com'; // Generate email using user_id (national ID)
        $contactNumber = $_POST['contact_number'];
        $role = 'Patient';
        $allergies = $_POST['allergies'];

        // Insert into the users table
        $insertUserStmt = $conn->prepare("INSERT INTO users (user_id, username, full_name, password, email, contact_number, date_of_birth, age, role) 
                                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insertUserStmt->bind_param('sssssssis', $user_id, $username, $fullName, $password, $email, $contactNumber, $dateOfBirth, $age, $role);
        $insertUserResult = $insertUserStmt->execute();

        if ($insertUserResult) {
            // Insert into the patients table
            $insertPatientStmt = $conn->prepare("INSERT INTO patients (user_id, allergies) VALUES (?, ?)");
            $insertPatientStmt->bind_param('ss', $user_id, $allergies);
            $insertPatientResult = $insertPatientStmt->execute();

            if ($insertPatientResult) {
                // Get the auto-generated patient_number
                $patient_number = $insertPatientStmt->insert_id;
                // Generate the patient_id with the format "P0001"
                $patient_id = 'P' . str_pad($patient_number, 4, '0', STR_PAD_LEFT);

                // Update the patient record with the patient_id
                $updatePatientStmt = $conn->prepare("UPDATE patients SET patient_id = ? WHERE user_id = ?");
                $updatePatientStmt->bind_param('ss', $patient_id, $user_id);
                $updatePatientResult = $updatePatientStmt->execute();

                if ($updatePatientResult) {
                    echo "<p>New patient added successfully! Patient ID: $patient_id</p>";
                } else {
                    echo "Error updating patient data: " . $conn->error;
                }
            } else {
                echo "Error inserting patient data: " . $conn->error;
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
    <title>Add New Patient</title>
    <link rel="stylesheet" type="text/css" href="add_patient.css">
</head>
<body>
<?php include "header.html";?>
    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Doctor')) : ?>
    <h2>Add New Patient</h2>
    <div class="add-patient-form">
        <form method="post" action="add_patient.php">
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
            <label for="allergies">Allergies:</label>
            <input type="text" id="allergies" name="allergies" required>
            <input type="submit" value="Add Patient">
        </form>
    </div>
    <?php else : ?>
        <p>You must be logged in as an admin or doctor to access this page.</p>
    <?php endif; ?>
    <?php include "footer.html";?>
</body>
</html>
