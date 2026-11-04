<?php
declare(strict_types=1);

namespace App\Model;

class Persona
{
    private string $nombre;
    private string $apellido;
    private int $edad;

    public function __construct(string $nombre, string $apellido, int $edad)
    {
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->edad = $edad;
    }

    public function obtenerNombre(): string
    {
        return $this->nombre;
    }

    public function obtenerApellido(): string
    {
        return $this->apellido;
    }

    public function obtenerEdad(): int
    {
        return $this->edad;
    }
}
