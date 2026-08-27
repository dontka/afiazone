<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

class App
{
    private Router $router;

    private Logger $logger;

    public function __construct(private array $config)
    {
        $this->router = new Router();
        $this->logger = new Logger(BASE_PATH . '/storage/logs/app.log');
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

        $message = 'Une erreur est survenue. Veuillez reessayer plus tard.';

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
        $this->logger->exception($exception);
    }
}