
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

    // Retrieve existing users for the author selection
    $users = $pdo->query("
        SELECT username, name
        FROM Users
        ORDER BY name
    ")->fetchAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $title = trim($_POST['title'] ?? '');
        $body = trim($_POST['body'] ?? '');
        $username = trim($_POST['username'] ?? '');

        if ($title === '' || $body === '' || $username === '') {

            $error = "Please fill in all fields.";

        } else {

            $stmt = $pdo->prepare("
                INSERT INTO Posts
                    (title, body, Users_username)
                VALUES
                    (:title, :body, :username)
            ");

            $stmt->execute([
                ':title' => $title,
                ':body' => $body,
                ':username' => $username
            ]);

            header("Location: index.php");
            exit;
        }
    }

} catch (PDOException $e) {

    $error = "An error occurred while communicating with the database.";

}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Post - Factorio Schematics</title>
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

        <h1>Add a Community Post</h1>

        <p>
            Share your Factorio creations with the community!
        </p>

        <?php if ($error !== ''): ?>

            <p class="error-message">
                <?= htmlspecialchars($error) ?>
            </p>

        <?php endif; ?>

        <form method="POST" action="add.php" class="post-form">

            <label for="title">Post Title</label>

            <input
                type="text"
                id="title"
                name="title"
                maxlength="255"
                required
            >

            <label for="body">Post Content</label>

            <textarea
                id="body"
                name="body"
                rows="8"
                required
            ></textarea>

            <label for="username">Author</label>

            <select
                id="username"
                name="username"
                required
            >

                <option value="">Select an author</option>

                <?php foreach ($users as $user): ?>

                    <option value="<?= htmlspecialchars($user['username']) ?>">
                        <?= htmlspecialchars($user['name']) ?>
                        (<?= htmlspecialchars($user['username']) ?>)
                    </option>

                <?php endforeach; ?>

            </select>

            <button type="submit" class="action-btn add-btn">
                Publish Post
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
