<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "garderobe_db";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("DB error: " . $conn->connect_error);
}

$email = trim($_POST['email'] ?? "");
$password = $_POST['password'] ?? "";

if ($email === "" || $password === "") {
    exit("<h3>Missing fields</h3><a href='../admin-login.html'>Back</a>");
}

$stmt = $conn->prepare("SELECT id, name, password_hash, role FROM users WHERE email = ? AND role='admin'");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($id, $name, $hash, $role);

if ($stmt->fetch()) {
    if (password_verify($password, $hash)) {
        // SUCCESS — admin only
        $_SESSION["admin_id"] = $id;
        $_SESSION["admin_name"] = $name;

        header("Location: ../admin_dashboard.html");
        exit;
    }
}

echo "<h3>Invalid admin credentials</h3>
      <a href='../admin-login.html'>Try Again</a>";

$stmt->close();
$conn->close();
?>
