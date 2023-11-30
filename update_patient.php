<?php
session_start();

// Check if user is logged in as a patient
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Patient') {
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];

     // Include the database connection file
     $host = 'localhost';
     $db_name = 'drug-dispensing-system';
     $user = 'root';
     $password = '';
 
     $conn = new mysqli($host, $user, $password, $db_name);
 
     if ($conn->connect_error) {
         die("Connection failed: " . $conn->connect_error);
     }
 
      // Prepare and execute the query to fetch patient's details from both users and patients tables
    $stmt = $conn->prepare("SELECT u.*, p.* FROM users u JOIN patients p ON u.user_id = p.user_id WHERE u.user_id = ?");
    $stmt->bind_param('s', $user_id);
    $stmt->execute();

    // Fetch the doctor's details
    $result = $stmt->get_result();
    $patient = $result->fetch_assoc();
 }

    

?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Patient Details</title>
    <link rel="stylesheet" type="text/css" href="update_patient.css">
</head>
<body>
<?php include "header.html";?>
    <?php if (isset($patient)) : ?>
    <h2>Update Patient Details</h2>
    <div class="update-form">
        <!-- Update Username -->
        <form action="update_patient.php" method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo $patient['username']; ?>" required>
            <input type="submit" name="update_username" value="Update">
        </form>

        <!-- Update Full Name -->
        <form action="update_patient.php" method="post">
            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo $patient['full_name']; ?>" required>
            <input type="submit" name="update_full_name" value="Update">
        </form>

        <!-- Update Password -->
        <form action="update_patient.php" method="post">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <input type="submit" name="update_password" value="Update">
        </form>

        <!-- Update Email -->
        <form action="update_patient.php" method="post">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo $patient['email']; ?>" required>
            <input type="submit" name="update_email" value="Update">
        </form>

        <!-- Update Contact Number -->
        <form action="update_patient.php" method="post">
            <label for="contact_number">Contact Number:</label>
            <input type="text" id="contact_number" name="contact_number" value="<?php echo $patient['contact_number']; ?>" required>
            <input type="submit" name="update_contact_number" value="Update">
        </form>

        <!-- Update Specialization -->
        <form action="update_patient.php" method="post">
            <label for="allergies">Allergies:</label>
            <input type="text" id="allergies" name="allergies" value="<?php echo $patient['allergies']; ?>" required>
            <input type="submit" name="update_allergies" value="Update">
        </form>
    </div>

    <?php else : ?>
        <p>You must be logged in as a patient to access this page.</p>
    <?php endif; ?>

    <?php
    // Handle individual attribute updates here
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Update Username
    if (isset($_POST['update_username'])) {
        $new_username = $_POST['username'];
        $update_stmt = $conn->prepare("UPDATE users SET username = ? WHERE user_id = ?");
        $update_stmt->bind_param('ss', $new_username, $user_id);
        $update_result = $update_stmt->execute();

        if ($update_result) {
            echo "<p>Username updated successfully!</p>";
        } else {
            echo "Error updating username: " . $conn->error;
        }
    }

    // Update Full Name
    if (isset($_POST['update_full_name'])) {
        $new_full_name = $_POST['full_name'];
        $update_stmt = $conn->prepare("UPDATE users SET full_name = ? WHERE user_id = ?");
        $update_stmt->bind_param('ss', $new_full_name, $user_id);
        $update_result = $update_stmt->execute();

        if ($update_result) {
            echo "<p>Full Name updated successfully!</p>";
        } else {
            echo "Error updating Full Name: " . $conn->error;
        }
    }
 

    // Update Password
    if (isset($_POST['update_password'])) {
        $new_password = $_POST['password'];
        // Hash the new password (you should use a secure hashing algorithm)
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $update_stmt->bind_param('ss', $hashed_password, $user_id);
        $update_result = $update_stmt->execute();

        if ($update_result) {
            echo "<p>Password updated successfully!</p>";
        } else {
            echo "Error updating password: " . $conn->error;
        }
    }


    // Update Email
    if (isset($_POST['update_email'])) {
        $new_email = $_POST['email'];
        $update_stmt = $conn->prepare("UPDATE users SET email = ? WHERE user_id = ?");
        $update_stmt->bind_param('ss', $new_email, $user_id);
        $update_result = $update_stmt->execute();

        if ($update_result) {
            echo "<p>Email updated successfully!</p>";
        } else {
            echo "Error updating email: " . $conn->error;
        }
    }
 

    // Update Contact Number
    if (isset($_POST['update_contact_number'])) {
        $new_contact_number = $_POST['contact_number'];
        $update_stmt = $conn->prepare("UPDATE users SET contact_number = ? WHERE user_id = ?");
        $update_stmt->bind_param('ss', $new_contact_number, $user_id);
        $update_result = $update_stmt->execute();

        if ($update_result) {
            echo "<p>Contact Number updated successfully!</p>";
        } else {
            echo "Error updating contact number: " . $conn->error;
        }
    }


    // Update Specialization
    if (isset($_POST['update_allergies'])) {
        $new_specialization = $_POST['allergies'];
        $update_stmt = $conn->prepare("UPDATE patients SET allergies = ? WHERE user_id = ?");
        $update_stmt->bind_param('ss', $new_allergies, $user_id);
        $update_result = $update_stmt->execute();

        if ($update_result) {
            echo "<p>Allergies updated successfully!</p>";
        } else {
            echo "Error updating specialization: " . $conn->error;
        }
      }
  
    }
    ?>
     <?php include "footer.html";?>
</body>
</html>
