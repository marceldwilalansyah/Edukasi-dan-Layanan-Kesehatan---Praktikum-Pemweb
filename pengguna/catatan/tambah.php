<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("pengguna");

$user_id = $_SESSION["user_id"];
$error   = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $tanggal_catatan = trim($_POST["tanggal_catatan"] ?? "");
    $durasi_tidur    = isset($_POST["durasi_tidur"]) ? (float) $_POST["durasi_tidur"] : 0;
    $aktivitas_fisik = trim($_POST["aktivitas_fisik"] ?? "");
    $asupan_air      = isset($_POST["asupan_air"]) ? (float) $_POST["asupan_air"] : 0;
    $keluhan         = trim($_POST["keluhan"] ?? "");

    if ($tanggal_catatan === "" || $aktivitas_fisik === "" || $durasi_tidur <= 0 || $asupan_air < 0) {
        $error = "Tanggal, durasi tidur, aktivitas fisik, dan asupan air wajib diisi dengan benar.";
    } else {
        $query = "
            INSERT INTO catatan_kesehatan
                (user_id, tanggal_catatan, durasi_tidur, aktivitas_fisik, asupan_air, keluhan)
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param(
            $stmt,
            "isdsds",
            $user_id,
            $tanggal_catatan,
            $durasi_tidur,
            $aktivitas_fisik,
            $asupan_air,
            $keluhan
        );
        mysqli_stmt_execute($stmt);

        header("Location: index.php?pesan=" . urlencode("Catatan kesehatan berhasil ditambahkan."));
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Catatan Kesehatan - Sehatku</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php require_once "../../include/header.php"; ?>

<div class="container">
    <a class="back-button" href="index.php">← Kembali ke Daftar Catatan</a>

    <h1>Tambah Catatan Kesehatan Harian</h1>

    <?php if ($error !== ""): ?>
        <div class="alert danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <form method="POST">
            <label>Tanggal</label>
            <input type="date" name="tanggal_catatan" value="<?= date('Y-m-d') ?>" required>

            <label>Durasi Tidur (jam)</label>
            <input type="number" name="durasi_tidur" step="0.5" min="0.5" max="24" required placeholder="Contoh: 7.5">

            <label>Aktivitas Fisik</label>
            <input type="text" name="aktivitas_fisik" required placeholder="Contoh: Jogging 30 menit, Jalan pagi">

            <label>Asupan Air (Liter)</label>
            <input type="number" name="asupan_air" step="0.1" min="0" required placeholder="Contoh: 2.0">

            <label>Keluhan (Opsional)</label>
            <textarea name="keluhan" rows="3" placeholder="Tulis keluhan jika ada, misalnya: sedikit pusing..."></textarea>

            <div class="form-actions">
                <button type="submit">Simpan Catatan</button>
                <a class="button secondary" href="index.php">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php require_once "../../include/footer.php"; ?>
</body>
</html>