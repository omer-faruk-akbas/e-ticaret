    <?php
    session_start();
    $user_name = $_SESSION['user_name'];
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

    // Sepetteki ürün sayısını hesapla
    $urun_sayisi = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

    // Kullanıcı oturum bilgilerini kontrol et
    $user_logged_in = isset($_SESSION['user_id']);
    ?>

    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ürün Listesi</title>
        <link rel="stylesheet" href="../css/urunler_css.css">
        <script src="../js/urunler_js.js"></script>
    </head>
    <body>

        <!-- Sol Menü -->
        <div class="sidebar">
            <?php if ($user_logged_in): ?>
            <h3>Hoşgeldiniz, <?php echo htmlspecialchars($user_name); ?></h3>
            <?php endif; ?>
            
            <h2>Menü</h2>
            <div class="buttons">               
                
                <?php if ($user_logged_in): ?>
                    <button class="btn-sepet" onclick="window.location.href='sepet.php'">
                        Sepet:<?php echo $urun_sayisi; ?> ürün</button> 
                    <button class="btn-hesabim" onclick="window.location.href='hesabim.php'">Hesabım</button>
                    <button class="btn-siparis" onclick="window.location.href='siparis_gecmisi.php'">Sipariş geçmişi</button>
                <?php else: ?>
                    <button class="btn-hesabim" onclick="window.location.href='../giris.php'">Giriş Yap</button>
                <?php endif; ?>
            </div>
            <h2>Kategoriler</h2>
            <ul>
                <?php
                $kategori_sql = "SELECT * FROM kategoriler";
                $kategori_stmt = sqlsrv_query($conn, $kategori_sql);

                if ($kategori_stmt === false) {
                    die("Kategori sorgu hatası: " . print_r(sqlsrv_errors(), true));
                }

                echo '<li><a href="urunler.php">Tüm Ürünler</a></li>';
                while ($kategori = sqlsrv_fetch_array($kategori_stmt, SQLSRV_FETCH_ASSOC)) {
                    echo '<li><a href="urunler.php?kategori_id=' . $kategori['id'] . '">' . htmlspecialchars($kategori['kategori_adi']) . '</a></li>';
                }
                ?>
            </ul>
        </div>

        <!-- Ürünler -->
        <div class="container">
            <!-- Üst Menü ve Arama Kısmı -->
            <div class="topbar">
                <h1>SahipOl</h1>
                <form class="search-bar" method="GET" action="urunler.php">
                    <input type="text" name="arama" placeholder="Ürün adı veya açıklama ara" value="<?php echo isset($_GET['arama']) ? htmlspecialchars($_GET['arama']) : ''; ?>">
                    <button type="submit">Ara</button>
                </form>
            </div>
            <?php
            $kategori_id = isset($_GET['kategori_id']) ? intval($_GET['kategori_id']) : 0;
            $arama = isset($_GET['arama']) ? $_GET['arama'] : '';

            if ($kategori_id > 0) {
                $urun_sql = "SELECT urunler.id AS urun_id, urunler.urun_adi, urunler.aciklama, urunler.fiyat, urunler.stok, urunler.foto_yolu, kategoriler.kategori_adi, saticilar.ad AS satici_ad, saticilar.soyad AS satici_soyad
                             FROM urunler
                             INNER JOIN kategoriler ON urunler.kategori_id = kategoriler.id
                             INNER JOIN saticilar ON urunler.satici_id = saticilar.id
                             WHERE urunler.kategori_id = ?";
                $urun_stmt = sqlsrv_query($conn, $urun_sql, [$kategori_id]);
            } 
            elseif (!empty($arama)) {
                // SQL Sorgusu: Arama için
                $urun_sql = "SELECT urunler.id AS urun_id, urunler.urun_adi, urunler.aciklama, urunler.fiyat, urunler.stok, urunler.foto_yolu, kategoriler.kategori_adi, saticilar.ad AS satici_ad, saticilar.soyad AS satici_soyad
                             FROM urunler
                             INNER JOIN kategoriler ON urunler.kategori_id = kategoriler.id
                             INNER JOIN saticilar ON urunler.satici_id = saticilar.id
                             WHERE urunler.urun_adi LIKE ? OR urunler.aciklama LIKE ?";

                // Parametreleri tanımla
                $parametreler = ['%' . $arama . '%', '%' . $arama . '%'];

                // Sorguyu çalıştır
                $urun_stmt = sqlsrv_query($conn, $urun_sql, $parametreler);

                // Hata kontrolü
                if ($urun_stmt === false) {
                    die("Arama sorgu hatası: " . print_r(sqlsrv_errors(), true));
                }
            }

            else {
                $urun_sql = "SELECT urunler.id AS urun_id, urunler.urun_adi, urunler.aciklama, urunler.fiyat, urunler.stok, urunler.foto_yolu, kategoriler.kategori_adi, saticilar.ad AS satici_ad, saticilar.soyad AS satici_soyad
                             FROM urunler
                             INNER JOIN kategoriler ON urunler.kategori_id = kategoriler.id
                             INNER JOIN saticilar ON urunler.satici_id = saticilar.id";
                $urun_stmt = sqlsrv_query($conn, $urun_sql);
            }

            if ($urun_stmt === false) {
                die("Ürün sorgu hatası: " . print_r(sqlsrv_errors(), true));
            }

            while ($urun = sqlsrv_fetch_array($urun_stmt, SQLSRV_FETCH_ASSOC)) {
                echo "<div class='card'>";
                echo "<img src='/e_ticaret/" . htmlspecialchars($urun['foto_yolu']) . "' alt='" . htmlspecialchars($urun['urun_adi']) . "'>";

                echo "<h2>" . htmlspecialchars($urun['urun_adi']) . "</h2>";
                echo "<p>" . htmlspecialchars($urun['aciklama']) . "</p>";
                echo "<p><strong>Fiyat:</strong> " . number_format($urun['fiyat'], 2) . " TL</p>";
                echo "<p><strong>Stok:</strong> " . htmlspecialchars($urun['stok']) . "</p>";
                echo "<p><strong>Kategori:</strong> " . htmlspecialchars($urun['kategori_adi']) . "</p>";
                echo "<p><strong>Satıcı:</strong> " . htmlspecialchars($urun['satici_ad'] . " " . $urun['satici_soyad']) . "</p>";
                echo "<button class='add-to-cart' onclick='sepete_ekle(" . $urun['urun_id'] . ", \"" . $urun['urun_adi'] . "\", " . $urun['fiyat'] . ", \"" . $urun['foto_yolu'] . "\")'>Sepete Ekle</button>";
                echo "</div>";
            }

            sqlsrv_close($conn);
            ?>
        </div>
    </body>
    </html>
