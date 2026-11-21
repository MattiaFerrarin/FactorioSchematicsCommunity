<?php
require_once 'auth.php';
$user = currentUser();

if (!$user) {
    header('Location: login.php');
    exit;
}

$errors = [];
$success = '';

try {
    $pdo = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $section = $_POST['section'] ?? '';

        if ($section === 'profile') {
            $newDescription = trim($_POST['profile_description'] ?? '');

            $stmt = $pdo->prepare('
                UPDATE Users
                SET profile_description = :profile_description
                WHERE username = :username
            ');
            $stmt->execute([
                ':profile_description' => $newDescription !== '' ? $newDescription : null,
                ':username' => $user['username'],
            ]);

            $success = 'Profile description updated.';
        } elseif ($section === 'sensitive') {
            $newName = trim($_POST['name'] ?? '');
            $newUsername = trim($_POST['username'] ?? '');
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if ($newName === '' || mb_strlen($newName) > 63) {
                $errors[] = 'Display name is required (max 63 characters).';
            }

            if ($newUsername === '' || !preg_match('/^[a-zA-Z0-9_-]{3,63}$/', $newUsername)) {
                $errors[] = 'Username must be 3–63 characters (letters, numbers, _, -).';
            }

            if ($currentPassword === '') {
                $errors[] = 'Current password is required to save sensitive account changes.';
            }

            $changingPassword = ($newPassword !== '' || $confirmPassword !== '');
            if ($changingPassword) {
                if (strlen($newPassword) < 8) {
                    $errors[] = 'New password must be at least 8 characters.';
                }
                if ($newPassword !== $confirmPassword) {
                    $errors[] = 'New passwords do not match.';
                }
            }

            if (empty($errors)) {
                $fetchUser = $pdo->prepare('
                    SELECT username, password
                    FROM Users
                    WHERE username = :username
                ');
                $fetchUser->execute([':username' => $user['username']]);
                $dbUser = $fetchUser->fetch();

                if (!$dbUser || !password_verify($currentPassword, $dbUser['password'])) {
                    $errors[] = 'Current password is incorrect.';
                }
            }

            if (empty($errors) && $newUsername !== $user['username']) {
                $takenStmt = $pdo->prepare('SELECT username FROM Users WHERE username = :new_username');
                $takenStmt->execute([':new_username' => $newUsername]);
                if ($takenStmt->fetch()) {
                    $errors[] = 'That username is already taken.';
                }
            }

            if (empty($errors)) {
                $pdo->beginTransaction();

                try {
                    if ($changingPassword) {
                        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                        $updateStmt = $pdo->prepare('
                            UPDATE Users
                            SET name = :name,
                                username = :new_username,
                                password = :password
                            WHERE username = :current_username
                        ');
                        $updateStmt->execute([
                            ':name' => $newName,
                            ':new_username' => $newUsername,
                            ':password' => $hash,
                            ':current_username' => $user['username'],
                        ]);
                    } else {
                        $updateStmt = $pdo->prepare('
                            UPDATE Users
                            SET name = :name,
                                username = :new_username
                            WHERE username = :current_username
                        ');
                        $updateStmt->execute([
                            ':name' => $newName,
                            ':new_username' => $newUsername,
                            ':current_username' => $user['username'],
                        ]);
                    }

                    if ($newUsername !== $user['username']) {
                        $postsStmt = $pdo->prepare('
                            UPDATE Posts
                            SET Users_username = :new_username
                            WHERE Users_username = :current_username
                        ');
                        $postsStmt->execute([
                            ':new_username' => $newUsername,
                            ':current_username' => $user['username'],
                        ]);
                    }

                    $pdo->commit();

                    $_SESSION['username'] = $newUsername;
                    $_SESSION['name'] = $newName;
                    $success = 'Account settings updated.';
                } catch (PDOException $txError) {
                    $pdo->rollBack();
                    $errors[] = 'Could not update account settings.';
                }
            }
        }
    }

    $stmt = $pdo->prepare('
        SELECT username, name, profile_description, profile_picture
        FROM Users
        WHERE username = :username
    ');
    $stmt->execute([':username' => $_SESSION['username']]);
    $profile = $stmt->fetch();

    if (!$profile) {
        header('Location: logout.php');
        exit;
    }

    $user = currentUser();
    $displayName = $_POST['name'] ?? $profile['name'];
    $displayUsername = $_POST['username'] ?? $profile['username'];
    $displayDescription = (($_POST['section'] ?? '') === 'profile')
        ? ($_POST['profile_description'] ?? '')
        : ($profile['profile_description'] ?? '');
} catch (PDOException $e) {
    die("Database connection or query failed.\n\n");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - Factorio Schematics Community</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php require_once 'layout_top.php'; ?>

    <!-- Main Content -->
    <main class="main-content settings-page">
        <h1>Account Settings</h1>
        <p>
            Manage your profile details and sensitive account credentials.
        </p>

        <?php if (!empty($errors)): ?>
            <div class="auth-errors">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
            <div class="settings-success">
                <p><?= htmlspecialchars($success) ?></p>
            </div>
        <?php endif; ?>

        <!-- Main Section: Profile Picture & Description -->
        <section class="settings-section" aria-labelledby="profile-section-title">
            <h2 id="profile-section-title">Profile</h2>

            <div class="profile-overview">
                <div class="profile-avatar-lg" aria-hidden="true">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <div class="profile-identity">
                    <div class="profile-name-lg"><?= htmlspecialchars($profile['name']) ?></div>
                    <div class="profile-handle-lg">@<?= htmlspecialchars($profile['username']) ?></div>
                </div>
            </div>

            <form method="post" action="account_settings.php" class="auth-form settings-form">
                <input type="hidden" name="section" value="profile">

                <label for="profile-description">Profile Description</label>
                <textarea id="profile-description" name="profile_description" rows="5" placeholder="Write something about yourself and your builds..."><?= htmlspecialchars((string)$displayDescription) ?></textarea>

                <div class="page-actions">
                    <button type="submit" class="action-btn add-btn">Save Profile</button>
                </div>
            </form>
        </section>

        <!-- Sensitive Section: Name, Username, Password -->
        <section class="settings-section sensitive-section" aria-labelledby="sensitive-section-title">
            <h2 id="sensitive-section-title">Sensitive Account Settings</h2>
            <p class="sensitive-note">
                Changing your username or password affects how you sign in. Enter your current password to confirm changes.
            </p>

            <form method="post" action="account_settings.php" class="auth-form settings-form">
                <input type="hidden" name="section" value="sensitive">

                <label for="name">Display Name</label>
                <input type="text" id="name" name="name" maxlength="63" value="<?= htmlspecialchars((string)$displayName) ?>" required>

                <label for="username">Username</label>
                <input type="text" id="username" name="username" maxlength="63" value="<?= htmlspecialchars((string)$displayUsername) ?>" required>

                <label for="current-password">Current Password</label>
                <input type="password" id="current-password" name="current_password" required>

                <label for="new-password">New Password (leave blank to keep current)</label>
                <input type="password" id="new-password" name="new_password">

                <label for="confirm-password">Confirm New Password</label>
                <input type="password" id="confirm-password" name="confirm_password">

                <div class="page-actions">
                    <button type="submit" class="action-btn add-btn">Update Credentials</button>
                </div>
            </form>
        </section>
    </main>

    <?php require_once 'layout_bottom.php'; ?>
</body>
</html>