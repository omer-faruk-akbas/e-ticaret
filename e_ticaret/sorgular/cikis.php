<?php
session_start();

// Oturum bilgilerini temizle
session_unset();
session_destroy();

// Giriş sayfasına yönlendir
header("Location: ../giris.php");
exit();
?>
