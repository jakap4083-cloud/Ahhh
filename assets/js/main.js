/* TopUp ML - Main JS (user) */
(function () {
    'use strict';

    // ===== Tabs kategori =====
    document.querySelectorAll('.tab-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = btn.getAttribute('data-tab');
            document.querySelectorAll('.tab-btn').forEach(function (b) { b.classList.remove('active'); });
            document.querySelectorAll('.tab-panel').forEach(function (p) { p.classList.remove('active'); });
            btn.classList.add('active');
            var panel = document.getElementById(target);
            if (panel) panel.classList.add('active');
        });
    });

    // ===== Ringkasan pesanan =====
    var sumProduct = document.getElementById('sumProduct');
    var sumCat     = document.getElementById('sumCat');
    var sumPrice   = document.getElementById('sumPrice');
    var sumNick    = document.getElementById('sumNick');

    function rupiah(n) {
        n = parseFloat(n) || 0;
        if (n <= 0) return 'Hubungi Admin';
        return 'Rp ' + n.toLocaleString('id-ID');
    }

    document.querySelectorAll('input[name="product_id"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            if (sumProduct) sumProduct.textContent = radio.getAttribute('data-name') || '-';
            if (sumCat) sumCat.textContent = radio.getAttribute('data-cat') || '-';
            if (sumPrice) sumPrice.textContent = rupiah(radio.getAttribute('data-price'));
        });
    });

    // ===== Cek nickname otomatis =====
    var btnCheck = document.getElementById('btnCheckId');
    var resultBox = document.getElementById('nicknameResult');
    var hiddenUsername = document.getElementById('game_username');
    var orderForm = document.getElementById('orderForm');

    function getCsrf() {
        var el = orderForm ? orderForm.querySelector('input[name="csrf_token"]') : null;
        return el ? el.value : '';
    }

    if (btnCheck) {
        btnCheck.addEventListener('click', function () {
            var userId   = (document.getElementById('game_user_id') || {}).value || '';
            var serverId = (document.getElementById('game_server_id') || {}).value || '';
            userId = userId.trim();

            if (!userId) {
                showResult(false, 'Masukkan User ID terlebih dahulu.');
                return;
            }

            btnCheck.disabled = true;
            btnCheck.textContent = '⏳ Mengecek...';

            var body = new FormData();
            body.append('game_user_id', userId);
            body.append('game_server_id', serverId);
            body.append('csrf_token', getCsrf());

            fetch((window.BASE_URL || '') + 'api/check_id.php', { method: 'POST', body: body })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        showResult(true, '✅ Nickname: ' + data.username);
                        if (hiddenUsername) hiddenUsername.value = data.username;
                        if (sumNick) sumNick.textContent = data.username;
                    } else {
                        showResult(false, '⚠️ ' + (data.message || 'ID tidak ditemukan.'));
                        if (hiddenUsername) hiddenUsername.value = '';
                        if (sumNick) sumNick.textContent = '-';
                    }
                })
                .catch(function () { showResult(false, 'Gagal menghubungi server.'); })
                .finally(function () {
                    btnCheck.disabled = false;
                    btnCheck.textContent = '🔍 Cek Nickname';
                });
        });
    }

    function showResult(ok, msg) {
        if (!resultBox) return;
        resultBox.hidden = false;
        resultBox.className = 'nickname-result ' + (ok ? 'ok' : 'err');
        resultBox.textContent = msg;
    }

    // ===== Validasi submit =====
    if (orderForm) {
        orderForm.addEventListener('submit', function (e) {
            var selected = orderForm.querySelector('input[name="product_id"]:checked');
            if (!selected) {
                e.preventDefault();
                alert('Silakan pilih produk terlebih dahulu.');
            }
        });
    }
})();
