<?php
session_start();

// Check if the user is logged in as a doctor, pharmacist, or admin
if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['Doctor', 'Pharmacist', 'Admin'])) {
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
    // Prepare the query to fetch prescriptions based on search
    $stmt = $conn->prepare("SELECT p.*, dr.name AS drug_name,
                                   u_d.full_name AS doctor_name,
                                   u_pt.full_name AS patient_name
                           FROM prescriptions p 
                           JOIN patients pt ON p.patient_id = pt.patient_id
                           JOIN doctors d ON p.doctor_id = d.doctor_id
                           JOIN users u_pt ON pt.user_id = u_pt.user_id 
                           JOIN users u_d ON d.user_id = u_d.user_id 
                           JOIN drugs dr ON p.drug_id = dr.drug_id 
                           WHERE p.prescription_id LIKE ? OR u_pt.full_name LIKE ? OR u_pt.user_id LIKE ?");
    $search_param = '%' . $search . '%';
    $stmt->bind_param('sss', $search_param, $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Fetch all prescriptions
    $stmt = $conn->prepare("SELECT p.*, dr.name AS drug_name,
                                    u_d.full_name AS doctor_name,
                                    u_pt.full_name AS patient_name
                            FROM prescriptions p 
                            JOIN patients pt ON p.patient_id = pt.patient_id
                            JOIN doctors d ON p.doctor_id = d.doctor_id
                            JOIN users u_pt ON pt.user_id = u_pt.user_id 
                            JOIN users u_d ON d.user_id = u_d.user_id 
                            JOIN drugs dr ON p.drug_id = dr.drug_id ");
    $stmt->execute();
    $result = $stmt->get_result();
}

}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Prescriptions</title>
    <link rel="stylesheet" type="text/css" href="view_prescriptions.css">
</head>
<body>
    <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['Doctor', 'Pharmacist', 'Admin'])) : ?>
    <h2>View Prescriptions</h2>
    <div class="search-form">
        <form method="get" action="view_prescriptions.php">
            <input type="text" name="search" placeholder="Search by Prescription ID, Patient Name, Patient ID, or User ID">
            <button type="submit">Search</button>
        </form>
    </div>
    <div class="prescriptions-table">
        <table>
            <tr>
                <th>Prescription ID</th>
                <th>Patient Name</th>
                <th>Doctor Name</th>
                <th>Drug Name</th>
                <th>Dosage</th>
                <th>Duration</th>
                <th>Dispensed</th>
                <th>Dispensed By</th>
                <th>Actions</th>
            </tr>
            <?php while ($prescription = $result->fetch_assoc()) : ?>
                <tr>
                <td><?php echo $prescription['prescription_id']; ?></td>
                    <td><?php echo $prescription['patient_name']; ?></td>
                    <td><?php echo $prescription['doctor_name']; ?></td>
                    <td><?php echo $prescription['drug_name']; ?></td>
                    <td><?php echo $prescription['dosage']; ?></td>
                    <td><?php echo $prescription['duration']; ?></td>
                    <td><?php echo $prescription['dispensed'] ? 'Yes' : 'No'; ?></td>
                    <td><?php echo $prescription['dispensed_by'] ? $prescription['dispensed_by'] : 'Not Dispensed Yet'; ?></td>
                    <td>
                    <?php if (!$prescription['dispensed']) : ?>
                        <a href="dispense_prescription.php?prescription_id=<?php echo $prescription['prescription_id']; ?>">Dispense</a>
                    <?php else : ?>
                        Prescription already dispensed.
                    <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
    <?php else : ?>
        <p>You must be logged in as a doctor, pharmacist, or admin to access this page.</p>
    <?php endif; ?>
</body>
</html>
