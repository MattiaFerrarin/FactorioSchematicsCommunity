
<?php
// Database connection
$host = getenv('DB_HOST') ?: 'db';
$dbname = getenv('DB_NAME') ?: 'scuola';
$dbuser = getenv('DB_USER') ?: 'studente';
$dbpass = getenv('DB_PASSWORD') ?: 'es_password';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $dbuser,
        $dbpass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // Retrieve all posts and their authors
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
    die("Database connection or query failed.");
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

    <!-- Top Navigation Bar -->
    <header class="top-navbar">

        <div class="top-left">

            <button id="hamburger-btn"
                    class="hamburger-btn"
                    aria-label="Toggle Menu"
                    aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <a href="index.php" class="logo">
                Factorio Schematics
            </a>

        </div>

        <nav class="top-links">
            <a href="index.php">Home</a>
            <a href="add.php">Add Post</a>
            <a href="remove.php">Remove Post</a>
        </nav>

        <div class="top-right">
            <a href="#" class="user-icon" aria-label="User Settings">
                <svg width="24" height="24"
                     viewBox="0 0 24 24"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </a>
        </div>

    </header>

    <!-- Sidebar -->
    <nav id="sidebar" class="sidebar">

        <ul class="sidebar-menu">
            <li><a href="index.php">Home</a></li>
            <li><a href="index.php">Blueprints</a></li>
            <li><a href="index.php">Community</a></li>
            <li><a href="#">About</a></li>
        </ul>

        <div class="sidebar-bottom">
            <a href="#">Settings</a>
        </div>

    </nav>

    <!-- Main Content -->
    <main class="main-content">

        <h1>Factorio Schematics Community</h1>

        <p>
            Welcome! Explore the posts and creations
            shared by the community.
        </p>

        <!-- Action Buttons -->
        <div class="page-actions">

            <a href="add.php" class="action-btn add-btn">
                + Add Post
            </a>

            <a href="remove.php" class="action-btn delete-btn">
                - Remove Post
            </a>

        </div>

        <h2>Community Posts</h2>

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
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($posts as $post): ?>

                            <tr>

                                <td>
                                    <?= htmlspecialchars((string)$post['post_id']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($post['title']) ?>
                                </td>

                                <td>
                                    <?= nl2br(htmlspecialchars($post['body'])) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($post['author_name']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($post['creation_timestamp']) ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php else: ?>

            <p class="empty-message">
                No posts have been published yet.
            </p>

        <?php endif; ?>

    </main>

    <script>
        const hamburgerBtn =
            document.getElementById('hamburger-btn');

        const sidebar =
            document.getElementById('sidebar');

        hamburgerBtn.addEventListener('click', () => {
            sidebar.classList.toggle('active');

            hamburgerBtn.setAttribute(
                'aria-expanded',
                sidebar.classList.contains('active')
            );
        });
    </script>

</body>
</html>
