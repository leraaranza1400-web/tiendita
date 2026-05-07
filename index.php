<?php
// ============================================================
//  index.php — Pantalla de Login / Registro
// ============================================================
require_once __DIR__ . '/config/session.php';

// Si ya hay sesión activa, redirigir al panel
if (!empty($_SESSION['user_id'])) {
    header('Location: panel.php');
    exit;
}

$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tienda Manager — Acceso</title>
<style>
/* ═══════════════════════════════════════════════════
   VARIABLES & RESET
═══════════════════════════════════════════════════ */
:root {
  --c-bg:       #0d1117;
  --c-surface:  #161b22;
  --c-border:   #30363d;
  --c-accent:   #3fb950;
  --c-accent2:  #58a6ff;
  --c-text:     #e6edf3;
  --c-muted:    #8b949e;
  --c-error:    #f85149;
  --c-warn:     #d29922;
  --radius:     12px;
  --trans:      .25s cubic-bezier(.4,0,.2,1);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{height:100%}
body{
  min-height:100vh;
  font-family:'Segoe UI',system-ui,sans-serif;
  background:var(--c-bg);
  color:var(--c-text);
  display:flex;
  align-items:center;
  justify-content:center;
  overflow:hidden;
  position:relative;
}

/* ── FONDO ANIMADO ── */
.bg-grid{
  position:fixed;inset:0;
  background-image:
    linear-gradient(rgba(63,185,80,.04) 1px,transparent 1px),
    linear-gradient(90deg,rgba(63,185,80,.04) 1px,transparent 1px);
  background-size:40px 40px;
  z-index:0;
  animation:gridMove 20s linear infinite;
}
@keyframes gridMove{to{background-position:40px 40px}}

.blob{
  position:fixed;border-radius:50%;filter:blur(80px);opacity:.18;
  animation:blobFloat 8s ease-in-out infinite alternate;
  pointer-events:none;z-index:0;
}
.blob-1{width:500px;height:500px;background:var(--c-accent);top:-150px;left:-150px;}
.blob-2{width:400px;height:400px;background:var(--c-accent2);bottom:-100px;right:-100px;animation-delay:-4s;}

/* ── TARJETA PRINCIPAL ── */
.card{
  position:relative;z-index:1;
  width:min(430px,94vw);
  background:var(--c-surface);
  border:1px solid var(--c-border);
  border-radius:var(--radius);
  padding:2.5rem 2rem;
  box-shadow:0 24px 80px rgba(0,0,0,.6);
  animation:cardIn .5s var(--trans) both;
}
@keyframes cardIn{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:none}}

.logo{
  display:flex;align-items:center;gap:.7rem;
  margin-bottom:1.8rem;
}
.logo-icon{
  width:44px;height:44px;
  background:linear-gradient(135deg,var(--c-accent),var(--c-accent2));
  border-radius:10px;
  display:grid;place-items:center;
  font-size:1.4rem;
}
.logo-text h1{font-size:1.3rem;font-weight:700;letter-spacing:-.5px;}
.logo-text span{font-size:.75rem;color:var(--c-muted);}

/* ── TABS ── */
.tabs{
  display:flex;border-bottom:1px solid var(--c-border);margin-bottom:1.6rem;
}
.tab-btn{
  flex:1;padding:.6rem;font-size:.85rem;font-weight:600;
  background:none;border:none;color:var(--c-muted);cursor:pointer;
  border-bottom:2px solid transparent;transition:var(--trans);
}
.tab-btn.active{color:var(--c-accent);border-bottom-color:var(--c-accent);}

/* ── FORMULARIOS ── */
.form-group{margin-bottom:1rem;}
label{display:block;font-size:.78rem;color:var(--c-muted);margin-bottom:.35rem;font-weight:500;text-transform:uppercase;letter-spacing:.5px;}
.input-wrap{position:relative;}
.input-wrap .icon{
  position:absolute;left:.75rem;top:50%;transform:translateY(-50%);
  color:var(--c-muted);font-size:.95rem;pointer-events:none;
}
input[type=text],input[type=email],input[type=password],select{
  width:100%;padding:.65rem .75rem .65rem 2.3rem;
  background:var(--c-bg);border:1px solid var(--c-border);
  border-radius:8px;color:var(--c-text);font-size:.9rem;
  transition:var(--trans);outline:none;
}
input:focus,select:focus{border-color:var(--c-accent);box-shadow:0 0 0 3px rgba(63,185,80,.15);}
input.error{border-color:var(--c-error);}

.row-2{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;}

