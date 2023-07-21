<?php
session_start();

// Check if user is logged in as an admin, pharmacist, or doctor
if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Pharmacist' || $_SESSION['role'] === 'Doctor')) {
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

    // Function to search patients based on National ID (user_id), patient_id, or full_name
    function searchPatients($conn, $searchTerm) {
        $stmt = $conn->prepare("SELECT u.user_id, u.full_name, u.email, u.contact_number, u.age, p.patient_id, p.allergies 
                               FROM users u 
                               LEFT JOIN patients p ON u.user_id = p.user_id 
                               WHERE (u.user_id = ? OR p.patient_id = ? OR u.full_name LIKE ?) AND u.role='patient'");
        $searchTerm = '%' . $searchTerm . '%';
        $stmt->bind_param('sss', $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // If a search query is submitted, search for patients based on the query
    $patients = [];
    if (isset($_GET['search'])) {
        $searchTerm = $_GET['search'];
        $patients = searchPatients($conn, $searchTerm);
    } else {
        // Fetch all patients
        $stmt = $conn->prepare("SELECT u.user_id, u.full_name, u.email, u.contact_number,u.age, p.patient_id, p.allergies 
                               FROM users u 
                               LEFT JOIN patients p ON u.user_id = p.user_id
                               WHERE u.role = 'patient'");
        $stmt->execute();
        $result = $stmt->get_result();
        $patients = $result->fetch_all(MYSQLI_ASSOC);
    }

   
       
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Patients</title>
    <link rel="stylesheet" type="text/css" href="view_patients.css">
</head>
<body>
    <h2>List of Patients</h2>
    <form method="GET" action="view_patients.php">
        <label for="search">Search:</label>
        <input type="text" id="search" name="search" placeholder="Search by National ID, Patient ID, or Full Name">
        <input type="submit" value="Search">
    </form>

    <?php if (isset($patients) && !empty($patients)) : ?>
    <table>
        <tr>
            <th>National ID</th>
            <th>Patient ID</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Contact Number</th>
            <th>Age</th>
            <th>Allergies</th>
        </tr>
        <?php foreach ($patients as $patient) : ?>
        <tr>
            <td><?php echo $patient['user_id']; ?></td>
            <td><?php echo $patient['patient_id']; ?></td>
            <td><?php echo $patient['full_name']; ?></td>
            <td><?php echo $patient['email']; ?></td>
            <td><?php echo $patient['contact_number']; ?></td>
            <!-- Age calculation (assuming it is fetched from the database earlier) -->
            <td><?php echo $patient['age']; ?></td>
            <td><?php echo $patient['allergies']; ?></td>

            <td>
                 <!-- Add a link to prescribe drugs for the patient -->
                 <a href="add_prescription.php?patient_id=<?php echo $patient['patient_id']; ?>">Prescribe Drugs</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

  
    <?php else : ?>
        <p>No patients found.</p>
    <?php endif; ?>
</body>
</html>
