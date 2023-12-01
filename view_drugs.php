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
        $search_stmt = $conn->prepare("SELECT * FROM drugs WHERE drug_id LIKE ? OR name LIKE ? OR drug_type LIKE ? OR category LIKE ?");
        $search_param = '%' . $search . '%';
        $search_stmt->bind_param('ssss', $search_param, $search_param, $search_param, $search_param);
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
    <?php include "header.html";?>
    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Doctor' || $_SESSION['role'] === 'Pharmacist' || $_SESSION['role'] === 'Admin')) : ?>
        <h2>View Drugs</h2>
        <div class="search-form">
            <form method="get" action="view_drugs.php">
                <input type="text" name="search" placeholder="Search by Drug ID, Name, Drug Type, or Category">
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
                    <th>Category</th>
                    <th>Image</th>
                    <?php if ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Pharmacist') : ?>
                        <th>Edit</th>
                    <?php endif; ?>
                </tr>
                <?php while ($drug = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo $drug['drug_id']; ?></td>
                        <td><?php echo $drug['name']; ?></td>
                        <td><?php echo $drug['drug_type']; ?></td>
                        <td><?php echo $drug['description']; ?></td>
                        <td><?php echo $drug['dosage_instructions']; ?></td>
                        <td><?php echo $drug['category']; ?></td>
                        <td>
                            <?php
                            if (!empty($drug['image'])) {
                                $imageData = base64_encode($drug['image']);
    
                                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                                $imageMimeType = finfo_buffer($finfo, base64_decode($imageData));
                                finfo_close($finfo);
    
                                // Create a link to the image viewer page
                                echo '<a href="image_viewer.php?drug_id=' . $drug['drug_id'] . '">';
                                echo '<img src="data:' . $imageMimeType . ';base64,' . $imageData . '" width="100" height="100" />';
                                echo '</a>';
    
                                // Add a button/link to view drug details
                                echo '<br>';
                                echo '<a href="view_drug_details.php?drug_id=' . $drug['drug_id'] . '">View Details</a>';
                            } else {
                                echo 'No Image';
                            }
                            ?>
                        </td>
                        <?php if ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Pharmacist') : ?>
                            <td>
                                <a href="edit_drug.php?drug_id=<?php echo $drug['drug_id']; ?>">Edit</a>
                            </td>
                        <?php endif; ?>
                        </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php else : ?>
        <p>You must be logged in as a doctor, pharmacist, or admin to access this page.</p>
    <?php endif; ?>
    <?php include "footer.html";?>
</body>
</html>
