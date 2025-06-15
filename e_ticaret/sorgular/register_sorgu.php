<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['in'])) {
    // Formdan gelen verileri al
    $name = $_POST["name"];
    $surname = $_POST["surname"];
    $adres = $_POST["adres"];
    $email = $_POST["email"];
    $password = $_POST["password"];

    $serverName = "YOUR_SERVER";
    $connectionOptions = [
        "Database" => "YOUR_DATABASE",
        "Uid" => "YOUR_USERNAME",
        "PWD" => "YOUR_PASSWORD",
        "CharacterSet" => "UTF-8"


    ];

    // Bağlantıyı oluştur
    $conn = sqlsrv_connect($serverName, $connectionOptions);

    if ($conn === false) {
        die("Veritabanı bağlantısı başarısız: " . print_r(sqlsrv_errors(), true));
    }

    // SQL sorgusu
    $sql = "INSERT INTO kullanicilar (ad, soyad, adres, eposta, sifre, kayit_tarihi) 
            VALUES (?, ?, ?, ?, ?, GETDATE())";

    // Sorguyu hazırlayıp çalıştır
    $params = [$name, $surname, $adres, $email, $password];
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        // Hata durumunda kayıt sayfasına hata mesajıyla dön
        header("Location: ../giris.php?error=kayit");
        exit();
    } 

    else {
        $sql = "SELECT id, ad, soyad, eposta, sifre FROM kullanicilar WHERE eposta = ?";
        $stmt = sqlsrv_query($conn, $sql, [$email]);
        $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        // Ürünler sayfasına yönlendir
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['ad'];
        $_SESSION['user_surname'] = $user['soyad'];
        $_SESSION['user_email'] = $user['eposta'];
        header("Location: ../sayfalar/urunler.php");
        exit();
    }

    // Bağlantıyı kapat
    sqlsrv_close($conn);
}
?>
