<?php
require_once "../include/auth_check.php";
require_once "../koneksi.php";
require_once "../include/fungsi.php";

cekRole("pengguna");

$user_id  = (int) $_SESSION["user_id"];
$hari_ini = date("Y-m-d");



$mulai_grafik = date("Y-m-d", strtotime("-6 days"));
$ringkasan    = ambilRingkasanHarian($koneksi, $user_id, $mulai_grafik, $hari_ini);

$hari_ini_data = $ringkasan[$hari_ini] ?? null;
$tercapai      = targetTercapai($hari_ini_data);
$persen_hari   = persenHarian($hari_ini_data);

$nama_hari = ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"];
$tujuh_hari = [];

for ($i = 6; $i >= 0; $i--) {
    $tgl  = date("Y-m-d", strtotime("-$i days"));
    $data = $ringkasan[$tgl] ?? null;

    $tujuh_hari[] = [
        "tanggal" => $tgl,
        "label"   => $nama_hari[(int) date("w", strtotime($tgl))],
        "persen"  => persenHarian($data),
        "tidur"   => $data["tidur"] ?? 0,
        "air"     => $data["air"] ?? 0,
    ];
}

$streak = hitungStreak($koneksi, $user_id);


$jari_jari = 52;
$keliling  = 2 * M_PI * $jari_jari;
$offset    = $keliling * (1 - $persen_hari / 100);



$stmt = mysqli_prepare(
    $koneksi,
    "SELECT r.skor, r.tanggal_kerjakan, k.judul
     FROM riwayat_kuis r
     JOIN kuis k ON k.kuis_id = r.kuis_id
     WHERE r.user_id = ?
     ORDER BY r.tanggal_kerjakan DESC, r.riwayat_id DESC
     LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$kuis_terakhir = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));



$stmt = mysqli_prepare(
    $koneksi,
    "SELECT k.tanggal_konsultasi, k.jam_mulai, k.jam_selesai, k.status,
            COALESCE(NULLIF(p.nama_lengkap, ''), u.username) AS nama_nakes
     FROM konsultasi k
     JOIN users u ON u.user_id = k.nakes_id
     LEFT JOIN user_profiles p ON p.user_id = k.nakes_id
     WHERE k.user_id = ? AND k.status IN ('menunggu', 'dikonfirmasi')
       AND k.tanggal_konsultasi >= ?
     ORDER BY k.tanggal_konsultasi, k.jam_mulai
     LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "is", $user_id, $hari_ini);
