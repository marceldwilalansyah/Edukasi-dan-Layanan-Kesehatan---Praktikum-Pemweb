<?php
require_once "../include/auth_check.php";
require_once "../koneksi.php";
require_once "../include/fungsi.php";

cekRoleSalahSatu(["pengguna", "nakes"]);

$user_id   = (int) $_SESSION["user_id"];
$dashboard = $_SESSION["role_name"] === "nakes" ? "../nakes/dashboard.php" : "../pengguna/dashboard.php";

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT notifikasi_id, jenis, judul, pesan, tautan, sudah_dibaca, dibuat_pada
     FROM notifikasi
     WHERE user_id = ?
     ORDER BY dibuat_pada DESC, notifikasi_id DESC
     LIMIT 50"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$daftar = mysqli_stmt_get_result($stmt);

$ikon = [
    "booking"    => "📅",
    "konsultasi" => "🩺",
    "badge"      => "🏅",
    "sistem"     => "🔔",
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi - Sehatku</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php require_once "../include/header.php"; ?>

<div class="container">
    <a class="back-button" href="<?= $dashboard ?>">← Kembali ke Dashboard</a>

    <h1>Notifikasi</h1>

    <?php if (isset($_GET["pesan"])): ?>
        <div class="alert success"><?= e($_GET["pesan"]) ?></div>
    <?php endif; ?>

    <div class="page-actions">
        <?php if ($notifBelumDibaca > 0): ?>
            <form class="inline-form" action="tandai_semua.php" method="POST">
                <button type="submit" class="button secondary">Tandai semua dibaca (<?= (int) $notifBelumDibaca ?>)</button>
            </form>
        <?php endif; ?>

        <?php if ($_SESSION["role_name"] === "pengguna"): ?>
            <a class="button secondary" href="../pengguna/pengingat/index.php">Atur Pengingat</a>
        <?php endif; ?>
    </div>

    <?php if (mysqli_num_rows($daftar) === 0): ?>
        <div class="card">
            <div class="empty-state">Belum ada notifikasi.</div>
        </div>
    <?php else: ?>
        <div class="notif-list">
            <?php while ($n = mysqli_fetch_assoc($daftar)): ?>
                <a class="notif-item<?= $n["sudah_dibaca"] ? "" : " unread" ?>"
                   href="buka.php?id=<?= (int) $n["notifikasi_id"] ?>">
                    <span class="notif-icon" aria-hidden="true"><?= $ikon[$n["jenis"]] ?? "🔔" ?></span>
                    <span class="notif-body">
                        <strong><?= e($n["judul"]) ?></strong>
                        <p><?= e($n["pesan"]) ?></p>
                        <span class="notif-time">
                            <?= e(tanggalIndo($n["dibuat_pada"], false)) ?>, <?= e(date("H:i", strtotime($n["dibuat_pada"]))) ?>
                        </span>
                    </span>
                    <?php if (!$n["sudah_dibaca"]): ?>
                        <span class="notif-dot" title="Belum dibaca"></span>
                    <?php endif; ?>
                </a>
            <?php endwhile; ?>
        </div>
        <p class="muted small">Menampilkan maksimal 50 notifikasi terbaru.</p>
    <?php endif; ?>
</div>

<?php require_once "../include/footer.php"; ?>
</body>
</html>