.btn{
  width:100%;padding:.75rem;border:none;border-radius:8px;
  font-size:.9rem;font-weight:700;cursor:pointer;
  transition:var(--trans);letter-spacing:.3px;
  position:relative;overflow:hidden;
}
.btn-primary{
  background:linear-gradient(135deg,var(--c-accent),#2ea043);
  color:#fff;margin-top:.5rem;
}
.btn-primary:hover{filter:brightness(1.1);transform:translateY(-1px);}
.btn-primary:active{transform:translateY(0);}
.btn-primary:disabled{opacity:.5;cursor:not-allowed;transform:none;}

/* Spinner dentro del botón */
.btn-spinner{
  display:none;width:16px;height:16px;border:2px solid rgba(255,255,255,.3);
  border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;
  vertical-align:middle;margin-right:.4rem;
}
.loading .btn-spinner{display:inline-block;}
.loading .btn-text{display:none;}
@keyframes spin{to{transform:rotate(360deg)}}

/* ── TOAST NOTIFICATIONS ── */
#toast-container{
  position:fixed;bottom:1.5rem;right:1.5rem;z-index:999;
  display:flex;flex-direction:column-reverse;gap:.5rem;
}
.toast{
  padding:.75rem 1.1rem;border-radius:8px;font-size:.85rem;font-weight:500;
  min-width:240px;max-width:340px;
  animation:toastIn .3s var(--trans) both;
  display:flex;align-items:center;gap:.6rem;
  box-shadow:0 8px 24px rgba(0,0,0,.4);
}
.toast.success{background:#1a3a2a;border:1px solid var(--c-accent);color:var(--c-accent);}
.toast.error{background:#3a1a1a;border:1px solid var(--c-error);color:var(--c-error);}
.toast.info{background:#1a2a3a;border:1px solid var(--c-accent2);color:var(--c-accent2);}
@keyframes toastIn{from{opacity:0;transform:translateX(20px)}to{opacity:1;transform:none}}

/* ── MISC ── */
.divider{text-align:center;color:var(--c-muted);font-size:.75rem;margin:.3rem 0 1rem;position:relative;}
.divider::before,.divider::after{content:'';position:absolute;top:50%;width:42%;height:1px;background:var(--c-border);}
.divider::before{left:0}.divider::after{right:0}

.err-msg{color:var(--c-error);font-size:.75rem;margin-top:.25rem;display:none;}
.err-msg.show{display:block;}
</style>
</head>
<body>
<div class="bg-grid"></div>
<div class="blob blob-1"></div>
<div class="blob blob-2"></div>

<div class="card">
  <div class="logo">
    <div class="logo-icon">🛍️</div>
    <div class="logo-text">
      <h1>Tienda Manager</h1>
      <span>Sistema de Gestión Integral</span>
    </div>
  </div>

  <!-- TABS -->
  <div class="tabs">
    <button class="tab-btn active" data-tab="login">Iniciar Sesión</button>
    <button class="tab-btn" data-tab="register">Nuevo Usuario</button>
  </div>

  <!-- ── FORM LOGIN ── -->
  <div id="tab-login">
    <form id="form-login" novalidate>
      <div class="form-group">
        <label>Usuario o Email</label>
        <div class="input-wrap">
          <span class="icon">👤</span>
          <input type="text" name="username" id="login-username" placeholder="tu_usuario" autocomplete="username" required>
        </div>
        <span class="err-msg" id="err-username">Ingresa tu usuario.</span>
      </div>
      <div class="form-group">
        <label>Contraseña</label>
        <div class="input-wrap">
          <span class="icon">🔒</span>
          <input type="password" name="password" id="login-password" placeholder="••••••••" autocomplete="current-password" required>
        </div>
        <span class="err-msg" id="err-password">Ingresa tu contraseña.</span>
      </div>
      <button type="submit" class="btn btn-primary" id="btn-login">
        <span class="btn-spinner"></span>
        <span class="btn-text">Entrar al Sistema</span>
      </button>
    </form>
  </div>

  <!-- ── FORM REGISTRO ── -->
  <div id="tab-register" style="display:none">
    <form id="form-register" novalidate>
      <div class="row-2">
        <div class="form-group">
          <label>Nombre</label>
          <div class="input-wrap">
            <span class="icon">✏️</span>
            <input type="text" name="nombre" placeholder="Ana" required>
          </div>
        </div>
        <div class="form-group">
          <label>Apellido</label>
          <div class="input-wrap">
            <span class="icon">✏️</span>
            <input type="text" name="apellido" placeholder="García" required>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label>Correo Electrónico</label>
        <div class="input-wrap">
          <span class="icon">📧</span>
          <input type="email" name="email" placeholder="correo@ejemplo.com" required>
        </div>
      </div>
      <div class="form-group">
        <label>Nombre de usuario</label>
        <div class="input-wrap">
          <span class="icon">@</span>
          <input type="text" name="username" placeholder="mi_usuario" required>
        </div>
      </div>
      <div class="row-2">
        <div class="form-group">
          <label>Contraseña</label>
          <div class="input-wrap">
            <span class="icon">🔒</span>
            <input type="password" name="password" placeholder="••••••" required>
          </div>
        </div>
        <div class="form-group">
          <label>Confirmar</label>
          <div class="input-wrap">
            <span class="icon">🔒</span>
            <input type="password" name="confirm" placeholder="••••••" required>
          </div>
        </div>
      </div>
      <div class="divider">Tu cuenta se creará con rol de Empleado</div>
      <button type="submit" class="btn btn-primary" id="btn-register">
        <span class="btn-spinner"></span>
        <span class="btn-text">Crear Cuenta</span>
      </button>
    </form>
  </div>
</div>

<div id="toast-container"></div>

<script>
// ── TABS ───────────────────────────────────────────────────
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const tab = btn.dataset.tab;
    document.getElementById('tab-login').style.display    = tab === 'login'    ? '' : 'none';
    document.getElementById('tab-register').style.display = tab === 'register' ? '' : 'none';
  });
});