mysqli_stmt_execute($stmt);
$konsultasi_dekat = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$daftar_target = [
    ["kunci" => "tidur", "nama" => "Tidur " . TARGET_TIDUR . " jam",
     "detail" => $hari_ini_data ? $hari_ini_data["tidur"] . " jam" : "belum dicatat"],
    ["kunci" => "air", "nama" => "Minum " . TARGET_AIR . " liter",
     "detail" => $hari_ini_data ? round($hari_ini_data["air"], 1) . " L" : "belum dicatat"],
    ["kunci" => "olahraga", "nama" => "Olahraga",
     "detail" => $tercapai["olahraga"] ? "tercatat" : "belum dicatat"],
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pengguna - Sehatku</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php require_once "../include/header.php"; ?>

<div class="container">
    <h1>Dashboard Pengguna</h1>

    <p>
        Halo, <strong><?= e($_SESSION["username"]) ?></strong>.
        Ini progres sehatmu hari ini.
    </p>

    <div class="grid-2">

        <div class="card">
            <h2>Progres Hari Ini</h2>

            <div class="progress-wrap">
                <svg class="ring" viewBox="0 0 120 120" role="img"
                     aria-label="Progres hari ini <?= $persen_hari ?> persen">
                    <circle class="ring-bg" cx="60" cy="60" r="<?= $jari_jari ?>"></circle>
                    <circle class="ring-fg" cx="60" cy="60" r="<?= $jari_jari ?>"
                            stroke-dasharray="<?= round($keliling, 2) ?>"
                            stroke-dashoffset="<?= round($offset, 2) ?>"
                            transform="rotate(-90 60 60)"></circle>
                    <text class="ring-text" x="60" y="68" text-anchor="middle"><?= $persen_hari ?>%</text>
                </svg>

                <ul class="goal-list">
                    <?php foreach ($daftar_target as $t): ?>
                        <li class="<?= $tercapai[$t["kunci"]] ? "done" : "" ?>">
                            <span class="goal-check" aria-hidden="true"><?= $tercapai[$t["kunci"]] ? "✓" : "" ?></span>
                            <span><?= e($t["nama"]) ?></span>
                            <span class="goal-detail"><?= e($t["detail"]) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <?php if ($hari_ini_data === null): ?>
                <a class="button" href="catatan/tambah.php">Catat Hari Ini</a>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Grafik 7 Hari Terakhir</h2>

            <?php
                
                $lebar_svg = 350; $tinggi_svg = 175;
                $dasar = 135; $tinggi_maks = 105;
                $lebar_batang = 32; $jarak = ($lebar_svg - 7 * $lebar_batang) / 8;
            ?>
            <svg class="chart-svg" viewBox="0 0 <?= $lebar_svg ?> <?= $tinggi_svg ?>" role="img"
                 aria-label="Grafik persentase target harian tercapai dalam 7 hari terakhir">
                <?php foreach ([0, 50, 100] as $garis): ?>
                    <?php $y = $dasar - $tinggi_maks * $garis / 100; ?>
                    <line class="chart-grid" x1="0" x2="<?= $lebar_svg ?>" y1="<?= $y ?>" y2="<?= $y ?>"></line>
                <?php endforeach; ?>

                <?php foreach ($tujuh_hari as $idx => $hari): ?>
                    <?php
                        $x      = $jarak + $idx * ($lebar_batang + $jarak);
                        $tinggi = max(3, $tinggi_maks * $hari["persen"] / 100);
                        $adalah_hari_ini = $hari["tanggal"] === $hari_ini;
                    ?>
                    <g>
                        <title><?= e(tanggalIndo($hari["tanggal"])) ?>: <?= $hari["persen"] ?>% target tercapai (tidur <?= e($hari["tidur"]) ?> jam, air <?= e(round($hari["air"], 1)) ?> L)</title>
                        <rect class="chart-bar<?= $adalah_hari_ini ? " today" : "" ?>"
                              x="<?= round($x, 1) ?>" y="<?= round($dasar - $tinggi, 1) ?>"
                              width="<?= $lebar_batang ?>" height="<?= round($tinggi, 1) ?>" rx="7"></rect>
                        <text class="chart-value" x="<?= round($x + $lebar_batang / 2, 1) ?>"
                              y="<?= round($dasar - $tinggi - 6, 1) ?>" text-anchor="middle"><?= $hari["persen"] ?>%</text>
                        <text x="<?= round($x + $lebar_batang / 2, 1) ?>" y="<?= $dasar + 20 ?>"
                              text-anchor="middle"><?= e($hari["label"]) ?></text>
                    </g>
                <?php endforeach; ?>
            </svg>
            <p class="muted small" style="margin-bottom: 0;">Persentase dari tiga target harian: tidur, air, dan olahraga.</p>
        </div>

    </div>

    <div class="grid-3">

        <div class="card">
            <h2>Streak Pencatatan</h2>
            <div class="streak-number">🔥 <?= $streak ?></div>
            <p class="muted small" style="margin-bottom: 0;">
                <?php if ($streak === 0): ?>
                    Catat kesehatanmu hari ini untuk memulai streak.
                <?php else: ?>
                    hari berturut-turut kamu mencatat kesehatan.
                <?php endif; ?>
            </p>
        </div>

        <div class="card">
            <h2>Badge Terbaru</h2>
            <?php if ($kuis_terakhir): ?>
                <?php $tingkat = tingkatBadge((int) $kuis_terakhir["skor"]); ?>
                <div class="badge-showcase">
                    <span class="badge-icon" aria-hidden="true"><?= ikonBadge($tingkat) ?></span>
                    <div>
                        <strong><?= e(namaBadge($tingkat)) ?></strong>
                        <div class="muted small">
                            <?= e($kuis_terakhir["judul"]) ?> · skor <?= (int) $kuis_terakhir["skor"] ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <p class="muted">Belum ada badge. Kerjakan kuis untuk mendapatkannya.</p>
                <a class="button secondary small-button" href="kuis/index.php">Mulai Kuis</a>
            <?php endif; ?>
        </div>

        <div class="card" id="konsultasi">
            <h2>Konsultasi</h2>
            <?php if ($konsultasi_dekat): ?>
                <strong><?= e($konsultasi_dekat["nama_nakes"]) ?></strong>
                <div class="muted small" style="margin: 4px 0 10px;">
                    <?= e(tanggalIndo($konsultasi_dekat["tanggal_konsultasi"])) ?>,
                    <?= e(rentangJam($konsultasi_dekat["jam_mulai"], $konsultasi_dekat["jam_selesai"])) ?>
                </div>
                <?= pillStatus($konsultasi_dekat["status"]) ?>
                <div>
                    <a class="button secondary small-button" href="konsultasi/index.php">Lihat Konsultasi</a>
                </div>
            <?php else: ?>
                <p class="muted">Belum ada konsultasi yang akan datang.</p>
                <a class="button small-button" href="konsultasi/pilih.php">Buat Konsultasi</a>
            <?php endif; ?>
        </div>

    </div>

    <div class="page-actions">
        <a class="button" href="catatan/index.php">Tracker Kebiasaan</a>
        <a class="button" href="artikel/index.php">Artikel Edukasi</a>
        <a class="button" href="kalkulator/index.php">Kalkulator Kesehatan</a>
        <a class="button secondary" href="profile.php">Profil Saya</a>
        <a class="button danger" href="../autentikasi/logout.php">Logout</a>
    </div>
</div>

<?php require_once "../include/footer.php"; ?>
</body>
</html>
