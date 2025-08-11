<?php

require __DIR__ . '/db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: login.html');
  exit;
}

$un   = trim($_POST['un'] ?? '');
$pass = $_POST['pass'] ?? '';

if ($un === '' || $pass === '') {
  header('Location: login.html?error=1');
  exit;
}

$sql  = "SELECT id, username, email, password_hash, fname, lname, gender
         FROM users
         WHERE username = ? OR email = ?
         LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'ss', $un, $un);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$user || !password_verify($pass, $user['password_hash'])) {
  header('Location: login.html?error=1');
  exit;
}


$_SESSION['user_id']  = (int)$user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['email']    = $user['email'];
$_SESSION['fname']    = $user['fname'] ?? ''; 
$_SESSION['lname']    = $user['lname'] ?? '';
$_SESSION['gender']   = $user['gender'] ?? '';

header('Location: dashboard.php');
exit;
