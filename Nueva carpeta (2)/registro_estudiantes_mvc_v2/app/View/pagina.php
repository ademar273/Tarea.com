<?php
declare(strict_types=1);

/** @var \App\Model\Estudiante[] $estudiantes */
$escapar = static fn (string $valor): string => htmlspecialchars(
    $valor,
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Registro y consulta de estudiantes con PHP, mapas y MVC.">
    <title>Registro de Estudiantes | Estructura de Datos</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
    <script src="assets/js/aplicacion.js" defer></script>
</head>
<body>
    <header class="encabezado">
        <div class="contenedor encabezado-contenido">
            <span class="marca">ED <span class="marca-separador">/</span> PHP</span>
            <span class="etiqueta">Proyecto de Estructura de Datos</span>
        </div>
    </header>

    <main class="contenedor">
        <section class="presentacion">
            <p class="sobre-titulo">Herencia · Mapas · Arquitectura MVC</p>
            <h1>Registro de estudiantes</h1>
            <p class="descripcion">Registra estudiantes y consulta sus datos usando el código como clave única del mapa.</p>
        </section>

        <div class="cuadricula">
            <section class="tarjeta" aria-labelledby="titulo-registro">
                <div class="tarjeta-cabecera">
                    <h2 id="titulo-registro">Nuevo estudiante</h2>
                    <p>Todos los campos son obligatorios.</p>
                </div>
                <form id="formulario-registro" action="api.php" method="post">
                    <div class="campo">
                        <label for="codigo">Código de estudiante</label>
                        <input id="codigo" name="codigo" type="text" minlength="3" maxlength="20"
                               pattern="[A-Za-z0-9_-]{3,20}" placeholder="Ej. SIS202601" required autocomplete="off">
                    </div>
                    <div class="campos-dobles">
                        <div class="campo">
                            <label for="nombre">Nombre(s)</label>
                            <input id="nombre" name="nombre" type="text" maxlength="70" placeholder="Ej. Ana María" required>
                        </div>
                        <div class="campo">
                            <label for="apellido">Apellido(s)</label>
                            <input id="apellido" name="apellido" type="text" maxlength="70" placeholder="Ej. Pérez Rojas" required>
                        </div>
                    </div>
                    <div class="campos-dobles">
                        <div class="campo">
                            <label for="edad">Edad</label>
                            <input id="edad" name="edad" type="number" min="1" max="110" step="1" placeholder="Ej. 21" required>
                        </div>
                        <div class="campo">
                            <label for="carrera">Carrera</label>
                            <input id="carrera" name="carrera" type="text" maxlength="100" placeholder="Ej. Ingeniería de Sistemas" required>
                        </div>
                    </div>
                    <button class="boton boton-principal" type="submit" id="boton-registrar">Registrar estudiante</button>
                </form>
                <div id="mensaje" class="mensaje oculto" role="status" aria-live="polite"></div>
            </section>

            <section class="tarjeta" aria-labelledby="titulo-consulta">
                <div class="tarjeta-cabecera">
                    <h2 id="titulo-consulta">Consultar estudiantes</h2>
                    <p>Busca por código o muestra todos los registros.</p>
                </div>
                <form id="formulario-busqueda" class="busqueda">
                    <div class="campo">
                        <label for="codigo-buscar">Código a consultar</label>
                        <input id="codigo-buscar" name="codigo-buscar" type="text" placeholder="Escribe un código" autocomplete="off">
                    </div>
                    <div class="acciones">
                        <button type="submit" class="boton boton-secundario">Buscar por código</button>
                        <button type="button" class="boton boton-discreto" id="boton-todos">Ver todos</button>
                    </div>
                </form>
                <div class="separador"></div>
                <div class="resultado-encabezado">
                    <h3>Resultado</h3>
                    <span id="total-registros" class="contador"><?= count($estudiantes) ?> registro(s)</span>
                </div>
                <div class="tabla-contenedor">
                    <table>
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre completo</th>
                                <th>Edad</th>
                                <th>Carrera</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-estudiantes">
                            <?php foreach ($estudiantes as $estudiante): ?>
                                <tr>
                                    <td><strong><?= $escapar($estudiante->obtenerCodigo()) ?></strong></td>
                                    <td><?= $escapar($estudiante->obtenerNombre() . ' ' . $estudiante->obtenerApellido()) ?></td>
                                    <td><?= $estudiante->obtenerEdad() ?></td>
                                    <td><?= $escapar($estudiante->obtenerCarrera()) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p id="estado-consulta" class="ayuda" aria-live="polite">Los datos se consultan usando HTTP GET.</p>
                <div class="enlaces-api">
                    <span>Prueba las respuestas JSON:</span>
                    <a href="api.php" target="_blank" rel="noopener">GET: todos</a>
                    <a href="api.php?codigo=<?= rawurlencode($estudiantes[0]->obtenerCodigo()) ?>" target="_blank" rel="noopener">GET: primer registro</a>
                </div>
            </section>
        </div>

        <footer class="pie">PHP con tipos estrictos · Mapa asociativo · Modelo–Vista–Controlador</footer>
    </main>
</body>
</html>
