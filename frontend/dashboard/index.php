<?php /* frontend/dashboard/index.php */ ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Dashboard - Nexus Inventario</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="./common.css">
  <link rel="stylesheet" href="./index.css">
</head>
<body onload="App.requireAuth()">
  <?php include '../includes/header.php'; ?>

  <div class="main">
    <?php include '../includes/sidebar.php'; ?>
    <div class="content container">
      <div class="page-title">Panel general</div>

      <div class="grid cols-3">
        <div class="card stat">
          <div>
            <div class="label">Productos</div>
            <div class="value" id="statProductos">-</div>
          </div>
        </div>
        <div class="card stat">
          <div>
            <div class="label">Clientes</div>
            <div class="value" id="statClientes">-</div>
          </div>
        </div>
        <div class="card stat">
          <div>
            <div class="label">Stock bajo</div>
            <div class="value" id="statLow">-</div>
          </div>
        </div>
      </div>

      <div style="height:12px"></div>

      <div class="card">
        <h3>Alertas de stock bajo</h3>
        <div id="lowStockWrap" class="alert hidden">No hay productos con stock bajo.</div>
        <div id="lowStockList" class="list"></div>
      </div>

      <?php include '../includes/footer.php'; ?>
    </div>
  </div>

  <script src="../js/main.js"></script>
  <script>
    async function cargarStats(){
      try{
        const [prod, cli, low] = await Promise.all([
          App.api.get('productos', { limite:1, pagina:1 }),
          App.api.get('clientes', { limite:1, pagina:1 }),
          // endpoint de bajo stock (según tu rutas.php): productos/stock-bajo
          App.api.get('productos/stock-bajo')
        ]);

        document.getElementById('statProductos').textContent = prod.datos?.paginacion?.total_registros ?? '-';
        document.getElementById('statClientes').textContent = cli.datos?.paginacion?.total_registros ?? '-';

        const registros = low.datos?.registros || low.registros || [];
        document.getElementById('statLow').textContent = registros.length;

        const wrap = document.getElementById('lowStockWrap');
        const list = document.getElementById('lowStockList');
        list.innerHTML = '';
        if(registros.length === 0){
          wrap.classList.remove('hidden');
        }else{
          wrap.classList.add('hidden');
          registros.forEach(p=>{
            const li = document.createElement('div');
            li.className='item';
            li.innerHTML = `
              <div>
                <b>${p.nombre || p.nombre_producto || 'Producto'}</b>
                <div class="badge">Stock: ${p.stock ?? '-'}</div>
              </div>
              <a class="btn ghost" href="productos.php?buscar=${encodeURIComponent(p.nombre||'')}">Ver</a>
            `;
            list.appendChild(li);
          });
        }
      }catch(e){
        App.toast('No se pudo cargar el dashboard: '+e.message, 'err');
      }
    }

    cargarStats();
  </script>
</body>
</html>
