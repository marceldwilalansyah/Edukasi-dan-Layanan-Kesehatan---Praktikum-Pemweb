<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <title>Tambah Kategori</title>

    <link rel="stylesheet"
          href="../assets/css/style.css">

</head>

<body>

<div class="container auth-container">

    <a class="back-button" href="index.php">← Kembali ke Kategori</a>

    <h1>Tambah Kategori</h1>

    <form action="proses_tambah.php" method="POST">

        <label>Nama Kategori</label>

        <input
            type="text"
            name="category_name"
            required
        >

        <button type="submit">
            Simpan
        </button>

    </form>

    <a href="index.php">
        Kembali
    </a>

</div>

</body>

</html>