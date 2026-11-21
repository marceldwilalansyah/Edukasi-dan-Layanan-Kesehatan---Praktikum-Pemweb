<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("admin");

$kuis_id = isset($_GET["kuis_id"]) ? (int) $_GET["kuis_id"] : 0;
$error   = "";

$stmt_kuis = mysqli_prepare($koneksi, "SELECT * FROM kuis WHERE kuis_id = ?");
mysqli_stmt_bind_param($stmt_kuis, "i", $kuis_id);
mysqli_stmt_execute($stmt_kuis);
$kuis = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_kuis));

if (!$kuis) {
    die("Kuis tidak ditemukan.");
}

// Tambah pertanyaan baru
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $pertanyaan    = trim($_POST["pertanyaan"] ?? "");
    $opsi_a        = trim($_POST["opsi_a"] ?? "");
    $opsi_b        = trim($_POST["opsi_b"] ?? "");
    $opsi_c        = trim($_POST["opsi_c"] ?? "");
    $opsi_d        = trim($_POST["opsi_d"] ?? "");
    $jawaban_benar = $_POST["jawaban_benar"] ?? "";
    $pembahasan    = trim($_POST["pembahasan"] ?? "");

    if ($pertanyaan === "" || $opsi_a === "" || $opsi_b === "" || $opsi_c === "" || $opsi_d === "" || !in_array($jawaban_benar, ["A", "B", "C", "D"])) {
        $error = "Pertanyaan, semua pilihan (A–D), dan kunci jawaban wajib diisi.";
    } else {
        $query_insert = "
            INSERT INTO pertanyaan_kuis
                (kuis_id, pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, jawaban_benar, pembahasan)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt_insert = mysqli_prepare($koneksi, $query_insert);
        mysqli_stmt_bind_param(
            $stmt_insert,
            "isssssss",
            $kuis_id,
            $pertanyaan,
            $opsi_a,
            $opsi_b,
            $opsi_c,
            $opsi_d,
            $jawaban_benar,
            $pembahasan
        );
        mysqli_stmt_execute($stmt_insert);

        header("Location: soal.php?kuis_id=" . $kuis_id . "&pesan=" . urlencode("Pertanyaan berhasil ditambahkan."));
        exit;
    }
}

// Ambil semua soal pada kuis ini
$stmt_soal = mysqli_prepare($koneksi, "SELECT * FROM pertanyaan_kuis WHERE kuis_id = ? ORDER BY pertanyaan_id ASC");
mysqli_stmt_bind_param($stmt_soal, "i", $kuis_id);
mysqli_stmt_execute($stmt_soal);
$daftar_soal = mysqli_stmt_get_result($stmt_soal);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Soal - <?= htmlspecialchars($kuis["judul"]) ?></title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php require_once "../../include/header.php"; ?>

<div class="container">
    <a class="back-button" href="index.php">← Kembali ke Daftar Kuis</a>

    <h1>Kelola Soal: <?= htmlspecialchars($kuis["judul"]) ?></h1>

    <?php if (isset($_GET["pesan"])): ?>
        <div class="alert success"><?= htmlspecialchars($_GET["pesan"]) ?></div>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <div class="alert danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="card" style="margin-bottom: 20px;">
        <h2 style="margin-top: 0;">Tambah Pertanyaan Baru</h2>
        <form method="POST">
            <label>Pertanyaan</label>
            <textarea name="pertanyaan" rows="2" required placeholder="Tulis pertanyaan kuis..."></textarea>

            <label>Opsi A</label>
            <input type="text" name="opsi_a" required>

            <label>Opsi B</label>
            <input type="text" name="opsi_b" required>

            <label>Opsi C</label>
            <input type="text" name="opsi_c" required>

            <label>Opsi D</label>
            <input type="text" name="opsi_d" required>

            <label>Kunci Jawaban Benar</label>
            <select name="jawaban_benar" required>
                <option value="">-- Pilih Jawaban Benar --</option>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="C">C</option>
                <option value="D">D</option>
            </select>

            <label>Pembahasan (Ditampilkan setelah pengguna selesai mengerjakan)</label>
            <textarea name="pembahasan" rows="2" placeholder="Jelaskan alasan mengapa jawaban tersebut benar..."></textarea>

            <div class="form-actions">
                <button type="submit">+ Simpan Soal</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2 style="margin-top: 0;">Daftar Soal (<?= mysqli_num_rows($daftar_soal) ?>)</h2>

        <?php if (mysqli_num_rows($daftar_soal) === 0): ?>
            <p>Belum ada pertanyaan pada kuis ini.</p>
        <?php else: ?>
            <?php $no = 1; while ($s = mysqli_fetch_assoc($daftar_soal)): ?>
                <div style="border-bottom: 1px solid #e5e7eb; padding: 14px 0;">
                    <p style="margin: 0 0 8px 0;">
                        <strong><?= $no++ ?>. <?= htmlspecialchars($s["pertanyaan"]) ?></strong>
                    </p>
                    <ul style="margin: 0 0 8px 20px;">
                        <li>A. <?= htmlspecialchars($s["opsi_a"]) ?></li>
                        <li>B. <?= htmlspecialchars($s["opsi_b"]) ?></li>
                        <li>C. <?= htmlspecialchars($s["opsi_c"]) ?></li>
                        <li>D. <?= htmlspecialchars($s["opsi_d"]) ?></li>
                    </ul>
                    <p style="margin: 0 0 6px 0; color: #2b8a3e;">
                        <strong>Kunci Jawaban: <?= $s["jawaban_benar"] ?></strong>
                    </p>
                    <?php if (!empty($s["pembahasan"])): ?>
                        <p style="margin: 0 0 10px 0; color: #555; font-size: 0.95em;">
                            <em>Pembahasan: <?= htmlspecialchars($s["pembahasan"]) ?></em>
                        </p>
                    <?php endif; ?>

                    <form action="hapus_soal.php" method="POST" style="margin: 0;">
                        <input type="hidden" name="pertanyaan_id" value="<?= $s["pertanyaan_id"] ?>">
                        <input type="hidden" name="kuis_id" value="<?= $kuis_id ?>">
                        <button
                            type="submit"
                            class="button danger"
                            onclick="return confirm('Hapus soal ini?')"
                        >
                            Hapus Soal
                        </button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once "../../include/footer.php"; ?>
</body>
</html>