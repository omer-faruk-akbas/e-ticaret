<?php
// Hangi sekmenin açık olacağını belirlemek için değişken
$activeTab = isset($_GET['error']) && $_GET['error'] === 'kayit' ? 'register' : (isset($_GET['error']) && $_GET['error'] === 'satici' ? 'satici' : 'login');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş ve Üye Ol</title>
    <link rel="stylesheet" href="css/giris_css.css">
    <script src="js/giris_js.js"></script>
</head>
<body>

    <div class="container">
        <div class="site-name">SahipOl</div>

        <!-- Sekmeler -->
        <div class="tabs">
            <button class="tab-button <?php echo $activeTab === 'login' ? 'active' : ''; ?>" onclick="showTab('login')">Giriş Yap</button>
            <button class="tab-button <?php echo $activeTab === 'register' ? 'active' : ''; ?>" onclick="showTab('register')">Üye Ol</button>
            <button class="tab-button <?php echo $activeTab === 'satici' ? 'active' : ''; ?>" onclick="showTab('satici')">Satıcı Giriş</button>
        </div>

        <!-- Giriş Yap Bölümü -->
         <div id="login" style="display: <?php echo $activeTab === 'login' ? 'block' : 'none'; ?>;">
            <form method="POST" action="sorgular/login_sorgu.php">
                <label for="email">E-Posta</label>
                <input type="email" id="email" name="email" placeholder="E-posta giriniz" required>
                <label for="password">Şifre</label>
                <input type="password" id="password" name="password" placeholder="Şifre giriniz" required>
                <button type="submit" name="on">Giriş Yap</button>
            </form>
            <a href="sayfalar/urunler.php">Giriş yapmadan devam et</a>
            <?php if (isset($_GET['error']) && $_GET['error'] === 'giris'): ?>
                <div class="error-message" style="color: red; margin-top: 10px;">
                    E-posta veya şifre hatalı!
                </div>
            <?php endif; ?>
        </div>

        <div id="satici" style="display: <?php echo $activeTab === 'satici' ? 'block' : 'none'; ?>;">
            <form method="POST" action="sorgular/satici_sorgu.php">
                <label for="email">E-Posta</label>
                <input type="email" id="email" name="email" placeholder="E-posta giriniz" required>
                <label for="password">Şifre</label>
                <input type="password" id="password" name="password" placeholder="Şifre giriniz" required>
                <button type="submit" name="on">Giriş Yap</button>
            </form>

            <?php if (isset($_GET['error']) && $_GET['error'] === 'satici'): ?>
                <div class="error-message" style="color: red; margin-top: 10px;">
                    E-posta veya şifre hatalı!
                </div>
            <?php endif; ?>
        </div>

        <!-- Üye Ol Bölümü -->
        <div id="register" style="display: <?php echo $activeTab === 'register' ? 'block' : 'none'; ?>;">
            <form method="POST" action="sorgular/register_sorgu.php">
                <label for="name">Ad</label>
                <input type="text" id="name" name="name" placeholder="Adınızı giriniz" required>

                <label for="surname">Soyad</label>
                <input type="text" id="surname" name="surname" placeholder="Soyadınızı giriniz" required>

                <label for="adres">Adres</label>
                <input type="text" id="adres" name="adres" placeholder="Adres giriniz" required>

                <label for="email">E-posta</label>
                <input type="email" id="email" name="email" placeholder="E-posta giriniz" required>

                <label for="password">Şifre</label>
                <input type="password" id="password" name="password" placeholder="Şifre oluşturunuz" required>

                <button type="submit" name="in">Üye Ol</button>
            </form>
            <?php if (isset($_GET['error']) && $_GET['error'] === 'kayit'): ?>
                <div class="error-message" style="color: red; margin-top: 10px;">
                    Kayıt başarısız!
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
