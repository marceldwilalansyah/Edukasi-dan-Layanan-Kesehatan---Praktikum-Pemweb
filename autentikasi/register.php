<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Sehatku</title>

    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="container auth-container">

    <a class="back-button" href="login.php">← Kembali ke Login</a>

    <h1>Daftar Akun</h1>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert error">
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <form action="proses_register.php" method="POST">

        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <label>Konfirmasi Password</label>
        <input type="password" name="konfirmasi_password" required>

        <button type="submit">Daftar</button>

    </form>

    <p>
        Sudah punya akun?
        <a href="login.php">Login</a>
    </p>

</div>

</body>
</html>