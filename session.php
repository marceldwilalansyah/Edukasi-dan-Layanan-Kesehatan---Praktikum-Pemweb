<?php

session_start();

$timeout = 900; // 15 menit

if (isset($_SESSION['last_activity'])) {

    if (time() - $_SESSION['last_activity'] > $timeout) {

        session_unset();
        session_destroy();

        header("Location: /sehatku/autentikasi/login.php?expired=1");
        exit;
    }
}

$_SESSION['last_activity'] = time();