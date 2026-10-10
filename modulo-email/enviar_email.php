<?php
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../conexao.php';        // carrega o .env e a função env()
require_once __DIR__ . '/../vendor/autoload.php'; // PHPMailer (composer require phpmailer/phpmailer)

/**
 * Envia um e-mail em HTML por SMTP.
 * Devolve true se enviou, false se falhou (o erro vai para o log do PHP).
 */
function enviarEmail(string $para, string $assunto, string $html): bool
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = env('SMTP_HOST');
        $mail->SMTPAuth   = true;
        $mail->Username   = env('SMTP_USER');
        $mail->Password   = env('SMTP_PASS');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(env('SMTP_USER'), 'GymUp');
        $mail->addAddress($para);

        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body    = $html;
        $mail->AltBody = trim(strip_tags($html)); // versão texto, para clientes sem HTML

        $mail->send();
        return true;
    } catch (\Throwable $e) {
        error_log('Erro ao enviar e-mail: ' . $e->getMessage());
        return false;
    }
}