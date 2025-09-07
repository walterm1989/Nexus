<?php /* frontend/dashboard/usuarios.php */ ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Usuarios - Nexus Inventario</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="./common.css">
  <link rel="stylesheet" href="./usuarios.css">
</head>
<body onload="App.requireAuth()">
  <?php include '../includes/header.php'; ?>

  <div class="main">
    <?php include '../includes/sidebar.php'; ?>
    <div class="content container">
      <div class="page-title">Usuarios</div>

      <div class="card">
        <div class="toolbar">
          <button class="btn primary" onclick="abrirCrear()">Nuevo usuario</button>
        </div>

        <table class="table" id="usuariosTable">
          <thead>
            <tr>
              <th>#</th><th>Nombre</th><th>Email</th><th>Rol</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>

      <div id="modal" class="card hidden">
        <h3 id="modalTitle">Nuevo usuario</h3>
        <div class="form-grid">
          <input class="input" id="u_nombre" placeholder="Nombre">
          <input class="input" id="u_email" placeholder="Email">
          <input class="input" id="u_pass" placeholder="Contraseña" type="password">
          <select id="u_rol" class="input">
            <option value="1">Administrador</option>
            <option value="2" selected>Vendedor</option>
          </select>
        </div>
        <div style="height:10px"></div>
        <div class="toolbar">
          <button class="btn primary" onclick="guardarUsuario()">Guardar</button>
          <button class="btn ghost" onclick="cerrarModal()">Cancelar</button>
        </div>
      </div>

      <?php include '../includes/footer.php'; ?>
    </div>
  </div>

  <script src="../js/main.js"></script>
  <script>
    function abrirCrear(){ modal.classList.remove('hidden'); }
    function cerrarModal(){ modal.classList.add('hidden'); }

    async function listarUsuarios(){
      try{
        const data = await App.api.get('usuarios', { limite:100, pagina:1 });
        const regs = data.datos?.usuarios || data.usuarios || data.datos?.registros || [];
        const tbody = document.querySelector('#usuariosTable tbody');
        tbody.innerHTML = '';
        regs.forEach(u=>{
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${u.id ?? '-'}</td>
            <td>${u.nombre}</td>
            <td>${u.email}</td>
            <td><span class="badge">${u.rol_id==1?'Admin':'Vendedor'}</span></td>
          `;
          tbody.appendChild(tr);
        });
      }catch(e){ App.toast('Error listando: '+e.message, 'err'); }
    }

    async function guardarUsuario(){
      const body = {
        nombre: u_nombre.value.trim(),
        email: u_email.value.trim(),
        password: u_pass.value.trim(),
        rol_id: parseInt(u_rol.value,10)
      };
      try{
        await App.api.post('usuarios', body);
        App.toast('Usuario creado');
        cerrarModal(); listarUsuarios();
      }catch(e){ App.toast(e.message,'err'); }
    }

    listarUsuarios();
  </script>
</body>
</html>
