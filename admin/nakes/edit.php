<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";
require_once "../../include/fungsi.php";

cekRole("admin");

$id = isset($_GET["id"]) ? (int) $_GET["id"] : (int) ($_POST["user_id"] ?? 0);

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT u.user_id, u.username, u.status, p.nama_lengkap, p.spesialisasi, p.bio
     FROM users u
     JOIN roles r ON u.role_id = r.role_id
     LEFT JOIN user_profiles p ON u.user_id = p.user_id
     WHERE u.user_id = ? AND r.role_name = 'nakes'"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$nakes = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$nakes) {
    header("Location: index.php?error=" . urlencode("Tenaga kesehatan tidak ditemukan."));
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama_lengkap = trim($_POST["nama_lengkap"] ?? "");
    $spesialisasi = trim($_POST["spesialisasi"] ?? "");
    $bio          = trim($_POST["bio"] ?? "");
    $status       = ($_POST["status"] ?? "") === "nonaktif" ? "nonaktif" : "aktif";
    $password     = $_POST["password"] ?? "";

    // Tampilkan kembali isian terbaru kalau ada error
    $nakes["nama_lengkap"] = $nama_lengkap;
    $nakes["spesialisasi"] = $spesialisasi;
    $nakes["bio"]          = $bio;
    $nakes["status"]       = $status;

    if ($nama_lengkap === "" || $spesialisasi === "") {
        $error = "Nama lengkap dan spesialisasi wajib diisi.";
    } elseif ($password !== "" && strlen($password) < 6) {
        $error = "Password baru minimal 6 karakter.";
    } else {
        mysqli_begin_transaction($koneksi);

        try {
            // user_id pada user_profiles UNIQUE, jadi baris profil yang belum ada ikut dibuat
            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO user_profiles (user_id, nama_lengkap, spesialisasi, bio)
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                    nama_lengkap = ?, spesialisasi = ?, bio = ?"
            );
            mysqli_stmt_bind_param(
                $stmt, "issssss",
                $id, $nama_lengkap, $spesialisasi, $bio,
                $nama_lengkap, $spesialisasi, $bio
            );
            mysqli_stmt_execute($stmt);

            $stmt = mysqli_prepare($koneksi, "UPDATE users SET status = ? WHERE user_id = ?");
            mysqli_stmt_bind_param($stmt, "si", $status, $id);
            mysqli_stmt_execute($stmt);

            if ($password !== "") {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = mysqli_prepare($koneksi, "UPDATE users SET password = ? WHERE user_id = ?");
                mysqli_stmt_bind_param($stmt, "si", $password_hash, $id);
                mysqli_stmt_execute($stmt);
            }

            mysqli_commit($koneksi);

            header("Location: index.php?pesan=" . urlencode("Data tenaga kesehatan berhasil diperbarui."));
            exit;
        } catch (mysqli_sql_exception $ex) {
            mysqli_rollback($koneksi);
            $error = "Data gagal disimpan. Coba lagi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tenaga Kesehatan - Sehatku</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php require_once "../../include/header.php"; ?>

<div class="container auth-container">
    <a class="back-button" href="index.php">← Kembali ke Daftar Nakes</a>

    <h1>Edit Tenaga Kesehatan</h1>

    <?php if ($error !== ""): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="user_id" value="<?= (int) $nakes["user_id"] ?>">

        <label>Username</label>
        <input type="text" value="<?= e($nakes["username"]) ?>" disabled>

        <label>Nama Lengkap</label>
        <input type="text" name="nama_lengkap" value="<?= e($nakes["nama_lengkap"] ?? "") ?>" required>

        <label>Spesialisasi</label>
        <input type="text" name="spesialisasi" value="<?= e($nakes["spesialisasi"] ?? "") ?>" required>

        <label>Bio</label>
        <textarea name="bio" rows="4" style="min-height: 110px;"><?= e($nakes["bio"] ?? "") ?></textarea>

        <label>Password Baru</label>
        <input type="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah">

        <label>Status Akun</label>
        <select name="status">
            <option value="aktif" <?= $nakes["status"] === "aktif" ? "selected" : "" ?>>Aktif</option>
            <option value="nonaktif" <?= $nakes["status"] === "nonaktif" ? "selected" : "" ?>>Nonaktif</option>
        </select>

        <div class="form-actions">
            <button type="submit">Simpan Perubahan</button>
            <a class="button secondary" href="index.php">Batal</a>
        </div>
    </form>
</div>

<?php require_once "../../include/footer.php"; ?>
</body>
</html>
