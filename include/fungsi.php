<?php

/*
 * Fungsi bantu bersama (dipakai Modul 7 - 9)
 * Konsultasi, Notifikasi & Pengingat, Dashboard.
 */

// Target harian yang dipakai progres harian & pengingat
const TARGET_TIDUR = 7.0;   // jam
const TARGET_AIR   = 2.0;   // liter

const JENIS_PENGINGAT = [
    "air" => [
        "label" => "Minum air",
        "ikon"  => "💧",
        "pesan" => "Waktunya minum air. Targetmu hari ini 2 liter.",
    ],
    "tidur" => [
        "label" => "Tidur",
        "ikon"  => "🌙",
        "pesan" => "Waktunya bersiap tidur supaya istirahatmu cukup.",
    ],
    "olahraga" => [
        "label" => "Olahraga",
        "ikon"  => "🏃",
        "pesan" => "Waktunya bergerak. Aktivitas ringan pun sudah cukup.",
    ],
];

/* ---------- Umum ---------- */

function e($teks)
{
    return htmlspecialchars((string) $teks, ENT_QUOTES, "UTF-8");
}

function tanggalIndo($tanggal, $denganHari = true)
{
    $waktu = strtotime((string) $tanggal);

    if (!$waktu) {
        return "-";
    }

    $hari  = ["Min", "Sen", "Sel", "Rab", "Kam", "Jum", "Sab"];
    $bulan = ["", "Jan", "Feb", "Mar", "Apr", "Mei", "Jun",
              "Jul", "Agu", "Sep", "Okt", "Nov", "Des"];

    $hasil = date("j", $waktu) . " " . $bulan[(int) date("n", $waktu)] . " " . date("Y", $waktu);

    return $denganHari ? $hari[(int) date("w", $waktu)] . ", " . $hasil : $hasil;
}

function jamSingkat($jam)
{
    return substr((string) $jam, 0, 5);
}

function rentangJam($mulai, $selesai)
{
    return jamSingkat($mulai) . " - " . jamSingkat($selesai);
}

// Nama yang ditampilkan: nama lengkap, kalau kosong pakai username
function namaTampil($namaLengkap, $username)
{
    $nama = trim((string) $namaLengkap);

    return $nama !== "" ? $nama : (string) $username;
}

function pillStatus($status)
{
    $label = [
        "menunggu"     => "Menunggu",
        "dikonfirmasi" => "Dikonfirmasi",
        "selesai"      => "Selesai",
        "dibatalkan"   => "Dibatalkan",
        "tersedia"     => "Tersedia",
        "dibooking"    => "Dibooking",
    ];

    $teks = $label[$status] ?? $status;

    return '<span class="pill pill-' . e($status) . '">' . e($teks) . '</span>';
}

/* ---------- Notifikasi ---------- */

function buatNotifikasi($koneksi, $user_id, $jenis, $judul, $pesan, $tautan = null)
{
    $stmt = mysqli_prepare(
        $koneksi,
        "INSERT INTO notifikasi (user_id, jenis, judul, pesan, tautan) VALUES (?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param($stmt, "issss", $user_id, $jenis, $judul, $pesan, $tautan);
    mysqli_stmt_execute($stmt);
}

function jumlahNotifikasiBelumDibaca($koneksi, $user_id)
{
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT COUNT(*) AS total FROM notifikasi WHERE user_id = ? AND sudah_dibaca = 0"
    );

    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);

    $baris = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    return (int) ($baris["total"] ?? 0);
}

/* ---------- Badge kuis ---------- */

function tingkatBadge($skor)
{
    if ($skor >= 80) {
        return "duta";
    }

    if ($skor >= 60) {
        return "pejuang";
    }

    return "pemula";
}

// Rentang skor [min, maks] tiap tingkat, dipakai untuk mengecek badge yang sudah pernah diraih
function rentangBadge($tingkat)
{
    $rentang = [
        "duta"    => [80, 100],
        "pejuang" => [60, 79],
        "pemula"  => [0, 59],
    ];

    return $rentang[$tingkat];
}

function labelBadge($tingkat)
{
    $label = [
        "duta"    => "🏆 Duta Sehat (Luar Biasa!)",
        "pejuang" => "🥈 Pejuang Sehat (Bagus!)",
        "pemula"  => "🌱 Pemula Sehat (Terus Belajar!)",
    ];

    return $label[$tingkat];
}

function namaBadge($tingkat)
{
    $nama = [
        "duta"    => "Duta Sehat",
        "pejuang" => "Pejuang Sehat",
        "pemula"  => "Pemula Sehat",
    ];

    return $nama[$tingkat];
}

function ikonBadge($tingkat)
{
    $ikon = [
        "duta"    => "🏆",
        "pejuang" => "🥈",
        "pemula"  => "🌱",
    ];

    return $ikon[$tingkat];
}

/* ---------- Tracker: progres harian & streak ---------- */

