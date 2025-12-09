<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
}

$conn = new mysqli("localhost", "root", "", "student_residence");
date_default_timezone_set('Asia/Karachi'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = $_POST["name"];
    $email    = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $signup_time = date("Y-m-d H:i:s");

    $check = $conn->query("SELECT * FROM users WHERE email='$email'");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO users (name, email, password, signup_time) VALUES ('$name', '$email', '$password', '$signup_time')");
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['user_name'] = $name; // ✅ Store name in session
        echo "<script>
            localStorage.setItem('loggedIn', 'true');
            alert('Welcome, $name!');
            window.location.href = '../hostels.html';
        </script>";
        exit();
    } else {
        $error = "Email already exists";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef2f7;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 400px;
            margin: 80px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            width: 100%;
            background-color: #2563eb;
            color: #fff;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
        }
        button:hover {
            background-color: #1e4fc7;
        }
        .form-footer {
            text-align: center;
            margin-top: 15px;
        }
        .form-footer a {
            color: #2563eb;
            text-decoration: none;
        }
        .form-footer a:hover {
            text-decoration: underline;
        }
        .error {
            color: red;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Register</h2>
    <form method="post">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Register</button>
    </form>
    <div class="form-footer">
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    </div>
</div>
</body>
</html>
