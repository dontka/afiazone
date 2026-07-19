<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

class App
{
    private Router $router;

    public function __construct(private array $config)
    {
        $this->router = new Router();
        Database::configure($this->config['database'] ?? []);
    }

    public function router(): Router
    {
        return $this->router;
    }

    public function loadRoutes(array $modules): void
    {
        $router = $this->router;

        foreach ($modules as $module) {
            $routeFile = BASE_PATH . '/app/Modules/' . $module . '/routes.php';

            if (is_file($routeFile)) {
                require $routeFile;
            }
        }
    }

    public function run(Request $request): void
    {
        try {
            $response = $this->router->dispatch($request);
        } catch (Throwable $exception) {
            $response = $this->handleException($exception);
        }

        $response->send();
    }

    private function handleException(Throwable $exception): Response
    {
        $this->logException($exception);

        $debug = (bool) ($this->config['debug'] ?? false);
        $message = $debug
            ? '<pre>' . htmlspecialchars((string) $exception, ENT_QUOTES, 'UTF-8') . '</pre>'
            : 'Une erreur est survenue. Veuillez reessayer plus tard.';

        return new Response(
            View::render('Shared::errors/500', [
                'title' => 'Erreur serveur',
                'message' => $message,
            ]),
            500
        );
    }

    private function logException(Throwable $exception): void
    {
        $logFile = BASE_PATH . '/storage/logs/app.log';

        if (! is_dir(dirname($logFile))) {
            return;
        }

        $message = '[' . date('Y-m-d H:i:s') . '] ' . (string) $exception . PHP_EOL;
        error_log($message, 3, $logFile);
    }
}