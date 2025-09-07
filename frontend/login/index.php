<?php /* frontend/login/index.php */ ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Login - Nexus Inventario</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="./style.css">
</head>
<body>
  <div class="login-container">
    <img src="../img/logo.png" class="logo" onerror="this.style.display='none'">
    <h2>Iniciar sesión</h2>
    <form id="loginForm">
      <input type="email" id="email" placeholder="Correo" required />
      <input type="password" id="password" placeholder="Contraseña" required />
      <button type="submit">Entrar</button>
    </form>
    <div class="small">* Por ahora, el backend acepta todo (payload JWT simulado).</div>
  </div>

  <script src="../js/main.js"></script>
  <script src="./main.js"></script>
</body>
</html>

