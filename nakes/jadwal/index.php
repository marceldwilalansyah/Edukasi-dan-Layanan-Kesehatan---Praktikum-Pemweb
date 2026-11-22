<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";
require_once "../../include/fungsi.php";

cekRole("nakes");

$nakes_id = (int) $_SESSION["user_id"];
$hari_ini = date("Y-m-d");
$jam_ini  = date("H:i");

$error = "";
$tanggal_input = $hari_ini;
$mulai_input   = "";
$selesai_input = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $tanggal_input = trim($_POST["tanggal"] ?? "");
    $mulai_input   = trim($_POST["jam_mulai"] ?? "");
    $selesai_input = trim($_POST["jam_selesai"] ?? "");

    $tanggal_valid = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_input) && strtotime($tanggal_input);
    $jam_valid     = preg_match('/^\d{2}:\d{2}$/', $mulai_input) && preg_match('/^\d{2}:\d{2}$/', $selesai_input);

    if (!$tanggal_valid || !$jam_valid) {
        $error = "Tanggal dan jam wajib diisi dengan benar.";
    } elseif ($tanggal_input < $hari_ini || ($tanggal_input === $hari_ini && $mulai_input <= $jam_ini)) {
        $error = "Jadwal harus di waktu yang akan datang.";
    } elseif ($selesai_input <= $mulai_input) {
        $error = "Jam selesai harus lebih besar dari jam mulai.";
    } else {
        $mulai   = $mulai_input . ":00";
        $selesai = $selesai_input . ":00";

        // Cegah jadwal yang tumpang tindih di hari yang sama
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT jadwal_id FROM jadwal_praktik
             WHERE nakes_id = ? AND tanggal = ? AND jam_mulai < ? AND jam_selesai > ?
             LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, "isss", $nakes_id, $tanggal_input, $selesai, $mulai);
        mysqli_stmt_execute($stmt);

        if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) {
            $error = "Jadwal bertabrakan dengan jadwal lain di tanggal yang sama.";
        } else {
            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO jadwal_praktik (nakes_id, tanggal, jam_mulai, jam_selesai) VALUES (?, ?, ?, ?)"
            );
            mysqli_stmt_bind_param($stmt, "isss", $nakes_id, $tanggal_input, $mulai, $selesai);
            mysqli_stmt_execute($stmt);

            header("Location: index.php?pesan=" . urlencode("Jadwal praktik berhasil ditambahkan."));
            exit;
        }
    }
}

$stmt = mysqli_prepare(
    $koneksi,
    "SELECT j.jadwal_id, j.tanggal, j.jam_mulai, j.jam_selesai, j.status,
            k.konsultasi_id, k.status AS status_konsultasi,
            COALESCE(NULLIF(p.nama_lengkap, ''), u.username) AS pasien
     FROM jadwal_praktik j
     LEFT JOIN konsultasi k
            ON k.jadwal_id = j.jadwal_id AND k.status IN ('menunggu', 'dikonfirmasi', 'selesai')
     LEFT JOIN users u ON u.user_id = k.user_id
     LEFT JOIN user_profiles p ON p.user_id = k.user_id
     WHERE j.nakes_id = ? AND j.tanggal >= ?
     ORDER BY j.tanggal, j.jam_mulai"
);
mysqli_stmt_bind_param($stmt, "is", $nakes_id, $hari_ini);
mysqli_stmt_execute($stmt);
$daftar_jadwal = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Praktik - Sehatku</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php require_once "../../include/header.php"; ?>

<div class="container">
    <a class="back-button" href="../dashboard.php">← Kembali ke Dashboard</a>

    <h1>Jadwal Praktik</h1>
    <p>Atur waktu yang bisa dipilih pengguna untuk berkonsultasi denganmu.</p>

    <?php if (isset($_GET["pesan"])): ?>
        <div class="alert success"><?= e($_GET["pesan"]) ?></div>
    <?php endif; ?>

    <?php if (isset($_GET["error"])): ?>
        <div class="alert error"><?= e($_GET["error"]) ?></div>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="card">
        <h2>Tambah Jadwal</h2>
        <form method="POST">
            <div class="form-row">
                <div>
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" min="<?= e($hari_ini) ?>" value="<?= e($tanggal_input) ?>" required>
                </div>
                <div>
                    <label>Jam Mulai</label>
                    <input type="time" name="jam_mulai" value="<?= e($mulai_input) ?>" required>
                </div>
                <div>
                    <label>Jam Selesai</label>
                    <input type="time" name="jam_selesai" value="<?= e($selesai_input) ?>" required>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit">+ Tambah Jadwal</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Jadwal Mendatang</h2>

        <?php if (mysqli_num_rows($daftar_jadwal) === 0): ?>
            <div class="empty-state">Belum ada jadwal. Tambahkan jadwal agar pengguna bisa booking.</div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table width="100%">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jam</th>
                            <th>Status</th>
                            <th>Pasien</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($jadwal = mysqli_fetch_assoc($daftar_jadwal)): ?>
                            <tr>
                                <td><?= e(tanggalIndo($jadwal["tanggal"])) ?></td>
                                <td><?= e(rentangJam($jadwal["jam_mulai"], $jadwal["jam_selesai"])) ?></td>
                                <td>
                                    <?= pillStatus($jadwal["status"]) ?>
                                    <?php if ($jadwal["status_konsultasi"]): ?>
                                        <?= pillStatus($jadwal["status_konsultasi"]) ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= $jadwal["pasien"] ? e($jadwal["pasien"]) : "-" ?></td>
                                <td>
                                    <?php if ($jadwal["status"] === "tersedia"): ?>
                                        <form class="inline-form" action="hapus.php" method="POST">
                                            <input type="hidden" name="jadwal_id" value="<?= (int) $jadwal["jadwal_id"] ?>">
                                            <button type="submit" class="button danger small-button"
                                                data-confirm="Hapus jadwal ini?">Hapus</button>
                                        </form>
                                    <?php elseif ($jadwal["konsultasi_id"]): ?>
                                        <a class="button secondary small-button"
                                           href="../konsultasi/detail.php?id=<?= (int) $jadwal["konsultasi_id"] ?>">Lihat</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <p class="muted small" style="margin-bottom: 0;">
                Jadwal yang sudah dibooking tidak bisa dihapus. Batalkan konsultasinya dulu dari menu Konsultasi.
            </p>
        <?php endif; ?>
    </div>
</div>

<?php require_once "../../include/footer.php"; ?>
</body>
</html>
