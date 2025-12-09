<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student_residence";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

// Fetch hostels from the database
$sql = "SELECT id, name, description, rent, address, amenities, images, image2, image3 FROM hostels";
$result = $conn->query($sql);

$hostels = [];
while ($row = $result->fetch_assoc()) {
    $images = array_filter([$row['images'], $row['image2'], $row['image3']]);
    $amenities = !empty($row['amenities']) ? array_map('trim', explode(',', $row['amenities'])) : [];

    $hostels[] = [
        "id" => (int) $row['id'],
        "name" => $row['name'],
        "description" => $row['description'],
        "rent" => (int) $row['rent'],
        "address" => $row['address'] ?? "N/A",
        "amenities" => $amenities,
        "images" => array_values($images)
    ];
}

echo json_encode(["success" => true, "hostels" => $hostels], JSON_UNESCAPED_SLASHES);

$conn->close();
?>
