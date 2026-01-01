/**
 * TudásBázis - Vanilla JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {
    // CSRF token kinyerése
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ||
        document.querySelector('input[name="csrf_token"]')?.value || '';

    // Szavazás kezelése
    initSzavazas();

    // Flash üzenetek automatikus eltüntetése
    initFlashMessages();
});

/**
 * Szavazás inicializálása
 */
function initSzavazas() {
    document.querySelectorAll('.szavazat-gomb').forEach(function (gomb) {
        gomb.addEventListener('click', async function (e) {
            e.preventDefault();

            const valaszId = this.dataset.valaszId;
            const irany = this.dataset.irany;
            const csrfToken = document.querySelector('input[name="csrf_token"]')?.value || '';

            if (!valaszId || !irany) {
                return;
            }

            try {
                const response = await fetch('/szavazas', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `valasz_id=${valaszId}&irany=${irany}&csrf_token=${csrfToken}`
                });

                const data = await response.json();

                if (data.success) {
                    // Szavazat siker - oldal frissítése
                    location.reload();
                } else {
                    showMessage(data.error || 'Hiba történt a szavazás során.', 'error');
                }
            } catch (error) {
                showMessage('Hiba történt a szavazás során.', 'error');
                console.error('Szavazás hiba:', error);
            }
        });
    });
}

/**
 * Flash üzenetek kezelése
 */
function initFlashMessages() {
    document.querySelectorAll('.flash-message').forEach(function (msg) {
        // 5 másodperc után eltűnik
        setTimeout(function () {
            msg.style.transition = 'opacity 0.5s';
            msg.style.opacity = '0';
            setTimeout(function () {
                msg.remove();
            }, 500);
        }, 5000);
    });
}

/**
 * Üzenet megjelenítése
 */
function showMessage(text, type = 'success') {
    const container = document.querySelector('.main-content') || document.body;

    const msg = document.createElement('div');
    msg.className = `flash-message flash-${type}`;
    msg.textContent = text;

    container.insertBefore(msg, container.firstChild);

    setTimeout(function () {
        msg.style.transition = 'opacity 0.5s';
        msg.style.opacity = '0';
        setTimeout(function () {
            msg.remove();
        }, 500);
    }, 5000);
}

/**
 * Escape HTML karakterek
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
