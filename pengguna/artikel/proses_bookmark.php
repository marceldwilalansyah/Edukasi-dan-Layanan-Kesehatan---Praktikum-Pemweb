<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("pengguna");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$user_id    = $_SESSION["user_id"];
$article_id = isset($_POST["article_id"]) ? (int) $_POST["article_id"] : 0;
$aksi       = $_POST["aksi"] ?? "";
$tujuan     = $_POST["tujuan"] ?? "detail";

if ($article_id <= 0) {
    header("Location: index.php");
    exit;
}

if ($aksi === "simpan") {
    $query = "INSERT IGNORE INTO bookmark_artikel (user_id, article_id) VALUES (?, ?)";
    $stmt  = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $article_id);
    mysqli_stmt_execute($stmt);
    $pesan = "Artikel berhasil disimpan ke bookmark.";
} elseif ($aksi === "hapus") {
    $query = "DELETE FROM bookmark_artikel WHERE user_id = ? AND article_id = ?";
    $stmt  = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $article_id);
    mysqli_stmt_execute($stmt);
    $pesan = "Artikel berhasil dihapus dari bookmark.";
} else {
    $pesan = "Aksi tidak valid.";
}

if ($tujuan === "bookmark") {
    header("Location: bookmark.php?pesan=" . urlencode($pesan));
} else {
    header("Location: detail.php?id=" . $article_id . "&pesan=" . urlencode($pesan));
}
exit;