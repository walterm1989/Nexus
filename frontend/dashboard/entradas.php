<?php /* frontend/dashboard/entradas.php */ ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Entradas de Inventario - Nexus</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="./common.css">
  <link rel="stylesheet" href="./entradas.css">
</head>
<body onload="App.requireAuth()">
  <?php include '../includes/header.php'; ?>

  <div class="main">
    <?php include '../includes/sidebar.php'; ?>
    <div class="content container">
      <div class="page-title">Entradas de Inventario</div>

      <div class="card">
        <h3>Nueva entrada</h3>
        <div class="form-grid">
          <input class="input" id="e_producto_id" type="number" min="1" placeholder="ID Producto">
          <input class="input" id="e_cantidad" type="number" min="1" placeholder="Cantidad">
          <input class="input" id="e_precio" type="number" min="0" step="0.01" placeholder="Precio compra">
          <input class="input" id="e_notas" placeholder="Notas">
        </div>
        <div style="height:10px"></div>
        <button class="btn primary" onclick="crearEntrada()">Guardar entrada</button>
      </div>

      <div style="height:12px"></div>

      <div class="card">
        <div class="toolbar">
          <input class="input" id="filtroProducto" type="number" min="1" placeholder="Filtrar por ID producto">
          <button class="btn ghost" onclick="listarEntradas()">Buscar</button>
        </div>

        <table class="table">
          <thead>
            <tr>
              <th>#</th><th>Fecha</th><th>Producto</th><th>Cantidad</th><th>Precio compra</th><th>Usuario</th><th>Notas</th>
            </tr>
          </thead>
          <tbody id="entradasBody"></tbody>
        </table>
      </div>

      <?php include '../includes/footer.php'; ?>
    </div>
  </div>

  <script src="../js/main.js"></script>
  <script>
    async function crearEntrada(){
      const body = {
        producto_id: parseInt(e_producto_id.value||'0',10),
        cantidad: parseInt(e_cantidad.value||'0',10),
        precio_compra: Number(e_precio.value||'0'),
        notas: e_notas.value.trim()
      };
      try{
        await App.api.post('entradas', body);
        App.toast('Entrada creada');
        e_producto_id.value=''; e_cantidad.value=''; e_precio.value=''; e_notas.value='';
        listarEntradas();
      }catch(e){ App.toast(e.message,'err'); }
    }

    async function listarEntradas(){
      const filtro_producto_id = parseInt(document.getElementById('filtroProducto').value||'0',10) || null;
      try{
        const data = await App.api.get('entradas', { filtro_producto_id, limite:50, pagina:1, ordenar_por:'fecha_entrada', direccion:'DESC' });
        const regs = data.registros || data.datos?.registros || [];
        const tb = document.getElementById('entradasBody');
        tb.innerHTML='';
        regs.forEach(r=>{
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${r.id}</td>
            <td>${(r.fecha_entrada||'').replace('T',' ').replace('Z','')}</td>
            <td>${r.nombre_producto ?? ('#'+r.producto_id)}</td>
            <td>${r.cantidad}</td>
            <td>$${Number(r.precio_compra).toFixed(2)}</td>
            <td>${r.nombre_usuario ?? ('#'+r.usuario_id)}</td>
            <td>${r.notas || ''}</td>
          `;
          tb.appendChild(tr);
        });
      }catch(e){ App.toast('Error listando: '+e.message,'err'); }
    }

    listarEntradas();
  </script>
</body>
</html>
