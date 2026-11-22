<?php

require_once "../include/auth_check.php";
require_once "../koneksi.php";
require_once "../include/fungsi.php";

cekRole("admin");

// Pengguna per peran (peran tanpa akun tetap tampil dengan angka 0)
$hasil = mysqli_query(
    $koneksi,
    "SELECT r.role_name, COUNT(u.user_id) AS jumlah
     FROM roles r
     LEFT JOIN users u ON u.role_id = r.role_id
     GROUP BY r.role_id, r.role_name
     ORDER BY r.role_id"
);

$per_peran    = [];
$total_akun   = 0;
while ($baris = mysqli_fetch_assoc($hasil)) {
    $per_peran[] = $baris;
    $total_akun += (int) $baris["jumlah"];
}

// Status konsultasi
$status_konsultasi = ["menunggu" => 0, "dikonfirmasi" => 0, "selesai" => 0, "dibatalkan" => 0];
$hasil = mysqli_query($koneksi, "SELECT status, COUNT(*) AS jumlah FROM konsultasi GROUP BY status");

while ($baris = mysqli_fetch_assoc($hasil)) {
    $status_konsultasi[$baris["status"]] = (int) $baris["jumlah"];
}
$total_konsultasi = array_sum($status_konsultasi);

// Artikel terpopuler = paling banyak disimpan (bookmark)
$hasil = mysqli_query(
    $koneksi,
    "SELECT a.article_id, a.title, c.category_name, COUNT(b.bookmark_id) AS jumlah
     FROM articles a
     JOIN categories c ON c.category_id = a.category_id
     LEFT JOIN bookmark_artikel b ON b.article_id = a.article_id
     WHERE a.status = 'terbit'
     GROUP BY a.article_id, a.title, c.category_name, a.created_at
     ORDER BY jumlah DESC, a.created_at DESC
     LIMIT 5"
);

$artikel_populer = [];
while ($baris = mysqli_fetch_assoc($hasil)) {
    $artikel_populer[] = $baris;
}
$bookmark_tertinggi = $artikel_populer ? max(1, (int) $artikel_populer[0]["jumlah"]) : 1;

$total_artikel = (int) mysqli_fetch_assoc(
    mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM articles WHERE status = 'terbit'")
)["total"];

$nama_peran = ["admin" => "Admin", "nakes" => "Tenaga kesehatan", "pengguna" => "Pengguna"];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sehatku</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<?php require_once "../include/header.php"; ?>

<div class="container">

    <h1>Dashboard Admin</h1>

    <p>Selamat datang, <?= e($_SESSION["username"]) ?>. Ini gambaran Sehatku saat ini.</p>

    <div class="stat-grid">
        <div class="stat-card accent">
            <span class="stat-label">Total akun</span>
            <span class="stat-value"><?= $total_akun ?></span>
            <span class="stat-note">semua peran</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Konsultasi</span>
            <span class="stat-value"><?= $total_konsultasi ?></span>
            <span class="stat-note"><?= $status_konsultasi["menunggu"] ?> menunggu respons</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Artikel terbit</span>
            <span class="stat-value"><?= $total_artikel ?></span>
            <span class="stat-note">tersedia untuk pengguna</span>
        </div>
    </div>

    <div class="grid-2">

        <div class="card">
            <h2>Pengguna per Peran</h2>
            <div class="bar-list">
                <?php foreach ($per_peran as $peran): ?>
                    <?php $lebar = $total_akun > 0 ? round($peran["jumlah"] / $total_akun * 100) : 0; ?>
                    <div class="bar-row">
                        <div class="bar-label">
                            <strong><?= e($nama_peran[$peran["role_name"]] ?? $peran["role_name"]) ?></strong>
                            <span><?= (int) $peran["jumlah"] ?></span>
                        </div>
                        <div class="bar-track" role="img" aria-label="<?= $lebar ?> persen dari seluruh akun">
                            <div class="bar-fill" style="width: <?= $lebar ?>%;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card">
            <h2>Status Konsultasi</h2>
            <?php if ($total_konsultasi === 0): ?>
                <div class="empty-state">Belum ada konsultasi.</div>
            <?php else: ?>
                <div class="bar-list">
                    <?php foreach ($status_konsultasi as $status => $jumlah): ?>
                        <?php $lebar = round($jumlah / $total_konsultasi * 100); ?>
                        <div class="bar-row">
                            <div class="bar-label">
                                <strong><?= ucfirst($status) ?></strong>
                                <span><?= $jumlah ?></span>
                            </div>
                            <div class="bar-track" role="img" aria-label="<?= $lebar ?> persen dari seluruh konsultasi">
                                <div class="bar-fill <?= e($status) ?>" style="width: <?= $lebar ?>%;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <div class="card">
        <h2>Artikel Terpopuler</h2>
        <p class="muted small" style="margin-top: -6px;">Diurutkan dari jumlah pengguna yang menyimpan artikel.</p>

        <?php if (count($artikel_populer) === 0): ?>
            <div class="empty-state">Belum ada artikel terbit.</div>
        <?php else: ?>
            <ol class="rank-list">
                <?php foreach ($artikel_populer as $no => $artikel): ?>
                    <?php $lebar = round($artikel["jumlah"] / $bookmark_tertinggi * 100); ?>
                    <li>
                        <span class="rank-no"><?= $no + 1 ?></span>
                        <div>
                            <div class="rank-title">
                                <a href="artikel/edit.php?id=<?= (int) $artikel["article_id"] ?>"><?= e($artikel["title"]) ?></a>
                                <span class="muted"><?= (int) $artikel["jumlah"] ?> simpanan</span>
                            </div>
                            <div class="bar-track">
                                <div class="bar-fill coral" style="width: <?= $lebar ?>%;"></div>
                            </div>
                            <span class="muted small"><?= e($artikel["category_name"]) ?></span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </div>

    <h2 style="margin-top: 32px;">Pintasan</h2>

    <div class="page-actions">
        <a class="button" href="user/index.php">Kelola Pengguna</a>
        <a class="button" href="nakes/index.php">Kelola Nakes</a>
        <a class="button" href="artikel/index.php">Kelola Artikel</a>
        <a class="button" href="../kategori/index.php">Kelola Kategori</a>
        <a class="button" href="kuis/index.php">Kelola Kuis</a>
        <a class="button danger" href="../autentikasi/logout.php">Logout</a>
    </div>

</div>

<?php require_once "../include/footer.php"; ?>
</body>

</html>
