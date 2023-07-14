<?php
session_start();

// Check if the user is logged in as a pharmacist
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Pharmacist') {
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];

    // Retrieve the pharmacist's details from the database
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

    // Prepare and execute the query to fetch pharmacist's details
    $stmt = $conn->prepare("SELECT * FROM pharmacists WHERE user_id = ?");
    $stmt->bind_param('s', $user_id);
    $stmt->execute();

    // Fetch the pharmacist's details
    $result = $stmt->get_result();
    $pharmacist = $result->fetch_assoc();

    // HTML template for pharmacist's dashboard
    $dashboard = "
    <html>
    <head>
      <title>Pharmacist Dashboard</title>
      <style>
        .dropdown {
          position: relative;
          display: inline-block;
        }
        .dropdown-content {
          display: none;
          position: absolute;
          right: 0;
          background-color: #f9f9f9;
          min-width: 160px;
          box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
          padding: 12px 16px;
          z-index: 1;
        }
        .dropdown:hover .dropdown-content {
          display: block;
        }
      </style>
    </head>
    <body>
      <h2>Welcome, $username!</h2>
      <div class='dropdown'>
        <button>Actions</button>
        <div class='dropdown-content'>
          <a href='view_pharmacist.php'>View Details</a>
          <a href='update_pharmacist.php'>Update Details</a>
          <a href='delete_pharmacist.php'>Delete Account</a>
          <a href='disable_pharmacist.php'>Disable Account</a>
          <a href='logout.php'>Logout</a>
        </div>
      </div>
      <div>
        <h3>Pharmacist Details:</h3>
        <p>Name: {$pharmacist['full_name']}</p>
        <p>Email: {$pharmacist['email']}</p>
        <p>Contact Number: {$pharmacist['contact_number']}</p>
      </div>
    </body>
    </html>
  ";

  echo $dashboard;

  // Close the database connection
  $conn->close();
} else {
  header('Location: login.html');
  exit();
}
?>
