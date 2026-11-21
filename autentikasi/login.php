<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Sehatku</title>

    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="container auth-container">

    <h1>Login</h1>

    <?php if (isset($_GET['error'])): ?>

        <div class="alert error">
            <?= htmlspecialchars($_GET['error']) ?>
        </div>

    <?php endif; ?>


    <?php if (isset($_GET['success'])): ?>

        <div class="alert success">
            <?= htmlspecialchars($_GET['success']) ?>
        </div>

    <?php endif; ?>


    <?php if (isset($_GET['expired'])): ?>

        <div class="alert error">
            Session telah berakhir. Silakan login kembali.
        </div>

    <?php endif; ?>


    <form action="proses_login.php" method="POST">

        <label>Username</label>

        <input
            type="text"
            name="username"
            required
        >


        <label>Password</label>

        <input
            type="password"
            name="password"
            required
        >


        <button type="submit">
            Login
        </button>

    </form>


    <p>
        Belum punya akun?
        <a href="register.php">Daftar</a>
    </p>

</div>

</body>
</html>