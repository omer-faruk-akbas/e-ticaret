<?php
session_start();

// Veritabanı bağlantısı
$serverName = "YOUR_SERVER";
$connectionOptions = [
    "Database" => "YOUR_DATABASE",
    "Uid" => "YOUR_USERNAME",
    "PWD" => "YOUR_PASSWORD",
    "CharacterSet" => "UTF-8"
];


$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT id, ad, soyad, eposta, sifre FROM saticilar WHERE eposta = ?";
    $stmt = sqlsrv_query($conn, $sql, [$email]);

    if ($stmt === false) {
        die("Sorgu hatası: " . print_r(sqlsrv_errors(), true));
    }

    $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    if ($user && $user['sifre'] === $password) {
        // Kullanıcı bilgilerini oturumda sakla
        $_SESSION['satici_id'] = $user['id'];
        $_SESSION['satici_name'] = $user['ad'];
        $_SESSION['satici_surname'] = $user['soyad'];
        $_SESSION['satici_email'] = $user['eposta'];
        // Ürünler sayfasına yönlendir
        header("Location: ../sayfalar/satici_panel.php");
        exit();
    } else {
        header("Location: ../giris.php?error=satici");
        exit();
    }
}
?>
