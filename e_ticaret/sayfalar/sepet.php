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

// POST işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['siparis'])) {
        // Stok kontrolü
        $stok_hatasi = [];
        foreach ($_SESSION['cart'] as $item) {
            $stok_sql = "SELECT stok FROM urunler WHERE id = ?";
            $stok_params = [$item['urun_id']];
            $stok_stmt = sqlsrv_query($conn, $stok_sql, $stok_params);

            if ($stok_stmt === false || !($stok = sqlsrv_fetch_array($stok_stmt, SQLSRV_FETCH_ASSOC))) {
                $_SESSION['error_message'] = "Stok bilgisi alınırken hata oluştu.";
                header("Location: sepet.php");
                exit();
            }

            if ($stok['stok'] < $item['adet']) {
                $stok_hatasi[] = "'{$item['urun_adi']}' ürünü için yeterli stok yok. Kalan stok: {$stok['stok']}.";
            }
        }

        // Eğer stok hatası varsa, hata mesajını oturuma ekle ve kullanıcıyı bilgilendir
        if (!empty($stok_hatasi)) {
            $_SESSION['error_message'] = implode('<br>', $stok_hatasi);
            header("Location: sepet.php");
            exit();
        }

        // Sipariş kaydetme işlemi (stok hatası yoksa)
        $user_id = $_SESSION['user_id'];
        $siparis_tarihi = date('Y-m-d H:i:s');

        // Sipariş kaydet
        $siparis_sql = "INSERT INTO siparisler (kullanici_id, siparis_tarihi) OUTPUT INSERTED.id VALUES (?, ?)";
        $siparis_params = [$user_id, $siparis_tarihi];
        $siparis_stmt = sqlsrv_query($conn, $siparis_sql, $siparis_params);

        if ($siparis_stmt) {
            $siparis = sqlsrv_fetch_array($siparis_stmt, SQLSRV_FETCH_ASSOC);
            if ($siparis && isset($siparis['id'])) {
                $siparis_id = $siparis['id'];

                // Sipariş detaylarını kaydet ve stok güncelle
                foreach ($_SESSION['cart'] as $item) {
                    $detay_sql = "INSERT INTO siparis_detaylari (siparis_id, urun_id, adet, birim_fiyat) VALUES (?, ?, ?, ?)";
                    $detay_params = [$siparis_id, $item['urun_id'], $item['adet'], $item['urun_fiyat']];
                    $detay_stmt = sqlsrv_query($conn, $detay_sql, $detay_params);

                    // Stok güncelle
                    $stok_update_sql = "UPDATE urunler SET stok = stok - ? WHERE id = ?";
                    $stok_update_params = [$item['adet'], $item['urun_id']];
                    $stok_update_stmt = sqlsrv_query($conn, $stok_update_sql, $stok_update_params);

                    if ($detay_stmt === false || $stok_update_stmt === false) {
                        $_SESSION['error_message'] = "Sipariş işlemi sırasında bir hata oluştu.";
                        header("Location: sepet.php");
                        exit();
                    }
                }

                // Sipariş başarılı
                unset($_SESSION['cart']);
                $_SESSION['success_message'] = "Sipariş başarıyla oluşturuldu. Sipariş numaranız: $siparis_id";
                header("Location: tesekkur.php");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Sipariş oluşturulamadı. Veritabanı hatası.";
            header("Location: sepet.php");
            exit();
        }
    }
     else if (isset($_POST['increase'])) {
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['urun_id'] == $_POST['urun_id']) {
                $item['adet'] += 1;
                break;
            }
        }
    } elseif (isset($_POST['decrease'])) {
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['urun_id'] == $_POST['urun_id'] && $item['adet'] > 1) {
                $item['adet'] -= 1;
                break;
            }
        }
    } elseif (isset($_POST['remove'])) {
        $_SESSION['cart'] = array_filter($_SESSION['cart'], function ($item) {
            return $item['urun_id'] != $_POST['urun_id'];
        });
    } elseif (isset($_POST['clear'])) {
        $_SESSION['cart'] = [];
    }
}
?>


<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sepetim</title>
    <link rel="stylesheet" href="../css/sepet_css.css">
</head>
<body>
    <h1>Sepetim</h1>
    <?php
    // Hata mesajını göster
    if (isset($_SESSION['error_message'])) {
        echo '<div class="error-message" style="color: red; background-color: #ffe6e6; border: 1px solid red; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center;">' 
             . $_SESSION['error_message'] . 
             '</div>';
        unset($_SESSION['error_message']);
    }
    ?>
    <?php if (!empty($_SESSION['cart'])): ?>
        <table>
            <thead>
                <tr>
                    <th>Resim</th>
                    <th>Ürün Adı</th>
                    <th>Fiyat</th>
                    <th>Adet</th>
                    <th>Toplam</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['cart'] as $item): ?>
                    <tr>
                        <td><img src="/e_ticaret/<?php echo htmlspecialchars($item['foto_yolu']); ?>" class="product-image"></td>
                        <td><?php echo htmlspecialchars($item['urun_adi']); ?></td>
                        <td><?php echo number_format($item['urun_fiyat'], 2); ?> TL</td>
                        <td><?php echo $item['adet']; ?></td>
                        <td><?php echo number_format($item['urun_fiyat'] * $item['adet'], 2); ?> TL</td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="urun_id" value="<?php echo $item['urun_id']; ?>">
                                <button type="submit" name="increase">+</button>
                                <button type="submit" name="decrease">-</button>
                                <button type="submit" name="remove">Kaldır</button>
                            </form>
                        </td>
                    </tr>
                    <?php $total_price += $item['urun_fiyat'] * $item['adet']; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p style="color: white;"><strong>Toplam Fiyat:</strong> <?php echo number_format($total_price, 2); ?> TL</p>

        <form method="POST" style="text-align: center;">
            <button type="submit" name="clear">Sepeti Temizle</button>

            <button type="submit" name="siparis">Sipariş Oluştur</button>
        </form>
    <?php else: ?>
        <p style="color: white;">Sepetinizde ürün bulunmamaktadır.</p>
        <a href="urunler.php" style="background: #0077b6; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;">Ürünlere Göz At</a>
    <?php endif; ?>
</body>
</html>