// Kolom aktivitas wajib diisi, jadi tulisan "tidak ada" tidak dihitung olahraga
function aktivitasTercatat($teks)
{
    $teks = mb_strtolower(trim((string) $teks), "UTF-8");

    if ($teks === "") {
        return false;
    }

    return !preg_match('/^(-+|0|(tidak|nihil|belum|nggak|gak|ga)(\s|$))/u', $teks);
}

/*
 * Ringkasan catatan per tanggal untuk rentang tertentu.
 * Satu tanggal boleh punya lebih dari satu catatan: air dijumlah,
 * tidur diambil yang terbesar, olahraga cukup salah satu tercatat.
 */
function ambilRingkasanHarian($koneksi, $user_id, $dari, $sampai)
{
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT tanggal_catatan, durasi_tidur, aktivitas_fisik, asupan_air
         FROM catatan_kesehatan
         WHERE user_id = ? AND tanggal_catatan BETWEEN ? AND ?"
    );

    mysqli_stmt_bind_param($stmt, "iss", $user_id, $dari, $sampai);
    mysqli_stmt_execute($stmt);
    $hasil = mysqli_stmt_get_result($stmt);

    $ringkasan = [];

    while ($baris = mysqli_fetch_assoc($hasil)) {
        $tgl = $baris["tanggal_catatan"];

        if (!isset($ringkasan[$tgl])) {
            $ringkasan[$tgl] = ["tidur" => 0.0, "air" => 0.0, "olahraga" => false];
        }

        $ringkasan[$tgl]["tidur"]   = max($ringkasan[$tgl]["tidur"], (float) $baris["durasi_tidur"]);
        $ringkasan[$tgl]["air"]    += (float) $baris["asupan_air"];
        $ringkasan[$tgl]["olahraga"] = $ringkasan[$tgl]["olahraga"] || aktivitasTercatat($baris["aktivitas_fisik"]);
    }

    return $ringkasan;
}

// Tiga target harian: tidur, air, olahraga
function targetTercapai($hari)
{
    return [
        "tidur"    => $hari !== null && $hari["tidur"] >= TARGET_TIDUR,
        "air"      => $hari !== null && $hari["air"] >= TARGET_AIR,
        "olahraga" => $hari !== null && $hari["olahraga"],
    ];
}

function persenHarian($hari)
{
    $tercapai = array_filter(targetTercapai($hari));

    return (int) round(count($tercapai) / 3 * 100);
}

// Hari beruntun yang punya catatan. Kalau hari ini belum diisi, hitung mundur dari kemarin.
function hitungStreak($koneksi, $user_id)
{
    $hariIni = date("Y-m-d");

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT DISTINCT tanggal_catatan
         FROM catatan_kesehatan
         WHERE user_id = ? AND tanggal_catatan <= ?
         ORDER BY tanggal_catatan DESC
         LIMIT 400"
    );

    mysqli_stmt_bind_param($stmt, "is", $user_id, $hariIni);
    mysqli_stmt_execute($stmt);
    $hasil = mysqli_stmt_get_result($stmt);

    $ada = [];

    while ($baris = mysqli_fetch_assoc($hasil)) {
        $ada[$baris["tanggal_catatan"]] = true;
    }

    $tanggal = isset($ada[$hariIni]) ? $hariIni : date("Y-m-d", strtotime("-1 day"));
    $streak  = 0;

    while (isset($ada[$tanggal])) {
        $streak++;
        $tanggal = date("Y-m-d", strtotime($tanggal . " -1 day"));
    }

    return $streak;
}

/* ---------- Pengingat ---------- */

/*
 * Pengingat yang sudah lewat jamnya hari ini dan belum ditutup.
 * Pengingat air hilang sendiri kalau target air sudah tercapai,
 * pengingat olahraga hilang kalau olahraga sudah dicatat.
 */
function pengingatJatuhTempo($koneksi, $user_id)
{
    $hariIni = date("Y-m-d");
    $jamIni  = date("H:i:s");

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT jenis, jam FROM pengingat
         WHERE user_id = ? AND aktif = 1 AND jam <= ?
           AND (terakhir_ditutup IS NULL OR terakhir_ditutup < ?)
         ORDER BY jam"
    );

    mysqli_stmt_bind_param($stmt, "iss", $user_id, $jamIni, $hariIni);
    mysqli_stmt_execute($stmt);
    $hasil = mysqli_stmt_get_result($stmt);

    $daftar = [];

    while ($baris = mysqli_fetch_assoc($hasil)) {
        $daftar[] = $baris;
    }

    if (count($daftar) === 0) {
        return [];
    }

    $ringkasan = ambilRingkasanHarian($koneksi, $user_id, $hariIni, $hariIni);
    $hari      = $ringkasan[$hariIni] ?? null;
    $tercapai  = targetTercapai($hari);

    return array_values(array_filter($daftar, function ($p) use ($tercapai) {
        if ($p["jenis"] === "air") {
            return !$tercapai["air"];
        }

        if ($p["jenis"] === "olahraga") {
            return !$tercapai["olahraga"];
        }

        return true;
    }));
}
