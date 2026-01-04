<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "garderobe_db";

$conn = new mysqli($host, $user, $pass, $dbname);
$conn->set_charset("utf8mb4");

$email = "admin@gmail.com";
$testPassword = "admin123";

$stmt = $conn->prepare("SELECT id, username, email, password_hash FROM admin_accounts WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($id, $username, $dbEmail, $hash);

if ($stmt->fetch()) {
    echo "<p>Email in DB: " . htmlspecialchars($dbEmail) . "</p>";
    echo "<p>Hash: " . htmlspecialchars($hash) . "</p>";

    if (password_verify($testPassword, $hash)) {
        echo "<h3>password_verify: TRUE — password matches</h3>";
    } else {
        echo "<h3>password_verify: FALSE — password does NOT match</h3>";
    }
} else {
    echo "<h3>No admin row found for that email.</h3>";
}
