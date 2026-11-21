<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("admin");

$query = "
    SELECT k.*, COUNT(p.pertanyaan_id) AS total_soal
    FROM kuis k
    LEFT JOIN pertanyaan_kuis p ON k.kuis_id = p.kuis_id
    GROUP BY k.kuis_id
    ORDER BY k.created_at DESC
";
$daftar_kuis = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kuis - Admin Sehatku</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php require_once "../../include/header.php"; ?>

<div class="container">
    <a class="back-button" href="../dashboard.php">← Kembali ke Dashboard</a>

    <h1>Kelola Kuis Kesehatan</h1>

    <?php if (isset($_GET["pesan"])): ?>
        <div class="alert success">
            <?= htmlspecialchars($_GET["pesan"]) ?>
        </div>
    <?php endif; ?>

    <div class="page-actions">
        <a class="button" href="tambah.php">+ Tambah Kuis Baru</a>
    </div>

    <div class="card">
        <?php if (mysqli_num_rows($daftar_kuis) === 0): ?>
            <p>Belum ada kuis yang dibuat.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul Kuis</th>
                            <th>Deskripsi</th>
                            <th>Jumlah Soal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($daftar_kuis)): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><strong><?= htmlspecialchars($row["judul"]) ?></strong></td>
                                <td><?= htmlspecialchars($row["deskripsi"] ?? "-") ?></td>
                                <td><?= (int) $row["total_soal"] ?> soal</td>
                                <td>
                                    <div class="form-actions" style="margin: 0;">
                                        <a class="button" href="soal.php?kuis_id=<?= $row["kuis_id"] ?>">Kelola Soal</a>
                                        <a class="button secondary" href="edit.php?id=<?= $row["kuis_id"] ?>">Edit</a>

                                        <form action="hapus.php" method="POST" style="margin: 0;">
                                            <input type="hidden" name="kuis_id" value="<?= $row["kuis_id"] ?>">
                                            <button
                                                type="submit"
                                                class="button danger"
                                                onclick="return confirm('Hapus kuis ini beserta semua soalnya?')"
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