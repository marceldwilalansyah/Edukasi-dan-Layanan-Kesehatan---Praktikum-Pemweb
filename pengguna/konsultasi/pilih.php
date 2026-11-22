<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";
require_once "../../include/fungsi.php";

cekRole("pengguna");

$cari     = trim($_GET["cari"] ?? "");
$hari_ini = date("Y-m-d");
$jam_ini  = date("H:i:s");


$query = "
    SELECT u.user_id,
           COALESCE(NULLIF(p.nama_lengkap, ''), u.username) AS nama,
           p.spesialisasi, p.bio,
           (SELECT COUNT(*) FROM jadwal_praktik j
             WHERE j.nakes_id = u.user_id AND j.status = 'tersedia'
               AND (j.tanggal > ? OR (j.tanggal = ? AND j.jam_mulai > ?))) AS slot_tersedia
    FROM users u
    JOIN roles r ON r.role_id = u.role_id
    LEFT JOIN user_profiles p ON p.user_id = u.user_id
    WHERE r.role_name = 'nakes' AND u.status = 'aktif'
";
$params = [$hari_ini, $hari_ini, $jam_ini];
$types  = "sss";

if ($cari !== "") {
    $query   .= " AND (p.nama_lengkap LIKE ? OR p.spesialisasi LIKE ? OR u.username LIKE ?)";
    $like     = "%" . $cari . "%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types   .= "sss";
}

$query .= " ORDER BY slot_tersedia DESC, nama";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$daftar_nakes = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Tenaga Kesehatan - Sehatku</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php require_once "../../include/header.php"; ?>

<div class="container">
    <a class="back-button" href="index.php">← Kembali ke Konsultasi</a>

    <h1>Pilih Tenaga Kesehatan</h1>

    <div class="card" style="margin-bottom: 20px;">
        <form method="GET">
            <label>Cari nama atau spesialisasi</label>
            <input type="text" name="cari" value="<?= e($cari) ?>" placeholder="Contoh: gizi, dokter umum">
            <div class="form-actions">
                <button type="submit">Cari</button>
                <?php if ($cari !== ""): ?>
                    <a class="button secondary" href="pilih.php">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <?php if (mysqli_num_rows($daftar_nakes) === 0): ?>
        <div class="card">
            <div class="empty-state">Tenaga kesehatan tidak ditemukan.</div>
        </div>
    <?php else: ?>
        <div class="item-list">
            <?php while ($nakes = mysqli_fetch_assoc($daftar_nakes)): ?>
                <div class="item-card">
                    <div class="item-head">
                        <div>
                            <h3><?= e($nakes["nama"]) ?></h3>
                            <div class="meta"><?= e($nakes["spesialisasi"] ?: "Tenaga kesehatan") ?></div>
                        </div>
                        <?php if ((int) $nakes["slot_tersedia"] > 0): ?>
                            <span class="pill pill-tersedia"><?= (int) $nakes["slot_tersedia"] ?> jadwal tersedia</span>
                        <?php else: ?>
                            <span class="pill">Belum ada jadwal</span>
                        <?php endif; ?>
                    </div>
                    <div class="item-body">
                        <?php if ($nakes["bio"]): ?>
                            <p><?= nl2br(e($nakes["bio"])) ?></p>
                        <?php endif; ?>

                        <?php if ((int) $nakes["slot_tersedia"] > 0): ?>
                            <a class="button small-button" href="booking.php?nakes_id=<?= (int) $nakes["user_id"] ?>">
                                Lihat Jadwal & Booking
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once "../../include/footer.php"; ?>
</body>
</html>
