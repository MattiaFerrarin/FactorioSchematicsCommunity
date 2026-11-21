<?php
require_once 'auth.php';

$errors = [];
$username = '';
$name = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($username === '' || !preg_match('/^[a-zA-Z0-9_-]{3,30}$/', $username)) {
        $errors[] = 'Username must be 3–30 characters (letters, numbers, _, -)';
    }

    if ($name === '' || mb_strlen($name) > 63) {
        $errors[] = 'Display name is required (max 63 characters)';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }

    if (empty($errors)) {
        try {
            $pdo = getDB();

            $check = $pdo->prepare('SELECT username FROM Users WHERE username = :username');
            $check->execute([':username' => $username]);

            if ($check->fetch()) {
                $errors[] = 'That username is already taken';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $insert = $pdo->prepare('
                    INSERT INTO Users (username, name, password, creation_date)
                    VALUES (:username, :name, :password, :creation_date)
                ');
                $insert->execute([
                    ':username' => $username,
                    ':name' => $name,
                    ':password' => $hash,
                    ':creation_date' => gmdate('Y-m-d h:i:s', time()),
                ]);

                session_regenerate_id(true);
                $_SESSION['username'] = $username;
                $_SESSION['name'] = $name;

                header('Location: index.php');
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = 'Could not complete registration. Please try again';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Factorio Schematics</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="main-content auth-container">
        <h1>Create Account</h1>

        <?php if (!empty($errors)): ?>
            <div class="auth-errors">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="register.php" class="auth-form">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required>

            <label for="name">Display Name</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit" class="action-btn add-btn">Register</button>
        </form>

        <p class="auth-switch">
            Already have an account? <a href="login.php">Log in</a>
        </p>
    </main>
</body>
</html>