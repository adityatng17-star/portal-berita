// Skrip panel admin

document.addEventListener('DOMContentLoaded', function () {

    // Konfirmasi sebelum submit form yang punya atribut data-konfirmasi
    document.querySelectorAll('form[data-konfirmasi]').forEach(function (form) {
        form.addEventListener('submit', function (ev) {
            if (!window.confirm(form.getAttribute('data-konfirmasi'))) {
                ev.preventDefault();
            }
        });
    });

    // Penghitung sisa karakter pada textarea
    document.querySelectorAll('textarea[data-hitung]').forEach(function (area) {
        var tujuan = document.querySelector(area.getAttribute('data-hitung'));
        if (!tujuan) return;

        function perbarui() {
            tujuan.textContent = area.maxLength - area.value.length;
        }
        area.addEventListener('input', perbarui);
        perbarui();
    });

    // Pratinjau gambar sebelum diunggah
    document.querySelectorAll('input[type="file"][data-pratinjau]').forEach(function (input) {
        var gambar = document.querySelector(input.getAttribute('data-pratinjau'));
        if (!gambar) return;

        input.addEventListener('change', function () {
            var berkas = input.files && input.files[0];
            if (!berkas) return;
            if (berkas.size > 2 * 1024 * 1024) {
                alert('Ukuran gambar maksimal 2 MB.');
                input.value = '';
                return;
            }
            gambar.src = URL.createObjectURL(berkas);
            gambar.hidden = false;
        });
    });

});
