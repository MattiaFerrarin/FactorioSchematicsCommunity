<?php
require_once 'auth.php';
$user = $user ?? currentUser();
?>
<header class="site-header">
    <div class="header-left">
        <button type="button" id="hamburger-btn" class="hamburger-btn" aria-label="Toggle navigation menu" aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <a href="/" class="logo">Factorio Schematics</a>
    </div>

    <div class="user-menu">
        <?php if ($user): ?>
            <button type="button" id="user-menu-btn" class="user-icon-btn" aria-label="Account menu" aria-expanded="false" aria-controls="user-dropdown">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </button>

            <div id="user-dropdown" class="user-dropdown">
                <div class="user-dropdown-header">
                    <span class="user-dropdown-name"><?= htmlspecialchars($user['name']) ?></span>
                    <span class="user-dropdown-handle">@<?= htmlspecialchars($user['username']) ?></span>
                </div>
                <a href="my_posts.php" class="user-dropdown-item">My Posts</a>
                <a href="account_settings.php" class="user-dropdown-item">Account Settings</a>
                <div class="user-dropdown-divider"></div>
                <a href="logout.php" class="user-dropdown-item">Log out</a>
            </div>
        <?php else: ?>
            <a href="login.php" class="login-btn">Log in</a>
        <?php endif; ?>
    </div>
</header>

<!-- Sidebar -->
<nav id="sidebar" class="sidebar" aria-label="Sidebar navigation">
    <ul class="sidebar-menu">
        <li><a href="/">Home</a></li>
        <li><a href="add.php">Create post</a></li>
        <li><a href="index.php">People (WIP)</a></li>
    </ul>
    <div class="sidebar-bottom">
        <a href="settings.php">Settings (WIP)</a>
    </div>
</nav>