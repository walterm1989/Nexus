// frontend/js/main.js

// ⛳ Ajusta esto si tu backend público está en otro lugar
const BASE_URL = 'http://localhost/nexus_inventario/backend/publico/index.php';

// --- Auth helpers ---
function setToken(token){ localStorage.setItem('token', token); }
function getToken(){ return localStorage.getItem('token'); }
function setUser(user){ localStorage.setItem('user', JSON.stringify(user)); }
function getUser(){ try { return JSON.parse(localStorage.getItem('user')||'null'); } catch { return null; } }
function logout(){ localStorage.removeItem('token'); localStorage.removeItem('user'); location.href = '../login/index.php'; }
function requireAuth(){
  if(!getToken()){ location.href = '../login/index.php'; }
}

// --- Fetch helper con método y body ---
async function apiRequest(ruta, method='GET', body=null, query=null){
  const url = new URL(BASE_URL);
  url.searchParams.set('ruta', ruta);
  if(query && typeof query==='object'){
    Object.entries(query).forEach(([k,v]) => {
      if(v!==undefined && v!==null) url.searchParams.set(k, v);
    });
  }

  const headers = { 'Content-Type':'application/json' };
  const token = getToken();
  if(token){ headers['Authorization'] = 'Bearer ' + token; }

  const res = await fetch(url.toString(), {
    method,
    headers,
    body: body ? JSON.stringify(body) : null
  });

  let data=null;
  try { data = await res.json(); } catch(e){ /* puede no ser JSON */ }

  if(!res.ok){
    const msg = (data && (data.mensaje || data.error)) || ('Error HTTP '+res.status);
    throw new Error(msg);
  }
  return data;
}

const api = {
  get: (ruta, query) => apiRequest(ruta,'GET',null,query),
  post: (ruta, body, query) => apiRequest(ruta,'POST',body,query),
  put: (ruta, body, query) => apiRequest(ruta,'PUT',body,query),
  del: (ruta, query) => apiRequest(ruta,'DELETE',null,query),
};

// --- UI helpers ---
function toast(msg, type='ok'){
  const el = document.createElement('div');
  el.textContent = msg;
  el.style.position='fixed';
  el.style.right='16px';
  el.style.bottom='16px';
  el.style.padding='12px 14px';
  el.style.borderRadius='10px';
  el.style.boxShadow='0 2px 12px rgba(0,0,0,.15)';
  el.style.background = type==='ok' ? '#2e7d32' : '#c62828';
  el.style.color='#fff';
  el.style.zIndex=9999;
  document.body.appendChild(el);
  setTimeout(()=>{ el.remove(); }, 2500);
}

window.App = { api, toast, logout, getUser, requireAuth };
