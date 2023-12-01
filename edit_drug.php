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

    $drug = []; 
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $drug_id = htmlspecialchars($_POST['drug_id']);
        $name = htmlspecialchars($_POST['name']);
        $drug_type = htmlspecialchars($_POST['drug_type']);
        $description = htmlspecialchars($_POST['description']);
        $dosage_instructions = htmlspecialchars($_POST['dosage_instructions']);
        $category = htmlspecialchars($_POST['category']);

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($fileExtension, $allowedExtensions)) {
                $imageData = file_get_contents($_FILES['image']['tmp_name']);

                if ($imageData !== false) {
                    // Update the drugs table
                    $stmt_drugs = $conn->prepare("UPDATE drugs SET name=?, drug_type=?, description=?, dosage_instructions=?, image=?, category=? WHERE drug_id=?");
                    $stmt_drugs->bind_param('sssssss', $name, $drug_type, $description, $dosage_instructions, $imageData, $category, $drug_id);

                    if ($stmt_drugs->execute()) {
                        echo "<p>Drug updated successfully!</p>";
                    } else {
                        echo "<p>Error updating the drug. " . $stmt_drugs->error . "</p>";
                    }
                } else {
                    echo "<p>Error uploading the image. Please try again.</p>";
                }
            } else {
                echo "<p>Invalid image format. Please use jpg, jpeg, png, or gif.</p>";
            }
        } else {
            // If no new image is uploaded, update without changing the image
            $stmt_drugs = $conn->prepare("UPDATE drugs SET name=?, drug_type=?, description=?, dosage_instructions=?, category=? WHERE drug_id=?");
            $stmt_drugs->bind_param('ssssss', $name, $drug_type, $description, $dosage_instructions, $category, $drug_id);

            if ($stmt_drugs->execute()) {
                echo "<p>Drug updated successfully!</p>";
            } else {
                echo "<p>Error updating the drug. " . $stmt_drugs->error . "</p>";
            }
        }
    }

    // Fetch drug details for pre-filling the form
    if (isset($_GET['id'])) {
        $drug_id = htmlspecialchars($_GET['id']);
        $stmt_get_drug = $conn->prepare("SELECT * FROM drugs WHERE drug_id=?");
        $stmt_get_drug->bind_param('s', $drug_id);
        $stmt_get_drug->execute();
        $result = $stmt_get_drug->get_result();

        if ($result->num_rows > 0) {
            $drug = $result->fetch_assoc();
        } else {
            // Handle drug not found
            die("Drug not found");
        }
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Edit Drug</title>
    <link rel="stylesheet" type="text/css" href="edit_drug.css">
</head>
<body>
    <?php include "header.html";?>
    <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Pharmacist' || $_SESSION['role'] === 'Admin')) : ?>
    <h2>Edit Drug</h2>
    <div class="edit-drug-form">
        <form method="post" action="edit_drug.php" enctype="multipart/form-data">
            <input type="hidden" name="drug_id" value="<?php echo isset($drug['drug_id']) ? $drug['drug_id'] : ''; ?>">


            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo isset($drug['name']) ? $drug['name'] : ''; ?>" required>
            
            <label for="drug_type">Drug Type:</label>
            <input type="text" id="drug_type" name="drug_type" value="<?php echo isset($drug['drug_type']) ? $drug['drug_type'] : ''; ?>" required>
            
            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="3" required><?php echo isset($drug['description']) ? $drug['description'] : ''; ?></textarea>
            
            <label for="dosage_instructions">Dosage Instructions:</label>
            <textarea id="dosage_instructions" name="dosage_instructions" rows="3" required><?php echo isset($drug['dosage_instructions']) ? $drug['dosage_instructions'] : ''; ?></textarea>
            
            <label for="image">Drug Image:</label>
            <input type="file" id="image" name="image" accept="image/*">
            
            <label for="category">Category:</label>
            <input type="text" id="category" name="category" value="<?php echo isset($drug['category']) ? $drug['category'] : ''; ?>" required>

            <input type="submit" value="Update Drug">
        </form>
    </div>
    <?php else : ?>
        <p>You must be logged in as a pharmacist or admin to access this page.</p>
    <?php endif; ?>
    <?php include "footer.html";?>
</body>
</html>
