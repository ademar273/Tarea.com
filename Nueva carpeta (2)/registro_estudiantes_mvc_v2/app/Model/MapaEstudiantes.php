<?php
declare(strict_types=1);

namespace App\Model;

use RuntimeException;

/**
 * Mapa de estudiantes: código => objeto Estudiante.
 * El archivo JSON conserva los registros entre peticiones HTTP.
 */
class MapaEstudiantes
{
    private string $rutaArchivo;

    public function __construct(string $rutaArchivo, Estudiante $estudianteInicial)
    {
        $this->rutaArchivo = $rutaArchivo;
        $carpeta = dirname($rutaArchivo);

        if (!is_dir($carpeta) && !mkdir($carpeta, 0775, true) && !is_dir($carpeta)) {
            throw new RuntimeException('No se pudo crear la carpeta de datos.');
        }

        // Se crea el estudiante por defecto únicamente si el mapa está vacío.
        $archivo = $this->abrirArchivo(LOCK_EX);
        try {
            $mapa = $this->leerMapa($archivo);
            if ($mapa === []) {
                $mapa[$estudianteInicial->obtenerCodigo()] = $estudianteInicial;
                $this->guardarMapa($archivo, $mapa);
            }
        } finally {
            $this->cerrarArchivo($archivo);
        }
    }

    /** @return Estudiante[] */
    public function listar(): array
    {
        $archivo = $this->abrirArchivo(LOCK_SH);
        try {
            $mapa = $this->leerMapa($archivo);
            ksort($mapa, SORT_STRING);
            return array_values($mapa);
        } finally {
            $this->cerrarArchivo($archivo);
        }
    }

    public function buscar(string $codigo): ?Estudiante
    {
        $archivo = $this->abrirArchivo(LOCK_SH);
        try {
            $mapa = $this->leerMapa($archivo);
            return $mapa[$codigo] ?? null;
        } finally {
            $this->cerrarArchivo($archivo);
        }
    }

    /** Devuelve false si ya existe el código. */
    public function registrar(Estudiante $estudiante): bool
    {
        $archivo = $this->abrirArchivo(LOCK_EX);
        try {
            $mapa = $this->leerMapa($archivo);
            $codigo = $estudiante->obtenerCodigo();

            if (isset($mapa[$codigo])) {
                return false;
            }

            $mapa[$codigo] = $estudiante;
            $this->guardarMapa($archivo, $mapa);
            return true;
        } finally {
            $this->cerrarArchivo($archivo);
        }
    }

    /** @return resource */
    private function abrirArchivo(int $tipoBloqueo)
    {
        $archivo = fopen($this->rutaArchivo, 'c+');
        if ($archivo === false) {
            throw new RuntimeException('No se pudo abrir el archivo de estudiantes.');
        }

        if (!flock($archivo, $tipoBloqueo)) {
            fclose($archivo);
            throw new RuntimeException('No se pudo bloquear el archivo de estudiantes.');
        }

        return $archivo;
    }

    /** @param resource $archivo */
    private function cerrarArchivo($archivo): void
    {
        flock($archivo, LOCK_UN);
        fclose($archivo);
    }

    /**
     * @param resource $archivo
     * @return array<string, Estudiante>
     */
    private function leerMapa($archivo): array
    {
        rewind($archivo);
        $contenido = stream_get_contents($archivo);
        if ($contenido === false) {
            throw new RuntimeException('No se pudo leer el mapa de estudiantes.');
        }
        if (trim($contenido) === '') {
            return [];
        }

        $filas = json_decode($contenido, true);
        if (!is_array($filas) || json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('El archivo de estudiantes tiene datos inválidos.');
        }

        $mapa = [];
        foreach ($filas as $fila) {
            if (!is_array($fila) || !isset(
                $fila['codigo'], $fila['nombre'], $fila['apellido'],
                $fila['edad'], $fila['carrera']
            )) {
                throw new RuntimeException('Se encontró un estudiante incompleto en el archivo.');
            }

            $estudiante = new Estudiante(
                (string) $fila['codigo'],
                (string) $fila['nombre'],
                (string) $fila['apellido'],
                (int) $fila['edad'],
                (string) $fila['carrera']
            );
            $mapa[$estudiante->obtenerCodigo()] = $estudiante;
        }
        return $mapa;
    }

    /**
     * @param resource $archivo
     * @param array<string, Estudiante> $mapa
     */
    private function guardarMapa($archivo, array $mapa): void
    {
        $datos = [];
        foreach ($mapa as $codigo => $estudiante) {
            $datos[$codigo] = $estudiante->convertirEnArray();
        }

        $json = json_encode(
            $datos,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT
        );
        if ($json === false) {
            throw new RuntimeException('No se pudieron preparar los datos para guardar.');
        }

        rewind($archivo);
        if (!ftruncate($archivo, 0)) {
            throw new RuntimeException('No se pudo actualizar el archivo de estudiantes.');
        }
        if (fwrite($archivo, $json) !== strlen($json)) {
            throw new RuntimeException('No se pudieron escribir todos los estudiantes.');
        }
        fflush($archivo);
    }
}
