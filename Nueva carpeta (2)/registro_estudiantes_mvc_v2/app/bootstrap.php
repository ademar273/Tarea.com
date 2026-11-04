<?php
declare(strict_types=1);

use App\Controller\EstudianteController;
use App\Model\Estudiante;
use App\Model\MapaEstudiantes;

spl_autoload_register(static function (string $clase): void {
    $prefijo = 'App\\';
    if (strncmp($clase, $prefijo, strlen($prefijo)) !== 0) {
        return;
    }

    $nombre = substr($clase, strlen($prefijo));
    $ruta = __DIR__ . '/' . str_replace('\\', '/', $nombre) . '.php';
    if (is_file($ruta)) {
        require_once $ruta;
    }
});

$inicial = require __DIR__ . '/../config/estudiante_inicial.php';
$estudianteInicial = new Estudiante(
    strtoupper(trim($inicial['codigo'])),
    trim($inicial['nombre']),
    trim($inicial['apellido']),
    (int) $inicial['edad'],
    trim($inicial['carrera'])
);

$mapa = new MapaEstudiantes(__DIR__ . '/../data/estudiantes.json', $estudianteInicial);
return new EstudianteController($mapa);
