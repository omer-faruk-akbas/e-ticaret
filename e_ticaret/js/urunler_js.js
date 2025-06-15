function sepete_ekle(urunId, urunAdi, urunFiyat, fotoYolu) {
    fetch('../sorgular/sepeti_kaydet.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ urun_id: urunId, urun_adi: urunAdi, urun_fiyat: urunFiyat, foto_yolu: fotoYolu })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Sepet bilgisini güncellemek için sayfayı yeniden yükle
        }
    })
    .catch(error => console.error("Hata:", error));
}
