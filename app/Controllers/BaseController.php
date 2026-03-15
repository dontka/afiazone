<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\HttpException;

/**
 * Base Controller Class
 * 
 * All controllers should extend this class to inherit
 * common methods and utilities.
 */
abstract class BaseController
{
    /**
     * Request method
     */
    protected string $method;

    /**
     * Request path
     */
    protected string $path;

    /**
     * Request data
     */
    protected array $data = [];

    /**
     * Authenticated user model
     */
    protected ?\App\Models\User $authUser = null;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $this->parseRequestData();

        // Populate from middleware
        $this->authUser = $GLOBALS['auth_user'] ?? null;
    }

    /**
     * Parse request data from JSON, POST, or GET
     */
    protected function parseRequestData(): void
    {
        if ($this->method === 'GET') {
            $this->data = $_GET;
        } elseif (in_array($this->method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (strpos($contentType, 'application/json') !== false) {
                $this->data = json_decode(file_get_contents('php://input'), true) ?? [];
            } else {
                $this->data = $_POST;
            }
        }
    }

    protected function authUserId(): int
    {
        if (!$this->authUser) {
            throw new HttpException('Unauthorized', 401);
        }
        return (int) $this->authUser->id;
    }

    /**
     * Get request data
     */
    protected function getData(?string $key = null): mixed
    {
        if ($key === null) {
            return $this->data;
        }

        return $this->data[$key] ?? null;
    }

    /**
     * Set response JSON
     */
    protected function jsonResponse(
        array $data,
        int $statusCode = 200,
        string $message = ''
    ): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');

        $response = [
            'success' => $statusCode >= 200 && $statusCode < 300,
            'status_code' => $statusCode,
            'data' => $data,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Set error response
     */
    protected function errorResponse(
        string $message,
        int $statusCode = 400,
        array $errors = []
    ): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');

        $response = [
            'success' => false,
            'status_code' => $statusCode,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Render HTML view with layout
     */
    protected function render(
        string $view, 
        array $data = [],
        string $layout = 'front.layouts.frontend'
    ): void {
        // Prepare variables for the view
        extract($data);
        
        // Capture the view content
        ob_start();
        require view_path($view);
        $content = ob_get_clean();
        
        // Set content variable for layout
        $pageTitle = $data['pageTitle'] ?? 'AfiaZone';
        $additionalStyles = $data['additionalStyles'] ?? [];
        $additionalScripts = $data['additionalScripts'] ?? [];
        
        // Render the layout with the content
        require view_path($layout);
    }

    /**
     * Render admin page with admin layout
     */
    protected function renderAdmin(
        string $view, 
        array $data = []
    ): void {
        $this->render($view, $data, 'back.layouts.admin');
    }

    /**
     * Render auth page with auth layout
     */
    protected function renderAuth(
        string $view, 
        array $data = []
    ): void {
        $this->render($view, $data, 'back.layouts.auth');
    }

    /**
     * Render JSON response
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        $this->jsonResponse($data, $statusCode);
    }

    /**
     * Redirect to URL
     */
    protected function redirect(string $url, int $statusCode = 302): void
    {
        http_response_code($statusCode);
        header("Location: {$url}");
        exit;
    }

    protected function authorize(string $permission): void
    {
        if (!$this->authUser) {
            throw new HttpException('Unauthorized', 401);
        }
        if (!$this->authUser->hasPermission($permission)) {
            throw new \App\Exceptions\ForbiddenException("Missing permission: {$permission}");
        }
    }

    protected function requireAuth(): void
    {
        if (!$this->authUser) {
            throw new HttpException('Unauthorized', 401);
        }
    }

    /**
     * Get current authenticated user ID
     */
    protected function getCurrentUserId(): int
    {
        $this->requireAuth();
        return (int) $this->authUser->id;
    }

    /**
     * Get current authenticated user
     */
    protected function getCurrentUser(): \App\Models\User
    {
        $this->requireAuth();
        return $this->authUser;
    }
}
