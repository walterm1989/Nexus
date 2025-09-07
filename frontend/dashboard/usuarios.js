// frontend/dashboard/usuarios.js
document.addEventListener('DOMContentLoaded', () => {
    const tablaBody = document.querySelector('#tablaUsuarios tbody');

    function cargarUsuarios() {
        fetch(`../../backend/app/rutas.php?ruta=usuarios`)
            .then(res => res.json())
            .then(data => {
                tablaBody.innerHTML = '';
                if(data.estado === 'exito') {
                    data.datos.registros.forEach(usuario => {
                        const fila = document.createElement('tr');
                        fila.innerHTML = `
                            <td>${usuario.id}</td>
                            <td>${usuario.nombre}</td>
                            <td>${usuario.correo}</td>
                            <td>${usuario.rol_nombre}</td>
                            <td>
                                <button onclick="editarUsuario(${usuario.id})">Editar</button>
                                <button onclick="eliminarUsuario(${usuario.id})">Eliminar</button>
                            </td>
                        `;
                        tablaBody.appendChild(fila);
                    });
                }
            });
    }

    window.editarUsuario = id => alert(`Editar usuario ${id}`);
    window.eliminarUsuario = id => {
        if(confirm('¿Eliminar este usuario?')) {
            fetch(`../../backend/app/rutas.php?ruta=usuarios&id=${id}`, { method:'DELETE' })
                .then(res => res.json())
                .then(data => {
                    alert(data.mensaje);
                    cargarUsuarios();
                });
        }
    };

    cargarUsuarios();
});
