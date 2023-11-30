<?php
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
    // Get and sanitize input from the API request
    $requestData = json_decode(file_get_contents('php://input'), true);

    $user_id = $requestData['user_id'];
    $username = $requestData['username'];
    $full_name = $requestData['full_name'];
    $password = $requestData['password'];
    $email = $requestData['email'];
    $contact_number = $requestData['contact_number'];
    $date_of_birth = $requestData['date_of_birth'];
    $role = $requestData['role'];

    // Calculate the age based on the date of birth
    $dob = new DateTime($date_of_birth);
    $current_date = new DateTime();
    $age = $dob->diff($current_date)->y;

    if (isset($user_id) && !empty($user_id)) {
        // Prepare the query for inserting into the Users table
        $stmt_users = $conn->prepare("INSERT INTO Users (user_id, username, full_name, password, email, contact_number, date_of_birth, age, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_users->bind_param('sssssssss', $user_id, $username, $full_name, $password, $email, $contact_number, $date_of_birth, $age, $role);
        $stmt_users->execute();

        if ($stmt_users->affected_rows > 0) {
            // Insert into the respective table based on the role
            switch ($role) {
                case 'Doctor':
                    $specialization = $requestData['specialization'] ?? null;
    $stmt_doctors = $conn->prepare("INSERT INTO Doctors (user_id, specialization) VALUES (?, ?)");
    $stmt_doctors->bind_param('ss', $user_id, $specialization);
    $stmt_doctors->execute();

    $doctor_number = $stmt_doctors->insert_id;
    $doctor_id = 'D' . str_pad($doctor_number, 4, '0', STR_PAD_LEFT);

    // Update the doctor record with the doctor_unique_id
    $stmt_update = $conn->prepare("UPDATE Doctors SET doctor_id = ? WHERE doctor_number = ?");
    $stmt_update->bind_param('si', $doctor_id, $doctor_number);
    $stmt_update->execute();

    $stmt_doctors->close();
                    break;
                case 'Pharmacist':
                    $stmt_pharmacists = $conn->prepare("INSERT INTO Pharmacists (user_id) VALUES (?)");
                    $stmt_pharmacists->bind_param('i', $user_id);
                    
                    $stmt_pharmacists->execute();
                    
                    $pharmacist_number = $stmt_pharmacists->insert_id;
                    $pharmacist_id = 'Ph' . str_pad($pharmacist_number, 4, '0', STR_PAD_LEFT);
        
                    // Update the pharmacist record with the pharmacist_unique_id
                    $stmt_update = $conn->prepare("UPDATE Pharmacists SET pharmacist_id = ? WHERE pharmacist_number = ?");
                    $stmt_update->bind_param('si', $pharmacist_id, $pharmacist_number);
                    $stmt_update->execute();
                    
                    $stmt_pharmacists->close();
                    break;
                case 'Patient':
                    $allergies = isset($_POST['allergies']) ? $_POST['allergies']: null;
                    $stmt_patients = $conn->prepare("INSERT INTO Patients (user_id,allergies) VALUES (?,?)");
                    $stmt_patients->bind_param('ss', $user_id, $allergies);
            
                    $stmt_patients->execute();
        
                    $patient_number = $stmt_patients->insert_id;
                    $patient_id = 'P' . str_pad($patient_number, 4, '0', STR_PAD_LEFT);
                    
                    // Update the patient record with the patient_unique_id
                    $stmt_update = $conn->prepare("UPDATE Patients SET patient_id = ? WHERE patient_number = ?");
                    $stmt_update->bind_param('si', $patient_id, $patient_number);
                    $stmt_update->execute();
                    
                    $stmt_patients->close();
                    break;
                case 'Admin':
                    $stmt_admins = $conn->prepare("INSERT INTO Admins (user_id) VALUES (?)");
                    $stmt_admins->bind_param('i', $user_id);
        
                    $stmt_admins->execute();
        
                    $admin_number = $stmt_admins->insert_id;
                    $admin_id = 'A' . str_pad($admin_number, 4, '0', STR_PAD_LEFT);
                    
                    // Update the admin record with the admin_unique_id
                    $stmt_update = $conn->prepare("UPDATE Admins SET admin_id = ? WHERE admin_number = ?");
                    $stmt_update->bind_param('si', $admin_id, $admin_number);
                    $stmt_update->execute();
                    
                    $stmt_admins->close();
                    break;
                default:
                    break;
            }

            $user_id = $stmt_users->insert_id;

            // Return the API key or any other relevant information in the response
            $api_key = substr(md5(uniqid()), 0, 16); // Generate a simple API key
            echo json_encode(['api_key' => $api_key, 'user_id' => $user_id]);
        } else {
            echo json_encode(['error' => 'Failed to register. Please try again']);
        }

        // Close the database connection
        $stmt_users->close();
    } else {
        echo json_encode(['error' => 'User ID is not provided.']);
    }

    $conn->close();
} else {
    echo json_encode(['error' => 'Method Not Allowed']);
}
?>
