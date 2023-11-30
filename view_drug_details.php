<!DOCTYPE html>
<html>
<head>
    <title>Drug Details</title>
    <link rel="stylesheet" type="text/css" href="view_drug_details.css">

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

    // Check if the drug_id is provided in the query parameter
    if (isset($_GET['drug_id'])) {
        $drug_id = $_GET['drug_id'];

        // Perform a database query to fetch all details for the specified drug
        // Replace 'your_table_name' with the actual name of your database table
        $sql = "SELECT * FROM drugs WHERE drug_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $drug_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        // Check if drug details were found in the database
        if ($result->num_rows > 0) {
            $drug = $result->fetch_assoc();

            // Display drug details, including the image
            echo '<h2>Drug Details</h2>';
            echo '<p>Drug ID: ' . $drug['drug_id'] . '</p>';
            echo '<p>Name: ' . $drug['name'] . '</p>';
            echo '<p>Drug Type: ' . $drug['drug_type'] . '</p>';
            echo '<p>Description: ' . $drug['description'] . '</p>';
            echo '<p>Dosage Instructions: ' . $drug['dosage_instructions'] . '</p>';
            
            if (!empty($drug['image'])) {
                $imageData = base64_encode($drug['image']);
                $imageMimeType = $drug['image_mime_type'];

                // Display the image with the determined MIME type
                echo '<a href="image_view.php?drug_id=' . $drug['drug_id'] . '">';
                echo '<img src="data:' . $imageMimeType . ';base64,' . $imageData . '" width="300" height="300" />';
                echo '</a>';
} else {
    echo 'No Image';
            }
        } else {
            echo "Drug details not found for the specified drug.";
        }
    } else {
        echo "Drug ID not specified.";
    }
}
    ?>
    <?php include "footer.html";?>
</body>
</html>
