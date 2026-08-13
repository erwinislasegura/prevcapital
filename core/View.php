<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class View
{
    public static function render(string $view, array $data = [], ?string $layout = null): void
    {
        $viewPath = APP_ROOT . '/app/Views/' . $view . '.php';
        if (!is_file($viewPath)) {
            throw new RuntimeException("Vista no encontrada: {$view}");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $viewPath;
        $content = (string) ob_get_clean();

        if ($layout === null) {
            echo $content;
            return;
        }

        $layoutPath = APP_ROOT . '/app/Views/layouts/' . $layout . '.php';
        if (!is_file($layoutPath)) {
            throw new RuntimeException("Layout no encontrado: {$layout}");
        }
        require $layoutPath;
    }
}
