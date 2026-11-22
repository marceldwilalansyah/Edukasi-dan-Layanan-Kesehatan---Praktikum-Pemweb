<?php
require_once "../../include/auth_check.php";
require_once "../../koneksi.php";
require_once "../../include/fungsi.php";

cekRole("pengguna");

$user_id = (int) $_SESSION["user_id"];


$jam_bawaan = ["air" => "10:00", "tidur" => "22:00", "olahraga" => "17:00"];

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data_baru = [];

    foreach (JENIS_PENGINGAT as $jenis => $info_jenis) {
        $jam = trim($_POST["jam"][$jenis] ?? "");

        if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $jam)) {
            $error = "Jam pengingat " . strtolower($info_jenis["label"]) . " tidak valid.";
            break;
        }

        $data_baru[$jenis] = [
            "jam"   => $jam . ":00",
            "aktif" => isset($_POST["aktif"][$jenis]) ? 1 : 0,
        ];
    }

    if ($error === "") {
       
        $stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO pengingat (user_id, jenis, jam, aktif)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE jam = ?, aktif = ?, terakhir_ditutup = NULL"
        );

        foreach ($data_baru as $jenis => $nilai) {
            mysqli_stmt_bind_param(
                $stmt, "issisi",
                $user_id, $jenis, $nilai["jam"], $nilai["aktif"],
                $nilai["jam"], $nilai["aktif"]
            );
            mysqli_stmt_execute($stmt);
        }

        header("Location: index.php?pesan=" . urlencode("Pengaturan pengingat disimpan."));
        exit;
    }
}

$stmt = mysqli_prepare($koneksi, "SELECT jenis, jam, aktif FROM pengingat WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);

$tersimpan = [];
while ($baris = mysqli_fetch_assoc($hasil)) {
    $tersimpan[$baris["jenis"]] = $baris;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengingat - Sehatku</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

<?php require_once "../../include/header.php"; ?>

<div class="container">
    <a class="back-button" href="../dashboard.php">← Kembali ke Dashboard</a>

    <h1>Pengingat Kebiasaan Sehat</h1>
    <p>Saat jam pengingat tiba, kamu akan melihat pengingat di bagian atas halaman Sehatku sampai kamu menutupnya.</p>

    <?php if (isset($_GET["pesan"])): ?>
        <div class="alert success"><?= e($_GET["pesan"]) ?></div>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="POST">
            <?php foreach (JENIS_PENGINGAT as $jenis => $info_jenis): ?>
                <?php
                    $ada   = $tersimpan[$jenis] ?? null;
                    $jam   = $ada ? jamSingkat($ada["jam"]) : $jam_bawaan[$jenis];
                    $aktif = $ada ? (int) $ada["aktif"] === 1 : false;
                ?>
                <div class="reminder-row">
                    <div class="reminder-name">
                        <span aria-hidden="true"><?= $info_jenis["ikon"] ?></span>
                        <span><?= e($info_jenis["label"]) ?></span>
                    </div>
                    <input type="time" name="jam[<?= e($jenis) ?>]" value="<?= e($jam) ?>"
                           aria-label="Jam pengingat <?= e(strtolower($info_jenis["label"])) ?>" required>
                    <label class="switch">
                        <input type="checkbox" name="aktif[<?= e($jenis) ?>]" value="1" <?= $aktif ? "checked" : "" ?>>
                        Aktif
                    </label>
                </div>
            <?php endforeach; ?>

            <div class="form-actions">
                <button type="submit">Simpan Pengaturan</button>
            </div>
        </form>
    </div>

    <p class="muted small">
        Pengingat air hilang otomatis setelah targetmu tercapai di Tracker, dan pengingat olahraga hilang setelah
        aktivitas hari ini dicatat.
    </p>
</div>

<?php require_once "../../include/footer.php"; ?>
</body>
</html>
