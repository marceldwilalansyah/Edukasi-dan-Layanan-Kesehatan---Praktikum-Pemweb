<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";
require_once "../../include/fungsi.php";

cekRole("nakes");

$nakes_id = (int) $_SESSION["user_id"];
$id       = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT k.*, COALESCE(NULLIF(p.nama_lengkap, ''), u.username) AS pasien
     FROM konsultasi k
     JOIN users u ON u.user_id = k.user_id
     LEFT JOIN user_profiles p ON p.user_id = k.user_id
     WHERE k.konsultasi_id = ? AND k.nakes_id = ?"
);
mysqli_stmt_bind_param($stmt, "ii", $id, $nakes_id);
mysqli_stmt_execute($stmt);
$k = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$k) {
    header("Location: index.php");
    exit;
}

$aktif = in_array($k["status"], ["menunggu", "dikonfirmasi"], true);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Konsultasi - Sehatku</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php require_once "../../include/header.php"; ?>

<div class="container">
    <a class="back-button" href="index.php">← Kembali ke Daftar Konsultasi</a>

    <h1>Detail Konsultasi</h1>

    <?php if (isset($_GET["pesan"])): ?>
        <div class="alert success"><?= e($_GET["pesan"]) ?></div>
    <?php endif; ?>

    <?php if (isset($_GET["error"])): ?>
        <div class="alert error"><?= e($_GET["error"]) ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="item-head">
            <div>
                <h2 style="margin-bottom: 4px;"><?= e($k["pasien"]) ?></h2>
                <div class="muted">
                    <?= e(tanggalIndo($k["tanggal_konsultasi"])) ?> ·
                    <?= e(rentangJam($k["jam_mulai"], $k["jam_selesai"])) ?>
                </div>
            </div>
            <?= pillStatus($k["status"]) ?>
        </div>

        <div class="item-body">
            <p><strong>Keluhan pasien</strong></p>
            <div class="note-box"><?= nl2br(e($k["keluhan"])) ?></div>

            <?php if (!$aktif && $k["catatan_nakes"]): ?>
                <p style="margin-top: 16px;"><strong>Catatan Anda</strong></p>
                <div class="note-box"><?= nl2br(e($k["catatan_nakes"])) ?></div>
            <?php endif; ?>

            <?php if ($k["status"] === "dibatalkan" && $k["dibatalkan_oleh"] === "pengguna"): ?>
                <p class="muted small">Dibatalkan oleh pasien.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($aktif): ?>
        <div class="card">
            <h2>Tindak Lanjut</h2>

            <form action="proses.php" method="POST">
                <input type="hidden" name="konsultasi_id" value="<?= (int) $k["konsultasi_id"] ?>">

                <label>Catatan untuk pasien</label>
                <textarea name="catatan" rows="4" style="min-height: 110px;"
                    placeholder="<?= $k["status"] === "menunggu"
                        ? "Opsional saat konfirmasi, wajib saat membatalkan."
                        : "Wajib diisi saat menyelesaikan atau membatalkan: saran, hasil, atau alasan." ?>"></textarea>

                <div class="form-actions">
                    <?php if ($k["status"] === "menunggu"): ?>
                        <button type="submit" name="aksi" value="konfirmasi">Konfirmasi</button>
                    <?php else: ?>
                        <button type="submit" name="aksi" value="selesai">Tandai Selesai</button>
                    <?php endif; ?>

                    <button type="submit" name="aksi" value="batal" class="button danger"
                        data-confirm="Batalkan konsultasi ini? Pasien akan diberi tahu.">Batalkan</button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php require_once "../../include/footer.php"; ?>
</body>
</html>
