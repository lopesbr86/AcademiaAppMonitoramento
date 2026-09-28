// Registra o service worker (sw.js). Só funciona em https ou em localhost/127.0.0.1, não em file://
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('sw.js').catch(err => console.error('Service worker não registrado:', err));
    });
}