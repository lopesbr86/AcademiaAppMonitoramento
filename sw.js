// Troque o número da versão sempre que publicar uma atualização.
const CACHE_NAME = 'gymup-v2';

// Telas e arquivos que ficam disponíveis mesmo sem internet.
const urlsToCache = [
    'login.html',
    'cadastro.html',
    'esqueciSenha.html',
    'alterarSenha.html',
    'cadastrar_academia.html',
    'index.html',
    'historico.html',
    'perfil.html',
    'style.css',
    'manifest.json',
    'pwa.js',
    'icon.png'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache =>
            // Guarda um por um: se algum arquivo não existir, os outros continuam sendo salvos
            Promise.allSettled(urlsToCache.map(url => cache.add(url)))
        ).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys()
            .then(nomes => Promise.all(nomes.filter(n => n !== CACHE_NAME).map(n => caches.delete(n))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', event => {
    const req = event.request;
    const url = new URL(req.url);

    // Só cuida de GET do próprio site. Votos (POST) e outros domínios passam direto.
    if (req.method !== 'GET' || url.origin !== self.location.origin) return;

    // Dados em tempo real (lotação, busca, login) nunca vêm do cache.
    if (url.pathname.endsWith('.php')) return;

    // Telas, CSS e demais arquivos: tenta a rede primeiro (sempre a versão nova)
    // e só usa o cache quando estiver sem internet.
    event.respondWith(
        fetch(req)
            .then(resposta => {
                const copia = resposta.clone();
                caches.open(CACHE_NAME).then(cache => cache.put(req, copia));
                return resposta;
            })
            .catch(() =>
                caches.match(req).then(salvo =>
                    salvo || (req.mode === 'navigate' ? caches.match('index.html') : undefined)
                )
            )
    );
});