
<?php

$host = getenv('DB_HOST') ?: 'db';
$dbname = getenv('DB_NAME') ?: 'scuola';
$dbuser = getenv('DB_USER') ?: 'studente';
$dbpass = getenv('DB_PASSWORD') ?: 'es_password';

$error = '';

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

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $post_id = filter_input(
            INPUT_POST,
            'post_id',
            FILTER_VALIDATE_INT
        );

        if ($post_id === false || $post_id === null || $post_id <= 0) {

            $error = "Please enter a valid post ID.";

        } else {

            $stmt = $pdo->prepare("
                DELETE FROM Posts
                WHERE post_id = :id
            ");

            $stmt->execute([
                ':id' => $post_id
            ]);

            if ($stmt->rowCount() > 0) {

                header("Location: index.php");
                exit;

            } else {

                $error = "No post with that ID was found.";

            }
        }
    }

} catch (PDOException $e) {

    $error = "An error occurred while deleting the post. It may have associated comments.";

}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remove Post - Factorio Schematics</title>
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

        <h1>Remove a Community Post</h1>

        <p>
            Enter the ID of the post you want to remove.
        </p>

        <?php if ($error !== ''): ?>

            <p class="error-message">
                <?= htmlspecialchars($error) ?>
            </p>

        <?php endif; ?>

        <form
            method="POST"
            action="remove.php"
            class="post-form"
            onsubmit="return confirm(
                'Are you sure you want to permanently delete this post?'
            );"
        >

            <label for="post_id">Post ID</label>

            <input
                type="number"
                id="post_id"
                name="post_id"
                min="1"
                required
            >

            <button type="submit" class="action-btn delete-btn">
                Delete Post
            </button>

        </form>

        <a href="index.php" class="back-link">
            ← Back to Home
        </a>

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
