<?php
declare(strict_types=1);

try {
    $controlador = require __DIR__ . '/../app/bootstrap.php';
    $controlador->mostrarPagina();
} catch (Throwable $error) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'No se pudo abrir la aplicación. Revisa los permisos de la carpeta data y el registro de errores de PHP.';
}
