<?php
include 'db_connect.php';

header("Content-Type: application/json");
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

$name = $data['name'];
$description = $data['description'];
$rent = $data['rent'];
$address = $data['address'];
$amenities = implode(", ", $data['amenities']);
$coordinates = $data['coordinates'];

$images = isset($data['images'][0]) ? $data['images'][0] : "";
$image2 = isset($data['images'][1]) ? $data['images'][1] : "";
$image3 = isset($data['images'][2]) ? $data['images'][2] : "";

if (isset($data['id']) && !empty($data['id'])) {
    $id = $data['id'];
    $sql = "UPDATE hostels SET name=?, description=?, rent=?, address=?, amenities=?, images=?, image2=?, image3=?, coordinates=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssissssssi", $name, $description, $rent, $address, $amenities, $images, $image2, $image3, $coordinates, $id);
} else {
    $sql = "INSERT INTO hostels (name, description, rent, address, amenities, images, image2, image3, coordinates) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssissssss", $name, $description, $rent, $address, $amenities, $images, $image2, $image3, $coordinates);
}

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Hostel saved successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to save hostel: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
