<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";
require_once "../../include/fungsi.php";

cekRole("admin");

$error        = "";
$username_baru     = "";
$nama_lengkap = "";
$spesialisasi = "";
$bio          = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username_baru     = trim($_POST["username"] ?? "");
    $password     = $_POST["password"] ?? "";
    $nama_lengkap = trim($_POST["nama_lengkap"] ?? "");
    $spesialisasi = trim($_POST["spesialisasi"] ?? "");
    $bio          = trim($_POST["bio"] ?? "");

    if ($username_baru === "" || $nama_lengkap === "" || $spesialisasi === "") {
        $error = "Username, nama lengkap, dan spesialisasi wajib diisi.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } else {
        $stmt = mysqli_prepare($koneksi, "SELECT user_id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username_baru);
        mysqli_stmt_execute($stmt);

        if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) {
            $error = "Username sudah digunakan.";
        }
    }

    if ($error === "") {
        $role_nakes = mysqli_fetch_assoc(
            mysqli_query($koneksi, "SELECT role_id FROM roles WHERE role_name = 'nakes'")
        );
        $role_id       = (int) $role_nakes["role_id"];
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        mysqli_begin_transaction($koneksi);

        try {
            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO users (role_id, username, password) VALUES (?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt, "iss", $role_id, $username_baru, $password_hash);
            mysqli_stmt_execute($stmt);

            $user_id = mysqli_insert_id($koneksi);

            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO user_profiles (user_id, nama_lengkap, spesialisasi, bio) VALUES (?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt, "isss", $user_id, $nama_lengkap, $spesialisasi, $bio);
            mysqli_stmt_execute($stmt);

            mysqli_commit($koneksi);

            header("Location: index.php?pesan=" . urlencode("Tenaga kesehatan berhasil ditambahkan."));
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
    <title>Tambah Tenaga Kesehatan - Sehatku</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php require_once "../../include/header.php"; ?>

<div class="container auth-container">
    <a class="back-button" href="index.php">← Kembali ke Daftar Nakes</a>

    <h1>Tambah Tenaga Kesehatan</h1>

    <?php if ($error !== ""): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" value="<?= e($username_baru) ?>" required>

        <label>Password</label>
        <input type="password" name="password" minlength="6" required placeholder="Minimal 6 karakter">

        <label>Nama Lengkap</label>
        <input type="text" name="nama_lengkap" value="<?= e($nama_lengkap) ?>" required placeholder="Contoh: dr. Sinta Maharani">

        <label>Spesialisasi</label>
        <input type="text" name="spesialisasi" value="<?= e($spesialisasi) ?>" required placeholder="Contoh: Dokter Umum, Ahli Gizi">

        <label>Bio</label>
        <textarea name="bio" rows="4" style="min-height: 110px;" placeholder="Pengalaman singkat dan bidang yang ditangani"><?= e($bio) ?></textarea>

        <div class="form-actions">
            <button type="submit">Simpan</button>
            <a class="button secondary" href="index.php">Batal</a>
        </div>
    </form>
</div>

<?php require_once "../../include/footer.php"; ?>
</body>
</html>
