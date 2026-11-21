<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("admin");

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $judul     = trim($_POST["judul"] ?? "");
    $deskripsi = trim($_POST["deskripsi"] ?? "");

    if ($judul === "") {
        $error = "Judul kuis wajib diisi.";
    } else {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO kuis (judul, deskripsi) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "ss", $judul, $deskripsi);
        mysqli_stmt_execute($stmt);

        $kuis_baru_id = mysqli_insert_id($koneksi);
        header("Location: soal.php?kuis_id=" . $kuis_baru_id . "&pesan=" . urlencode("Kuis berhasil dibuat. Silakan tambahkan soal."));
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kuis - Admin Sehatku</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php require_once "../../include/header.php"; ?>

<div class="container">
    <a class="back-button" href="index.php">← Kembali ke Daftar Kuis</a>

    <h1>Tambah Kuis Kesehatan</h1>

    <?php if ($error !== ""): ?>
        <div class="alert danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="POST">
            <label>Judul Kuis</label>
            <input type="text" name="judul" required placeholder="Contoh: Kuis Nutrisi & Gizi Seimbang">

            <label>Deskripsi Singkat</label>
            <textarea name="deskripsi" rows="3" placeholder="Jelaskan topik kuis ini..."></textarea>

            <div class="form-actions">
                <button type="submit">Simpan & Lanjut Tambah Soal</button>
                <a class="button secondary" href="index.php">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php require_once "../../include/footer.php"; ?>
</body>
</html>