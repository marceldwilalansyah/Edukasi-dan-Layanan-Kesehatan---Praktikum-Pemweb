<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";
require_once "../../include/fungsi.php";

cekRole("pengguna");

$user_id = $_SESSION["user_id"];
$kuis_id = isset($_GET["id"]) ? (int) $_GET["id"] : 0;

$stmt_kuis = mysqli_prepare($koneksi, "SELECT * FROM kuis WHERE kuis_id = ?");
mysqli_stmt_bind_param($stmt_kuis, "i", $kuis_id);
mysqli_stmt_execute($stmt_kuis);
$kuis = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_kuis));

if (!$kuis) {
    die("Kuis tidak ditemukan.");
}

$stmt_soal = mysqli_prepare($koneksi, "SELECT * FROM pertanyaan_kuis WHERE kuis_id = ? ORDER BY pertanyaan_id ASC");
mysqli_stmt_bind_param($stmt_soal, "i", $kuis_id);
mysqli_stmt_execute($stmt_soal);
$res_soal = mysqli_stmt_get_result($stmt_soal);

$daftar_soal = [];
while ($row = mysqli_fetch_assoc($res_soal)) {
    $daftar_soal[] = $row;
}

if (count($daftar_soal) === 0) {
    die("Belum ada soal pada kuis ini.");
}

$hasil        = null;
$jawaban_user = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $jawaban_user = $_POST["jawaban"] ?? [];
    $jumlah_benar = 0;
    $total_soal   = count($daftar_soal);

    foreach ($daftar_soal as $soal) {
        $id_soal = $soal["pertanyaan_id"];
        if (isset($jawaban_user[$id_soal]) && $jawaban_user[$id_soal] === $soal["jawaban_benar"]) {
            $jumlah_benar++;
        }
    }

    $skor = (int) round(($jumlah_benar / $total_soal) * 100);

    
    $tingkat = tingkatBadge($skor);
    $badge   = labelBadge($tingkat);

   
    [$skor_min, $skor_maks] = rentangBadge($tingkat);
    $stmt_cek = mysqli_prepare(
        $koneksi,
        "SELECT COUNT(*) AS total FROM riwayat_kuis WHERE user_id = ? AND skor BETWEEN ? AND ?"
    );
    mysqli_stmt_bind_param($stmt_cek, "iii", $user_id, $skor_min, $skor_maks);
    mysqli_stmt_execute($stmt_cek);
    $badge_baru = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_cek))["total"] === 0;

    $stmt_simpan = mysqli_prepare(
        $koneksi,
        "INSERT INTO riwayat_kuis (user_id, kuis_id, skor, jumlah_benar, total_soal) VALUES (?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt_simpan, "iiiii", $user_id, $kuis_id, $skor, $jumlah_benar, $total_soal);
    mysqli_stmt_execute($stmt_simpan);

   
    if ($badge_baru) {
        buatNotifikasi(
            $koneksi,
            $user_id,
            "badge",
            "Badge baru!",
            "Kamu mendapat badge " . namaBadge($tingkat) . " dari kuis \"" . $kuis["judul"] . "\" dengan skor " . $skor . ".",
            "pengguna/kuis/index.php"
        );
    }

    $hasil = [
        "skor"         => $skor,
        "jumlah_benar" => $jumlah_benar,
        "total_soal"   => $total_soal,
        "badge"        => $badge
    ];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($kuis["judul"]) ?> - Sehatku</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php require_once "../../include/header.php"; ?>

<div class="container">
    <a class="back-button" href="index.php">← Kembali ke Daftar Kuis</a>

    <h1><?= htmlspecialchars($kuis["judul"]) ?></h1>
    <p><?= htmlspecialchars($kuis["deskripsi"] ?? "") ?></p>

    <?php if ($hasil !== null): ?>
        <div class="card" style="margin-bottom: 20px; border-left: 5px solid var(--primary, #2b8a3e);">
            <h2 style="margin-top: 0;">Hasil Kuis Anda</h2>
            <p>Jawaban Benar: <strong><?= $hasil["jumlah_benar"] ?> dari <?= $hasil["total_soal"] ?> soal</strong></p>
            <p>Skor Akhir: <strong><?= $hasil["skor"] ?> / 100</strong></p>
            <p>Badge Diperoleh: <strong><?= $hasil["badge"] ?></strong></p>

            <div class="form-actions">
                <a class="button" href="index.php">Lihat Riwayat Kuis</a>
                <a class="button secondary" href="kerjakan.php?id=<?= $kuis_id ?>">Ulangi Kuis</a>
            </div>
        </div>

        <h2>Pembahasan Jawaban</h2>
        <?php foreach ($daftar_soal as $index => $soal): ?>
            <?php
                $id_soal   = $soal["pertanyaan_id"];
                $jawab_usr = $jawaban_user[$id_soal] ?? "-";
                $is_benar  = ($jawab_usr === $soal["jawaban_benar"]);
            ?>
            <div class="card" style="margin-bottom: 14px; border-left: 4px solid <?= $is_benar ? '#2b8a3e' : '#c92a2a' ?>;">
                <p style="font-weight: bold; margin-top: 0;">
                    <?= ($index + 1) . ". " . htmlspecialchars($soal["pertanyaan"]) ?>
                </p>
                <p style="margin: 4px 0;">
                    Jawaban Anda: <strong><?= htmlspecialchars($jawab_usr) ?></strong>
                    (<?= $is_benar ? '<span style="color:#2b8a3e;">Benar</span>' : '<span style="color:#c92a2a;">Salah</span>' ?>)
                    | Kunci Jawaban: <strong><?= $soal["jawaban_benar"] ?></strong>
                </p>
                <?php if (!empty($soal["pembahasan"])): ?>
                    <p style="margin: 6px 0 0 0; color: #444;">
                        <strong>Pembahasan:</strong> <?= htmlspecialchars($soal["pembahasan"]) ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <form method="POST">
            <?php foreach ($daftar_soal as $index => $soal): ?>
                <div class="card" style="margin-bottom: 16px;">
                    <p style="font-weight: bold; margin-top: 0;">
                        <?= ($index + 1) . ". " . htmlspecialchars($soal["pertanyaan"]) ?>
                    </p>

                    <label style="display: block; margin-bottom: 6px; font-weight: normal;">
                        <input type="radio" name="jawaban[<?= $soal["pertanyaan_id"] ?>]" value="A" required>
                        A. <?= htmlspecialchars($soal["opsi_a"]) ?>
                    </label>
                    <label style="display: block; margin-bottom: 6px; font-weight: normal;">
                        <input type="radio" name="jawaban[<?= $soal["pertanyaan_id"] ?>]" value="B">
                        B. <?= htmlspecialchars($soal["opsi_b"]) ?>
                    </label>
                    <label style="display: block; margin-bottom: 6px; font-weight: normal;">
                        <input type="radio" name="jawaban[<?= $soal["pertanyaan_id"] ?>]" value="C">
                        C. <?= htmlspecialchars($soal["opsi_c"]) ?>
                    </label>
                    <label style="display: block; margin-bottom: 6px; font-weight: normal;">
                        <input type="radio" name="jawaban[<?= $soal["pertanyaan_id"] ?>]" value="D">
                        D. <?= htmlspecialchars($soal["opsi_d"]) ?>
                    </label>
                </div>
            <?php endforeach; ?>

            <div class="form-actions">
                <button type="submit">Kirim Jawaban</button>
                <a class="button secondary" href="index.php">Batal</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once "../../include/footer.php"; ?>
</body>
</html>