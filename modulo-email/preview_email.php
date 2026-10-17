<?php
// Só para VER o layout dos e-mails no navegador, sem precisar enviar nada.
//   /modulo-email/preview_email.php                    -> e-mail de cadastro
//   /modulo-email/preview_email.php?tipo=recuperacao   -> e-mail de recuperação
// Apague este arquivo antes de publicar o app.
require_once __DIR__ . '/template_email.php';

if (($_GET['tipo'] ?? '') === 'recuperacao') {
    echo montarEmail(
        'Redefinir senha',
        'Recebemos um pedido para criar uma nova senha na sua conta. O link vale por 1 hora.',
        'Redefinir senha',
        'http://localhost:8000/redefinir_senha.php?token=exemplo',
        'Se você não pediu isso, ignore esta mensagem. Sua senha continua a mesma.'
    );
} else {
    echo montarEmail(
        'Bem-vinda ao GymUp, Maria!',
        'Falta só um passo: confirme seu e-mail para começar a acompanhar a lotação das academias.',
        'Confirmar e-mail',
        'http://localhost:8000/modulo-email/verificar_email.php?token=exemplo',
        'Se você não criou uma conta, ignore esta mensagem.'
    );
}