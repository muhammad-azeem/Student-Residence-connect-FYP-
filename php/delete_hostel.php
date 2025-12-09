

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

$data = json_decode(file_get_contents("php://input"), true);
$id = isset($data["id"]) ? intval($data["id"]) : 0;

if ($id == 0) {
    echo json_encode(["success" => false, "message" => "Invalid hostel ID"]);
    exit();
}

$sql = "DELETE FROM hostels WHERE id = $id";
if ($conn->query($sql) === TRUE) {
    echo json_encode(["success" => true, "message" => "Hostel deleted successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Error: " . $conn->error]);
}

$conn->close();
?>
