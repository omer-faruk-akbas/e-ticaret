<?php
session_start();

// Kullanıcı giriş yapmamışsa giriş sayfasına yönlendir
if (!isset($_SESSION['user_id'])) {
    header("Location: giris.php");
    exit();
}

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

// Güncelleme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ad = $_POST['ad'];
    $soyad = $_POST['soyad'];
    $adres = $_POST['adres'];
    $sifre = $_POST['sifre'];

    // E-posta bilgisi oturumdan alınır
    $email = $_SESSION['user_email'];

    // Kullanıcı bilgilerini güncelle
    $update_sql = "UPDATE kullanicilar SET ad = ?, soyad = ?, adres = ?, sifre = ? WHERE eposta = ?";
    $params = [$ad, $soyad, $adres, $sifre, $email];
    $stmt = sqlsrv_query($conn, $update_sql, $params);

    if ($stmt === false) {
        echo "Güncelleme hatası: " . print_r(sqlsrv_errors(), true);
    } else {
        // Oturum bilgilerini güncelle
        $_SESSION['user_name'] = $ad;
        $_SESSION['user_surname'] = $soyad;
        echo "<p style='color: white;'>Bilgiler başarıyla güncellendi.</p>";
    }
}

// Kullanıcının mevcut bilgilerini çek
$user_id = $_SESSION['user_id'];
$sql = "SELECT ad, soyad, eposta, adres, sifre FROM kullanicilar WHERE id = ?";
$params = [$user_id];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die("Kullanıcı bilgileri alınamadı: " . print_r(sqlsrv_errors(), true));
}

$user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
sqlsrv_close($conn);
?>

<!DOCTYPE html>
    <html lang="tr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Hesabım</title>
            <link rel="stylesheet" href="../css/hesabim_css.css">
         
        </head>
    <body>
        <h1>Hesabım</h1>

        <div class="info">
            <form method="POST" action="">

                <label for="ad">Ad:</label>
                <input type="text" id="ad" name="ad" value="<?php echo htmlspecialchars($user['ad']); ?>" required>

                <label for="soyad">Soyad:</label>
                <input type="text" id="soyad" name="soyad" value="<?php echo htmlspecialchars($user['soyad']); ?>" required>

                <label for="adres">Adres:</label>
                <input type="text" id="adres" name="adres" value="<?php echo htmlspecialchars($user['adres']); ?>" required>

                <label for="sifre">Şifre:</label>
                <input type="password" id="sifre" name="sifre" value="<?php echo htmlspecialchars($user['sifre']); ?>" required>

                <button type="submit" class="btn btn-update">Bilgileri Güncelle</button>
            </form>
        </div>

        <a href="urunler.php" class="btn btn-back">Ürünlere Geri Dön</a>
        <a href="../sorgular/cikis.php" class="btn btn-logout">Çıkış Yap</a>
    </body>
</html>
