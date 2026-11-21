<?php

require_once "../koneksi.php";

session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit;
}

$username = trim($_POST["username"]);
$password = $_POST["password"];


$query = "
    SELECT
        u.user_id,
        u.username,
        u.password,
        u.role_id,
        u.status,
        r.role_name
    FROM users u
    JOIN roles r
        ON u.role_id = r.role_id
    WHERE u.username = ?
";

$stmt = mysqli_prepare($koneksi, $query);

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $username
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

if (!$user) {

    header("Location: login.php?error=Username atau password salah");
    exit;
}

if ($user["status"] !== "aktif") {

    header("Location: login.php?error=Akun tidak aktif");
    exit;
}

if (!password_verify($password, $user["password"])) {

    header("Location: login.php?error=Username atau password salah");
    exit;
}

session_regenerate_id(true);

$_SESSION["user_id"] = $user["user_id"];
$_SESSION["username"] = $user["username"];
$_SESSION["role_id"] = $user["role_id"];
$_SESSION["role_name"] = $user["role_name"];
$_SESSION["last_activity"] = time();

header("Location: ../index.php");
exit;