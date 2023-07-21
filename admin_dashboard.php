<?php
session_start();

// Check if the user is logged in as an admin
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin') {
    $admin_id = $_SESSION['user_id'];
    $admin_username = $_SESSION['username'];

    // Retrieve admin's details from the database
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

    // Retrieve admin's details
    $stmt = $conn->prepare("SELECT u.*, a.* FROM users u JOIN admins a ON u.user_id = a.user_id WHERE u.user_id = ?");
    $stmt->bind_param('s', $admin_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" type="text/css" href="admin_dashboard.css">
</head>
<body>
    <?php if (isset($admin)) : ?>
        <h2>Welcome, <?php echo $admin_username; ?></h2>
        <div class="dropdown">
            <button>Actions</button>
            <div class="dropdown-content">
                <!-- Link to view admin details -->
                <a href="#admin_profile">View Profile</a>
                <!-- Link to update admin details -->
                <a href="update_admin.php">Update Details</a>
                <!-- Link to delete admin account -->
                <a href="delete_admin.php" onclick="return confirm('Are you sure you want to delete your account?')">Delete Account</a>
                <!-- Link to disable admin account -->
                <a href="disable_admin.php" onclick="return confirm('Are you sure you want to disable your account?')">Disable Account</a>
                <!-- Link to view all doctors -->
                <a href="view_doctors.php">View All Doctors</a>
                <!-- Link to add new doctor -->
                <a href="add_doctor.php">Add New Doctor</a>
                <!-- Link to view all patients -->
                <a href="view_patients.php">View All Patients</a>
                <!-- Link to add new patient -->
                <a href="add_patient.php">Add New Patient</a>
                <!-- Link to view all pharmacists -->
                <a href="view_pharmacists.php">View All Pharmacists</a>
                <!-- Link to add new pharmacist -->
                <a href="add_pharmacist.php">Add New Pharmacist</a>
                <!-- Link to view all drugs -->
                <a href="view_drugs.php">View All Drugs</a>
                <!-- Link to add new drug -->
                <a href="add_drug.php">Add New Drug</a>
                <!-- Link to view all prescriptions -->
                <a href="view_prescriptions.php">View All Prescriptions</a>
                <!-- Logout link -->
                <a href="logout.php">Logout</a>
            </div>
        </div>

        <!-- Admin Profile -->
        <div class="main" id="admin_profile">
            <h2>Admin Profile</h2>
            <table>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Contact Number</th>
                    <th>Age</th>
                </tr>
                <tr>
                    <td><?php echo $admin['full_name']; ?></td>
                    <td><?php echo $admin['email']; ?></td>
                    <td><?php echo $admin['contact_number']; ?></td>
                    <td><?php echo $admin['age']; ?></td>
                </tr>
            </table>
        </div>

    <?php else : ?>
        <p>You must be logged in as an admin to access this page.</p>
    <?php endif; ?>
</body>
</html>
