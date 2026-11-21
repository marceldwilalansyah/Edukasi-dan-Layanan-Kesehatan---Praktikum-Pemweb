<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";

cekRole("pengguna");

$user_id = $_SESSION["user_id"];
$hasil   = null;
$error   = "";

// Proses hitung & simpan saat form disubmit
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $berat_badan  = isset($_POST["berat_badan"]) ? (float) $_POST["berat_badan"] : 0;
    $tinggi_badan = isset($_POST["tinggi_badan"]) ? (float) $_POST["tinggi_badan"] : 0;

    if ($berat_badan <= 0 || $tinggi_badan <= 0) {
        $error = "Berat badan dan tinggi badan harus lebih dari 0.";
    } else {
        // Konversi tinggi dari cm ke meter
        $tinggi_meter = $tinggi_badan / 100;

        // Hitung BMI
        $nilai_bmi = round($berat_badan / ($tinggi_meter * $tinggi_meter), 2);

        // Klasifikasi BMI
        if ($nilai_bmi < 18.5) {
            $kategori_bmi = "Kurus";
        } elseif ($nilai_bmi < 25) {
            $kategori_bmi = "Normal";
        } elseif ($nilai_bmi < 30) {
            $kategori_bmi = "Gemuk";
        } else {
            $kategori_bmi = "Obesitas";
        }

        // Estimasi kebutuhan cairan harian (35 ml per kg berat badan -> liter)
        $kebutuhan_air = round($berat_badan * 0.035, 2);

        // Simpan ke tabel riwayat_kalkulator
        $query_simpan = "
            INSERT INTO riwayat_kalkulator
                (user_id, berat_badan, tinggi_badan, nilai_bmi, kategori_bmi, kebutuhan_air)
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        $stmt_simpan = mysqli_prepare($koneksi, $query_simpan);
        mysqli_stmt_bind_param(
            $stmt_simpan,
            "idddsd",
            $user_id,
            $berat_badan,
            $tinggi_badan,
            $nilai_bmi,
            $kategori_bmi,
            $kebutuhan_air
        );
        mysqli_stmt_execute($stmt_simpan);

        $hasil = [
            "berat_badan"   => $berat_badan,
            "tinggi_badan"  => $tinggi_badan,
            "nilai_bmi"     => $nilai_bmi,
            "kategori_bmi"  => $kategori_bmi,
            "kebutuhan_air" => $kebutuhan_air
        ];
    }
}

// Ambil riwayat perhitungan milik pengguna yang sedang login
$query_riwayat = "
    SELECT *
    FROM riwayat_kalkulator
    WHERE user_id = ?
    ORDER BY tanggal_hitung DESC
";
$stmt_riwayat = mysqli_prepare($koneksi, $query_riwayat);
mysqli_stmt_bind_param($stmt_riwayat, "i", $user_id);
mysqli_stmt_execute($stmt_riwayat);
$daftar_riwayat = mysqli_stmt_get_result($stmt_riwayat);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalkulator Kesehatan - Sehatku</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php require_once "../../include/header.php"; ?>

<div class="container">
    <a class="back-button" href="../dashboard.php">← Kembali ke Dashboard</a>

    <h1>Kalkulator Kesehatan (BMI & Kebutuhan Cairan)</h1>

    <?php if ($error !== ""): ?>
        <div class="alert danger">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET["pesan"])): ?>
        <div class="alert success">
            <?= htmlspecialchars($_GET["pesan"]) ?>
        </div>
    <?php endif; ?>

    <div class="card" style="margin-bottom: 20px;">
        <h2 style="margin-top: 0;">Hitung BMI & Cairan Harian</h2>
        <form method="POST">
            <label>Berat Badan (kg)</label>
            <input
                type="number"
                name="berat_badan"
                step="0.1"
                min="1"
                required
                placeholder="Contoh: 60"
            >

            <label>Tinggi Badan (cm)</label>
            <input
                type="number"
                name="tinggi_badan"
                step="0.1"
                min="1"
                required
                placeholder="Contoh: 170"
            >

            <div class="form-actions">
                <button type="submit">Hitung & Simpan</button>
            </div>
        </form>
    </div>

    <?php if ($hasil !== null): ?>
        <div class="card" style="margin-bottom: 20px; border-left: 5px solid var(--primary, #2b8a3e);">
            <h2 style="margin-top: 0;">Hasil Perhitungan</h2>
            <p>
                Berat Badan: <strong><?= $hasil["berat_badan"] ?> kg</strong> |
                Tinggi Badan: <strong><?= $hasil["tinggi_badan"] ?> cm</strong>
            </p>
            <p>
                Nilai BMI: <strong><?= $hasil["nilai_bmi"] ?></strong>
                (Kategori: <strong><?= htmlspecialchars($hasil["kategori_bmi"]) ?></strong>)
            </p>
            <p>
                Estimasi Kebutuhan Cairan Harian:
                <strong><?= $hasil["kebutuhan_air"] ?> Liter / hari</strong>
            </p>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2 style="margin-top: 0;">Riwayat Perhitungan</h2>

        <?php if (mysqli_num_rows($daftar_riwayat) === 0): ?>
            <p>Belum ada riwayat perhitungan.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Berat (kg)</th>
                            <th>Tinggi (cm)</th>
                            <th>BMI</th>
                            <th>Kategori</th>
                            <th>Kebutuhan Air (L)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($daftar_riwayat)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row["tanggal_hitung"]) ?></td>
                                <td><?= $row["berat_badan"] ?></td>
                                <td><?= $row["tinggi_badan"] ?></td>
                                <td><strong><?= $row["nilai_bmi"] ?></strong></td>
                                <td><?= htmlspecialchars($row["kategori_bmi"]) ?></td>
                                <td><?= $row["kebutuhan_air"] ?> L</td>
                                <td>
                                    <form action="hapus.php" method="POST" style="margin: 0;">
                                        <input type="hidden" name="kalkulator_id" value="<?= $row["kalkulator_id"] ?>">
                                        <button
                                            type="submit"
                                            class="button danger"
                                            onclick="return confirm('Hapus riwayat perhitungan ini?')"
                                        >
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once "../../include/footer.php"; ?>
</body>
</html>