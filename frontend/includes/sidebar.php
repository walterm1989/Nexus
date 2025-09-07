<!-- frontend/includes/sidebar.php -->
<aside class="sidebar">
  <ul>
    <li><a href="index.php">Dashboard</a></li>
    <li><a href="clientes.php">Clientes</a></li>
    <li><a href="productos.php">Productos</a></li>
    <li><a href="entradas.php">Entradas Inventario</a></li>
    <li><a href="usuarios.php">Usuarios</a></li>
  </ul>
</aside>

<style>
.sidebar{
  position:sticky; top:56px; height:calc(100vh - 56px);
  width:220px; background:#fff; box-shadow:0 2px 12px rgba(0,0,0,.06);
  border-radius:0 14px 14px 0; padding:12px; overflow:auto;
}
.sidebar ul{ margin:0; padding:0; list-style:none; }
.sidebar li{ margin-bottom:6px; }
.sidebar a{
  display:block; padding:10px 12px; border-radius:10px; color:#333;
}
.sidebar a:hover{ background:#f3f3f3; }
</style>
