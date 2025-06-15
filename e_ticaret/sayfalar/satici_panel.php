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

// Satıcı bilgisi
$satici_id = $_SESSION['satici_id'];

// Ürün güncelleme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_product'])) {
    $urun_id = $_POST['urun_id'];
    $urun_adi = $_POST['urun_adi'];
    $aciklama = $_POST['aciklama'];
    $fiyat = $_POST['fiyat'];
    $stok = $_POST['stok'];

    $update_sql = "UPDATE urunler SET urun_adi = ?, aciklama = ?, fiyat = ?, stok = ? WHERE id = ? AND satici_id = ?";
    $params = [$urun_adi, $aciklama, $fiyat, $stok, $urun_id, $satici_id];

    $stmt = sqlsrv_query($conn, $update_sql, $params);

    if ($stmt === false) {
        $_SESSION['update_message'] = "<p style='color: red;'>Ürün güncelleme başarısız: " . print_r(sqlsrv_errors(), true) . "</p>";
    } else {
        $_SESSION['update_message'] = "<p style='color: green;'>Ürün başarıyla güncellendi.</p>";
    }

    // Sayfayı yeniden yükleyerek mesajı göstermek
    header("Location: satici_panel.php");
    exit();
}

// Satıcıya ait ürünleri çek
$urun_sql = "SELECT * FROM urunler WHERE satici_id = ?";
$urun_stmt = sqlsrv_query($conn, $urun_sql, [$satici_id]);

if ($urun_stmt === false) {
    die("Ürün sorgu hatası: " . print_r(sqlsrv_errors(), true));
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Satıcı Paneli</title>
    <link rel="stylesheet" href="../css/satici_panel_css.css">
</head>
<body>
    <div class="container">
        <h1>Satıcı Paneli</h1>

        <!-- Güncelleme Mesajı -->
        <?php
        if (isset($_SESSION['update_message'])) {
            echo $_SESSION['update_message'];
            unset($_SESSION['update_message']); // Mesaj bir kez görüntülendikten sonra kaldırılır
        }
        ?>

        <table>
            <thead>
                <tr>
                    <th>Resim</th>
                    <th>Ürün Adı</th>
                    <th>Açıklama</th>
                    <th>Fiyat</th>
                    <th>Stok</th>
                    <th>Güncelle</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($urun = sqlsrv_fetch_array($urun_stmt, SQLSRV_FETCH_ASSOC)): ?>
                    <tr>
                        <td>
                            <img src="../<?php echo htmlspecialchars($urun['foto_yolu']); ?>" alt="<?php echo htmlspecialchars($urun['urun_adi']); ?>" class="product-image">
                        </td>
                        <td><?php echo htmlspecialchars($urun['urun_adi']); ?></td>
                        <td><?php echo htmlspecialchars($urun['aciklama']); ?></td>
                        <td><?php echo number_format($urun['fiyat'], 2); ?> TL</td>
                        <td><?php echo htmlspecialchars($urun['stok']); ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="urun_id" value="<?php echo $urun['id']; ?>">
                                <input type="text" name="urun_adi" value="<?php echo htmlspecialchars($urun['urun_adi']); ?>" required>
                                <textarea name="aciklama" rows="2" required><?php echo htmlspecialchars($urun['aciklama']); ?></textarea>
                                <input type="number" name="fiyat" value="<?php echo htmlspecialchars($urun['fiyat']); ?>" step="0.01" required>
                                <input type="number" name="stok" value="<?php echo htmlspecialchars($urun['stok']); ?>" required>
                                <button type="submit" name="update_product">Güncelle</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <button class="logout-button" onclick="window.location.href='../sorgular/cikis.php'">Çıkış Yap</button>
    </div>
</body>
</html>
