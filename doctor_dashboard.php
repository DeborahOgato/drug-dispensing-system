<?php
session_start();

// Check if user is logged in as a doctor
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Doctor') {
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];

    // Retrieve the doctor's details from the database
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

    // Prepare and execute the query to fetch doctor's details
    $stmt = $conn->prepare("SELECT u.*, d.* FROM users u JOIN doctors d ON u.user_id = d.user_id WHERE u.user_id = ?");
    $stmt->bind_param('s', $user_id);
    $stmt->execute();

    // Fetch the doctor's details
    $result = $stmt->get_result();
    $doctor = $result->fetch_assoc();

}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Doctor Dashboard</title>
    <link rel="stylesheet" type="text/css" href="doctor_dashboard.css">
</head>
<body>
    <?php if (isset($doctor)) : ?>
    <h2>Welcome, <?php echo $username; ?>!</h2>
    <div class="dropdown">
        <button>Actions</button>
        <div class="dropdown-content">
            <!-- Link to view doctor details -->
            <a href="#doctor_profile">View Profile</a>
            <!-- Link to update doctor details -->
            <a href="update_doctor.php">Update Details</a>
            <!-- Link to delete doctor account -->
            <a href="delete_doctor.php" onclick="return confirm('Are you sure you want to delete your account?')">Delete Account</a>
            <!-- Link to disable doctor account -->
            <a href="disable_doctor.php" onclick="return confirm('Are you sure you want to disable your account?')">Disable Account</a>
            <!-- Link to view all patients -->
            <a href="view_patients.php">View All Patients</a>
            <!-- Link to add new patient -->
            <a href="add_patient.php">Add New Patient</a>
            <!-- Link to view all prescriptions -->
            <a href="view_prescriptions.php">View All Prescriptions</a>
            <!-- Link to add new prescription -->
            <a href="add_prescription.php">Add New Prescription</a>
            <!-- Link to view all drugs -->
            <a href="view_drugs.php">View All Drugs</a>
            <!-- Logout link -->
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <!-- Doctor Profile -->
    <div class="main" id="doctor_profile">
        <h2>Doctor Profile</h2>
        <table>
            <tr>
                <th>Full Name</th>
                <th>Email</th>
                <th>Contact Number</th>
                <th>Age</th>
            </tr>
            <tr>
                <td><?php echo $doctor['full_name']; ?></td>
                <td><?php echo $doctor['email']; ?></td>
                <td><?php echo $doctor['contact_number']; ?></td>
                <td><?php echo $doctor['age']; ?></td>
            </tr>
        </table>
    </div>

    <?php else : ?>
        <p>You must be logged in as a doctor to access this page.</p>
    <?php endif; ?>
</body>
</html>
