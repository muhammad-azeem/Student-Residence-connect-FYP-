<?php
header("Content-Type: application/json");
include 'db_connect.php';  // Make sure this includes the correct database connection

$data = json_decode(file_get_contents("php://input"), true);

// Retrieve credentials from POST request
$email = isset($data["email"]) ? $data["email"] : '';
$password = isset($data["password"]) ? $data["password"] : '';

// Check if both email and password are provided
if (empty($email) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Email and password are required"]);
    exit();
}

// Prepare SQL query to check the admin credentials
$sql = "SELECT * FROM admins WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Check if a user with that email exists
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    // Assuming the password is stored in plain text (for simplicity, otherwise use password_hash and password_verify)
    if ($password === $row['password']) {
        // Login successful
        echo json_encode(["success" => true, "message" => "Login successful"]);
    } else {
        // Incorrect password
        echo json_encode(["success" => false, "message" => "Invalid password"]);
    }
} else {
    // No user found with that email
    echo json_encode(["success" => false, "message" => "Email not found"]);
}

$stmt->close();
$conn->close();
?>
