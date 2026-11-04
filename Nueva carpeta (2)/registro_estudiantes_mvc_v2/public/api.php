<?php
declare(strict_types=1);

try {
    $controlador = require __DIR__ . '/../app/bootstrap.php';
    $controlador->manejarApi();
} catch (Throwable $error) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Ocurrió un error interno. Revisa la configuración y los permisos de la carpeta data.',
    ], JSON_UNESCAPED_UNICODE);
}
