<?php
session_start();

// Check if the user is logged in as a patient
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Patient') {
    $patient_id = $_SESSION['user_id'];

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

    // Fetch prescription details for the patient from the database
    $stmt = $conn->prepare("SELECT p.*,  dr.name, dr.drug_type, dr.description,
                                  doc.user_id AS doctor_user_id,
                                  ph.user_id AS pharmacist_user_id,
                                  u_d.full_name AS doctor_full_name, u_d.email AS doctor_email, u_d.contact_number AS doctor_contact_number,
                                  u_ph.full_name AS pharmacist_full_name, u_ph.email AS pharmacist_email, u_ph.contact_number AS pharmacist_contact_number
                           FROM prescriptions p
                           JOIN drugs dr ON p.drug_id = dr.drug_id
                           LEFT JOIN pharmacists ph ON p.dispensed_by = ph.pharmacist_id
                           LEFT JOIN doctors doc ON p.doctor_id = doc.doctor_id
                           LEFT JOIN users u_d ON doc.user_id = u_d.user_id
                           LEFT JOIN users u_ph ON ph.user_id = u_ph.user_id
                      
                           WHERE p.patient_id = ?
                           ORDER BY p.prescription_id DESC");
    $stmt->bind_param('s', $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Medical Records</title>
    <link rel="stylesheet" type="text/css" href="view_medical_records.css">
</head>
<body>
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Patient') : ?>
    <h2>Medical Records</h2>
    <div class="medical-records">
        <?php if ($result->num_rows > 0) : ?>
            <table>
                <tr>
                    <th>Record ID</th>
                    <th>Doctor Name</th>
                    <th>Doctor Email</th>
                    <th>Doctor Contact</th>
                    <th>Pharmacist Name</th>
                    <th>Pharmacist Email</th>
                    <th>Pharmacist Contact</th>
                    <th>Drug Name</th>
                    <th>Drug Type</th>
                    <th>Dosage</th>
                    <th>Duration</th>
                </tr>
                <?php while ($record = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo $record['prescription_id']; ?></td>
                        <td><?php echo $record['doctor_full_name']; ?></td>
                        <td><?php echo $record['doctor_email']; ?></td>
                        <td><?php echo $record['doctor_contact_number']; ?></td>
                        <td><?php echo $record['pharmacist_full_name']; ?></td>
                        <td><?php echo $record['pharmacist_email']; ?></td>
                        <td><?php echo $record['pharmacist_contact_number']; ?></td>
                        <td><?php echo $record['drug_name']; ?></td>
                        <td><?php echo $record['drug_type']; ?></td>
                        <td><?php echo $record['dosage']; ?></td>
                        <td><?php echo $record['duration']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else : ?>
            <p>No medical records found.</p>
        <?php endif; ?>
    </div>
    <?php else : ?>
        <p>You must be logged in as a patient to access this page.</p>
    <?php endif; ?>
</body>
</html>
