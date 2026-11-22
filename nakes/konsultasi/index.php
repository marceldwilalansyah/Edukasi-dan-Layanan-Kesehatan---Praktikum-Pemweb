<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";
require_once "../../include/fungsi.php";

cekRole("nakes");

$nakes_id = (int) $_SESSION["user_id"];

$status_valid  = ["menunggu", "dikonfirmasi", "selesai", "dibatalkan"];
$filter_status = $_GET["status"] ?? "";
if (!in_array($filter_status, $status_valid, true)) {
    $filter_status = "";
}

$query  = "
    SELECT k.konsultasi_id, k.tanggal_konsultasi, k.jam_mulai, k.jam_selesai,
           k.keluhan, k.status,
           COALESCE(NULLIF(p.nama_lengkap, ''), u.username) AS pasien
    FROM konsultasi k
    JOIN users u ON u.user_id = k.user_id
    LEFT JOIN user_profiles p ON p.user_id = k.user_id
    WHERE k.nakes_id = ?
";
$params = [$nakes_id];
$types  = "i";

if ($filter_status !== "") {
    $query   .= " AND k.status = ?";
    $params[] = $filter_status;
    $types   .= "s";
}

$query .= " ORDER BY FIELD(k.status, 'menunggu', 'dikonfirmasi', 'selesai', 'dibatalkan'),
                     k.tanggal_konsultasi, k.jam_mulai";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$daftar = mysqli_stmt_get_result($stmt);

$tab = [
    ""             => "Semua",
    "menunggu"     => "Menunggu",
    "dikonfirmasi" => "Dikonfirmasi",
    "selesai"      => "Selesai",
    "dibatalkan"   => "Dibatalkan",
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permintaan Konsultasi - Sehatku</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php require_once "../../include/header.php"; ?>

<div class="container">
    <a class="back-button" href="../dashboard.php">← Kembali ke Dashboard</a>

    <h1>Permintaan Konsultasi</h1>

    <?php if (isset($_GET["pesan"])): ?>
        <div class="alert success"><?= e($_GET["pesan"]) ?></div>
    <?php endif; ?>

    <div class="tabs">
        <?php foreach ($tab as $nilai => $nama): ?>
            <a class="tab<?= $filter_status === $nilai ? " is-active" : "" ?>"
               href="index.php<?= $nilai !== "" ? "?status=" . $nilai : "" ?>"><?= e($nama) ?></a>
        <?php endforeach; ?>
    </div>

    <?php if (mysqli_num_rows($daftar) === 0): ?>
        <div class="card">
            <div class="empty-state">Belum ada konsultasi<?= $filter_status !== "" ? " dengan status ini" : "" ?>.</div>
        </div>
    <?php else: ?>
        <div class="item-list">
            <?php while ($k = mysqli_fetch_assoc($daftar)): ?>
                <div class="item-card">
                    <div class="item-head">
                        <div>
                            <h3><?= e($k["pasien"]) ?></h3>
                            <div class="meta">
                                <?= e(tanggalIndo($k["tanggal_konsultasi"])) ?> ·
                                <?= e(rentangJam($k["jam_mulai"], $k["jam_selesai"])) ?>
                            </div>
                        </div>
                        <?= pillStatus($k["status"]) ?>
                    </div>
                    <div class="item-body">
                        <p>
                            <strong>Keluhan:</strong>
                            <?= e(mb_strimwidth($k["keluhan"], 0, 140, "…", "UTF-8")) ?>
                        </p>
                        <a class="button <?= $k["status"] === "menunggu" ? "" : "secondary" ?> small-button"
                           href="detail.php?id=<?= (int) $k["konsultasi_id"] ?>">
                            <?= $k["status"] === "menunggu" ? "Tinjau Permintaan" : "Lihat Detail" ?>
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once "../../include/footer.php"; ?>
</body>
</html>
