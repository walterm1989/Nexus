<!-- frontend/includes/header.php -->
<header class="topbar">
  <div class="left">
    <img src="/nexus_inventario/frontend/assets/logo.png" alt="Logo" class="logo" onerror="this.style.display='none'">
    <span class="appname">Nexus Inventario</span>
  </div>
  <div class="right">
    <span id="userName"></span>
    <button class="btn ghost" onclick="App.logout()">Salir</button>
  </div>
</header>

<style>
.topbar{
  position:sticky; top:0; z-index:100;
  display:flex; align-items:center; justify-content:space-between;
  padding:10px 16px; background:#fff; box-shadow:0 2px 10px rgba(0,0,0,.06);
}
.topbar .left{ display:flex; align-items:center; gap:10px; }
.topbar .logo{ width:32px; height:32px; }
.topbar .appname{ font-weight:700; }
.topbar .right{ display:flex; align-items:center; gap:10px; }
</style>

<script>
  (function(){
    try{
      const u = App.getUser();
      document.getElementById('userName').textContent = u ? u.nombre : 'Invitado';
    }catch(e){}
  })();
</script>
