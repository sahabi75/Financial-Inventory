<?php
include 'db.php';
session_unset();
session_destroy();
header('Location: login.html');
exit;
