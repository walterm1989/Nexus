// frontend/dashboard/productos.js
document.addEventListener('DOMContentLoaded', () => {
    const tablaBody = document.querySelector('#tablaProductos tbody');

    function cargarProductos() {
        App.api.get('/productos')
            .then(data => {
                tablaBody.innerHTML = '';
                if(data.estado === 'exito') {
                    const registros = (data.datos && (data.datos.registros || data.datos)) || [];
                    registros.forEach(producto => {
                        const fila = document.createElement('tr');
                        fila.innerHTML = `
                            <td>${producto.id}</td>
                            <td>${producto.nombre}</td>
                            <td>${producto.marca}</td>
                            <td>${producto.precio}</td>
                            <td>${producto.stock}</td>
                            <td>${producto.nombre_categoria || ''}</td>
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
            App.api.del(`/productos/${id}`)
                .then(data => {
                    alert(data.mensaje);
                    cargarProductos();
                });
        }
    };

    cargarProductos();
});