// ── TOAST ──────────────────────────────────────────────────
function showToast(msg, type = 'info') {
  const icons = {success:'✅', error:'❌', info:'ℹ️'};
  const t = document.createElement('div');
  t.className = `toast ${type}`;
  t.innerHTML = `<span>${icons[type]}</span><span>${msg}</span>`;
  document.getElementById('toast-container').appendChild(t);
  setTimeout(() => { t.style.opacity = '0'; t.style.transform = 'translateX(20px)'; t.style.transition = '.3s'; setTimeout(() => t.remove(), 300); }, 3500);
}

// ── LOGIN ──────────────────────────────────────────────────
document.getElementById('form-login').addEventListener('submit', async e => {
  e.preventDefault();
  const btn = document.getElementById('btn-login');
  const username = document.getElementById('login-username').value.trim();
  const password = document.getElementById('login-password').value;

  // Validación
  let ok = true;
  if (!username) { document.getElementById('err-username').classList.add('show'); ok = false; }
  else document.getElementById('err-username').classList.remove('show');
  if (!password) { document.getElementById('err-password').classList.add('show'); ok = false; }
  else document.getElementById('err-password').classList.remove('show');
  if (!ok) return;

  btn.classList.add('loading'); btn.disabled = true;
  try {
    const fd = new FormData();
    fd.append('action','login'); fd.append('username',username); fd.append('password',password);
    const res  = await fetch('auth/auth.php', { method:'POST', body: fd });
    const data = await res.json();
    if (data.ok) {
      showToast(data.msg, 'success');
      setTimeout(() => window.location.href = data.redirect, 800);
    } else {
      showToast(data.msg, 'error');
      btn.classList.remove('loading'); btn.disabled = false;
    }
  } catch {
    showToast('Error de conexión.', 'error');
    btn.classList.remove('loading'); btn.disabled = false;
  }
});

// ── REGISTRO ───────────────────────────────────────────────
document.getElementById('form-register').addEventListener('submit', async e => {
  e.preventDefault();
  const btn = document.getElementById('btn-register');
  const form = e.target;
  const data = Object.fromEntries(new FormData(form).entries());

  if (!data.nombre || !data.apellido || !data.email || !data.username || !data.password) {
    return showToast('Completa todos los campos.', 'error');
  }
  if (!/\S+@\S+\.\S+/.test(data.email)) return showToast('Email inválido.', 'error');
  if (data.password.length < 6) return showToast('La contraseña necesita al menos 6 caracteres.', 'error');
  if (data.password !== data.confirm) return showToast('Las contraseñas no coinciden.', 'error');

  btn.classList.add('loading'); btn.disabled = true;
  try {
    const fd = new FormData(form);
    fd.append('action','register');
    const res  = await fetch('auth/auth.php', { method:'POST', body: fd });
    const resp = await res.json();
    showToast(resp.msg, resp.ok ? 'success' : 'error');
    if (resp.ok) {
      form.reset();
      setTimeout(() => document.querySelector('[data-tab="login"]').click(), 1000);
    }
  } catch {
    showToast('Error de conexión.', 'error');
  }
  btn.classList.remove('loading'); btn.disabled = false;
});

// Mostrar mensaje de sesión cerrada
<?php if ($msg === 'sesion_cerrada'): ?>
showToast('Sesión cerrada correctamente.', 'info');
<?php endif; ?>
</script>
</body>
</html>
