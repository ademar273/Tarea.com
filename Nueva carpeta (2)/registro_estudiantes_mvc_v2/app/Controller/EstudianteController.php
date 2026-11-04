<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Estudiante;
use App\Model\MapaEstudiantes;

class EstudianteController
{
    private MapaEstudiantes $mapa;

    public function __construct(MapaEstudiantes $mapa)
    {
        $this->mapa = $mapa;
    }

    public function mostrarPagina(): void
    {
        $estudiantes = $this->mapa->listar();
        require __DIR__ . '/../View/pagina.php';
    }

    public function manejarApi(): void
    {
        $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if ($metodo === 'GET') {
            $this->consultar();
            return;
        }
        if ($metodo === 'POST') {
            $this->registrar();
            return;
        }

        header('Allow: GET, POST');
        $this->responder(405, false, 'Método no permitido. Usa GET o POST.');
    }

    private function consultar(): void
    {
        if (!array_key_exists('codigo', $_GET)) {
            $estudiantes = array_map(
                static fn (Estudiante $estudiante): array => $estudiante->convertirEnArray(),
                $this->mapa->listar()
            );
            $this->responder(200, true, 'Consulta realizada.', [
                'total' => count($estudiantes),
                'estudiantes' => $estudiantes,
            ]);
            return;
        }

        if (!is_string($_GET['codigo'])) {
            $this->responder(400, false, 'El código debe ser un texto válido.');
            return;
        }

        $codigo = strtoupper(trim($_GET['codigo']));
        if (!$this->codigoValido($codigo)) {
            $this->responder(400, false, 'El código debe tener de 3 a 20 letras, números, guiones o guiones bajos.');
            return;
        }

        $estudiante = $this->mapa->buscar($codigo);
        if ($estudiante === null) {
            $this->responder(404, false, 'No se encontró un estudiante con ese código.');
            return;
        }

        $this->responder(200, true, 'Estudiante encontrado.', [
            'estudiante' => $estudiante->convertirEnArray(),
        ]);
    }

    private function registrar(): void
    {
        $tipo = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));

        if ($tipo === 'application/json') {
            $contenido = file_get_contents('php://input');
            $datos = is_string($contenido) ? json_decode($contenido, true) : null;
            if (!is_array($datos) || json_last_error() !== JSON_ERROR_NONE || array_is_list($datos)) {
                $this->responder(400, false, 'El contenido JSON no es válido. Envía un objeto con los datos del estudiante.');
                return;
            }
        } elseif ($tipo === 'application/x-www-form-urlencoded' || $tipo === 'multipart/form-data' || $tipo === '') {
            $datos = $_POST;
        } else {
            $this->responder(415, false, 'Formato no admitido. Envía JSON o datos de formulario.');
            return;
        }

        [$valores, $errores] = $this->validarRegistro($datos);
        if ($errores !== []) {
            $this->responder(422, false, 'Revisa los datos ingresados.', ['errores' => $errores]);
            return;
        }

        $estudiante = new Estudiante(
            $valores['codigo'],
            $valores['nombre'],
            $valores['apellido'],
            $valores['edad'],
            $valores['carrera']
        );

        if (!$this->mapa->registrar($estudiante)) {
            $this->responder(409, false, 'Ya existe un estudiante registrado con ese código.');
            return;
        }

        header('Location: api.php?codigo=' . rawurlencode($estudiante->obtenerCodigo()));
        $this->responder(201, true, 'Estudiante registrado correctamente.', [
            'estudiante' => $estudiante->convertirEnArray(),
        ]);
    }

    /** @return array{0: array, 1: array} */
    private function validarRegistro(array $datos): array
    {
        $errores = [];
        $valores = [];

        foreach (['nombre', 'apellido', 'codigo', 'carrera'] as $campo) {
            $valores[$campo] = isset($datos[$campo]) && is_string($datos[$campo])
                ? trim($datos[$campo])
                : '';
        }
        $valores['codigo'] = strtoupper($valores['codigo']);

        foreach (['nombre', 'apellido'] as $campo) {
            $texto = $valores[$campo];
            if (strlen($texto) < 2 || strlen($texto) > 100 ||
                preg_match("/^[\p{L}\p{M}]+(?:[ '\-][\p{L}\p{M}]+)*$/u", $texto) !== 1) {
                $errores[$campo] = 'Ingresa un nombre o apellido válido (solo letras y separadores habituales).';
            }
        }

        if (!$this->codigoValido($valores['codigo'])) {
            $errores['codigo'] = 'El código debe tener de 3 a 20 letras, números, guiones o guiones bajos.';
        }
        if (strlen($valores['carrera']) < 3 || strlen($valores['carrera']) > 100) {
            $errores['carrera'] = 'La carrera debe tener entre 3 y 100 caracteres.';
        }

        $edadOriginal = $datos['edad'] ?? null;
        $edad = (is_int($edadOriginal) || is_string($edadOriginal))
            ? filter_var(
                $edadOriginal,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1, 'max_range' => 110]]
            )
            : false;
        if ($edad === false) {
            $errores['edad'] = 'La edad debe ser un número entero entre 1 y 110.';
        } else {
            $valores['edad'] = $edad;
        }

        return [$valores, $errores];
    }

    private function codigoValido(string $codigo): bool
    {
        return preg_match('/^[A-Z0-9_-]{3,20}$/D', $codigo) === 1;
    }

    private function responder(int $estado, bool $exito, string $mensaje, array $extra = []): void
    {
        http_response_code($estado);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode(
            array_merge(['exito' => $exito, 'mensaje' => $mensaje], $extra),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }
}
