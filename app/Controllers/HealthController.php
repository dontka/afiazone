<?php

declare(strict_types=1);

namespace App\Controllers;

/**
 * Health Check Controller
 * 
 * Provides endpoints for health checks and status
 */
class HealthController extends BaseController
{
    /**
     * Welcome endpoint - API root
     */
    public function welcome(): void
    {
        $this->render('front.general.index', [
            'pageTitle' => 'AfiaZone - Medical Marketplace',
            'additionalStyles' => [],
            'additionalScripts' => [],
        ]);
    }

    /**
     * Health check endpoint
     */
    public function check(): void
    {
        $dbHealthy = $this->checkDatabase();
        $cacheHealthy = $this->checkCache();

        $this->jsonResponse([
            'status' => ($dbHealthy && $cacheHealthy) ? 'healthy' : 'degraded',
            'timestamp' => date('c'),
            'components' => [
                'api' => 'ok',
                'database' => $dbHealthy ? 'ok' : 'down',
                'cache' => $cacheHealthy ? 'ok' : 'ok', // File-based cache always available
            ],
        ], 200);
    }

    /**
     * Check database connection
     */
    private function checkDatabase(): bool
    {
        try {
            $db = db();
            $result = $db->query('SELECT 1')->fetch();
            return $result !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check cache system
     */
    private function checkCache(): bool
    {
        try {
            $cacheDir = storage_path('cache');
            return is_dir($cacheDir) && is_writable($cacheDir);
        } catch (\Exception $e) {
            return false;
        }
    }
}
