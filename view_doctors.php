<?php
session_start();

// Check if user is logged in as an admin
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin') {

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

    // Fetch all doctors with their corresponding user details from the database using JOIN
    $doctorsQuery = "SELECT d.*, u.* FROM doctors d JOIN users u ON d.user_id = u.user_id";
    $doctorsResult = $conn->query($doctorsQuery);

    if (!$doctorsResult) {
        die("Error fetching doctors: " . $conn->error);
    }

}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Doctors</title>
    <link rel="stylesheet" type="text/css" href="view_doctors.css">
</head>
<body>
<?php include "header.html";?>
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin') : ?>
    <h2>View Doctors</h2>
    <div class="doctor-list">
        <?php if ($doctorsResult->num_rows > 0) : ?>
            <table>
                <tr>
                    <th>Doctor ID</th>
                    <th>National ID</th>
                    <th>Full Name</th>
                    <th>Specialization</th>
                    <th>Email</th>
                    <th>Phone</th>
                </tr>
                <?php while ($doctor = $doctorsResult->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo $doctor['doctor_id']; ?></td>
                        <td><?php echo $doctor['user_id']; ?></td>
                        <td><?php echo $doctor['full_name']; ?></td>
                        <td><?php echo $doctor['specialization']; ?></td>
                        <td><?php echo $doctor['email']; ?></td>
                        <td><?php echo $doctor['contact_number']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else : ?>
            <p>No doctors found.</p>
        <?php endif; ?>
    </div>
    <?php else : ?>
        <p>You must be logged in as an admin to access this page.</p>
    <?php endif; ?>
    <?php include "footer.html";?>
</body>
</html>
