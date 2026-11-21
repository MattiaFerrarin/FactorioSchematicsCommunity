<?php
$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$dbuser = getenv('DB_USER');
$dbpass = getenv('DB_PASSWORD');

try {
    require_once 'auth.php';
    $user = currentUser();
    $pdo = getDB();
    $stmt = $pdo->query("
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
        ORDER BY Posts.creation_timestamp DESC
    ");
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
    <title>Factorio Schematics Community</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php require_once 'layout_top.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <h1>Factorio Schematics Community</h1>
        <p>
            Welcome! Explore the posts and creations
            shared by the community.
        </p>

        <!-- Action Buttons -->
        <div class="page-actions">
            <a href="add.php" class="action-btn add-btn">+ Create new Post</a>
        </div>

        <h2>Community Posts</h2>

        <?php if (count($posts) > 0){
            echo("
            <div class=\"table-container\">
                <table class=\"posts-table\">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Content</th>
                            <th>Author</th>
                            <th>Creation Date</th>
                        </tr>
                    </thead>
                    <tbody>
            ");
            foreach ($posts as $post){
                echo("
                <tr>
                    <td>".htmlspecialchars((string)$post['post_id'])."</td>
                    <td>".htmlspecialchars($post['title'])."</td>
                    <td>".nl2br(htmlspecialchars($post['body']))."</td>
                    <td>".htmlspecialchars($post['author_name'])."</td>
                    <td>".htmlspecialchars($post['creation_timestamp'])."</td>
                </tr>
                ");
            }
            echo("
            </tbody>
            </table>
            </div>
            ");
        } else {
            echo("
            <p class=\"empty-message\">
                No posts have been published yet
            </p>
            ");
        } ?>
    </main>

    <?php require_once 'layout_bottom.php'; ?>
</body>
</html>
