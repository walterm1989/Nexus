<?php /* frontend/dashboard/clientes.php */ ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Clientes - Nexus Inventario</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="./common.css">
  <link rel="stylesheet" href="./clientes.css">
</head>
<body onload="App.requireAuth()">
  <?php include '../includes/header.php'; ?>

  <div class="main">
    <?php include '../includes/sidebar.php'; ?>
    <div class="content container">
      <div class="page-title">Clientes</div>

      <div class="card">
        <div class="toolbar">
          <input class="input" id="filtroNombre" placeholder="Filtrar por nombre...">
          <button class="btn ghost" onclick="listarClientes()">Buscar</button>
          <button class="btn primary" onclick="abrirCrear()">Nuevo cliente</button>
        </div>

        <table class="table" id="clientesTable">
          <thead>
            <tr>
              <th>#</th><th>Nombre</th><th>Correo</th><th>Teléfono</th><th>Dirección</th><th>Acciones</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>

      <!-- Modal simple -->
      <div id="modal" class="card hidden">
        <h3 id="modalTitle">Nuevo cliente</h3>
        <div class="form-grid">
          <input class="input" id="c_nombre" placeholder="Nombre">
          <input class="input" id="c_correo" placeholder="Correo">
          <input class="input" id="c_telefono" placeholder="Teléfono">
          <input class="input" id="c_direccion" placeholder="Dirección">
        </div>
        <div style="height:10px"></div>
        <div class="toolbar">
          <button class="btn primary" onclick="guardarCliente()">Guardar</button>
          <button class="btn ghost" onclick="cerrarModal()">Cancelar</button>
        </div>
      </div>

      <?php include '../includes/footer.php'; ?>
    </div>
  </div>

  <script src="../js/main.js"></script>
  <script>
    let editId = null;

    function abrirCrear(){ 
      editId = null; 
      document.getElementById('modalTitle').textContent='Nuevo cliente'; 
      document.getElementById('c_nombre').value = '';
      document.getElementById('c_correo').value = '';
      document.getElementById('c_telefono').value = '';
      document.getElementById('c_direccion').value = '';
      document.getElementById('modal').classList.remove('hidden'); 
    }
    
    function cerrarModal(){ 
      document.getElementById('modal').classList.add('hidden'); 
    }

    async function listarClientes(){
      const filtro = document.getElementById('filtroNombre').value.trim();
      try{
        const data = await App.api.get('clientes', { filtro_nombre: filtro, limite:50, pagina:1 });
        const registros = data.registros || [];
        const tbody = document.querySelector('#clientesTable tbody');
        tbody.innerHTML = '';
        registros.forEach((c, i)=>{
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${c.id}</td>
            <td>${c.nombre}</td>
            <td>${c.correo}</td>
            <td>${c.telefono}</td>
            <td>${c.direccion}</td>
            <td class="acciones">
              <button class="btn ghost" onclick='editar(${JSON.stringify(c).replace(/'/g,"&apos;")})'>Editar</button>
              <button class="btn" style="background:#ef5350;color:#fff" onclick="eliminar(${c.id})">Eliminar</button>
            </td>
          `;
          tbody.appendChild(tr);
        });
      }catch(e){
        App.toast('Error listando: '+e.message, 'err');
      }
    }

    function editar(c){
      editId = c.id;
      document.getElementById('modalTitle').textContent='Editar cliente';
      document.getElementById('c_nombre').value = c.nombre || '';
      document.getElementById('c_correo').value = c.correo || '';
      document.getElementById('c_telefono').value = c.telefono || '';
      document.getElementById('c_direccion').value = c.direccion || '';
      document.getElementById('modal').classList.remove('hidden');
    }

    async function guardarCliente(){
      const body = {
        nombre: document.getElementById('c_nombre').value.trim(),
        correo: document.getElementById('c_correo').value.trim(),
        telefono: document.getElementById('c_telefono').value.trim(),
        direccion: document.getElementById('c_direccion').value.trim()
      };
      try{
        if(editId){
          await App.api.put('clientes', body, { id: editId });
          App.toast('Cliente actualizado');
        }else{
          await App.api.post('clientes', body);
          App.toast('Cliente creado');
        }
        cerrarModal();
        listarClientes();
      }catch(e){
        App.toast(e.message, 'err');
      }
    }

    async function eliminar(id){
      if(!confirm('¿Eliminar cliente?')) return;
      try{
        await App.api.del('clientes', { id });
        App.toast('Cliente eliminado');
        listarClientes();
      }catch(e){
        App.toast(e.message, 'err');
      }
    }

    listarClientes();
  </script>
</body>
</html>
