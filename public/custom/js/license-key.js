// license-key.js

// optional: submit handler to show loading UI
document.addEventListener('DOMContentLoaded', function () {
    const licenseForm = document.getElementById('licenseForm');
    if (licenseForm) {
        licenseForm.addEventListener('submit', function () {
            const btn = this.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerText = 'Activating...';
            }
        });
    }

    // enforce autocomplete off on all inputs
    document.querySelectorAll('input').forEach(el => {
        el.setAttribute('autocomplete', 'off');
        el.setAttribute('autocorrect', 'off');
        el.setAttribute('autocapitalize', 'off');
        el.setAttribute('spellcheck', 'false');
    });
});
