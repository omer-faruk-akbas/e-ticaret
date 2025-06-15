<?php
session_start();

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$urun_id = $input['urun_id'];
$urun_adi = $input['urun_adi'];
$urun_fiyat = $input['urun_fiyat'];
$foto_yolu = $input['foto_yolu'];

$found = false;

foreach ($_SESSION['cart'] as &$item) {
    if ($item['urun_id'] == $urun_id) {
        $item['adet'] += 1;
        $found = true;
        break;
    }
}

if (!$found) {
    $_SESSION['cart'][] = [
        'urun_id' => $urun_id,
        'urun_adi' => $urun_adi,
        'urun_fiyat' => $urun_fiyat,
        'foto_yolu' => $foto_yolu,
        'adet' => 1
    ];
}

echo json_encode(['success' => true]);
?>
