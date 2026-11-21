<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$baseUrl = '/sehatku';
$role = $_SESSION['role_name'] ?? '';
$username = $_SESSION['username'] ?? 'Pengguna';
$currentPage = basename($_SERVER['PHP_SELF']);
$currentPath = $_SERVER['REQUEST_URI'] ?? '';

function navActive($needle, $currentPath) {
    return strpos($currentPath, $needle) !== false ? ' is-active' : '';
}
?>

<header class="site-header">
    <div class="topbar">
        <a class="brand" href="<?= $baseUrl ?>/index.php" aria-label="SehatKu Beranda">
            <span class="brand-mark" aria-hidden="true"><span></span></span>
            <span class="brand-copy">
                <strong>SehatKu</strong>
                <small>Ruang sehatmu</small>
            </span>
        </a>

        <nav class="main-nav" aria-label="Navigasi utama">
            <?php if ($role === 'pengguna'): ?>
                <a class="nav-link<?= navActive('/pengguna/dashboard.php', $currentPath) ?>" href="<?= $baseUrl ?>/pengguna/dashboard.php">Beranda</a>
                <a class="nav-link<?= navActive('/pengguna/artikel/', $currentPath) ?>" href="<?= $baseUrl ?>/pengguna/artikel/index.php">Artikel</a>
                <a class="nav-link" href="<?= $baseUrl ?>/pengguna/dashboard.php#kuis">Kuis</a>
                <a class="nav-link" href="<?= $baseUrl ?>/pengguna/dashboard.php#tracker">Tracker</a>
                <a class="nav-link" href="<?= $baseUrl ?>/pengguna/dashboard.php#konsultasi">Konsultasi</a>
            <?php elseif ($role === 'admin'): ?>
                <a class="nav-link<?= navActive('/admin/dashboard.php', $currentPath) ?>" href="<?= $baseUrl ?>/admin/dashboard.php">Dashboard</a>
                <a class="nav-link<?= navActive('/admin/user/', $currentPath) ?>" href="<?= $baseUrl ?>/admin/user/index.php">Pengguna</a>
                <a class="nav-link<?= navActive('/admin/artikel/', $currentPath) ?>" href="<?= $baseUrl ?>/admin/artikel/index.php">Artikel</a>
                <a class="nav-link<?= navActive('/kategori/', $currentPath) ?>" href="<?= $baseUrl ?>/kategori/index.php">Kategori</a>
            <?php elseif ($role === 'nakes'): ?>
                <a class="nav-link<?= navActive('/nakes/dashboard.php', $currentPath) ?>" href="<?= $baseUrl ?>/nakes/dashboard.php">Dashboard</a>
                <a class="nav-link<?= navActive('/nakes/profile.php', $currentPath) ?>" href="<?= $baseUrl ?>/nakes/profile.php">Profil</a>
            <?php endif; ?>
        </nav>

        <div class="header-actions">
            <div class="user-chip">
                <span class="avatar"><?= strtoupper(substr($username, 0, 1)) ?></span>
                <span class="user-chip-text">
                    <strong><?= htmlspecialchars($username) ?></strong>
                    <small><?= htmlspecialchars($role) ?></small>
                </span>
            </div>
            <a class="icon-button" href="<?= $baseUrl ?>/autentikasi/logout.php" title="Logout" aria-label="Logout">↗</a>
        </div>
    </div>
</header>

<main class="page-shell">
