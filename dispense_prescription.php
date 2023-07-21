<?php
session_start();

// Check if the user is logged in as a pharmacist
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Pharmacist') {
    // Check if the prescription_id is provided in the URL
    if (isset($_GET['prescription_id'])) {

        $prescription_id = $_GET['prescription_id'];

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

        // Fetch the pharmacist ID of the pharmacist who is currently using the system
        $pharmacist_user_id = $_SESSION['user_id']; 

        // Fetch the pharmacist ID from the pharmacists table based on the user_id
        $stmt = $conn->prepare("SELECT pharmacist_id FROM pharmacists WHERE user_id = ?");
        $stmt->bind_param('s', $pharmacist_user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $pharmacist = $result->fetch_assoc();

        // Check if the pharmacist exists in the pharmacists table
        if (!$pharmacist) {
            echo "<p>Invalid pharmacist ID. Please make sure you are logged in as a pharmacist.</p>";
            exit();
        }

        $pharmacist_id = $pharmacist['pharmacist_id'];

        // Fetch the pharmacist's name (full_name) from the users table based on the pharmacist_id
$stmt = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
$stmt->bind_param('s', $pharmacist_id);
$stmt->execute();
$result = $stmt->get_result();
$pharmacist_name = $result->fetch_assoc()['full_name'];

var_dump($prescription_id);

        // Fetch the prescription details
        $stmt = $conn->prepare("SELECT * FROM prescriptions WHERE prescription_id = ?");
        $stmt->bind_param('s', $prescription_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $prescription = $result->fetch_assoc();

        // Check if the prescription exists and is not already dispensed
        if (!$prescription || $prescription['dispensed'] == 1) {
            echo "<p>The prescription does not exist or is already dispensed.</p>";
        } else {
            // Update the prescription record to mark it as dispensed and record the pharmacist who dispensed it
            $updatePrescriptionStmt = $conn->prepare("UPDATE prescriptions SET dispensed = 1, dispensed_by = ? WHERE prescription_id = ?");
            $updatePrescriptionStmt->bind_param('ss', $dispensed_by, $prescription_id);
            $updatePrescriptionStmt->execute();

            echo "<p>Prescription dispensed successfully!</p>";
        }

        // Close the database connection
        $conn->close();

    } else {
        echo "<p>Prescription ID not provided. Please go back and click on a prescription to dispense.</p>";
    }
} else {
    echo "<p>You must be logged in as a pharmacist to access this page.</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dispense Prescription</title>
    <link rel="stylesheet" type="text/css" href="dispense_prescription.css">
</head>
<body>
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Pharmacist') : ?>
    <h2>Dispense Prescription</h2>
    <div class="dispense-prescription">
        <?php if (!$prescription || $prescription['dispensed'] == 1) : ?>
            <p>No prescription to dispense or prescription is already dispensed.</p>
        <?php else : ?>
            <p>Prescription ID: <?php echo $prescription_id; ?></p>
            <p>Patient ID: <?php echo $prescription['patient_id']; ?></p>
            <!-- Add more prescription details here -->
            <form method="post" action="dispense_prescription.php?prescription_id=<?php echo $prescription_id; ?>">
                <!-- Add any additional fields for dispensing, if needed -->
                <input type="submit" value="Dispense Prescription">
            </form>
        <?php endif; ?>
    </div>
    <?php else : ?>
        <p>You must be logged in as a pharmacist to access this page.</p>
    <?php endif; ?>
</body>
</html>
