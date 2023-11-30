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
   
    $requestData = json_decode(file_get_contents('php://input'), true);

    $user_id = $requestData['user_id'];
    $api_key = $requestData['api_key'];
    $drug_id = $requestData['drug_id'];

  
    if (validateUserCredentials($user_id, $api_key, $conn)) {
       
        if (productExists($drug_id, $conn)) {
           
            if (subscribeUserToProduct($user_id, $drug_id, $conn)) {
                echo json_encode(['success' => 'Subscription successful']);
            } else {
                echo json_encode(['error' => 'Failed to subscribe user']);
            }
        } else {
            echo json_encode(['error' => 'Invalid product']);
        }
    } else {
        echo json_encode(['error' => 'Invalid credentials']);
    }
} else {
    echo json_encode(['error' => 'Method Not Allowed']);
}

function validateUserCredentials($user_id, $api_key, $conn) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? AND api_key = ?");
    $stmt->bind_param('ss', $user_id, $api_key);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows > 0;
}

function productExists($drug_id, $conn) {
    $stmt = $conn->prepare("SELECT * FROM drugs WHERE drug_id = ?");
    $stmt->bind_param('s', $drug_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows > 0;
}

function isUserSubscribed($user_id, $drug_id, $conn) {
    $stmt = $conn->prepare("SELECT * FROM user_subscriptions WHERE user_id = ? AND drug_id = ?");
    $stmt->bind_param('ss', $user_id, $drug_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->num_rows > 0;
}

function subscribeUserToProduct($user_id, $drug_id, $conn) {
    if (isUserSubscribed($user_id, $drug_id, $conn)) {
        return true; 
    }

    $stmt = $conn->prepare("INSERT INTO user_subscriptions (user_id, drug_id) VALUES (?, ?)");
    $stmt->bind_param('ss', $user_id, $drug_id);
    $stmt->execute();

    return $stmt->affected_rows > 0;
}
?>
