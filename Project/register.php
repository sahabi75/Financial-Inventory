<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $fname = htmlspecialchars(trim($_POST["fname"]));
    $lname = htmlspecialchars(trim($_POST["lname"]));
    $username = htmlspecialchars(trim($_POST["un"]));
    $email = htmlspecialchars(trim($_POST["email"]));
    $password = htmlspecialchars(trim($_POST["pass"]));
    $phone = htmlspecialchars(trim($_POST["phone"]));
    $gender = htmlspecialchars(trim($_POST["gender"]));

    
    $host = "localhost";
    $dbname = "register_db";  
    $user = "root";
    $pass = "";

    
    $conn = new mysqli($host, $user, $pass, $dbname);

    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    
    $stmt = $conn->prepare("INSERT INTO users (fname, lname, username, email, password, phone, gender) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $fname, $lname, $username, $email, $hashedPassword, $phone, $gender);

    if ($stmt->execute()) {
        echo "<h3>Registration successful!</h3>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid Request";
}
?>
