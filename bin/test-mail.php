<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

(Dotenv\Dotenv::createImmutable(dirname(__DIR__)))->load();

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

$host = $_ENV['MAIL_HOST'] ?? 'localhost';
$port = $_ENV['MAIL_PORT'] ?? '1025';
$dsn  = "smtp://{$host}:{$port}";

echo "DSN: {$dsn}" . PHP_EOL;

$mailer = new Mailer(Transport::fromDsn($dsn));

$email = (new Email())
    ->from('noreply@afiazone.test')
    ->to('admin@afiazone.com')
    ->subject('Test reset password — AfiaZone')
    ->html('
        <h2>Réinitialisation de mot de passe</h2>
        <p>Ceci est un email de test envoyé depuis <strong>bin/test-mail.php</strong>.</p>
        <p><a href="http://afiazone.test/admin/reset-password?token=TEST123"
              style="background:#007bff;color:#fff;padding:12px 24px;text-decoration:none;border-radius:5px;">
            Réinitialiser mon mot de passe
        </a></p>
        <p>Ce lien expire dans 30 minutes.</p>
    ');

try {
    $mailer->send($email);
    echo 'Email sent successfully!' . PHP_EOL;
    echo 'Check Mailpit at: http://localhost:8025' . PHP_EOL;
} catch (\Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
}
