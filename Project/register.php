<?php
include 'db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$fname  = trim($_POST['fname'] ?? '');
$lname  = trim($_POST['lname'] ?? '');
$un     = trim($_POST['un'] ?? '');
$email  = trim($_POST['email'] ?? '');
$pass   = $_POST['pass'] ?? '';
$phone  = trim($_POST['phone'] ?? '');
$age    = trim($_POST['age'] ?? '');
$gender = $_POST['gender'] ?? null;

if ($un === '' || $email === '' || $pass === '') {
    header('Location: register.html?error=missing');
    exit;
}

$hash = password_hash($pass, PASSWORD_DEFAULT);

$sql = "INSERT INTO users (fname, lname, username, email, password_hash, phone, age, gender) 
        VALUES (?,?,?,?,?,?,?,?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'ssssssis', $fname, $lname, $un, $email, $hash, $phone, $age, $gender);

if (!mysqli_stmt_execute($stmt)) {
    if (mysqli_errno($conn) == 1062) {
        header('Location: register.html?error=duplicate');
        exit;
    }
    header('Location: register.html?error=server');
    exit;
}

header('Location: login.html?registered=1');
exit;
?>
