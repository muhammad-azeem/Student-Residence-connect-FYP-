<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require 'db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields (rating removed)
if (!isset($data['id'], $data['name'], $data['description'], $data['rent'], $data['address'], $data['amenities'])) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

$id = (int) $data['id'];
$name = trim($data['name']);
$description = trim($data['description']);
$rent = (float) $data['rent'];
$address = trim($data['address']);
$amenities = !empty($data['amenities']) ? implode(", ", $data['amenities']) : "";
$coordinates = isset($data['coordinates']) ? trim($data['coordinates']) : "";

// 🟢 Fetch current data from the database (rating removed)
$query = "SELECT name, description, rent, address, amenities, images, image2, image3, coordinates FROM hostels WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($old_name, $old_description, $old_rent, $old_address, $old_amenities, $old_images, $old_image2, $old_image3, $old_coordinates);
$stmt->fetch();
$stmt->close();

$old_name = trim($old_name ?? "");
$old_description = trim($old_description ?? "");
$old_rent = (float) ($old_rent ?? 0);
$old_address = trim($old_address ?? "");
$old_amenities = trim($old_amenities ?? "");
$old_images = trim($old_images ?? "");
$old_image2 = trim($old_image2 ?? "");
$old_image3 = trim($old_image3 ?? "");
$old_coordinates = trim($old_coordinates ?? "");

// Use old data if new data is empty
$images = !empty($data['images'][0]) ? $data['images'][0] : $old_images;
$image2 = !empty($data['images'][1]) ? $data['images'][1] : $old_image2;
$image3 = !empty($data['images'][2]) ? $data['images'][2] : $old_image3;
$coordinates = !empty($coordinates) ? $coordinates : $old_coordinates;

// Check if any data has changed (rating removed)
if (
    $name === $old_name &&
    $description === $old_description &&
    $rent == $old_rent &&
    $address === $old_address &&
    $amenities === $old_amenities &&
    $images === $old_images &&
    $image2 === $old_image2 &&
    $image3 === $old_image3 &&
    $coordinates === $old_coordinates
) {
    echo json_encode(["success" => false, "message" => "No changes detected"]);
    exit;
}

// Update query (rating removed)
$query = "UPDATE hostels SET name=?, description=?, rent=?, address=?, amenities=?, images=?, image2=?, image3=?, coordinates=? WHERE id=?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssdssssssi", $name, $description, $rent, $address, $amenities, $images, $image2, $image3, $coordinates, $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Hostel updated successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update hostel"]);
}

$stmt->close();
$conn->close();
?>
