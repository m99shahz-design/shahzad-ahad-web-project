<?php
// backend/auth_register.php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "garderobe_db";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

$name     = trim($_POST['name'] ?? ''); // username
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';
$notRobot = isset($_POST['not_robot']) ? $_POST['not_robot'] : null;

$errors = [];

if ($name === '' || $email === '' || $password === '' || $confirm === '') {
    $errors[] = "All required fields must be filled.";
}

if (strtolower($name) === 'admin') {
    $errors[] = 'Username "admin" is not allowed.';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
}

if ($password !== $confirm) {
    $errors[] = "Passwords do not match.";
}

if (!$notRobot) {
    $errors[] = 'Please confirm "I\'m not a robot".';
}


if ($name === '' || $email === '' || $password === '' || $confirm === '') {
    $errors[] = "All fields are required.";
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
}
if ($password !== $confirm) {
    $errors[] = "Passwords do not match.";
}

if (empty($errors)) {
    // check duplicate
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "Email already registered.";
    }
    $stmt->close();
}

if (empty($errors)) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'user')");
    $stmt->bind_param("sss", $name, $email, $hash);
    if ($stmt->execute()) {
        $success = true;
    } else {
        $errors[] = "Error saving user.";
    }
    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Register Result</title>
</head>
<body>
<?php if (!empty($errors)): ?>
  <h3>Registration failed:</h3>
  <ul>
    <?php foreach ($errors as $e): ?>
      <li><?php echo htmlspecialchars($e); ?></li>
    <?php endforeach; ?>
  </ul>
  <p><a href="../register.html">Back to register</a></p>
<?php else: ?>
  <h3>Registration successful!</h3>
  <p><a href="../login.html">Go to login</a></p>
<?php endif; ?>
</body>
</html>
