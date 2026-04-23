import './bootstrap';

// Cloudflare Turnstile callbacks: bridge the widget's window-level callbacks
// to the Livewire contact form. Handlers are attached to `window` because
// the <div class="cf-turnstile"> widget is inside `wire:ignore` and Cloudflare's
// api.js only sees global names.
window.onTurnstileSuccess = function (token) {
    if (window.Livewire) {
        window.Livewire.dispatch('turnstile-verified', { token });
    }
};
window.onTurnstileExpired = function () {
    if (window.Livewire) {
        window.Livewire.dispatch('turnstile-verified', { token: '' });
    }
    if (window.turnstile) {
        const el = document.querySelector('.cf-turnstile');
        if (el) window.turnstile.reset(el);
    }
};

// Smooth scroll for internal anchors (ported from index-v2.html lines 1232-1238)
document.querySelectorAll('a[href^="#"]').forEach((a) => {
    a.addEventListener('click', (e) => {
        const id = a.getAttribute('href');
        if (!id || id.length < 2) return;
        const el = document.querySelector(id);
        if (!el) return;
        e.preventDefault();
        window.scrollTo({
            top: el.getBoundingClientRect().top + window.scrollY - 80,
            behavior: 'smooth',
        });
    });
});
