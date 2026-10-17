<?php
/**
 * Monta o HTML dos e-mails do GymUp.
 *
 * E-mail só aceita CSS escrito direto nas tags e layout em tabelas,
 * então este modelo não usa o style.css do app.
 *
 * $titulo      título grande do e-mail
 * $mensagem    texto explicando o que a pessoa deve fazer
 * $textoBotao  texto do botão
 * $link        endereço aberto pelo botão
 * $nota        aviso pequeno no rodapé (opcional)
 *
 * Os textos são escapados aqui dentro; passe o texto puro.
 */
function montarEmail(string $titulo, string $mensagem, string $textoBotao, string $link, string $nota = ''): string
{
    $e = fn(string $t): string => htmlspecialchars($t, ENT_QUOTES, 'UTF-8');

    $titulo     = $e($titulo);
    $mensagem   = $e($mensagem);
    $textoBotao = $e($textoBotao);
    $link       = $e($link);
    $nota       = $e($nota);

    $fonte = "font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;";

    ob_start();
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GymUp</title>
</head>
<body style="margin:0;padding:0;background-color:#0b1120;">

    <!-- Texto de pré-visualização (aparece ao lado do assunto na caixa de entrada) -->
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;"><?= $mensagem ?></div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#0b1120" style="background-color:#0b1120;">
        <tr>
            <td align="center" style="padding:32px 16px;">

                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:480px;">

                    <!-- Logo -->
                    <tr>
                        <td align="center" style="<?= $fonte ?>padding-bottom:20px;font-size:34px;font-weight:800;font-style:italic;letter-spacing:-1px;color:#ffffff;">
                            Gym<span style="color:#38bdf8;">Up</span>
                        </td>
                    </tr>

                    <!-- Cartão -->
                    <tr>
                        <td bgcolor="#1e293b" style="background-color:#1e293b;border:1px solid #334155;border-radius:16px;padding:32px 28px;">

                            <h1 style="<?= $fonte ?>margin:0 0 12px 0;font-size:24px;line-height:1.3;font-weight:700;color:#f8fafc;">
                                <?= $titulo ?>
                            </h1>

                            <p style="<?= $fonte ?>margin:0 0 28px 0;font-size:16px;line-height:1.6;color:#cbd5e1;">
                                <?= $mensagem ?>
                            </p>

                            <!-- Botão -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="center" bgcolor="#38bdf8" style="background-color:#38bdf8;border-radius:10px;">
                                        <a href="<?= $link ?>" style="<?= $fonte ?>display:block;padding:15px 24px;font-size:16px;font-weight:700;color:#0b1120;text-decoration:none;border-radius:10px;">
                                            <?= $textoBotao ?>
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Link por extenso, para quando o botão não funcionar -->
                            <p style="<?= $fonte ?>margin:24px 0 6px 0;font-size:12px;line-height:1.5;color:#94a3b8;">
                                Se o botão não funcionar, copie e cole este endereço no navegador:
                            </p>
                            <p style="<?= $fonte ?>margin:0;font-size:12px;line-height:1.5;word-break:break-all;">
                                <a href="<?= $link ?>" style="color:#38bdf8;text-decoration:underline;"><?= $link ?></a>
                            </p>

                        </td>
                    </tr>

                    <!-- Rodapé -->
                    <tr>
                        <td align="center" style="<?= $fonte ?>padding-top:20px;font-size:12px;line-height:1.6;color:#94a3b8;">
                            <?php if ($nota !== ''): ?>
                                <?= $nota ?><br>
                            <?php endif; ?>
                            Esta é uma mensagem automática, não responda este e-mail.
                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
<?php
    return ob_get_clean();
}