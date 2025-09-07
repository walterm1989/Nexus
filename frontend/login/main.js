// frontend/login/main.js
document.getElementById('loginForm').addEventListener('submit', async (e)=>{
  e.preventDefault();
  const email = document.getElementById('email').value.trim();
  const password = document.getElementById('password').value.trim();

  // Si tienes endpoint real de login, podrías llamar:
  // const res = await App.api.post('login', { email, password });
  // App.setToken(res.datos.token); App.setUser(res.datos.usuario);

  // Como tu backend actual simula JWT en rutas, guardamos un token fake:
  const fakeToken = 'token_demo';
  const user = { nombre: email.split('@')[0] || 'Usuario', email, rol_id:1 };
  setToken(fakeToken);
  setUser(user);
  App.toast('Bienvenido, ' + user.nombre);
  setTimeout(()=> location.href = '../dashboard/index.php', 500);
});

