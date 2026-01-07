<?php
session_start();
date_default_timezone_set('Europe/Bucharest');
// Redirect to login if not authenticated
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        body { background: #0f172a; color: white; text-align: center; padding-top: 50px; font-family: sans-serif; }
        .btn-logout { color: #f87171; text-decoration: none; border: 1px solid #f87171; padding: 10px 20px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🔒 Access Granted</h1>
    <p>Welcome to the protected members area.</p>
    <br><br>
	<p><?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit;
}
?>                       checks if the user is logged in
	</p>
    <a href="logout.php" class="btn-logout">Logout</a>
</body>
</html>