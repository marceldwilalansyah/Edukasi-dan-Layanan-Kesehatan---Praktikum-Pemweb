<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("pengguna");

$kata_kunci  = isset($_GET["search"]) ? trim($_GET["search"]) : "";
$kategori_id = isset($_GET["category"]) ? (int) $_GET["category"] : 0;
$halaman     = isset($_GET["halaman"]) ? (int) $_GET["halaman"] : 1;
if ($halaman < 1) {
    $halaman = 1;
}

$batas = 5;
$mulai = ($halaman - 1) * $batas;


$daftar_kategori = mysqli_query($koneksi, "SELECT * FROM categories ORDER BY category_name");


$kondisi = "WHERE a.status = 'terbit'";
$params  = [];
$types   = "";

if ($kata_kunci !== "") {
    $kondisi .= " AND (a.title LIKE ? OR a.content LIKE ?)";
    $cari_param = "%" . $kata_kunci . "%";
    $params[] = $cari_param;
    $params[] = $cari_param;
    $types .= "ss";
}

if ($kategori_id > 0) {
    $kondisi .= " AND a.category_id = ?";
    $params[] = $kategori_id;
    $types .= "i";
}


$query_total = "SELECT COUNT(*) AS total FROM articles a $kondisi";
$stmt_total = mysqli_prepare($koneksi, $query_total);

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt_total, $types, ...$params);
}

mysqli_stmt_execute($stmt_total);
$hasil_total   = mysqli_stmt_get_result($stmt_total);
$total_data    = mysqli_fetch_assoc($hasil_total)["total"];
$total_halaman = ceil($total_data / $batas);

$query = "
    SELECT
        a.article_id,
        a.title,
        a.content,
        a.created_at,
        c.category_name
    FROM articles a
    JOIN categories c ON a.category_id = c.category_id
    $kondisi
    ORDER BY a.created_at DESC
    LIMIT ?, ?
";

$params_data = $params;
$params_data[] = $mulai;
$params_data[] = $batas;
$types_data = $types . "ii";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, $types_data, ...$params_data);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artikel Edukasi - Sehatku</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php require_once "../../include/header.php"; ?>

<div class="container">
    <a class="back-button" href="../dashboard.php">← Kembali ke Dashboard</a>

    <h1>Artikel Edukasi</h1>

    <div class="page-actions">
        <a class="button secondary" href="bookmark.php">★ Artikel Tersimpan (Bookmark)</a>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <form method="GET">
            <label>Cari Artikel (Judul atau Kata Kunci)</label>
            <input
                type="text"
                name="search"
                value="<?= htmlspecialchars($kata_kunci) ?>"
                placeholder="Contoh: nutrisi, tidur, olahraga..."
            >

            <label>Filter Kategori</label>
            <select name="category">
                <option value="0">Semua Kategori</option>
                <?php while ($kategori = mysqli_fetch_assoc($daftar_kategori)): ?>
                    <option
                        value="<?= $kategori["category_id"] ?>"
                        <?= $kategori_id == $kategori["category_id"] ? "selected" : "" ?>
                    >
                        <?= htmlspecialchars($kategori["category_name"]) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <div class="form-actions">
                <button type="submit">Cari</button>
                <?php if ($kata_kunci !== "" || $kategori_id > 0): ?>
                    <a class="button secondary" href="index.php">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <?php if (mysqli_num_rows($result) === 0): ?>
        <div class="card">
            <p>Tidak ada artikel yang ditemukan.</p>
        </div>
    <?php endif; ?>

    <?php while ($artikel = mysqli_fetch_assoc($result)): ?>
        <div class="card" style="margin-bottom: 16px;">
            <h2 style="margin-top: 0;">
                <?= htmlspecialchars($artikel["title"]) ?>
            </h2>
            <small style="color: var(--muted);">
                Kategori: <strong><?= htmlspecialchars($artikel["category_name"]) ?></strong> |
                <?= htmlspecialchars($artikel["created_at"]) ?>
            </small>
            <p>
                <?= htmlspecialchars(substr($artikel["content"], 0, 200)) ?>...
            </p>
            <a class="button" href="detail.php?id=<?= $artikel["article_id"] ?>">
                Baca Selengkapnya
            </a>
        </div>
    <?php endwhile; ?>

    <?php if ($total_halaman > 1): ?>
        <div class="page-actions" style="justify-content: center; margin-top: 25px;">
            <?php if ($halaman > 1): ?>
                <a class="button secondary" href="?search=<?= urlencode($kata_kunci) ?>&category=<?= $kategori_id ?>&halaman=<?= $halaman - 1 ?>">
                    ← Sebelumnya
                </a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                <a
                    class="button <?= $i == $halaman ? '' : 'secondary' ?>"
                    href="?search=<?= urlencode($kata_kunci) ?>&category=<?= $kategori_id ?>&halaman=<?= $i ?>"
                >
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($halaman < $total_halaman): ?>
                <a class="button secondary" href="?search=<?= urlencode($kata_kunci) ?>&category=<?= $kategori_id ?>&halaman=<?= $halaman + 1 ?>">
                    Selanjutnya →
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once "../../include/footer.php"; ?>
</body>
</html>