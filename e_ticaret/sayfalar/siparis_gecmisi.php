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

// Kullanıcı ID'sini al
$user_id = $_SESSION['user_id'];

// Sipariş geçmişini çek
$siparisler_sql = "
    SELECT 
        s.id AS siparis_id,
        s.siparis_tarihi,
        sd.urun_id,
        sd.adet,
        sd.birim_fiyat,
        u.urun_adi,
        u.foto_yolu
    FROM 
        siparisler s
    INNER JOIN 
        siparis_detaylari sd ON s.id = sd.siparis_id
    INNER JOIN 
        urunler u ON sd.urun_id = u.id
    WHERE 
        s.kullanici_id = ?
    ORDER BY 
        s.siparis_tarihi DESC, s.id DESC
";

$params = [$user_id];
$stmt = sqlsrv_query($conn, $siparisler_sql, $params);

if ($stmt === false) {
    die("Sipariş geçmişi sorgusu başarısız: " . print_r(sqlsrv_errors(), true));
}

// Sipariş geçmişini düzenli bir yapıya dönüştür
$siparisler = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $siparis_id = $row['siparis_id'];

    if (!isset($siparisler[$siparis_id])) {
        $siparisler[$siparis_id] = [
            'siparis_tarihi' => $row['siparis_tarihi'],
            'urunler' => []
        ];
    }

    $siparisler[$siparis_id]['urunler'][] = [
        'urun_adi' => $row['urun_adi'],
        'adet' => $row['adet'],
        'birim_fiyat' => $row['birim_fiyat'],
        'foto_yolu' => $row['foto_yolu']
    ];
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sipariş Geçmişi</title>
    <link rel="stylesheet" href="../css/siparis_gecmisi_css.css">
</head>
<body>
    <h1>Sipariş Geçmişi</h1>

    <div class="siparis-container">
        <?php if (!empty($siparisler)): ?>
            <?php foreach ($siparisler as $siparis_id => $siparis): ?>
                <div class="siparis">
                    <h2>Sipariş No: <?php echo $siparis_id; ?> | Tarih: <?php echo $siparis['siparis_tarihi']->format('Y-m-d '); ?></h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Resim</th>
                                <th>Ürün Adı</th>
                                <th>Adet</th>
                                <th>Birim Fiyat</th>
                                <th>Toplam</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($siparis['urunler'] as $urun): ?>
                                <tr>
                                    <td><img src="/e_ticaret/<?php echo htmlspecialchars($urun['foto_yolu']); ?>" alt="<?php echo htmlspecialchars($urun['urun_adi']); ?>" class="product-image"></td>
                                    <td><?php echo htmlspecialchars($urun['urun_adi']); ?></td>
                                    <td><?php echo $urun['adet']; ?></td>
                                    <td><?php echo number_format($urun['birim_fiyat'], 2); ?> TL</td>
                                    <td><?php echo number_format($urun['adet'] * $urun['birim_fiyat'], 2); ?> TL</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Sipariş geçmişiniz bulunmamaktadır.</p>
        <?php endif; ?>

        <a href="urunler.php" class="back-button">Ürünlere Geri Dön</a>
    </div>
</body>
</html>
