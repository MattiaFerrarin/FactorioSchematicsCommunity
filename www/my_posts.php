<?php
require_once 'auth.php';
$user = currentUser();

if (!$user) {
    header('Location: login.php');
    exit;
}

$error = '';

try {
    $pdo = getDB();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
        $postId = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);

        if ($postId) {
            $deleteStmt = $pdo->prepare("
                DELETE FROM Posts
                WHERE post_id = :post_id
                  AND Users_username = :username
            ");
            $deleteStmt->execute([
                ':post_id' => $postId,
                ':username' => $user['username'],
            ]);

            header('Location: my_posts.php');
            exit;
        } else {
            $error = 'Invalid post ID.';
        }
    }

    $stmt = $pdo->prepare("
        SELECT
            Posts.post_id,
            Posts.title,
            Posts.body,
            Posts.creation_timestamp,
            Posts.Users_username,
            Users.name AS author_name
        FROM Posts
        INNER JOIN Users
            ON Posts.Users_username = Users.username
        WHERE Posts.Users_username = :username
        ORDER BY Posts.creation_timestamp DESC
    ");
    $stmt->execute([':username' => $user['username']]);
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database connection or query failed.\n\n");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Posts - Factorio Schematics Community</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php require_once 'layout_top.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <h1>My Posts</h1>
        <p>
            Viewing posts created by <?= htmlspecialchars($user['name']) ?> (@<?= htmlspecialchars($user['username']) ?>).
        </p>

        <?php if ($error !== ''): ?>
            <div class="auth-errors">
                <p><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="page-actions">
            <a href="add.php" class="action-btn add-btn">+ Create Post</a>
        </div>

        <h2>Your Published Posts</h2>

        <?php if (count($posts) > 0): ?>
            <div class="table-container">
                <table class="posts-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Content</th>
                            <th>Author</th>
                            <th>Creation Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                            <?php $postId = (int)$post['post_id']; ?>
                            <tr>
                                <td><?= htmlspecialchars((string)$postId) ?></td>
                                <td><?= htmlspecialchars($post['title']) ?></td>
                                <td><?= nl2br(htmlspecialchars($post['body'])) ?></td>
                                <td><?= htmlspecialchars($post['author_name']) ?></td>
                                <td><?= htmlspecialchars($post['creation_timestamp']) ?></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="edit.php?id=<?= $postId ?>" class="action-btn">Modify</a>
                                        <form method="post" action="my_posts.php" class="inline-form" onsubmit="return confirm('Delete this post?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="post_id" value="<?= $postId ?>">
                                            <button type="submit" class="action-btn delete-btn">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="empty-message">
                You haven't published any posts yet.
            </p>
        <?php endif; ?>
    </main>

    <?php require_once 'layout_bottom.php'; ?>
</body>
</html>