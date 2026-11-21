<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("pengguna");

$user_id = $_SESSION["user_id"];

$query = "
    SELECT
        b.tanggal_simpan,
        a.article_id,
        a.title,
        a.content,
        c.category_name
    FROM bookmark_artikel b
    JOIN articles a ON b.article_id = a.article_id
    JOIN categories c ON a.category_id = c.category_id
    WHERE b.user_id = ?
    AND a.status = 'terbit'
    ORDER BY b.tanggal_simpan DESC
";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artikel Tersimpan - Sehatku</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php require_once "../../include/header.php"; ?>

<div class="container">
    <a class="back-button" href="index.php">← Kembali ke Daftar Artikel</a>

    <h1>Artikel Tersimpan (Bookmark)</h1>

    <?php if (isset($_GET["pesan"])): ?>
        <div class="alert success">
            <?= htmlspecialchars($_GET["pesan"]) ?>
        </div>
    <?php endif; ?>

    <?php if (mysqli_num_rows($result) === 0): ?>
        <div class="card">
            <p>Belum ada artikel yang Anda simpan.</p>
            <a class="button" href="index.php">Jelajahi Artikel</a>
        </div>
    <?php endif; ?>

    <?php while ($artikel = mysqli_fetch_assoc($result)): ?>
        <div class="card" style="margin-bottom: 16px;">
            <h2 style="margin-top: 0;">
                <?= htmlspecialchars($artikel["title"]) ?>
            </h2>
            <small style="color: var(--muted);">
                Kategori: <strong><?= htmlspecialchars($artikel["category_name"]) ?></strong> |
                Disimpan pada: <?= htmlspecialchars($artikel["tanggal_simpan"]) ?>
            </small>
            <p>
                <?= htmlspecialchars(substr($artikel["content"], 0, 200)) ?>...
            </p>

            <div class="form-actions">
                <a class="button" href="detail.php?id=<?= $artikel["article_id"] ?>">
                    Baca Selengkapnya
                </a>

                <form action="proses_bookmark.php" method="POST" style="margin: 0;">
                    <input type="hidden" name="article_id" value="<?= $artikel["article_id"] ?>">
                    <input type="hidden" name="aksi" value="hapus">
                    <input type="hidden" name="tujuan" value="bookmark">
                    <button type="submit" class="button danger" onclick="return confirm('Hapus artikel ini dari bookmark?')">
                        Hapus Bookmark
                    </button>
                </form>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<?php require_once "../../include/footer.php"; ?>
</body>
</html>