<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

class View
{
    public static function render(string $view, array $data = [], ?string $layout = 'public'): string
    {
        $viewFile = self::resolveViewFile($view);
        $content = self::renderFile($viewFile, $data);

        if ($layout === null) {
            return $content;
        }

        $layoutFile = BASE_PATH . '/app/Modules/Shared/Views/layouts/' . $layout . '.php';

        return self::renderFile($layoutFile, array_merge($data, [
            'content' => $content,
        ]));
    }

    private static function resolveViewFile(string $view): string
    {
        if (! str_contains($view, '::')) {
            throw new RuntimeException('Invalid view name: ' . $view);
        }

        [$module, $template] = explode('::', $view, 2);
        $template = str_replace('.', '/', $template);

        return BASE_PATH . '/app/Modules/' . $module . '/Views/' . $template . '.php';
    }

    private static function renderFile(string $file, array $data): string
    {
        if (! is_file($file)) {
            throw new RuntimeException('View file not found: ' . $file);
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $file;
        return (string) ob_get_clean();
    }
}