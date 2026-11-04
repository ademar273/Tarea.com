# Registro de Estudiantes — Segunda versión (PHP + MVC)

Proyecto de Estructura de Datos: implementación de un **mapa** de estudiantes en PHP con **herencia**, **encapsulamiento**, **tipos estrictos**, respuestas **JSON** y arquitectura **Modelo–Vista–Controlador (MVC)**.

## 1. Requisitos

- XAMPP con **PHP 8.1 o superior** y Apache activado.
- Un navegador web.
- Permisos de escritura en la carpeta `data/`.
- Apache debe permitir las reglas `.htaccess` incluidas, que bloquean el acceso web directo a `app/`, `config/` y `data/`.
- **No se necesita MySQL**: la consigna pide un mapa y este proyecto lo conserva en un archivo JSON.

## 2. Antes de ejecutar: colocar tus datos reales

Abre `config/estudiante_inicial.php` y reemplaza **NOMBRE_REAL**, **APELLIDO_REAL** y **CODIGO_REAL** con tus datos reales. Por ejemplo, la forma de escribirlos es:

```php
return [
    'nombre' => 'Tu Nombre',
    'apellido' => 'Tus Apellidos',
    'codigo' => 'SIS202601',
    'edad' => 21,
    'carrera' => 'Ingeniería de Sistemas',
];
```

**Atención:** los datos de ejemplo **no son tus datos reales**. El ingeniero exige que los tres primeros sean auténticos. El código debe tener entre 3 y 20 caracteres (letras, números, guion y guion bajo). Si escribes tu código en minúsculas se convertirá a mayúsculas.

Haz este cambio **antes de abrir la aplicación por primera vez**. El archivo `data/estudiantes.json` se crea al primer acceso e incluye al estudiante inicial. Si ejecutaste el proyecto con datos de prueba y **todavía no tienes otros registros que conservar**, puedes borrar `data/estudiantes.json` y abrirlo nuevamente para regenerarlo con la nueva configuración. Borrar ese archivo elimina todos los registros almacenados.

## 3. Instalación en XAMPP (Windows)

1. Extrae la carpeta `registro_estudiantes_mvc_v2` en `C:\xampp\htdocs\`.
2. Edita `config/estudiante_inicial.php` con tus datos reales.
3. Abre **XAMPP Control Panel** y activa **Apache**.
4. En el navegador abre:

   `http://localhost/registro_estudiantes_mvc_v2/public/`

5. Verás tu estudiante inicial sin necesidad de completar el formulario.

Si XAMPP está en otro directorio, coloca la carpeta del proyecto en su correspondiente `htdocs`. Si Apache usa un puerto distinto del 80, agrega el puerto a la URL (ej.: `localhost:8080`).

## 4. Carpetas y responsabilidades MVC

```text
registro_estudiantes_mvc_v2/
├── app/
│   ├── Controller/
│   │   └── EstudianteController.php  # Recibe GET/POST y valida
│   ├── Model/
│   │   ├── Persona.php               # Clase base
│   │   ├── Estudiante.php            # Hereda de Persona
│   │   └── MapaEstudiantes.php       # Mapa código => Estudiante
│   ├── View/
│   │   └── pagina.php                # Formulario y tabla HTML
│   └── bootstrap.php                 # Carga clases y conecta capas
├── config/
│   └── estudiante_inicial.php        # Registro inicial obligatorio
├── data/
│   └── estudiantes.json              # Se genera al ejecutar
├── public/
│   ├── assets/
│   │   ├── css/estilos.css
│   │   └── js/aplicacion.js
│   ├── api.php                       # Punto de entrada JSON
│   └── index.php                     # Punto de entrada visual
├── DOCUMENTACION.md
└── README.md
```

**Modelo:** maneja los objetos, el mapa y su persistencia. **Vista:** muestra el formulario y los resultados. **Controlador:** interpreta GET/POST, valida y comunica el modelo con la vista o con la respuesta JSON.

