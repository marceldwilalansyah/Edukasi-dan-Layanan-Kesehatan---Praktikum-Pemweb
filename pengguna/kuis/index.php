<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("pengguna");

$user_id = $_SESSION["user_id"];


$query_kuis = "
    SELECT k.*, COUNT(p.pertanyaan_id) AS total_soal
    FROM kuis k
    LEFT JOIN pertanyaan_kuis p ON k.kuis_id = p.kuis_id
    GROUP BY k.kuis_id
    ORDER BY k.created_at DESC
";
$daftar_kuis = mysqli_query($koneksi, $query_kuis);


$query_riwayat = "
    SELECT r.*, k.judul
    FROM riwayat_kuis r
    JOIN kuis k ON r.kuis_id = k.kuis_id
    WHERE r.user_id = ?
    ORDER BY r.tanggal_kerjakan DESC
";
$stmt_riwayat = mysqli_prepare($koneksi, $query_riwayat);
mysqli_stmt_bind_param($stmt_riwayat, "i", $user_id);
mysqli_stmt_execute($stmt_riwayat);
$daftar_riwayat = mysqli_stmt_get_result($stmt_riwayat);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuis Kesehatan - Sehatku</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php require_once "../../include/header.php"; ?>

<div class="container">
    <a class="back-button" href="../dashboard.php">← Kembali ke Dashboard</a>

    <h1>Kuis Kesehatan</h1>

    <div class="card" style="margin-bottom: 20px;">
        <h2 style="margin-top: 0;">Daftar Kuis Tersedia</h2>

        <?php if (mysqli_num_rows($daftar_kuis) === 0): ?>
            <p>Belum ada kuis yang tersedia saat ini.</p>
        <?php else: ?>
            <?php while ($k = mysqli_fetch_assoc($daftar_kuis)): ?>
                <div style="border-bottom: 1px solid #e5e7eb; padding: 14px 0;">
                    <h3 style="margin: 0 0 6px 0;"><?= htmlspecialchars($k["judul"]) ?></h3>
                    <p style="margin: 0 0 10px 0; color: #555;">
                        <?= htmlspecialchars($k["deskripsi"] ?? "") ?>
                        (<strong><?= (int) $k["total_soal"] ?> soal</strong>)
                    </p>
                    <?php if ((int) $k["total_soal"] > 0): ?>
                        <a class="button" href="kerjakan.php?id=<?= $k["kuis_id"] ?>">Mulai Kuis</a>
                    <?php else: ?>
                        <span style="color: #888;">Soal belum tersedia</span>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2 style="margin-top: 0;">Riwayat Pengerjaan Kuis</h2>

        <?php if (mysqli_num_rows($daftar_riwayat) === 0): ?>
            <p>Anda belum pernah mengerjakan kuis.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Judul Kuis</th>
                            <th>Benar</th>
                            <th>Skor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($r = mysqli_fetch_assoc($daftar_riwayat)): ?>
                            <tr>
                                <td><?= htmlspecialchars($r["tanggal_kerjakan"]) ?></td>
                                <td><?= htmlspecialchars($r["judul"]) ?></td>
                                <td><?= $r["jumlah_benar"] ?> / <?= $r["total_soal"] ?></td>
                                <td><strong><?= $r["skor"] ?></strong></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once "../../include/footer.php"; ?>
</body>
</html>