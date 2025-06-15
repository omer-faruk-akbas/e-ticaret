// Sekme geçişi fonksiyonu
function showTab(tabId) {
    document.getElementById('login').style.display = tabId === 'login' ? 'block' : 'none';
    document.getElementById('register').style.display = tabId === 'register' ? 'block' : 'none';
    document.getElementById('satici').style.display = tabId === 'satici' ? 'block' : 'none';

    // active sınıfı kaldırılıyor
    document.querySelectorAll('.tab-button').forEach(button => button.classList.remove('active'));
    // Seçili sekmeye active sınıfı ekleniyor
    document.querySelector(`.tab-button[onclick="showTab('${tabId}')"]`).classList.add('active');
}
