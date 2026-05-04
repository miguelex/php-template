<?php

declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailerException;

/**
 * Servicio de email vía SMTP usando PHPMailer.
 *
 * Configuración en .env:
 *   MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD
 *   MAIL_FROM_ADDRESS, MAIL_FROM_NAME, APP_URL
 */
final class EmailService
{
    private PHPMailer $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(exceptions: true);
        $this->configure();
    }

    public function send(
        string $to,
        string $toName,
        string $subject,
        string $htmlBody,
    ): bool {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to, $toName);
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $htmlBody;

            return $this->mailer->send();
        } catch (MailerException) {
            return false;
        }
    }

    // ── Templates básicos ─────────────────────────────────────────────────────

    public function sendAccountConfirmation(
        string $email,
        string $name,
        string $token,
    ): bool {
        $url  = rtrim($_ENV['APP_URL'] ?? '', '/');
        $link = "{$url}/confirmar-cuenta?token={$token}";
        $appName = $_ENV['APP_NAME'] ?? 'App';

        $body = $this->template("Confirma tu cuenta en {$appName}", "
            <p>Hola <strong>{$name}</strong>,</p>
            <p>Has creado tu cuenta correctamente. Para activarla, haz clic en el botón:</p>
            <p style='text-align:center;margin:2rem 0'>
                <a href='{$link}' style='background:#2563eb;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none'>
                    Confirmar cuenta
                </a>
            </p>
            <p style='color:#64748b;font-size:0.875rem'>Si no creaste esta cuenta, ignora este mensaje.</p>
        ");

        return $this->send($email, $name, "Confirma tu cuenta en {$appName}", $body);
    }

    public function sendPasswordReset(
        string $email,
        string $name,
        string $token,
    ): bool {
        $url  = rtrim($_ENV['APP_URL'] ?? '', '/');
        $link = "{$url}/reestablecer?token={$token}";
        $appName = $_ENV['APP_NAME'] ?? 'App';

        $body = $this->template('Reestablece tu contraseña', "
            <p>Hola <strong>{$name}</strong>,</p>
            <p>Has solicitado restablecer tu contraseña. Haz clic en el botón para continuar:</p>
            <p style='text-align:center;margin:2rem 0'>
                <a href='{$link}' style='background:#2563eb;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none'>
                    Restablecer contraseña
                </a>
            </p>
            <p style='color:#64748b;font-size:0.875rem'>Si no solicitaste este cambio, ignora este mensaje.</p>
        ");

        return $this->send($email, $name, 'Reestablece tu contraseña', $body);
    }

    // ── Privados ──────────────────────────────────────────────────────────────

    private function configure(): void
    {
        $this->mailer->isSMTP();
        $this->mailer->Host       = $_ENV['MAIL_HOST']     ?? '';
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Port       = (int) ($_ENV['MAIL_PORT'] ?? 587);
        $this->mailer->Username   = $_ENV['MAIL_USERNAME'] ?? '';
        $this->mailer->Password   = $_ENV['MAIL_PASSWORD'] ?? '';
        $this->mailer->CharSet    = 'UTF-8';
        $this->mailer->isHTML(true);

        $this->mailer->setFrom(
            $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@example.com',
            $_ENV['MAIL_FROM_NAME']    ?? ($_ENV['APP_NAME'] ?? 'App'),
        );
    }

    private function template(string $title, string $content): string
    {
        $appName = htmlspecialchars($_ENV['APP_NAME'] ?? 'App');

        return <<<HTML
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>{$title}</title>
        </head>
        <body style="font-family:system-ui,sans-serif;background:#f8fafc;margin:0;padding:2rem">
            <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;padding:2rem;box-shadow:0 1px 3px rgba(0,0,0,.1)">
                <h1 style="color:#1e293b;font-size:1.25rem;margin-bottom:1.5rem">{$title}</h1>
                {$content}
                <hr style="border:none;border-top:1px solid #e2e8f0;margin:2rem 0">
                <p style="color:#94a3b8;font-size:0.75rem">{$appName}</p>
            </div>
        </body>
        </html>
        HTML;
    }
}
