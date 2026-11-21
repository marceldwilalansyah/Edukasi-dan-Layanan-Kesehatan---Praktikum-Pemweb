<?php

require_once "../koneksi.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: register.php");
    exit;
}

$username = trim($_POST["username"]);
$password = $_POST["password"];
$konfirmasi_password = $_POST["konfirmasi_password"];

if ($password !== $konfirmasi_password) {
    header("Location: register.php?error=Konfirmasi password tidak sesuai");
    exit;
}

if (strlen($password) < 6) {
    header("Location: register.php?error=Password minimal 6 karakter");
    exit;
}

$query = "SELECT user_id FROM users WHERE username = ?";

$stmt = mysqli_prepare($koneksi, $query);

mysqli_stmt_bind_param($stmt, "s", $username);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {

    header("Location: register.php?error=Username sudah digunakan");
    exit;
}

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$role_query = "SELECT role_id FROM roles WHERE role_name = 'pengguna'";

$role_result = mysqli_query($koneksi, $role_query);

$role = mysqli_fetch_assoc($role_result);

$role_id = $role["role_id"];

$query = "
    INSERT INTO users
    (role_id, username, password)
    VALUES (?, ?, ?)
";

$stmt = mysqli_prepare($koneksi, $query);

mysqli_stmt_bind_param(
    $stmt,
    "iss",
    $role_id,
    $username,
    $password_hash
);

if (mysqli_stmt_execute($stmt)) {

    $user_id = mysqli_insert_id($koneksi);
    
    $profile_query = "
        INSERT INTO user_profiles (user_id)
        VALUES (?)
    ";

    $profile_stmt = mysqli_prepare(
        $koneksi,
        $profile_query
    );

    mysqli_stmt_bind_param(
        $profile_stmt,
        "i",
        $user_id
    );

    mysqli_stmt_execute($profile_stmt);

    header("Location: login.php?success=Registrasi berhasil");
    exit;

} else {

    header("Location: register.php?error=Registrasi gagal");
    exit;
}