El **mapa** es un arreglo asociativo de PHP cuyas claves son los códigos y cuyos valores son objetos `Estudiante`. Ejemplo conceptual:

```php
$mapa['SIS202601'] = $estudiante; // $estudiante es un objeto Estudiante
```

El mapa se reconstruye desde JSON al atender una petición y se vuelve a guardar cuando se registra un estudiante. Así no se pierden los datos entre peticiones HTTP.

## 5. Peticiones HTTP

### GET: mostrar todos

Abre en un navegador:

`http://localhost/registro_estudiantes_mvc_v2/public/api.php`

Respuesta esperada: **200 OK** con el arreglo `estudiantes` y el campo `total`.

### GET: consultar por código

`http://localhost/registro_estudiantes_mvc_v2/public/api.php?codigo=SIS202601`

Cambia `SIS202601` por uno de tus códigos registrados. Si existe: **200 OK**; si no existe: **404 Not Found**. Un código mal formado devuelve **400 Bad Request**.

### POST: registrar estudiante

La página tiene un formulario que envía los campos con `fetch()` a `api.php` usando POST y JSON. También admite POST clásico con datos de formulario. Ejemplo de cuerpo JSON:

```json
{
  "codigo": "SIS202602",
  "nombre": "Ana Maria",
  "apellido": "Perez Rojas",
  "edad": 20,
  "carrera": "Ingeniería de Sistemas"
}
```

Respuesta esperada: **201 Created**. Al intentar registrar de nuevo el mismo código devuelve **409 Conflict**. Si faltan datos o son inválidos devuelve **422 Unprocessable Content**. Si el JSON está mal formado devuelve **400 Bad Request**. Un método HTTP distinto de GET/POST devuelve **405 Method Not Allowed**.

**Nota:** se usa `flock()` al leer y escribir el archivo para evitar que dos registros simultáneos con el mismo código se guarden por duplicado.

## 6. Validaciones realizadas

- Todos los campos son obligatorios.
- Nombre y apellido: letras (incluye acentos), espacios, apóstrofes y guiones.
- Código único: letras, números, `_` o `-`, entre 3 y 20 caracteres; se normaliza a mayúsculas.
- Edad: número entero entre 1 y 110.
- Carrera: entre 3 y 100 caracteres.
- Se controla la duplicidad y se devuelven mensajes de error y estados HTTP adecuados.

## 7. Prueba recomendada para presentar

1. Abre la página y comprueba que ya figura el estudiante inicial.
2. Pulsa **GET: todos** y muestra el JSON.
3. Registra otro estudiante mediante el formulario; comprueba el mensaje de éxito.
4. Busca el código registrado para mostrar la consulta GET.
5. Registra nuevamente el mismo código; observa el error de duplicidad.
6. Intenta enviar una edad fuera de rango (por ejemplo 150); observa la validación.
7. Actualiza el navegador; comprueba que los estudiantes siguen ahí.

## 8. Archivos que conviene mostrar durante la defensa

- `Persona.php` + `Estudiante.php`: relación de herencia y atributos privados.
- `MapaEstudiantes.php`: estructura asociativa con clave única y persistencia.
- `EstudianteController.php`: manejo GET, POST y validaciones.
- `pagina.php` + `aplicacion.js`: formulario, consultas y presentación de resultados.

## 9. Si algo no funciona

- **404 al entrar:** comprueba el nombre de carpeta y que estás abriendo `/public/`.
- **Error de conexión:** confirma que Apache esté iniciado.
- **Error 500:** revisa la carpeta `data/` y los permisos para crear/escribir `estudiantes.json`; consulta el log de errores de Apache/PHP.
- **Se muestra NOMBRE_REAL:** faltó editar la configuración antes del primer acceso (consulta la sección 2).
- **No registra con el mismo código:** es la validación de duplicidad prevista.
