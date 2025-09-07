<?php /* frontend/dashboard/productos.php */ ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Productos - Nexus Inventario</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="./common.css">
  <link rel="stylesheet" href="./productos.css">
</head>
<body onload="App.requireAuth()">
  <?php include '../includes/header.php'; ?>

  <div class="main">
    <?php include '../includes/sidebar.php'; ?>
    <div class="content container">
      <div class="page-title">Productos</div>

      <div class="card">
        <div class="toolbar">
          <input class="input" id="filtroNombre" placeholder="Filtrar por nombre...">
          <input class="input" id="filtroMarca" placeholder="Filtrar por marca...">
          <button class="btn ghost" onclick="listarProductos()">Buscar</button>
          <button class="btn primary" onclick="abrirCrear()">Nuevo producto</button>
        </div>

        <table class="table" id="productosTable">
          <thead>
            <tr>
              <th>#</th><th>Nombre</th><th>Marca</th><th>Precio</th><th>Stock</th><th>Categoría</th><th>Acciones</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>

      <!-- Alertas locales -->
      <div class="card" id="stockAlert" style="display:none;">
        <div class="alert">¡Algunos productos tienen <b>stock bajo</b>! Revisa tus entradas.</div>
      </div>

      <!-- Modal -->
      <div id="modal" class="card hidden">
        <h3 id="modalTitle">Nuevo producto</h3>
        <div class="form-grid">
          <input class="input" id="p_nombre" placeholder="Nombre">
          <input class="input" id="p_marca" placeholder="Marca">
          <input class="input" id="p_precio" placeholder="Precio" type="number" min="0" step="0.01">
          <input class="input" id="p_stock" placeholder="Stock" type="number" min="0" step="1">
          <input class="input" id="p_categoria" placeholder="ID Categoría" type="number" min="1" step="1">
          <textarea class="input" id="p_descripcion" placeholder="Descripción"></textarea>
        </div>
        <div style="height:10px"></div>
        <div class="toolbar">
          <button class="btn primary" onclick="guardarProducto()">Guardar</button>
          <button class="btn ghost" onclick="cerrarModal()">Cancelar</button>
        </div>
      </div>

      <?php include '../includes/footer.php'; ?>
    </div>
  </div>

  <script src="../js/main.js"></script>
  <script>
    let editId = null;

    function abrirCrear(){ editId = null; document.getElementById('modalTitle').textContent='Nuevo producto'; modal.classList.remove('hidden'); }
    function cerrarModal(){ modal.classList.add('hidden'); }

    async function listarProductos(){
      const filtroNombre = document.getElementById('filtroNombre').value.trim();
      const filtroMarca = document.getElementById('filtroMarca').value.trim();
      try{
        const data = await App.api.get('productos', {
          filtro_nombre: filtroNombre || null,
          filtro_marca: filtroMarca || null,
          limite:100, pagina:1, ordenar_por:'nombre', direccion:'ASC'
        });
        const regs = data.datos?.registros || data.registros || [];
        const tbody = document.querySelector('#productosTable tbody');
        tbody.innerHTML = '';
        let hayBajo = false;

        regs.forEach(p=>{
          if(typeof p.stock !== 'undefined' && p.stock <= 3) hayBajo = true;
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${p.id}</td>
            <td>${p.nombre}</td>
            <td>${p.marca}</td>
            <td>$${Number(p.precio).toFixed(2)}</td>
            <td><span class="badge" style="background:${(p.stock<=3)?'#ffebee':'#e8f5e9'}">${p.stock}</span></td>
            <td>${p.nombre_categoria ?? '-'}</td>
            <td class="acciones">
              <button class="btn ghost" onclick='editar(${JSON.stringify(p).replace(/'/g,"&apos;")})'>Editar</button>
              <button class="btn" style="background:#ef5350;color:#fff" onclick="eliminar(${p.id})">Eliminar</button>
            </td>
          `;
          tbody.appendChild(tr);
        });

        document.getElementById('stockAlert').style.display = hayBajo ? 'block' : 'none';

      }catch(e){ App.toast('Error listando: '+e.message, 'err'); }
    }

    function editar(p){
      editId = p.id;
      modalTitle.textContent='Editar producto';
      p_nombre.value = p.nombre || '';
      p_marca.value = p.marca || '';
      p_precio.value = p.precio || 0;
      p_stock.value = p.stock || 0;
      p_categoria.value = p.categoria_id || '';
      p_descripcion.value = p.descripcion || '';
      modal.classList.remove('hidden');
    }

    async function guardarProducto(){
      const body = {
        nombre: p_nombre.value.trim(),
        marca: p_marca.value.trim(),
        precio: Number(p_precio.value),
        stock: parseInt(p_stock.value||'0',10),
        categoria_id: parseInt(p_categoria.value||'0',10),
        descripcion: p_descripcion.value.trim()
      };
      try{
        if(editId){ await App.api.put('productos', body, { id: editId }); App.toast('Producto actualizado'); }
        else { await App.api.post('productos', body); App.toast('Producto creado'); }
        cerrarModal(); listarProductos();
      }catch(e){ App.toast(e.message, 'err'); }
    }

    async function eliminar(id){
      if(!confirm('¿Eliminar producto?')) return;
      try{ await App.api.del('productos', { id }); App.toast('Producto eliminado'); listarProductos(); }
      catch(e){ App.toast(e.message, 'err'); }
    }

    // si llegaste desde el dashboard con ?buscar=...
    (function(){
      const params = new URLSearchParams(location.search);
      const busca = params.get('buscar'); if(busca){ document.getElementById('filtroNombre').value = busca; }
    })();

    listarProductos();
  </script>
</body>
</html>
