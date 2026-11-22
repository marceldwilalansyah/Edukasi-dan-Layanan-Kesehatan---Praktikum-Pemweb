<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";
require_once "../../include/fungsi.php";

cekRole("pengguna");

$user_id  = (int) $_SESSION["user_id"];
$nakes_id = isset($_GET["nakes_id"]) ? (int) $_GET["nakes_id"] : (int) ($_POST["nakes_id"] ?? 0);
$hari_ini = date("Y-m-d");
$jam_ini  = date("H:i:s");


$stmt = mysqli_prepare(
    $koneksi,
    "SELECT u.user_id, COALESCE(NULLIF(p.nama_lengkap, ''), u.username) AS nama,
            p.spesialisasi, p.bio
     FROM users u
     JOIN roles r ON r.role_id = u.role_id
     LEFT JOIN user_profiles p ON p.user_id = u.user_id
     WHERE u.user_id = ? AND r.role_name = 'nakes' AND u.status = 'aktif'"
);
mysqli_stmt_bind_param($stmt, "i", $nakes_id);
mysqli_stmt_execute($stmt);
$nakes = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$nakes) {
    header("Location: pilih.php");
    exit;
}

$error  = "";
$keluhan_input = "";
$jadwal_dipilih = 0;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $jadwal_dipilih = (int) ($_POST["jadwal_id"] ?? 0);
    $keluhan_input  = trim($_POST["keluhan"] ?? "");

    if ($jadwal_dipilih <= 0) {
        $error = "Pilih salah satu jadwal terlebih dahulu.";
    } elseif ($keluhan_input === "") {
        $error = "Keluhan wajib diisi agar nakes bisa menyiapkan konsultasi.";
    } else {
        mysqli_begin_transaction($koneksi);

        try {
            
            $stmt = mysqli_prepare(
                $koneksi,
                "SELECT jadwal_id, tanggal, jam_mulai, jam_selesai, status
                 FROM jadwal_praktik
                 WHERE jadwal_id = ? AND nakes_id = ?
                 FOR UPDATE"
            );
            mysqli_stmt_bind_param($stmt, "ii", $jadwal_dipilih, $nakes_id);
            mysqli_stmt_execute($stmt);
            $slot = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

            $lewat = $slot && ($slot["tanggal"] < $hari_ini
                || ($slot["tanggal"] === $hari_ini && $slot["jam_mulai"] <= $jam_ini));

            if (!$slot || $slot["status"] !== "tersedia" || $lewat) {
                mysqli_rollback($koneksi);
                $error = "Jadwal tersebut sudah tidak tersedia. Silakan pilih jadwal lain.";
            } else {
                
                $stmt = mysqli_prepare(
                    $koneksi,
                    "SELECT konsultasi_id FROM konsultasi
                     WHERE user_id = ? AND status IN ('menunggu', 'dikonfirmasi')
                       AND tanggal_konsultasi = ? AND jam_mulai < ? AND jam_selesai > ?
                     LIMIT 1"
                );
                mysqli_stmt_bind_param(
                    $stmt, "isss",
                    $user_id, $slot["tanggal"], $slot["jam_selesai"], $slot["jam_mulai"]
                );
                mysqli_stmt_execute($stmt);

                if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) {
                    mysqli_rollback($koneksi);
                    $error = "Kamu sudah punya konsultasi lain di waktu yang bertabrakan.";
                } else {
                    $stmt = mysqli_prepare(
                        $koneksi,
                        "INSERT INTO konsultasi
                            (user_id, nakes_id, jadwal_id, tanggal_konsultasi, jam_mulai, jam_selesai, keluhan)
                         VALUES (?, ?, ?, ?, ?, ?, ?)"
                    );
                    mysqli_stmt_bind_param(
                        $stmt, "iiissss",
                        $user_id, $nakes_id, $jadwal_dipilih,
                        $slot["tanggal"], $slot["jam_mulai"], $slot["jam_selesai"], $keluhan_input
                    );
                    mysqli_stmt_execute($stmt);
                    $konsultasi_id = mysqli_insert_id($koneksi);

                    $stmt = mysqli_prepare(
                        $koneksi,
                        "UPDATE jadwal_praktik SET status = 'dibooking' WHERE jadwal_id = ?"
                    );
                    mysqli_stmt_bind_param($stmt, "i", $jadwal_dipilih);
                    mysqli_stmt_execute($stmt);

              
                    $stmt = mysqli_prepare(
                        $koneksi,
                        "SELECT COALESCE(NULLIF(p.nama_lengkap, ''), u.username) AS nama
                         FROM users u LEFT JOIN user_profiles p ON p.user_id = u.user_id
                         WHERE u.user_id = ?"
                    );
                    mysqli_stmt_bind_param($stmt, "i", $user_id);
                    mysqli_stmt_execute($stmt);
                    $pemesan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

                    buatNotifikasi(
                        $koneksi,
                        $nakes_id,
                        "booking",
                        "Booking konsultasi baru",
                        ($pemesan["nama"] ?? "Pengguna") . " meminta konsultasi pada "
                            . tanggalIndo($slot["tanggal"]) . ", "
                            . rentangJam($slot["jam_mulai"], $slot["jam_selesai"]) . ".",
                        "nakes/konsultasi/detail.php?id=" . $konsultasi_id
                    );

                    mysqli_commit($koneksi);

                    header("Location: index.php?pesan=" . urlencode("Booking terkirim. Tunggu konfirmasi dari nakes."));
                    exit;
                }
            }
        } catch (mysqli_sql_exception $ex) {
            mysqli_rollback($koneksi);
            $error = "Booking gagal diproses. Coba lagi.";
        }
    }
}


