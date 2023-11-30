<?php
session_start();

// Check if the user is logged in as a patient
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Patient') {
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];

    // Retrieve the patient's details from the database
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

    // Prepare and execute the query to fetch patient's details
    $stmt = $conn->prepare("SELECT u.*, p.* FROM users u JOIN patients p ON u.user_id = p.user_id WHERE u.user_id = ?");
    $stmt->bind_param('s', $user_id);
    $stmt->execute();

    // Fetch the patient's details
    $result = $stmt->get_result();
    $patient = $result->fetch_assoc();


}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Patient Dashboard</title>
    <link rel="stylesheet" type="text/css" href="patient_dashboard.css">
</head>
<body>
<?php include "header.html";?>
    <?php if (isset($patient)) : ?>
    <h2>Welcome, <?php echo $username; ?>!</h2>
    <div class="dropdown">
        <button>Actions</button>
        <div class="dropdown-content">
            <!-- Link to view patient details -->
            <a href="#patient_profile">View Profile</a>
            <!-- Link to update patient details -->
            <a href="update_patient.php">Update Account</a>
            <!-- Link to delete patient account -->
            <a href="delete_patient.php" onclick="return confirm('Are you sure you want to delete your account?')">Delete Account</a>
            <!-- Link to disable patient account -->
            <a href="disable_patient.php" onclick="return confirm('Are you sure you want to disable your account?')">Disable Account</a>
            <!-- Link to view medical records -->
            <a href="view_medical_records.php">View Medical Records</a>
            <!-- Logout link -->
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <!-- Patient Profile -->
    <div class="main" id="patient_profile">
        <h2>Patient Profile</h2>
        <table>
            <tr>
                <th>Full Name</th>
                <th>Email</th>
                <th>Contact Number</th>
                <th>Age</th>
            </tr>
            <tr>
                <td><?php echo $patient['full_name']; ?></td>
                <td><?php echo $patient['email']; ?></td>
                <td><?php echo $patient['contact_number']; ?></td>
                <td><?php echo $patient['age']; ?></td>
            </tr>
        </table>
    </div>

    <?php else : ?>
        <p>You must be logged in as a patient to access this page.</p>
    <?php endif; ?>
    <?php include "footer.html";?>
</body>
</html>
