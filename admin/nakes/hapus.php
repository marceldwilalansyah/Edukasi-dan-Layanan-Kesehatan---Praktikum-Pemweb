<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("admin");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$user_id = isset($_POST["user_id"]) ? (int) $_POST["user_id"] : 0;

// Hanya akun dengan peran nakes yang boleh dihapus lewat halaman ini
$stmt = mysqli_prepare(
    $koneksi,
    "DELETE u FROM users u
     JOIN roles r ON u.role_id = r.role_id
     WHERE u.user_id = ? AND r.role_name = 'nakes'"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

if (mysqli_stmt_affected_rows($stmt) > 0) {
    header("Location: index.php?pesan=" . urlencode("Tenaga kesehatan berhasil dihapus."));
} else {
    header("Location: index.php?error=" . urlencode("Data tidak ditemukan."));
}
exit;
