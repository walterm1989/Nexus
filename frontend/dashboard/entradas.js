// frontend/dashboard/entradas.js
document.addEventListener('DOMContentLoaded', () => {
    const tablaBody = document.querySelector('#tablaEntradas tbody');

    function cargarEntradas() {
        fetch(`../../backend/app/rutas.php?ruta=entradas`)
            .then(res => res.json())
            .then(data => {
                tablaBody.innerHTML = '';
                if(data.estado === 'exito') {
                    data.datos.registros.forEach(entrada => {
                        const fila = document.createElement('tr');
                        fila.innerHTML = `
                            <td>${entrada.id}</td>
                            <td>${entrada.nombre_producto}</td>
                            <td>${entrada.cantidad}</td>
                            <td>${entrada.nombre_usuario}</td>
                            <td>${entrada.fecha}</td>
                        `;
                        tablaBody.appendChild(fila);
                    });
                }
            });
    }

    cargarEntradas();
});
