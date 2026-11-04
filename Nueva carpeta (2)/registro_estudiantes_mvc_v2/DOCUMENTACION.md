# Documentación del proyecto

**Materia:** Estructura de Datos  
**Trabajo:** Implementación de mapas y herencia en PHP  
**Arquitectura:** Modelo–Vista–Controlador (MVC)  
**Versión:** 2

## 1. Objetivo

Desarrollar una aplicación web básica que permita registrar y consultar estudiantes mediante peticiones HTTP. Para identificar a cada estudiante se utiliza su código como clave única en un mapa implementado con un arreglo asociativo de PHP.

## 2. Desarrollo

Se crearon dos clases relacionadas por herencia:

- `Persona`: representa los datos generales de una persona (`nombre`, `apellido`, `edad`).
- `Estudiante`: extiende `Persona` y agrega `codigo` y `carrera`.

Los atributos son privados y se consultan por métodos públicos; así se aplica el encapsulamiento. Los archivos PHP utilizan `declare(strict_types=1);` y se declaran tipos en atributos, parámetros y resultados de métodos.

La clase `MapaEstudiantes` mantiene los estudiantes en un arreglo asociativo de la forma `codigo => objeto Estudiante`. Para garantizar la unicidad, antes de insertar un estudiante se comprueba si su código ya está ocupado. El mapa se guarda en `data/estudiantes.json`, de modo que los registros continúan disponibles luego de actualizar la página o efectuar nuevas peticiones.

## 3. Funcionamiento de MVC

```text
Navegador
   |
   | GET /public/                        GET /public/api.php
   |                                     POST /public/api.php
   v                                              |
public/index.php                           public/api.php
   |                                              |
   +------------> EstudianteController <----------+
                          |
                          v
                  MapaEstudiantes
                          |
                  Persona / Estudiante
                          |
                 data/estudiantes.json

                 EstudianteController
                          |
                          v
                  Vista pagina.php
                          |
                 HTML / CSS / JavaScript
```

- **Modelo:** maneja objetos, claves únicas y almacenamiento.
- **Vista:** proporciona el formulario y la tabla de resultados.
- **Controlador:** recibe la solicitud, valida campos, llama al modelo y devuelve HTML o JSON.

No se utilizó un framework, porque para este ejercicio se priorizó mostrar directamente los conceptos de programación orientada a objetos, mapas y MVC.

## 4. Endpoints y resultados

| Método | Dirección | Resultado esperado |
|---|---|---|
| GET | `public/api.php` | 200: devuelve todos los estudiantes |
| GET | `public/api.php?codigo=SIS202601` | 200: devuelve uno; 404: no encontrado |
| POST | `public/api.php` | 201: registro correcto |
| POST repetido | `public/api.php` | 409: código duplicado |
| POST inválido | `public/api.php` | 422: errores de validación |
| PUT/DELETE | `public/api.php` | 405: método no permitido |

Todas las respuestas de la API se generan en formato JSON. El código `SIS202601` es solo un ejemplo y debe sustituirse por uno que exista en el mapa.

## 5. Registro inicial obligatorio

Antes de ejecutar se modifica `config/estudiante_inicial.php` colocando el nombre, apellido y código reales del estudiante que realiza el ejercicio. Se permite elegir una edad y carrera ficticias. Cuando el archivo de datos todavía está vacío, la aplicación registra automáticamente a ese estudiante.

## 6. Pruebas de funcionamiento (para realizar en XAMPP)

| Prueba | Acción | Resultado esperado |
|---|---|---|
| 1 | Iniciar el proyecto | Aparece el estudiante inicial |
| 2 | Registrar un estudiante con datos válidos | Mensaje de registro exitoso; HTTP 201 |
| 3 | Registrar otra vez el mismo código | Error por código duplicado; HTTP 409 |
| 4 | Consultar un código registrado | Devuelve los datos del estudiante; HTTP 200 |
| 5 | Consultar un código inexistente | Error de estudiante no encontrado; HTTP 404 |
| 6 | Enviar edad negativa o texto | Error de validación; HTTP 422 |
| 7 | Volver a cargar la página | Los registros siguen disponibles |

**Al preparar la entrega:** documenta las pruebas realmente efectuadas con capturas de pantalla y sustituye las referencias de ejemplo por tus datos reales. No presentes una prueba como realizada si no la ejecutaste.

## 7. Conclusión

El ejercicio permite aplicar la estructura de datos mapa utilizando un código como clave única de búsqueda. La herencia ayuda a reutilizar la información de `Persona` en `Estudiante`, mientras que el encapsulamiento protege los atributos de las clases. La arquitectura MVC mantiene separada la lógica de datos, el tratamiento de las solicitudes y la interfaz web. Además, el archivo JSON permite mantener los estudiantes registrados entre peticiones sin incorporar una base de datos externa.
