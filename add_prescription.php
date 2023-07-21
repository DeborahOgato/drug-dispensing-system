<?php
session_start();

// Check if user is logged in as a doctor
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Doctor') {
    
   

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
    $user_id = $_SESSION['user_id'];

      // Get the doctor_id from the doctors table based on the user_id
      $stmt_doctor_id = $conn->prepare("SELECT doctor_id FROM Doctors WHERE user_id = ?");
      $stmt_doctor_id->bind_param('s', $user_id);
      $stmt_doctor_id->execute();
      $stmt_doctor_id->bind_result($doctor_id);
      $stmt_doctor_id->fetch();
      $stmt_doctor_id->close();


    $patient_id = $_GET['patient_id'];

    // Fetch the list of drugs from the drugs table for selection
    $stmt = $conn->prepare("SELECT drug_id, name FROM drugs");
    if ($stmt->execute()) {
        $drugResult = $stmt->get_result();
        $drugs = $drugResult->fetch_all(MYSQLI_ASSOC);
    } else {
        echo "Error fetching drug data: " . $conn->error;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $drug_id = $_POST['drug_id'];
        $dosage = $_POST['dosage'];
        $duration = $_POST['duration'];

        try {

                 // Insert into the prescriptions table
            $stmt_prescriptions = $conn->prepare("INSERT INTO prescriptions (patient_id, doctor_id, drug_id, dosage, duration, dispensed, dispensed_by) 
                                       VALUES (?, ?, ?, ?, ?, 0, NULL)");
            $stmt_prescriptions->bind_param('sssss', $patient_id, $doctor_id, $drug_id, $dosage, $duration);
            $stmt_prescriptions->execute();


            $prescription_number = $stmt_prescriptions->insert_id;
            $prescription_id = 'Pr' . str_pad($prescription_number, 4, '0', STR_PAD_LEFT);

            // Update the prescription record with the prescription_id
            $stmt_update = $conn->prepare("UPDATE prescriptions SET prescription_id = ? WHERE prescription_number = ?");
            $stmt_update->bind_param('ss', $prescription_id, $prescription_number);
            $stmt_update->execute();

    echo "<p>New prescription added successfully! Prescription ID: $prescription_id</p>";
  } catch (mysqli_sql_exception $e){
    echo "Error:" .$e->getMessage();

    }
  }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Prescription</title>
    <link rel="stylesheet" type="text/css" href="add_prescription.css">
</head>
<body>
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Doctor') : ?>
    <h2>Add Prescription</h2>
    <div class="add-prescription-form">
        <form method="post" action="add_prescription.php?patient_id=<?php echo $patient_id; ?>">
            <label for="drug_id">Select Drug:</label>
            <select id="drug_id" name="drug_id" required>
                <option value="">Select a drug</option>
                <?php foreach ($drugs as $drug) : ?>
                    <option value="<?php echo $drug['drug_id']; ?>"><?php echo $drug['name']; ?></option>
                <?php endforeach; ?>
            </select>
            <label for="dosage">Dosage:</label>
            <input type="text" id="dosage" name="dosage" required>
            <label for="duration">Duration:</label>
            <input type="text" id="duration" name="duration" required>
            <input type="submit" value="Add Prescription">
        </form>
    </div>
    <?php else : ?>
        <p>You must be logged in as a doctor to access this page.</p>
    <?php endif; ?>
</body>
</html>
