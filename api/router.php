<?php
$route = $_GET['route'] ?? '';
if (empty($route)) {
    http_response_code(404);
    echo "404 Not Found";
    exit;
}

// Защита от выхода из директории (LFI)
$route = str_replace(['..', "\0"], '', $route);
$path = realpath(__DIR__ . '/../pages/' . $route);

// Убедимся, что путь разрешается именно внутри папки pages
if ($path && strpos($path, realpath(__DIR__ . '/../pages')) === 0 && file_exists($path) && is_file($path)) {
    // Выполняем запрошенный файл
    require_once $path;
} else {
    http_response_code(404);
    echo "404 Not Found ($route)";
}
