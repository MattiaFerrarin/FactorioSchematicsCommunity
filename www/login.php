<?php
require_once 'auth.php';

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password';
    } else {
        try {
            $pdo = getDB();
            $stmt = $pdo->prepare('
                SELECT username, name, password
                FROM Users
                WHERE username = :username
            ');
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['username'] = $user['username'];
                $_SESSION['name'] = $user['name'];

                header('Location: index.php');
                exit;
            }

            $error = 'Invalid username or password';
        } catch (PDOException $e) {
            $error = 'Login failed due to a database error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - Factorio Schematics</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="main-content auth-container">
        <h1>Log In</h1>

        <?php if ($error !== ''): ?>
            <div class="auth-errors">
                <p><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>

        <form method="post" action="login.php" class="auth-form">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" class="action-btn add-btn">Log In</button>
        </form>

        <p class="auth-switch">
            Need an account? <a href="register.php">Register</a>
        </p>
    </main>
</body>
</html>