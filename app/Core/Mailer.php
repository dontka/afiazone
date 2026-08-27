<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

class Mailer
{
    public function __construct(private readonly array $config)
    {
    }

    public function sendVerificationEmail(string $email, string $name, string $verificationUrl): bool
    {
        $plain = "Bonjour {$name},\n\nConfirmez votre adresse email :\n{$verificationUrl}\n\nCe lien expire dans 24 heures.\n\nL'equipe AfiaZone";
        return $this->send($email, 'Confirmez votre adresse email AfiaZone', $plain, $this->template(
            'Bienvenue sur AfiaZone',
            'Confirmez votre adresse email',
            'Votre compte est presque pret. Confirmez votre adresse pour acceder a votre espace sante.',
            'Confirmer mon adresse email',
            $verificationUrl,
            'Ce lien est valable pendant 24 heures.'
        ));
    }

    public function sendPasswordResetEmail(string $email, string $name, string $resetUrl): bool
    {
        $plain = "Bonjour {$name},\n\nPour choisir un nouveau mot de passe :\n{$resetUrl}\n\nCe lien expire dans 1 heure.\n\nL'equipe AfiaZone";
        return $this->send($email, 'Reinitialisez votre mot de passe AfiaZone', $plain, $this->template(
            'Securite de votre compte',
            'Reinitialisez votre mot de passe',
            'Nous avons recu une demande pour modifier le mot de passe de votre compte AfiaZone.',
            'Choisir un nouveau mot de passe',
            $resetUrl,
            'Ce lien est valable pendant 1 heure. Si vous n’etes pas a l’origine de cette demande, ignorez cet email.'
        ));
    }

    public function sendNewSessionEmail(string $email, string $name, string $ipAddress): bool
    {
        $date = date('d/m/Y a H:i');
        $plain = "Bonjour {$name},\n\nUne nouvelle session vient d'etre ouverte sur votre compte AfiaZone.\nAdresse IP : {$ipAddress}\nDate : {$date}\n\nSi vous n'etes pas a l'origine de cette connexion, modifiez votre mot de passe.\n\nL'equipe AfiaZone";
        return $this->send($email, 'Nouvelle connexion a votre compte AfiaZone', $plain, $this->template(
            'Alerte de securite',
            'Nouvelle connexion detectee',
            'Une nouvelle session vient d’etre ouverte sur votre compte AfiaZone.',
            '',
            '',
            "Adresse IP : {$ipAddress}<br>Date : {$date}<br><br>Si vous n’etes pas a l’origine de cette connexion, modifiez votre mot de passe."
        ));
    }

    public function send(string $recipient, string $subject, string $body, ?string $htmlBody = null): bool
    {
        if (($this->config['mailer'] ?? 'smtp') !== 'smtp') {
            return false;
        }

        $host = (string) ($this->config['host'] ?? '127.0.0.1');
        $port = (int) ($this->config['port'] ?? 1025);
        $timeout = (int) ($this->config['timeout'] ?? 10);
        $encryption = (string) ($this->config['encryption'] ?? 'none');
        $transport = $encryption === 'ssl' ? 'ssl://' . $host : $host;
        $socket = @fsockopen($transport, $port, $errorCode, $errorMessage, $timeout);

        if (! is_resource($socket)) {
            $this->logFailure('Connexion SMTP impossible', $errorMessage, $host, $port);
            return false;
        }

        stream_set_timeout($socket, $timeout);
        try {
            $this->expect($socket, 220);
            $this->command($socket, 'EHLO afyazone.test', 250);
            if ($encryption === 'tls') {
                $this->command($socket, 'STARTTLS', 220);
                if (! stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new RuntimeException('Activation TLS impossible.');
                }
                $this->command($socket, 'EHLO afyazone.test', 250);
            }
            $username = (string) ($this->config['username'] ?? '');
            if ($username !== '') {
                $this->command($socket, 'AUTH LOGIN', 334);
                $this->command($socket, base64_encode($username), 334);
                $this->command($socket, base64_encode((string) ($this->config['password'] ?? '')), 235);
            }
            $from = (string) ($this->config['from_address'] ?? 'no-reply@afyazone.test');
            $this->command($socket, 'MAIL FROM:<' . $this->address($from) . '>', 250);
            $this->command($socket, 'RCPT TO:<' . $this->address($recipient) . '>', 250);
            $this->command($socket, 'DATA', 354);
            fwrite($socket, $this->message($from, $recipient, $subject, $body, $htmlBody));
            $this->expect($socket, 250);
            $this->command($socket, 'QUIT', 221);
            fclose($socket);
            return true;
        } catch (\Throwable $exception) {
            fclose($socket);
            $this->logFailure('Envoi SMTP impossible', $exception->getMessage(), $host, $port);
            return false;
        }
    }

