<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hostel_id = $_POST['hostel_id'];
    $username = $_POST['username'];
    $comment = $_POST['comment'];

    if (!empty($hostel_id) && !empty($username) && !empty($comment)) {
        $stmt = $conn->prepare("INSERT INTO comments (hostel_id, username, comment, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iss", $hostel_id, $username, $comment);

        if ($stmt->execute()) {
            $comment_id = $stmt->insert_id;

            // Run sentiment analysis in background
            $pythonPath = 'C:\Users\Dell\AppData\Local\Programs\Python\Python313\python.exe';
            $scriptPath = 'C:\xampp\htdocs\project\python\calculate_sentiment.py';
            $command = 'start /B ' . $pythonPath . ' ' . $scriptPath . ' ' . $comment_id;
            pclose(popen("cmd /c $command", "r"));

            echo json_encode(["success" => true, "message" => "Your Comment added"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error adding comment"]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "All fields are required"]);
    }
}

$conn->close();
?>
