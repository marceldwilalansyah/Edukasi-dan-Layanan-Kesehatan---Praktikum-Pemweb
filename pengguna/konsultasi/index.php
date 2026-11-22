<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";
require_once "../../include/fungsi.php";

cekRole("pengguna");

$user_id = (int) $_SESSION["user_id"];

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT k.konsultasi_id, k.tanggal_konsultasi, k.jam_mulai, k.jam_selesai,
            k.keluhan, k.status, k.catatan_nakes, k.dibatalkan_oleh,
            COALESCE(NULLIF(p.nama_lengkap, ''), u.username) AS nama_nakes,
            p.spesialisasi
     FROM konsultasi k
     JOIN users u ON u.user_id = k.nakes_id
     LEFT JOIN user_profiles p ON p.user_id = k.nakes_id
     WHERE k.user_id = ?
     ORDER BY k.tanggal_konsultasi DESC, k.jam_mulai DESC"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);

$aktif   = [];
$riwayat = [];

while ($baris = mysqli_fetch_assoc($hasil)) {
    if (in_array($baris["status"], ["menunggu", "dikonfirmasi"], true)) {
        $aktif[] = $baris;
    } else {
        $riwayat[] = $baris;
    }
}


$aktif = array_reverse($aktif);

function kartuKonsultasi($k, $bisaBatal)
{
    ?>
    <div class="item-card" id="k<?= (int) $k["konsultasi_id"] ?>">
        <div class="item-head">
            <div>
                <h3><?= e($k["nama_nakes"]) ?></h3>
                <div class="meta">
                    <?= e($k["spesialisasi"] ?: "Tenaga kesehatan") ?> ·
                    <?= e(tanggalIndo($k["tanggal_konsultasi"])) ?>,
                    <?= e(rentangJam($k["jam_mulai"], $k["jam_selesai"])) ?>
                </div>
            </div>
            <?= pillStatus($k["status"]) ?>
        </div>
        <div class="item-body">
            <p><strong>Keluhan:</strong> <?= nl2br(e($k["keluhan"])) ?></p>

            <?php if ($k["catatan_nakes"]): ?>
                <div class="note-box">
                    <strong>
                        <?= $k["status"] === "dibatalkan" ? "Alasan pembatalan" : "Catatan dari nakes" ?>:
                    </strong>
                    <?= nl2br(e($k["catatan_nakes"])) ?>
                </div>
            <?php endif; ?>

            <?php if ($k["status"] === "dibatalkan" && $k["dibatalkan_oleh"] === "pengguna"): ?>
                <p class="muted small">Dibatalkan olehmu.</p>
            <?php endif; ?>

            <?php if ($bisaBatal): ?>
                <form class="inline-form" action="batal.php" method="POST" style="margin-top: 10px;">
                    <input type="hidden" name="konsultasi_id" value="<?= (int) $k["konsultasi_id"] ?>">
                    <button type="submit" class="button danger small-button"
                        data-confirm="Batalkan booking ini?">Batalkan Booking</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konsultasi - Sehatku</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php require_once "../../include/header.php"; ?>

<div class="container">
    <a class="back-button" href="../dashboard.php">← Kembali ke Dashboard</a>

    <h1>Konsultasi Kesehatan</h1>
    <p>Pilih tenaga kesehatan, tentukan jadwal, lalu ceritakan keluhanmu.</p>

    <?php if (isset($_GET["pesan"])): ?>
        <div class="alert success"><?= e($_GET["pesan"]) ?></div>
    <?php endif; ?>

    <?php if (isset($_GET["error"])): ?>
        <div class="alert error"><?= e($_GET["error"]) ?></div>
    <?php endif; ?>

    <div class="page-actions">
        <a class="button" href="pilih.php">+ Buat Konsultasi Baru</a>
    </div>

    <h2>Konsultasi Aktif</h2>
    <?php if (count($aktif) === 0): ?>
        <div class="card">
            <div class="empty-state">Belum ada konsultasi aktif.</div>
        </div>
    <?php else: ?>
        <div class="item-list">
            <?php foreach ($aktif as $k) { kartuKonsultasi($k, true); } ?>
        </div>
    <?php endif; ?>

    <h2 style="margin-top: 32px;">Riwayat</h2>
    <?php if (count($riwayat) === 0): ?>
        <div class="card">
            <div class="empty-state">Riwayat konsultasi akan muncul di sini.</div>
        </div>
    <?php else: ?>
        <div class="item-list">
            <?php foreach ($riwayat as $k) { kartuKonsultasi($k, false); } ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once "../../include/footer.php"; ?>
</body>
</html>
