<?php
session_start();

// Check if the user is logged in as a doctor, pharmacist, or admin
if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Doctor' || $_SESSION['role'] === 'Pharmacist' || $_SESSION['role'] === 'Admin')) {
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

    // Handle search query
    if (isset($_GET['search'])) {
        $search = $_GET['search'];
        // Prepare the query to fetch drugs based on search
        $search_stmt = $conn->prepare("SELECT * FROM drugs WHERE drug_id LIKE ? OR name LIKE ? OR drug_type LIKE ?");
        $search_param = '%' . $search . '%';
        $search_stmt->bind_param('sss', $search_param, $search_param, $search_param);
        $search_stmt->execute();

        // Fetch the searched drugs
        $result = $search_stmt->get_result();
    } else {
        // Prepare the query to fetch all drugs
        $stmt = $conn->prepare("SELECT * FROM drugs");
        $stmt->execute();

        // Fetch all drugs
        $result = $stmt->get_result();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Drugs</title>
    <link rel="stylesheet" type="text/css" href="view_drugs.css">
</head>
<body>
    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Doctor' || $_SESSION['role'] === 'Pharmacist' || $_SESSION['role'] === 'Admin')) : ?>
    <h2>View Drugs</h2>
    <div class="search-form">
        <form method="get" action="view_drugs.php">
            <input type="text" name="search" placeholder="Search by Drug ID, Name, or Drug Type">
            <button type="submit">Search</button>
        </form>
    </div>
    <div class="drugs-table">
        <table>
            <tr>
                <th>Drug ID</th>
                <th>Name</th>
                <th>Drug Type</th>
                <th>Description</th>
                <th>Dosage Instructions</th>
            </tr>
            <?php while ($drug = $result->fetch_assoc()) : ?>
                <tr>
                    <td><?php echo $drug['drug_id']; ?></td>
                    <td><?php echo $drug['name']; ?></td>
                    <td><?php echo $drug['drug_type']; ?></td>
                    <td><?php echo $drug['description']; ?></td>
                    <td><?php echo $drug['dosage_instructions']; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
    <?php else : ?>
        <p>You must be logged in as a doctor, pharmacist, or admin to access this page.</p>
    <?php endif; ?>
</body>
</html>
