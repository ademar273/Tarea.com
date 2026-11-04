<?php
declare(strict_types=1);

namespace App\Model;

class Estudiante extends Persona
{
    private string $codigo;
    private string $carrera;

    public function __construct(
        string $codigo,
        string $nombre,
        string $apellido,
        int $edad,
        string $carrera
    ) {
        parent::__construct($nombre, $apellido, $edad);
        $this->codigo = $codigo;
        $this->carrera = $carrera;
    }

    public function obtenerCodigo(): string
    {
        return $this->codigo;
    }

    public function obtenerCarrera(): string
    {
        return $this->carrera;
    }

    public function convertirEnArray(): array
    {
        return [
            'codigo' => $this->codigo,
            'nombre' => $this->obtenerNombre(),
            'apellido' => $this->obtenerApellido(),
            'edad' => $this->obtenerEdad(),
            'carrera' => $this->carrera,
        ];
    }
}
