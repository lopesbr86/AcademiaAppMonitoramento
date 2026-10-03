// Adiciona um botão de "olho" (mostrar/ocultar senha) em todo campo de senha da página.
// Basta incluir este arquivo na página: <script src="mostrar-senha.js"></script>
(function () {
    const ICONE_MOSTRAR =
        '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" ' +
        'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
        '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';

    const ICONE_OCULTAR =
        '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" ' +
        'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
        '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>' +
        '<line x1="1" y1="1" x2="23" y2="23"/></svg>';

    function adicionarOlho(campo) {
        // Envolve o campo em uma div para posicionar o botão por cima dele.
        const envoltorio = document.createElement('div');
        envoltorio.className = 'campo-senha';
        campo.parentNode.insertBefore(envoltorio, campo);
        envoltorio.appendChild(campo);

        const botao = document.createElement('button');
        botao.type = 'button';
        botao.className = 'btn-olho';
        botao.innerHTML = ICONE_MOSTRAR;
        botao.setAttribute('aria-label', 'Mostrar senha');
        botao.setAttribute('aria-pressed', 'false');
        envoltorio.appendChild(botao);

        function definirVisivel(visivel) {
            campo.type = visivel ? 'text' : 'password';
            botao.innerHTML = visivel ? ICONE_OCULTAR : ICONE_MOSTRAR;
            botao.setAttribute('aria-label', visivel ? 'Ocultar senha' : 'Mostrar senha');
            botao.setAttribute('aria-pressed', visivel ? 'true' : 'false');
        }

        botao.addEventListener('click', function () {
            definirVisivel(campo.type === 'password');
            campo.focus();
        });

        // Ao enviar o formulário, volta para "password" (o gerenciador de senhas do navegador espera isso).
        if (campo.form) {
            campo.form.addEventListener('submit', function () {
                definirVisivel(false);
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('input[type="password"]').forEach(adicionarOlho);
    });
})();