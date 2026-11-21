<?php
require_once 'auth.php';
$user = currentUser();

if (!$user) {
    header('Location: login.php');
    exit;
}

$errors = [];
$postId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['body'] ?? '');

    if (!$postId) {
        $errors[] = 'Invalid post ID.';
    }

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
                UPDATE Posts
                SET title = :title, body = :body
                WHERE post_id = :post_id
                  AND Users_username = :username
            ');
            $stmt->execute([
                ':title' => $title,
                ':body' => $body,
                ':post_id' => $postId,
                ':username' => $user['username'],
            ]);

            if ($stmt->rowCount() === 0) {
                $checkStmt = $pdo->prepare('
                    SELECT post_id
                    FROM Posts
                    WHERE post_id = :post_id
                      AND Users_username = :username
                ');
                $checkStmt->execute([
                    ':post_id' => $postId,
                    ':username' => $user['username'],
                ]);

                if (!$checkStmt->fetch()) {
                    $errors[] = 'Post not found or you do not have permission to edit it.';
                } else {
                    header('Location: my_posts.php');
                    exit;
                }
            } else {
                header('Location: my_posts.php');
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = 'Failed to update post. Please try again.';
        }
    }
} else {
    if (!$postId) {
        header('Location: my_posts.php');
        exit;
    }

    try {
        $pdo = getDB();
        $stmt = $pdo->prepare('
            SELECT post_id, title, body
            FROM Posts
            WHERE post_id = :post_id
              AND Users_username = :username
        ');
        $stmt->execute([
            ':post_id' => $postId,
            ':username' => $user['username'],
        ]);
        $post = $stmt->fetch();

        if (!$post) {
            header('Location: my_posts.php');
            exit;
        }

        $title = $post['title'];
        $body = $post['body'];
    } catch (PDOException $e) {
        die("Database connection or query failed.\n\n");
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post - Factorio Schematics Community</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php require_once 'layout_top.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <h1>Edit Post #<?= htmlspecialchars((string)$postId) ?></h1>
        <p>
            Editing as <strong><?= htmlspecialchars($user['name']) ?></strong> (@<?= htmlspecialchars($user['username']) ?>).
        </p>

        <?php if (!empty($errors)): ?>
            <div class="auth-errors">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="edit.php?id=<?= htmlspecialchars((string)$postId) ?>" class="auth-form post-form">
            <input type="hidden" name="post_id" value="<?= htmlspecialchars((string)$postId) ?>">

            <label for="title">Title</label>
            <input type="text" id="title" name="title" maxlength="255" value="<?= htmlspecialchars($title ?? '') ?>" required>

            <label for="body">Schematic / Content</label>
            <textarea id="body" name="body" rows="8" required><?= htmlspecialchars($body ?? '') ?></textarea>

            <div class="page-actions">
                <button type="submit" class="action-btn add-btn">Save Changes</button>
                <a href="my_posts.php" class="action-btn">Cancel</a>
            </div>
        </form>
    </main>

    <?php require_once 'layout_bottom.php'; ?>
</body>
</html>