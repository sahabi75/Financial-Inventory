<?php

if (session_status() === PHP_SESSION_NONE) session_start();

$host = 'localhost'; 
$user = 'root';
$pass = '';
$db   = 'financial_inventory';

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) { die('DB ERROR: ' . mysqli_connect_error()); }
mysqli_set_charset($conn, 'utf8mb4');

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function bdt($n){ return '৳' . number_format((float)$n, 0); } 
function set_flash($m,$t='success'){ $_SESSION['_flash']=['m'=>$m,'t'=>$t]; }
function get_flash(){ if(!empty($_SESSION['_flash'])){ $f=$_SESSION['_flash']; unset($_SESSION['_flash']); return $f;} return null; }
