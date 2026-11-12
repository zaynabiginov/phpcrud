<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = [], ?string $layout = 'layouts/main'): void
    {
        $viewFile = __DIR__ . '/../Views/' . str_replace('.', '/', $view) . '.php';
        if (!is_file($viewFile)) {
            http_response_code(500);
            echo 'View not found: ' . htmlspecialchars($view);
            return;
        }
        extract($data, EXTR_SKIP);
        ob_start();
        include $viewFile;
        $content = ob_get_clean();
        if ($layout === null) {
            echo $content;
            return;
        }
        $layoutFile = __DIR__ . '/../Views/' . str_replace('.', '/', $layout) . '.php';
        if (!is_file($layoutFile)) {
            echo $content;
            return;
        }
        include $layoutFile;
    }
}
