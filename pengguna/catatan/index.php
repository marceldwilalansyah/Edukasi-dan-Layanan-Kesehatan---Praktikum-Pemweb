<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("pengguna");

$user_id       = $_SESSION["user_id"];
$tanggal_mulai = isset($_GET["tanggal_mulai"]) ? trim($_GET["tanggal_mulai"]) : "";
$tanggal_akhir = isset($_GET["tanggal_akhir"]) ? trim($_GET["tanggal_akhir"]) : "";


$query  = "SELECT * FROM catatan_kesehatan WHERE user_id = ?";
$params = [$user_id];
$types  = "i";

// Filter berdasarkan rentang tanggal jika diisi
if ($tanggal_mulai !== "" && $tanggal_akhir !== "") {
    $query   .= " AND tanggal_catatan BETWEEN ? AND ?";
    $params[] = $tanggal_mulai;
    $params[] = $tanggal_akhir;
    $types   .= "ss";
} elseif ($tanggal_mulai !== "") {
    $query   .= " AND tanggal_catatan >= ?";
    $params[] = $tanggal_mulai;
    $types   .= "s";
} elseif ($tanggal_akhir !== "") {
    $query   .= " AND tanggal_catatan <= ?";
    $params[] = $tanggal_akhir;
    $types   .= "s";
}

$query .= " ORDER BY tanggal_catatan DESC, catatan_id DESC";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$daftar_catatan = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catatan Kesehatan Harian - Sehatku</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php require_once "../../include/header.php"; ?>

<div class="container">
    <a class="back-button" href="../dashboard.php">← Kembali ke Dashboard</a>

    <h1>Catatan & Monitoring Kesehatan Pribadi</h1>

    <?php if (isset($_GET["pesan"])): ?>
        <div class="alert success">
            <?= htmlspecialchars($_GET["pesan"]) ?>
        </div>
    <?php endif; ?>

    <div class="page-actions">
        <a class="button" href="tambah.php">+ Tambah Catatan Harian</a>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <h2 style="margin-top: 0;">Filter Rentang Tanggal</h2>
        <form method="GET">
            <label>Dari Tanggal</label>
            <input type="date" name="tanggal_mulai" value="<?= htmlspecialchars($tanggal_mulai) ?>">

            <label>Sampai Tanggal</label>
            <input type="date" name="tanggal_akhir" value="<?= htmlspecialchars($tanggal_akhir) ?>">

            <div class="form-actions">
                <button type="submit">Filter</button>
                <?php if ($tanggal_mulai !== "" || $tanggal_akhir !== ""): ?>
                    <a class="button secondary" href="index.php">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card">
        <h2 style="margin-top: 0;">Daftar Catatan Kesehatan</h2>

        <?php if (mysqli_num_rows($daftar_catatan) === 0): ?>
            <p>Belum ada catatan kesehatan yang tersimpan.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Tidur (Jam)</th>
                            <th>Aktivitas Fisik</th>
                            <th>Asupan Air (L)</th>
                            <th>Keluhan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($daftar_catatan)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row["tanggal_catatan"]) ?></td>
                                <td><?= $row["durasi_tidur"] ?> jam</td>
                                <td><?= htmlspecialchars($row["aktivitas_fisik"]) ?></td>
                                <td><?= $row["asupan_air"] ?> L</td>
                                <td>
                                    <?= $row["keluhan"] !== null && $row["keluhan"] !== ""
                                        ? nl2br(htmlspecialchars($row["keluhan"]))
                                        : "-" ?>
                                </td>
                                <td>
                                    <div class="form-actions" style="margin: 0;">
                                        <a class="button secondary" href="edit.php?id=<?= $row["catatan_id"] ?>">Edit</a>

                                        <form action="hapus.php" method="POST" style="margin: 0;">
                                            <input type="hidden" name="catatan_id" value="<?= $row["catatan_id"] ?>">
                                            <button
                                                type="submit"
                                                class="button danger"
                                                onclick="return confirm('Hapus catatan kesehatan ini?')"
                                            >
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
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