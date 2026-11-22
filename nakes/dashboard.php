<?php

require_once "../include/auth_check.php";
require_once "../koneksi.php";
require_once "../include/fungsi.php";

cekRole("nakes");

$nakes_id = (int) $_SESSION["user_id"];
$hari_ini = date("Y-m-d");
$jam_ini  = date("H:i:s");


$stmt = mysqli_prepare(
    $koneksi,
    "SELECT j.jam_mulai, j.jam_selesai, j.status,
            k.konsultasi_id, k.status AS status_konsultasi,
            COALESCE(NULLIF(p.nama_lengkap, ''), u.username) AS pasien
     FROM jadwal_praktik j
     LEFT JOIN konsultasi k
            ON k.jadwal_id = j.jadwal_id AND k.status IN ('menunggu', 'dikonfirmasi', 'selesai')
     LEFT JOIN users u ON u.user_id = k.user_id
     LEFT JOIN user_profiles p ON p.user_id = k.user_id
     WHERE j.nakes_id = ? AND j.tanggal = ?
     ORDER BY j.jam_mulai"
);
mysqli_stmt_bind_param($stmt, "is", $nakes_id, $hari_ini);
mysqli_stmt_execute($stmt);
$jadwal_hari_ini = mysqli_stmt_get_result($stmt);

$jadwal_list = [];
while ($baris = mysqli_fetch_assoc($jadwal_hari_ini)) {
    $jadwal_list[] = $baris;
}


$stmt = mysqli_prepare(
    $koneksi,
    "SELECT k.konsultasi_id, k.tanggal_konsultasi, k.jam_mulai, k.jam_selesai, k.keluhan,
            COALESCE(NULLIF(p.nama_lengkap, ''), u.username) AS pasien
     FROM konsultasi k
     JOIN users u ON u.user_id = k.user_id
     LEFT JOIN user_profiles p ON p.user_id = k.user_id
     WHERE k.nakes_id = ? AND k.status = 'menunggu'
     ORDER BY k.tanggal_konsultasi, k.jam_mulai"
);
mysqli_stmt_bind_param($stmt, "i", $nakes_id);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);

$menunggu = [];
while ($baris = mysqli_fetch_assoc($hasil)) {
    $menunggu[] = $baris;
}


$stmt = mysqli_prepare(
    $koneksi,
    "SELECT
        SUM(status = 'dikonfirmasi' AND tanggal_konsultasi >= ?) AS terkonfirmasi,
        SUM(status = 'selesai') AS selesai
     FROM konsultasi WHERE nakes_id = ?"
);
mysqli_stmt_bind_param($stmt, "si", $hari_ini, $nakes_id);
mysqli_stmt_execute($stmt);
$angka = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT COUNT(*) AS total FROM jadwal_praktik
     WHERE nakes_id = ? AND status = 'tersedia'
       AND (tanggal > ? OR (tanggal = ? AND jam_mulai > ?))"
);
mysqli_stmt_bind_param($stmt, "isss", $nakes_id, $hari_ini, $hari_ini, $jam_ini);
mysqli_stmt_execute($stmt);
$slot_kosong = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))["total"];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Nakes - Sehatku</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<?php require_once "../include/header.php"; ?>

<div class="container">

    <h1>Dashboard Tenaga Kesehatan</h1>

    <p>Selamat datang, <?= e($_SESSION["username"]) ?>. Hari ini <?= e(tanggalIndo($hari_ini)) ?>.</p>

    <div class="stat-grid">
        <div class="stat-card accent">
            <span class="stat-label">Menunggu respons</span>
            <span class="stat-value"><?= count($menunggu) ?></span>
            <span class="stat-note">permintaan masuk</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Jadwal hari ini</span>
            <span class="stat-value"><?= count($jadwal_list) ?></span>
            <span class="stat-note">slot praktik</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Sudah dikonfirmasi</span>
            <span class="stat-value"><?= (int) $angka["terkonfirmasi"] ?></span>
            <span class="stat-note">akan berlangsung</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Slot kosong</span>
            <span class="stat-value"><?= $slot_kosong ?></span>
            <span class="stat-note"><?= (int) $angka["selesai"] ?> konsultasi selesai</span>
        </div>
    </div>

    <div class="grid-2">

        <div class="card">
            <h2>Jadwal Hari Ini</h2>

            <?php if (count($jadwal_list) === 0): ?>
                <div class="empty-state">
                    Tidak ada jadwal hari ini.<br>
                    <a href="jadwal/index.php">Tambah jadwal praktik</a>
                </div>
            <?php else: ?>
                <div class="bar-list">
                    <?php foreach ($jadwal_list as $j): ?>
                        <div class="item-head">
                            <div>
                                <strong><?= e(rentangJam($j["jam_mulai"], $j["jam_selesai"])) ?></strong>
                                <div class="muted small">
                                    <?php if ($j["pasien"]): ?>
                                        <a href="konsultasi/detail.php?id=<?= (int) $j["konsultasi_id"] ?>"><?= e($j["pasien"]) ?></a>
                                    <?php else: ?>
                                        Belum ada yang booking
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?= pillStatus($j["status_konsultasi"] ?: $j["status"]) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Permintaan Menunggu</h2>

            <?php if (count($menunggu) === 0): ?>
                <div class="empty-state">Tidak ada permintaan yang menunggu.</div>
            <?php else: ?>
                <div class="bar-list">
                    <?php foreach (array_slice($menunggu, 0, 5) as $m): ?>
                        <div class="item-head">
                            <div>
                                <strong><?= e($m["pasien"]) ?></strong>
                                <div class="muted small">
                                    <?= e(tanggalIndo($m["tanggal_konsultasi"])) ?>,
                                    <?= e(rentangJam($m["jam_mulai"], $m["jam_selesai"])) ?>
                                </div>
                                <div class="small"><?= e(mb_strimwidth($m["keluhan"], 0, 70, "…", "UTF-8")) ?></div>
                            </div>
                            <a class="button small-button" style="margin-top: 0;"
                               href="konsultasi/detail.php?id=<?= (int) $m["konsultasi_id"] ?>">Tinjau</a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (count($menunggu) > 5): ?>
                    <p class="small" style="margin-bottom: 0;">
                        <a href="konsultasi/index.php?status=menunggu">Lihat semua <?= count($menunggu) ?> permintaan</a>
                    </p>
                <?php endif; ?>
            <?php endif; ?>
        </div>

    </div>

    <div class="page-actions">
        <a class="button" href="jadwal/index.php">Kelola Jadwal</a>
        <a class="button" href="konsultasi/index.php">Semua Konsultasi</a>
        <a class="button secondary" href="profile.php">Profil Saya</a>
        <a class="button danger" href="../autentikasi/logout.php">Logout</a>
    </div>

</div>

<?php require_once "../include/footer.php"; ?>
</body>

</html>
