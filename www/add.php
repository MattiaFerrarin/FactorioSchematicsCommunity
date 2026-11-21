<?php
require_once 'auth.php';
$user = currentUser();

if (!$user) {
    header('Location: login.php');
    exit;
}

$errors = [];
$title = '';
$body = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['body'] ?? '');

    if ($title === '') {
        $errors[] = 'Title is required.';
    } elseif (mb_strlen($title) > 255) {
        $errors[] = 'Title must be 255 characters or fewer.';
    }

    if ($body === '') {
        $errors[] = 'Post content is required.';
    }

    if (empty($errors)) {
        try {
            $pdo = getDB();
            $stmt = $pdo->prepare('
                INSERT INTO Posts (creation_timestamp, title, body, Users_username)
                VALUES (NOW(), :title, :body, :username)
            ');
            $stmt->execute([
                ':title' => $title,
                ':body' => $body,
                ':username' => $user['username'],
            ]);

            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Failed to publish post. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Post - Factorio Schematics Community</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php require_once 'layout_top.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <h1>Create Post</h1>
        <p>
            Posting as <strong><?= htmlspecialchars($user['name']) ?></strong> (@<?= htmlspecialchars($user['username']) ?>).
        </p>

        <?php if (!empty($errors)): ?>
            <div class="auth-errors">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="add.php" class="auth-form post-form">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" maxlength="255" value="<?= htmlspecialchars($title) ?>" required>

            <label for="body">Content</label>
            <textarea id="body" name="body" rows="8" required><?= htmlspecialchars($body) ?></textarea>

            <div class="page-actions">
                <button type="submit" class="action-btn add-btn">Publish Post</button>
                <a href="index.php" class="action-btn">Cancel</a>
            </div>
        </form>
    </main>

    <?php require_once 'layout_bottom.php'; ?>
</body>
</html>