    private function command($socket, string $command, int $expectedCode): void
    {
        fwrite($socket, $command . "\r\n");
        $this->expect($socket, $expectedCode);
    }

    private function expect($socket, int $expectedCode): void
    {
        $response = '';
        while (($line = fgets($socket)) !== false) {
            $response .= $line;
            if (strlen($line) >= 4 && $line[3] === ' ') {
                break;
            }
        }
        if ((int) substr($response, 0, 3) !== $expectedCode) {
            throw new RuntimeException('SMTP attendu ' . $expectedCode . ', recu : ' . trim($response));
        }
    }

    private function message(string $from, string $recipient, string $subject, string $body, ?string $htmlBody): string
    {
        $encodedBody = preg_replace('/^\./m', '..', $body) ?? $body;
        $contentType = $htmlBody !== null ? 'multipart/alternative; boundary="afyazone-boundary"' : 'text/plain; charset=UTF-8';
        $content = $htmlBody === null
            ? str_replace("\n", "\r\n", $encodedBody)
            : "--afyazone-boundary\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\n"
                . str_replace("\n", "\r\n", $encodedBody)
                . "\r\n--afyazone-boundary\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n"
                . $htmlBody . "\r\n--afyazone-boundary--";
        return 'From: ' . $this->headerAddress($from, (string) ($this->config['from_name'] ?? 'AfiaZone')) . "\r\n"
            . 'To: ' . $this->headerAddress($recipient, '') . "\r\n"
            . 'Subject: ' . $this->header($subject) . "\r\n"
            . "MIME-Version: 1.0\r\nContent-Type: {$contentType}\r\nContent-Transfer-Encoding: 8bit\r\n\r\n"
            . $content . "\r\n.\r\n";
    }

    private function template(string $eyebrow, string $title, string $intro, string $buttonLabel, string $buttonUrl, string $note): string
    {
        $button = $buttonLabel !== '' ? '<a href="' . $this->html($buttonUrl) . '" style="display:inline-block;background:#20a66a;color:#ffffff;text-decoration:none;padding:14px 22px;border-radius:8px;font-weight:700;">' . $this->html($buttonLabel) . '</a>' : '';
        return '<!doctype html><html><body style="margin:0;background:#f1f6f3;font-family:Arial,sans-serif;color:#1d2b25;"><div style="padding:32px 12px;"><div style="max-width:560px;margin:auto;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e1ebe5;"><div style="background:#147b52;padding:22px 28px;color:#ffffff;"><div style="font-size:22px;font-weight:800;">AfiaZone</div><div style="font-size:12px;opacity:.8;margin-top:4px;">Marketplace sante hyperlocale</div></div><div style="padding:32px 28px;"><div style="color:#168355;font-size:11px;font-weight:800;letter-spacing:1px;text-transform:uppercase;">' . $this->html($eyebrow) . '</div><h1 style="font-size:28px;line-height:1.15;margin:12px 0;color:#1d2b25;">' . $this->html($title) . '</h1><p style="font-size:16px;line-height:1.6;color:#617069;margin:0 0 24px;">' . $this->html($intro) . '</p>' . $button . '<div style="margin-top:26px;padding:14px 16px;background:#f5faf7;border-left:3px solid #20a66a;color:#617069;font-size:13px;line-height:1.6;">' . $note . '</div></div><div style="padding:18px 28px;border-top:1px solid #edf1ee;color:#829088;font-size:12px;">Cet email a ete envoye automatiquement par AfiaZone.<br>Ne repondez pas a ce message.</div></div></div></body></html>';
    }

    private function html(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function headerAddress(string $value, string $name): string
    {
        return ($name !== '' ? $this->header($name) . ' ' : '') . '<' . $this->address($value) . '>';
    }

    private function header(string $value): string
    {
        return trim(str_replace(["\r", "\n"], '', $value));
    }

    private function address(string $value): string
    {
        return trim(str_replace(['\r', '\n', '<', '>', ' '], '', $value));
    }

    private function logFailure(string $message, string $detail, string $host, int $port): void
    {
        error_log('[' . date('c') . '] ' . $message . ' (' . $host . ':' . $port . ') : ' . $detail . PHP_EOL, 3, BASE_PATH . '/storage/logs/app.log');
    }
}
