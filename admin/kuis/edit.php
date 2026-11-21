<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("admin");

$kuis_id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
$error   = "";

$stmt = mysqli_prepare($koneksi, "SELECT * FROM kuis WHERE kuis_id = ?");
mysqli_stmt_bind_param($stmt, "i", $kuis_id);
mysqli_stmt_execute($stmt);
$kuis = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$kuis) {
    die("Kuis tidak ditemukan.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $judul     = trim($_POST["judul"] ?? "");
    $deskripsi = trim($_POST["deskripsi"] ?? "");

    if ($judul === "") {
        $error = "Judul kuis wajib diisi.";
    } else {
        $stmt_update = mysqli_prepare($koneksi, "UPDATE kuis SET judul = ?, deskripsi = ? WHERE kuis_id = ?");
        mysqli_stmt_bind_param($stmt_update, "ssi", $judul, $deskripsi, $kuis_id);
        mysqli_stmt_execute($stmt_update);

        header("Location: index.php?pesan=" . urlencode("Data kuis berhasil diperbarui."));
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kuis - Admin Sehatku</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php require_once "../../include/header.php"; ?>

<div class="container">
    <a class="back-button" href="index.php">← Kembali ke Daftar Kuis</a>

    <h1>Edit Kuis</h1>

    <?php if ($error !== ""): ?>
        <div class="alert danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="POST">
            <label>Judul Kuis</label>
            <input type="text" name="judul" value="<?= htmlspecialchars($kuis["judul"]) ?>" required>

            <label>Deskripsi Singkat</label>
            <textarea name="deskripsi" rows="3"><?= htmlspecialchars($kuis["deskripsi"] ?? "") ?></textarea>

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