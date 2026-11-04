'use strict';

const formularioRegistro = document.getElementById('formulario-registro');
const formularioBusqueda = document.getElementById('formulario-busqueda');
const tabla = document.getElementById('tabla-estudiantes');
const mensaje = document.getElementById('mensaje');
const estadoConsulta = document.getElementById('estado-consulta');
const contador = document.getElementById('total-registros');
const botonRegistrar = document.getElementById('boton-registrar');

function mostrarMensaje(texto, correcto) {
    mensaje.textContent = texto;
    mensaje.className = `mensaje ${correcto ? 'exito' : 'error'}`;
}

function mostrarEstudiantes(estudiantes) {
    tabla.replaceChildren();
    contador.textContent = `${estudiantes.length} registro(s)`;

    if (estudiantes.length === 0) {
        const fila = document.createElement('tr');
        const celda = document.createElement('td');
        celda.colSpan = 4;
        celda.textContent = 'No hay estudiantes para mostrar.';
        fila.appendChild(celda);
        tabla.appendChild(fila);
        return;
    }

    for (const estudiante of estudiantes) {
        const fila = document.createElement('tr');
        const valores = [
            estudiante.codigo,
            `${estudiante.nombre} ${estudiante.apellido}`,
            estudiante.edad,
            estudiante.carrera
        ];

        for (let indice = 0; indice < valores.length; indice++) {
            const celda = document.createElement('td');
            celda.textContent = valores[indice];
            if (indice === 0) {
                const codigo = document.createElement('strong');
                codigo.textContent = valores[indice];
                celda.replaceChildren(codigo);
            }
            fila.appendChild(celda);
        }
        tabla.appendChild(fila);
    }
}

async function consultar(codigo = '') {
    estadoConsulta.textContent = 'Consultando estudiantes...';
    try {
        const ruta = codigo ? `api.php?codigo=${encodeURIComponent(codigo.trim())}` : 'api.php';
        const respuesta = await fetch(ruta, { headers: { 'Accept': 'application/json' } });
        const datos = await respuesta.json();

        if (!respuesta.ok) {
            mostrarEstudiantes([]);
            estadoConsulta.textContent = datos.mensaje || 'No se pudo completar la consulta.';
            return;
        }

        const estudiantes = codigo ? [datos.estudiante] : datos.estudiantes;
        mostrarEstudiantes(estudiantes);
        estadoConsulta.textContent = codigo
            ? 'Consulta GET por código realizada correctamente.'
            : 'Consulta GET de todos los estudiantes realizada correctamente.';
    } catch (error) {
        estadoConsulta.textContent = 'Error de conexión: comprueba que Apache esté encendido en XAMPP.';
    }
}

formularioRegistro.addEventListener('submit', async (evento) => {
    evento.preventDefault();
    if (!formularioRegistro.reportValidity()) return;

    const datos = Object.fromEntries(new FormData(formularioRegistro).entries());
    botonRegistrar.disabled = true;
    botonRegistrar.textContent = 'Registrando...';

    try {
        const respuesta = await fetch('api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify(datos)
        });
        const resultado = await respuesta.json();

        if (respuesta.ok) {
            mostrarMensaje(resultado.mensaje, true);
            formularioRegistro.reset();
            document.getElementById('codigo-buscar').value = '';
            await consultar();
        } else {
            const detalles = resultado.errores ? ' ' + Object.values(resultado.errores).join(' ') : '';
            mostrarMensaje(resultado.mensaje + detalles, false);
        }
    } catch (error) {
        mostrarMensaje('No se pudo conectar con el servidor.', false);
    } finally {
        botonRegistrar.disabled = false;
        botonRegistrar.textContent = 'Registrar estudiante';
    }
});

formularioBusqueda.addEventListener('submit', (evento) => {
    evento.preventDefault();
    const codigo = document.getElementById('codigo-buscar').value.trim();
    if (!codigo) {
        estadoConsulta.textContent = 'Escribe un código para buscar o pulsa «Ver todos».';
        return;
    }
    consultar(codigo);
});

document.getElementById('boton-todos').addEventListener('click', () => {
    document.getElementById('codigo-buscar').value = '';
    consultar();
});

consultar();
