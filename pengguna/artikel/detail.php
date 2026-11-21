<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("pengguna");

$id      = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
$user_id = $_SESSION["user_id"];

$query = "
    SELECT
        a.article_id,
        a.title,
        a.content,
        a.created_at,
        c.category_name,
        u.username AS author
    FROM articles a
    JOIN categories c ON a.category_id = c.category_id
    JOIN users u ON a.author_id = u.user_id
    WHERE a.article_id = ?
    AND a.status = 'terbit'
";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$artikel = mysqli_fetch_assoc($result);

if (!$artikel) {
    die("Artikel tidak ditemukan.");
}

// Cek apakah artikel ini sudah di-bookmark oleh pengguna
$cek_query = "SELECT bookmark_id FROM bookmark_artikel WHERE user_id = ? AND article_id = ?";
$cek_stmt  = mysqli_prepare($koneksi, $cek_query);
mysqli_stmt_bind_param($cek_stmt, "ii", $user_id, $id);
mysqli_stmt_execute($cek_stmt);
$sudah_bookmark = mysqli_num_rows(mysqli_stmt_get_result($cek_stmt)) > 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($artikel["title"]) ?> - Sehatku</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php require_once "../../include/header.php"; ?>

<div class="container">
    <a class="back-button" href="index.php">← Kembali ke Artikel</a>

    <?php if (isset($_GET["pesan"])): ?>
        <div class="alert success">
            <?= htmlspecialchars($_GET["pesan"]) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h1 style="margin-top: 0;"><?= htmlspecialchars($artikel["title"]) ?></h1>

        <p style="color: var(--muted);">
            Kategori: <strong><?= htmlspecialchars($artikel["category_name"]) ?></strong><br>
            Penulis: <strong><?= htmlspecialchars($artikel["author"]) ?></strong><br>
            Tanggal: <?= htmlspecialchars($artikel["created_at"]) ?>
        </p>

        <form action="proses_bookmark.php" method="POST" style="margin-bottom: 20px;">
            <input type="hidden" name="article_id" value="<?= $artikel["article_id"] ?>">
            <input type="hidden" name="tujuan" value="detail">

            <?php if ($sudah_bookmark): ?>
                <input type="hidden" name="aksi" value="hapus">
                <button type="submit" class="button danger">★ Hapus dari Bookmark</button>
            <?php else: ?>
                <input type="hidden" name="aksi" value="simpan">
                <button type="submit" class="button secondary">☆ Simpan ke Bookmark</button>
            <?php endif; ?>
        </form>

        <hr style="border: 0; border-top: 1px solid var(--line); margin: 20px 0;">

        <div style="line-height: 1.8;">
            <?= nl2br(htmlspecialchars($artikel["content"])) ?>
        </div>
    </div>
</div>

<?php require_once "../../include/footer.php"; ?>
</body>
</html>