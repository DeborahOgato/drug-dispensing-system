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

    // Fetch all pharmacists with their corresponding user details from the database using JOIN
    $pharmacistsQuery = "SELECT p.*, u.* FROM pharmacists p JOIN users u ON p.user_id = u.user_id";
    $pharmacistsResult = $conn->query($pharmacistsQuery);

    if (!$pharmacistsResult) {
        die("Error fetching pharmacists: " . $conn->error);
    }

}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Pharmacists</title>
    <link rel="stylesheet" type="text/css" href="view_pharmacists.css">
</head>
<body>
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin') : ?>
    <h2>View Pharmacists</h2>
    <div class="pharmacist-list">
        <?php if ($pharmacistsResult->num_rows > 0) : ?>
            <table>
                <tr>
                    <th>Pharmacist ID</th>
                    <th>National ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                </tr>
                <?php while ($pharmacist = $pharmacistsResult->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo $pharmacist['pharmacist_id']; ?></td>
                        <td><?php echo $pharmacist['user_id']; ?></td>
                        <td><?php echo $pharmacist['full_name']; ?></td>
                        <td><?php echo $pharmacist['email']; ?></td>
                        <td><?php echo $pharmacist['contact_number']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else : ?>
            <p>No pharmacists found.</p>
        <?php endif; ?>
    </div>
    <?php else : ?>
        <p>You must be logged in as an admin to access this page.</p>
    <?php endif; ?>
</body>
</html>
