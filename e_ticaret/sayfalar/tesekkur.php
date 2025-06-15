<?php
session_start();
if (!isset($_SESSION['success_message'])) {
    header("Location: urunler.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teşekkürler</title>
    <link rel="stylesheet" type="text/css" href="../css/tesekkur_css.css">
</head>
<body>
    <h1>Teşekkürler!</h1>
    <p><?php echo $_SESSION['success_message']; ?></p>
    <a href="urunler.php">Alışverişe Devam Et</a>
</body>
</html>
