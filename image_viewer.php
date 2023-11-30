<!DOCTYPE html>
<html>
<head>
    <title>Image Viewer</title>
    <link rel="stylesheet" type="text/css" href="image_viewer.css">

</head>
<body>
<?php include "header.html";?>
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
    if (isset($_GET['drug_id'])) {
        $drug_id = $_GET['drug_id'];
    
        
        $sql = "SELECT image FROM drugs WHERE drug_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $drug_id);
        $stmt->execute();
        $stmt->bind_result($imageData);
        $stmt->fetch();
        $stmt->close();
    
        
        if (!empty($imageData)) {

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $imageMimeType = finfo_buffer($finfo, $imageData);
            finfo_close($finfo);

            // Set the appropriate Content-Type header
            header("Content-Type: " . $imageMimeType);
    
            // Output the image data to display it
            echo $imageData;
        } else {
            echo "Image not found for the specified drug.";
        }
    } else {
        echo "Drug ID not specified.";
    }
}
    ?>
    <?php include "footer.html";?>
    </body>
</html>