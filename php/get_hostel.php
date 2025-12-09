<?php
header("Content-Type: application/json");

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student_residence";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Database connection failed"]));
}

$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;

if ($id == 0) {
    echo json_encode(["success" => false, "message" => "Invalid hostel ID"]);
    exit();
}

$sql = "SELECT id, name, description, rent, address, amenities, images, image2, image3, coordinates FROM hostels WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["success" => true, "hostel" => $result->fetch_assoc()]);
} else {
    echo json_encode(["success" => false, "message" => "Hostel not found"]);
}

$stmt->close();
$conn->close();
?>
