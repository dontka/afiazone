<?php

declare(strict_types=1);

namespace App\Modules\System\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Response;
use Throwable;

class HealthCheckController extends Controller
{
    public function index(): Response
    {
        try {
            Database::connection()->query('SELECT 1');

            return $this->json([
                'success' => true,
                'message' => 'AfiaZone est operationnel.',
                'data' => [
                    'php' => PHP_VERSION,
                    'database' => 'connected',
                ],
            ]);
        } catch (Throwable) {
            return $this->json([
                'success' => false,
                'message' => 'Application active, mais la connexion MySQL doit etre configuree.',
                'data' => [
                    'php' => PHP_VERSION,
                    'database' => 'disconnected',
                ],
            ], 503);
        }
    }
}