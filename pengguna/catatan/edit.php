<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("pengguna");

$user_id    = $_SESSION["user_id"];
$catatan_id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
$error      = "";


$query = "SELECT * FROM catatan_kesehatan WHERE catatan_id = ? AND user_id = ?";
$stmt  = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "ii", $catatan_id, $user_id);
mysqli_stmt_execute($stmt);
$catatan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$catatan) {
    die("Catatan kesehatan tidak ditemukan.");
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $tanggal_catatan = trim($_POST["tanggal_catatan"] ?? "");
    $durasi_tidur    = isset($_POST["durasi_tidur"]) ? (float) $_POST["durasi_tidur"] : 0;
    $aktivitas_fisik = trim($_POST["aktivitas_fisik"] ?? "");
    $asupan_air      = isset($_POST["asupan_air"]) ? (float) $_POST["asupan_air"] : 0;
    $keluhan         = trim($_POST["keluhan"] ?? "");

    if ($tanggal_catatan === "" || $aktivitas_fisik === "" || $durasi_tidur <= 0 || $asupan_air < 0) {
        $error = "Tanggal, durasi tidur, aktivitas fisik, dan asupan air wajib diisi dengan benar.";
    } else {
        $query_update = "
            UPDATE catatan_kesehatan
            SET tanggal_catatan = ?, durasi_tidur = ?, aktivitas_fisik = ?, asupan_air = ?, keluhan = ?
            WHERE catatan_id = ? AND user_id = ?
        ";
        $stmt_update = mysqli_prepare($koneksi, $query_update);
        mysqli_stmt_bind_param(
            $stmt_update,
            "sdsdsii",
            $tanggal_catatan,
            $durasi_tidur,
            $aktivitas_fisik,
            $asupan_air,
            $keluhan,
            $catatan_id,
            $user_id
        );
        mysqli_stmt_execute($stmt_update);

        header("Location: index.php?pesan=" . urlencode("Catatan kesehatan berhasil diperbarui."));
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Catatan Kesehatan - Sehatku</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php require_once "../../include/header.php"; ?>

<div class="container">
    <a class="back-button" href="index.php">← Kembali ke Daftar Catatan</a>

    <h1>Edit Catatan Kesehatan</h1>

    <?php if ($error !== ""): ?>
        <div class="alert danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <form method="POST">
            <label>Tanggal</label>
            <input type="date" name="tanggal_catatan" value="<?= htmlspecialchars($catatan["tanggal_catatan"]) ?>" required>

            <label>Durasi Tidur (jam)</label>
            <input type="number" name="durasi_tidur" step="0.5" min="0.5" max="24" value="<?= $catatan["durasi_tidur"] ?>" required>

            <label>Aktivitas Fisik</label>
            <input type="text" name="aktivitas_fisik" value="<?= htmlspecialchars($catatan["aktivitas_fisik"]) ?>" required>

            <label>Asupan Air (Liter)</label>
            <input type="number" name="asupan_air" step="0.1" min="0" value="<?= $catatan["asupan_air"] ?>" required>

            <label>Keluhan (Opsional)</label>
            <textarea name="keluhan" rows="3"><?= htmlspecialchars($catatan["keluhan"] ?? "") ?></textarea>

            <div class="form-actions">
                <button type="submit">Simpan Perubahan</button>
                <a class="button secondary" href="index.php">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php require_once "../../include/footer.php"; ?>
</body>
</html>