$stmt = mysqli_prepare(
    $koneksi,
    "SELECT jadwal_id, tanggal, jam_mulai, jam_selesai
     FROM jadwal_praktik
     WHERE nakes_id = ? AND status = 'tersedia'
       AND (tanggal > ? OR (tanggal = ? AND jam_mulai > ?))
     ORDER BY tanggal, jam_mulai"
);
mysqli_stmt_bind_param($stmt, "isss", $nakes_id, $hari_ini, $hari_ini, $jam_ini);
mysqli_stmt_execute($stmt);
$hasil_slot = mysqli_stmt_get_result($stmt);

$slot_per_hari = [];
while ($baris = mysqli_fetch_assoc($hasil_slot)) {
    $slot_per_hari[$baris["tanggal"]][] = $baris;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Konsultasi - Sehatku</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php require_once "../../include/header.php"; ?>

<div class="container">
    <a class="back-button" href="pilih.php">← Kembali ke Daftar Nakes</a>

    <h1>Booking Konsultasi</h1>

    <div class="card" style="margin-bottom: 18px;">
        <h2 style="margin-bottom: 4px;"><?= e($nakes["nama"]) ?></h2>
        <div class="muted"><?= e($nakes["spesialisasi"] ?: "Tenaga kesehatan") ?></div>
        <?php if ($nakes["bio"]): ?>
            <p style="margin-bottom: 0;"><?= nl2br(e($nakes["bio"])) ?></p>
        <?php endif; ?>
    </div>

    <?php if ($error !== ""): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if (count($slot_per_hari) === 0): ?>
        <div class="card">
            <div class="empty-state">Belum ada jadwal tersedia untuk nakes ini.</div>
        </div>
    <?php else: ?>
        <form method="POST">
            <input type="hidden" name="nakes_id" value="<?= (int) $nakes_id ?>">

            <div class="card">
                <h2>1. Pilih Jadwal</h2>

                <?php foreach ($slot_per_hari as $tanggal => $daftar_slot): ?>
                    <div class="slot-day"><?= e(tanggalIndo($tanggal)) ?></div>
                    <div class="slot-grid">
                        <?php foreach ($daftar_slot as $slot): ?>
                            <label class="slot">
                                <input type="radio" name="jadwal_id" value="<?= (int) $slot["jadwal_id"] ?>"
                                    <?= $jadwal_dipilih === (int) $slot["jadwal_id"] ? "checked" : "" ?> required>
                                <span><?= e(rentangJam($slot["jam_mulai"], $slot["jam_selesai"])) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card">
                <h2>2. Ceritakan Keluhanmu</h2>
                <textarea name="keluhan" rows="5" style="min-height: 130px;" required
                    placeholder="Sejak kapan, apa yang dirasakan, dan apa yang sudah dicoba."><?= e($keluhan_input) ?></textarea>

                <div class="form-actions">
                    <button type="submit">Kirim Permintaan Konsultasi</button>
                    <a class="button secondary" href="pilih.php">Batal</a>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once "../../include/footer.php"; ?>
</body>
</html>
