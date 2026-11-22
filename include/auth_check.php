<?php

require_once __DIR__ . "/../session.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: /sehatku/autentikasi/login.php");
    exit;
}

function cekRole($role)
{
    if (!isset($_SESSION['role_name']) || $_SESSION['role_name'] !== $role) {
        header("Location: /sehatku/index.php");
        exit;
    }
}


function cekRoleSalahSatu($daftarRole)
{
    if (!isset($_SESSION['role_name']) || !in_array($_SESSION['role_name'], $daftarRole, true)) {
        header("Location: /sehatku/index.php");
        exit;
    }
}
