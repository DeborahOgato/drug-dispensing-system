<?php
session_start();

// Check if user is logged in as a pharmacist or admin
if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Pharmacist' || $_SESSION['role'] === 'Admin')) {
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

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $drug_type = $_POST['drug_type'];
        $description = $_POST['description'];
        $dosage_instructions = $_POST['dosage_instructions'];

        // Insert into the drugs table
        $stmt_drugs = $conn->prepare("INSERT INTO drugs (name, drug_type, description, dosage_instructions) 
                                         VALUES (?, ?, ?, ?)");
        $stmt_drugs->bind_param('ssss', $name, $drug_type, $description, $dosage_instructions);

        $stmt_drugs->execute();

        
            $drug_number = $stmt_drugs->insert_id;
            $drug_id = 'Drug' . str_pad($drug_number, 4, '0', STR_PAD_LEFT);

            // Update the drug record with the drug_id
            $stmt_update = $conn->prepare("UPDATE drugs SET drug_id = ? WHERE drug_number = ?");
            $stmt_update->bind_param('ss', $drug_id, $drug_number);
            $stmt_update->execute();

            

            echo "<p>New drug added successfully! Drug ID: $drug_id</p>";
       
        }
    
  
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Drug</title>
    <link rel="stylesheet" type="text/css" href="add_drug.css">
</head>
<body>
    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Pharmacist' || $_SESSION['role'] === 'Admin')) : ?>
    <h2>Add Drug</h2>
    <div class="add-drug-form">
        <form method="post" action="add_drug.php">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>
            <label for="drug_type">Drug Type:</label>
            <input type="text" id="drug_type" name="drug_type" required>
            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="4" required></textarea>
            <label for="dosage_instructions">Dosage Instructions:</label>
            <textarea id="dosage_instructions" name="dosage_instructions" rows="4" required></textarea>
            <input type="submit" value="Add Drug">
        </form>
    </div>
    <?php else : ?>
        <p>You must be logged in as a pharmacist or admin to access this page.</p>
    <?php endif; ?>
</body>
</html>
