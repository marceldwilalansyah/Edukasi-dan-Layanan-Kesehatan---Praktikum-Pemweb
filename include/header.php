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

// Notifikasi belum dibaca & pengingat (Modul 8). Dibungkus try/catch supaya
// halaman lama tetap jalan kalau tabel modul 8 belum di-import.
$notifBelumDibaca = 0;
$daftarPengingat = [];

if (isset($_SESSION['user_id']) && in_array($role, ['pengguna', 'nakes'], true)) {
    require_once __DIR__ . '/../koneksi.php';
    require_once __DIR__ . '/fungsi.php';

    try {
        $notifBelumDibaca = jumlahNotifikasiBelumDibaca($koneksi, (int) $_SESSION['user_id']);

        if ($role === 'pengguna') {
            $daftarPengingat = pengingatJatuhTempo($koneksi, (int) $_SESSION['user_id']);
        }
    } catch (mysqli_sql_exception $e) {
        $notifBelumDibaca = 0;
        $daftarPengingat = [];
    }
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
                <a class="nav-link<?= navActive('/pengguna/kalkulator/', $currentPath) ?>" href="<?= $baseUrl ?>/pengguna/kalkulator/index.php">Kalkulator</a>
                <a class="nav-link<?= navActive('/pengguna/kuis/', $currentPath) ?>" href="<?= $baseUrl ?>/pengguna/kuis/index.php">Kuis</a>
                <a class="nav-link<?= navActive('/pengguna/catatan/', $currentPath) ?>" href="<?= $baseUrl ?>/pengguna/catatan/index.php">Tracker</a>
                <a class="nav-link<?= navActive('/pengguna/konsultasi/', $currentPath) ?>" href="<?= $baseUrl ?>/pengguna/konsultasi/index.php">Konsultasi</a>
                <a class="nav-link<?= navActive('/pengguna/pengingat/', $currentPath) ?>" href="<?= $baseUrl ?>/pengguna/pengingat/index.php">Pengingat</a>
            <?php elseif ($role === 'admin'): ?>
                <a class="nav-link<?= navActive('/admin/dashboard.php', $currentPath) ?>" href="<?= $baseUrl ?>/admin/dashboard.php">Dashboard</a>
                <a class="nav-link<?= navActive('/admin/user/', $currentPath) ?>" href="<?= $baseUrl ?>/admin/user/index.php">Pengguna</a>
                <a class="nav-link<?= navActive('/admin/nakes/', $currentPath) ?>" href="<?= $baseUrl ?>/admin/nakes/index.php">Nakes</a>
                <a class="nav-link<?= navActive('/admin/artikel/', $currentPath) ?>" href="<?= $baseUrl ?>/admin/artikel/index.php">Artikel</a>
                <a class="nav-link<?= navActive('/kategori/', $currentPath) ?>" href="<?= $baseUrl ?>/kategori/index.php">Kategori</a>
                <a class="nav-link<?= navActive('/admin/kuis/', $currentPath) ?>" href="<?= $baseUrl ?>/admin/kuis/index.php">Kuis</a>
            <?php elseif ($role === 'nakes'): ?>
                <a class="nav-link<?= navActive('/nakes/dashboard.php', $currentPath) ?>" href="<?= $baseUrl ?>/nakes/dashboard.php">Dashboard</a>
                <a class="nav-link<?= navActive('/nakes/jadwal/', $currentPath) ?>" href="<?= $baseUrl ?>/nakes/jadwal/index.php">Jadwal</a>
                <a class="nav-link<?= navActive('/nakes/konsultasi/', $currentPath) ?>" href="<?= $baseUrl ?>/nakes/konsultasi/index.php">Konsultasi</a>
                <a class="nav-link<?= navActive('/nakes/profile.php', $currentPath) ?>" href="<?= $baseUrl ?>/nakes/profile.php">Profil</a>
            <?php endif; ?>
        </nav>

        <div class="header-actions">
            <?php if ($role === 'pengguna' || $role === 'nakes'): ?>
                <a class="icon-button bell<?= navActive('/notifikasi/', $currentPath) ?>" href="<?= $baseUrl ?>/notifikasi/index.php" title="Notifikasi" aria-label="Notifikasi<?= $notifBelumDibaca > 0 ? ', ' . $notifBelumDibaca . ' belum dibaca' : '' ?>">
                    🔔
                    <?php if ($notifBelumDibaca > 0): ?>
                        <span class="bell-count"><?= $notifBelumDibaca > 99 ? '99+' : $notifBelumDibaca ?></span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
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

<?php foreach ($daftarPengingat as $pengingat): ?>
    <?php $info = JENIS_PENGINGAT[$pengingat['jenis']]; ?>
    <div class="reminder-banner" role="status">
        <span class="reminder-icon" aria-hidden="true"><?= $info['ikon'] ?></span>
        <div class="reminder-text">
            <strong>Pengingat <?= htmlspecialchars(strtolower($info['label'])) ?> · <?= htmlspecialchars(substr($pengingat['jam'], 0, 5)) ?></strong>
            <span><?= htmlspecialchars($info['pesan']) ?></span>
        </div>
        <a class="button secondary" href="<?= $baseUrl ?>/pengguna/catatan/tambah.php">Catat</a>
        <form class="inline-form" action="<?= $baseUrl ?>/pengguna/pengingat/tutup.php" method="POST">
            <input type="hidden" name="jenis" value="<?= htmlspecialchars($pengingat['jenis']) ?>">
            <input type="hidden" name="kembali" value="<?= htmlspecialchars($currentPath) ?>">
            <button type="submit" class="secondary-ghost" title="Tutup pengingat hari ini" aria-label="Tutup pengingat hari ini">✕</button>
        </form>
    </div>
<?php endforeach; ?>
