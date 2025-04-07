/**
 * 🔐 Obsługuje formularz aktywacji licencji AJAX
 * Wymaga: #intencje-activate-license-form z polami license_key i api_key
 */
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('intencje-activate-license-form');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const licenseKey = form.querySelector('[name="license_key"]').value.trim();
        const apiKey     = form.querySelector('[name="api_key"]').value.trim();
        const nonce      = form.querySelector('[name="_wpnonce"]').value;

        if (!licenseKey || !apiKey) {
            alert('Uzupełnij oba pola: klucz licencji i klucz API.');
            return;
        }

        const data = new FormData();
        data.append('action', 'intencje_activate_license');
        data.append('license_key', licenseKey);
        data.append('api_key', apiKey);
        data.append('_wpnonce', nonce);

        fetch(ajaxurl, {
            method: 'POST',
            body: data,
        })
        .then(response => response.json())
        .then(result => {
            alert(result.message || 'Nieznana odpowiedź z serwera.');
            if (result.status === 'success') {
                location.reload();
            }
        })
        .catch(() => {
            alert('❌ Wystąpił błąd połączenia z serwerem.');
        });
    });
});
