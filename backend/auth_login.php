<?php
// backend/auth_login.php
session_start();

// Database connection
$host = "localhost";
$user = "root";   // XAMPP default
$pass = "";       // XAMPP default
$dbname = "garderobe_db";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Read POST data
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$errors = [];

// Basic validation
if ($email === '' || $password === '') {
    $errors[] = "Email and password are required.";
}

// If no errors, try finding user
if (empty($errors)) {
    $stmt = $conn->prepare("SELECT id, name, password_hash, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($id, $name, $hash, $role);

    if ($stmt->fetch()) {
        // Verify password
        if (password_verify($password, $hash)) {
            // Save login data
            $_SESSION['user_id']   = $id;
            $_SESSION['user_name'] = $name;
            $_SESSION['user_role'] = $role;

            // ✅ NEW: Store login cookie (7 days)
            // NOTE: This is just for your requirement (cookie data storage)
            // Cookie will be available in JS as well (not HttpOnly).
            setcookie(
                "user_login",
                json_encode([
                    "id"   => $id,
                    "name" => $name,
                    "role" => $role
                ]),
                time() + (7 * 24 * 60 * 60),
                "/"
            );

            // Admin redirect
            if ($role === "admin") {
                header("Location: ../admin.html");
                exit;
            }

            // Normal user redirect
            header("Location: ../index.html");
            exit;

        } else {
            $errors[] = "Invalid email or password.";
        }
    } else {
        $errors[] = "Invalid email or password.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login Result</title>
</head>
<body>

<?php if (!empty($errors)): ?>
  <h3>Login failed:</h3>
  <ul>
    <?php foreach ($errors as $e): ?>
      <li><?php echo htmlspecialchars($e); ?></li>
    <?php endforeach; ?>
  </ul>
  <p><a href="../login.html">Try again</a></p>
<?php endif; ?>

</body>
</html>
