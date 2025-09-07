// frontend/dashboard/productos.js
document.addEventListener('DOMContentLoaded', () => {
    const tablaBody = document.querySelector('#tablaProductos tbody');

    function cargarProductos() {
        fetch(`../../backend/app/rutas.php?ruta=productos`)
            .then(res => res.json())
            .then(data => {
                tablaBody.innerHTML = '';
                if(data.estado === 'exito') {
                    data.datos.registros.forEach(producto => {
                        const fila = document.createElement('tr');
                        fila.innerHTML = `
                            <td>${producto.id}</td>
                            <td>${producto.nombre}</td>
                            <td>${producto.marca}</td>
                            <td>${producto.precio}</td>
                            <td>${producto.stock}</td>
                            <td>${producto.nombre_categoria}</td>
                            <td>
                                <button onclick="editarProducto(${producto.id})">Editar</button>
                                <button onclick="eliminarProducto(${producto.id})">Eliminar</button>
                            </td>
                        `;
                        tablaBody.appendChild(fila);
                    });
                }
            });
    }

    window.editarProducto = id => alert(`Editar producto ${id}`);
    window.eliminarProducto = id => {
        if(confirm('¿Eliminar este producto?')) {
            fetch(`../../backend/app/rutas.php?ruta=productos&id=${id}`, { method:'DELETE' })
                .then(res => res.json())
                .then(data => {
                    alert(data.mensaje);
                    cargarProductos();
                });
        }
    };

    cargarProductos();
});

