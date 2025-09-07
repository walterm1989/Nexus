// frontend/dashboard/usuarios.js
document.addEventListener('DOMContentLoaded', () => {
    const tablaBody = document.querySelector('#tablaUsuarios tbody');

    function cargarUsuarios() {
        App.api.get('/usuarios')
            .then(data => {
                tablaBody.innerHTML = '';
                if(data.estado === 'exito') {
                    const registros = (data.datos && (data.datos.registros || data.datos)) || [];
                    registros.forEach(usuario => {
                        const fila = document.createElement('tr');
                        fila.innerHTML = `
                            <td>${usuario.id}</td>
                            <td>${usuario.nombre}</td>
                            <td>${usuario.correo}</td>
                            <td>${usuario.rol_nombre || ''}</td>
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
            App.api.del(`/usuarios/${id}`)
                .then(data => {
                    alert(data.mensaje);
                    cargarUsuarios();
                });
        }
    };

    cargarUsuarios();
});
