<?php
// backend/admin_auth.php
session_start();

// Direct DB connection just for admin login
$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "garderobe_db";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    echo "<script>
            alert('Database connection failed.');
            window.location.href = '../admin-login.html';
          </script>";
    exit;
}
$conn->set_charset("utf8mb4");

// Read form values
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    echo "<script>
            alert('Email and password are required.');
            window.location.href = '../admin-login.html';
          </script>";
    exit;
}

// Look up admin in admin_accounts
$stmt = $conn->prepare("SELECT id, username, email, password_hash FROM admin_accounts WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {
    $stmt->bind_result($id, $username, $dbEmail, $hash);
    $stmt->fetch();

    if (password_verify($password, $hash)) {
        // SUCCESS
        session_regenerate_id(true);
        $_SESSION['admin_id']   = $id;
        $_SESSION['admin_name'] = $username;
        $_SESSION['is_admin']   = true;

        $stmt->close();
        $conn->close();

        // ✅ Correct redirect: admin_dashboard.php (with "sh")
        header("Location: ../admin_dashboard.php");
        exit;
    }
}

// If we reach here: wrong email or password
$stmt->close();
$conn->close();

echo "<script>
        alert('Invalid admin credentials');
        window.location.href = '../admin-login.html';
      </script>";
exit;
