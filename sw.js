const CACHE_NAME = 'gymwaze-v1';
const urlsToCache = [
    'login.html',
    'cadastro.html',
    'esqueciSenha.html',
    'index.html', // Tela principal do mapa
    'login.css',
    'style.css',
    'script.js'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => cache.addAll(urlsToCache))
    );
});

self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request).then(response => response || fetch(event.request))
    );
});