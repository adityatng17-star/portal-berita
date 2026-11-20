// Skrip halaman publik

document.addEventListener('DOMContentLoaded', function () {

    // Tombol menu untuk tampilan HP
    var tombol = document.getElementById('tombolMenu');
    var menu = document.getElementById('daftarMenu');
    if (tombol && menu) {
        tombol.addEventListener('click', function () {
            var terbuka = menu.classList.toggle('terbuka');
            tombol.setAttribute('aria-expanded', terbuka ? 'true' : 'false');
        });
    }

    // Penghitung sisa karakter pada textarea (atribut data-hitung berisi selector tujuan)
    document.querySelectorAll('textarea[data-hitung]').forEach(function (area) {
        var tujuan = document.querySelector(area.getAttribute('data-hitung'));
        if (!tujuan) return;

        function perbarui() {
            tujuan.textContent = area.maxLength - area.value.length;
        }
        area.addEventListener('input', perbarui);
        perbarui();
    });

});
