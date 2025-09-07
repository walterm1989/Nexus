// frontend/dashboard/clientes.js
document.addEventListener('DOMContentLoaded', () => {
    const tablaBody = document.querySelector('#clientesTable tbody');
    const modal = document.querySelector('#modal');
    const modalTitle = document.querySelector('#modalTitle');
    const inputNombre = document.querySelector('#c_nombre');
    const inputCorreo = document.querySelector('#c_correo');
    const inputTelefono = document.querySelector('#c_telefono');
    const inputDireccion = document.querySelector('#c_direccion');
    let editId = null;

    function cargarClientes() {
        // CORREGIDO: Cambiar la URL de la petición para apuntar a index.php
        fetch(`../../backend/index.php?ruta=clientes`)
            .then(res => res.json())
            .then(data => {
                tablaBody.innerHTML = '';
                if(data.estado === 'exito') {
                    const registros = data.datos || [];
                    registros.forEach(cliente => {
                        const fila = document.createElement('tr');
                        fila.innerHTML = `
                            <td>${cliente.id}</td>
                            <td>${cliente.nombre}</td>
                            <td>${cliente.correo}</td>
                            <td>${cliente.telefono}</td>
                            <td>${cliente.direccion}</td>
                            <td>
                                <button class="btn ghost" onclick="editarCliente(${cliente.id})">Editar</button>
                                <button class="btn" style="background:#ef5350;color:#fff" onclick="eliminarCliente(${cliente.id})">Eliminar</button>
                            </td>
                        `;
                        tablaBody.appendChild(fila);
                    });
                } else {
                    alert('Error: ' + data.mensaje);
                }
            })
            .catch(error => {
                alert('Error al cargar clientes: ' + error.message);
            });
    }

    window.abrirCrear = () => {
        editId = null;
        modalTitle.textContent = 'Nuevo cliente';
        inputNombre.value = '';
        inputCorreo.value = '';
        inputTelefono.value = '';
        inputDireccion.value = '';
        modal.classList.remove('hidden');
    };

    window.editarCliente = id => {
        editId = id;
        modalTitle.textContent = 'Editar cliente';
        // CORREGIDO: Cambiar la URL de la petición para apuntar a index.php
        fetch(`../../backend/index.php?ruta=clientes&id=${id}`)
            .then(res => res.json())
            .then(data => {
                if (data.estado === 'exito') {
                    const cliente = data.datos;
                    inputNombre.value = cliente.nombre;
                    inputCorreo.value = cliente.correo;
                    inputTelefono.value = cliente.telefono;
                    inputDireccion.value = cliente.direccion;
                    modal.classList.remove('hidden');
                }
            });
    };

    window.eliminarCliente = id => {
        if(confirm('¿Eliminar este cliente?')) {
            // CORREGIDO: Cambiar la URL de la petición para apuntar a index.php
            fetch(`../../backend/index.php?ruta=clientes&id=${id}`, { method: 'DELETE' })
                .then(res => res.json())
                .then(data => {
                    alert(data.mensaje);
                    cargarClientes();
                });
        }
    };

    window.guardarCliente = () => {
        const cliente = {
            nombre: inputNombre.value,
            correo: inputCorreo.value,
            telefono: inputTelefono.value,
            direccion: inputDireccion.value
        };

        let url = `../../backend/index.php?ruta=clientes`;
        let method = 'POST';

        if (editId) {
            url += `&id=${editId}`;
            method = 'PUT';
        }

        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(cliente)
        })
        .then(res => res.json())
        .then(data => {
            alert(data.mensaje);
            modal.classList.add('hidden');
            cargarClientes();
        });
    };

    cargarClientes();
});
