<?php
// ============================================================
//  panel.php — Panel Principal (Admin + Empleado)
// ============================================================
require_once __DIR__ . '/config/session.php';
requireAuth();

$nombre     = $_SESSION['nombre'];
$rol        = $_SESSION['rol_nombre'];
$isAdmin    = isAdmin();
$csrf       = csrfToken();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tienda Manager — Panel</title>
<style>
/* ═══════════════════════════════════════════════════
   DESIGN SYSTEM
═══════════════════════════════════════════════════ */
:root{
  --bg:       #0d1117;
  --surf:     #161b22;
  --surf2:    #21262d;
  --border:   #30363d;
  --accent:   #3fb950;
  --accent2:  #58a6ff;
  --warn:     #d29922;
  --danger:   #f85149;
  --text:     #e6edf3;
  --muted:    #8b949e;
  --sidebar-w:230px;
  --hdr-h:    56px;
  --radius:   10px;
  --trans:    .22s cubic-bezier(.4,0,.2,1);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;overflow:hidden}
body{display:flex;flex-direction:column;font-family:'Segoe UI',system-ui,sans-serif;background:var(--bg);color:var(--text);font-size:14px;}

/* ── HEADER ── */
.hdr{
  height:var(--hdr-h);display:flex;align-items:center;justify-content:space-between;
  padding:0 1.2rem;background:var(--surf);border-bottom:1px solid var(--border);
  position:fixed;top:0;left:0;right:0;z-index:100;
}
.hdr-left{display:flex;align-items:center;gap:.8rem;}
.brand{font-size:1rem;font-weight:700;display:flex;align-items:center;gap:.5rem;}
.brand-dot{width:8px;height:8px;border-radius:50%;background:var(--accent);display:inline-block;animation:pulse 2s infinite;}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.hdr-right{display:flex;align-items:center;gap:.8rem;}
.user-chip{
  display:flex;align-items:center;gap:.5rem;padding:.35rem .7rem;
  background:var(--surf2);border:1px solid var(--border);border-radius:20px;font-size:.82rem;
}
.role-badge{
  font-size:.68rem;padding:.15rem .45rem;border-radius:20px;font-weight:700;
  background:<?= $isAdmin ? 'rgba(210,153,34,.2)' : 'rgba(88,166,255,.15)' ?>;
  color:<?= $isAdmin ? 'var(--warn)' : 'var(--accent2)' ?>;
  border:1px solid <?= $isAdmin ? 'rgba(210,153,34,.3)' : 'rgba(88,166,255,.25)' ?>;
}
.btn-logout{
  padding:.35rem .8rem;background:none;border:1px solid var(--border);
  border-radius:6px;color:var(--muted);font-size:.8rem;cursor:pointer;
  transition:var(--trans);
}
.btn-logout:hover{border-color:var(--danger);color:var(--danger);}

/* ── LAYOUT ── */
.layout{display:flex;height:100vh;padding-top:var(--hdr-h);}

/* ── SIDEBAR ── */
.sidebar{
  width:var(--sidebar-w);flex-shrink:0;
  background:var(--surf);border-right:1px solid var(--border);
  display:flex;flex-direction:column;overflow-y:auto;
  position:fixed;top:var(--hdr-h);bottom:0;left:0;z-index:90;
}
.nav-section{padding:1rem .75rem .25rem;color:var(--muted);font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;}
.nav-item{
  display:flex;align-items:center;gap:.65rem;
  padding:.55rem .9rem;border-radius:8px;margin:.1rem .5rem;
  color:var(--muted);font-size:.85rem;cursor:pointer;
  transition:var(--trans);border:1px solid transparent;user-select:none;
}
.nav-item:hover{background:var(--surf2);color:var(--text);}
.nav-item.active{background:rgba(63,185,80,.1);color:var(--accent);border-color:rgba(63,185,80,.2);}
.nav-item .icon{font-size:1.05rem;width:20px;text-align:center;}

/* ── MAIN CONTENT ── */
.main{
  margin-left:var(--sidebar-w);flex:1;
  overflow-y:auto;padding:1.4rem;
  height:calc(100vh - var(--hdr-h));
}

/* ── VIEWS (sections) ── */
.view{display:none;animation:fadeUp .3s var(--trans) both;}
.view.active{display:block;}
@keyframes fadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}

/* ── PAGE HEADER ── */
.page-hdr{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.2rem;}
.page-hdr h2{font-size:1.15rem;font-weight:700;}
.page-hdr p{font-size:.8rem;color:var(--muted);margin-top:.15rem;}

/* ── KPI CARDS ── */
.kpi-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:1.4rem;}
.kpi{
  background:var(--surf);border:1px solid var(--border);border-radius:var(--radius);
  padding:1.1rem 1.2rem;position:relative;overflow:hidden;
  transition:var(--trans);
}
.kpi:hover{border-color:var(--accent);transform:translateY(-2px);}
.kpi-label{font-size:.75rem;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px;}
.kpi-val{font-size:1.8rem;font-weight:800;margin:.3rem 0;line-height:1;}
.kpi-sub{font-size:.75rem;color:var(--muted);}
.kpi-icon{position:absolute;right:1rem;top:.9rem;font-size:1.8rem;opacity:.25;}

/* ── TABLA ── */
.tbl-wrap{background:var(--surf);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;}
.tbl-toolbar{
  display:flex;align-items:center;justify-content:space-between;gap:.75rem;
  padding:.75rem 1rem;border-bottom:1px solid var(--border);flex-wrap:wrap;
}
.search-box{
  display:flex;align-items:center;gap:.4rem;
  background:var(--bg);border:1px solid var(--border);border-radius:7px;
  padding:.4rem .7rem;flex:1;min-width:160px;max-width:280px;
}
.search-box input{background:none;border:none;outline:none;color:var(--text);font-size:.85rem;width:100%;}
table{width:100%;border-collapse:collapse;}
thead{background:var(--surf2);}
th{padding:.65rem .85rem;text-align:left;font-size:.73rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border);}
td{padding:.6rem .85rem;border-bottom:1px solid var(--border);font-size:.85rem;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:rgba(255,255,255,.02);}

/* ── BUTTONS ── */
.btn{
  display:inline-flex;align-items:center;gap:.35rem;
  padding:.45rem .85rem;border:none;border-radius:7px;
  font-size:.82rem;font-weight:600;cursor:pointer;transition:var(--trans);
}
.btn-sm{padding:.3rem .6rem;font-size:.77rem;}
.btn-primary{background:var(--accent);color:#000;}
.btn-primary:hover{filter:brightness(1.1);}
.btn-secondary{background:var(--surf2);border:1px solid var(--border);color:var(--text);}
.btn-secondary:hover{border-color:var(--accent2);}
.btn-danger{background:rgba(248,81,73,.15);border:1px solid rgba(248,81,73,.3);color:var(--danger);}
.btn-danger:hover{background:rgba(248,81,73,.25);}
.btn-warn{background:rgba(210,153,34,.15);border:1px solid rgba(210,153,34,.3);color:var(--warn);}
.btn-warn:hover{background:rgba(210,153,34,.25);}

/* ── BADGES ── */
.badge{display:inline-block;padding:.15rem .5rem;border-radius:20px;font-size:.72rem;font-weight:700;}
.badge-green{background:rgba(63,185,80,.15);color:var(--accent);}
.badge-red{background:rgba(248,81,73,.15);color:var(--danger);}
.badge-blue{background:rgba(88,166,255,.15);color:var(--accent2);}
.badge-warn{background:rgba(210,153,34,.15);color:var(--warn);}

/* ── MODAL ── */
.modal-overlay{
  position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:200;
  display:none;align-items:center;justify-content:center;
  animation:fadeIn .2s var(--trans);
  backdrop-filter:blur(4px);
}
.modal-overlay.open{display:flex;}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
.modal{
  background:var(--surf);border:1px solid var(--border);border-radius:var(--radius);
  width:min(500px,92vw);max-height:90vh;overflow-y:auto;
  animation:modalIn .25s var(--trans) both;
}
@keyframes modalIn{from{opacity:0;transform:scale(.94)}to{opacity:1;transform:scale(1)}}
.modal-hdr{
  display:flex;align-items:center;justify-content:space-between;
  padding:1.1rem 1.2rem;border-bottom:1px solid var(--border);
}
.modal-hdr h3{font-size:1rem;font-weight:700;}
.modal-close{background:none;border:none;color:var(--muted);font-size:1.2rem;cursor:pointer;padding:.2rem;}
.modal-close:hover{color:var(--text);}
.modal-body{padding:1.2rem;}
.modal-footer{padding:.9rem 1.2rem;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:.5rem;}

/* ── FORM GROUPS ── */
.fg{margin-bottom:1rem;}
.fg label{display:block;font-size:.75rem;color:var(--muted);margin-bottom:.35rem;font-weight:600;text-transform:uppercase;letter-spacing:.4px;}
.fg input,.fg select,.fg textarea{
  width:100%;padding:.6rem .8rem;
  background:var(--bg);border:1px solid var(--border);
  border-radius:7px;color:var(--text);font-size:.87rem;outline:none;
  transition:var(--trans);
}
.fg input:focus,.fg select:focus,.fg textarea:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(63,185,80,.12);}
.fg textarea{resize:vertical;min-height:70px;}
.row-2{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;}

/* ── VENTA CART ── */
.cart-item{
  display:flex;align-items:center;justify-content:space-between;
  padding:.55rem .7rem;background:var(--surf2);border:1px solid var(--border);
  border-radius:7px;margin-bottom:.4rem;gap:.5rem;
}
.cart-item-name{font-size:.85rem;flex:1;}
.cart-item-qty input{width:55px;text-align:center;}
.cart-total{
  text-align:right;padding:.8rem;background:rgba(63,185,80,.06);
  border:1px solid rgba(63,185,80,.2);border-radius:7px;margin-top:.8rem;
}
.cart-total span{font-size:1.2rem;font-weight:800;color:var(--accent);}

/* ── TOAST ── */
#toast{
  position:fixed;bottom:1.5rem;right:1.5rem;z-index:999;
  display:flex;flex-direction:column-reverse;gap:.5rem;
}
.toast-item{
  padding:.65rem 1rem;border-radius:8px;font-size:.83rem;font-weight:500;
  min-width:220px;animation:toastIn .25s var(--trans) both;
  display:flex;align-items:center;gap:.5rem;
  box-shadow:0 8px 24px rgba(0,0,0,.5);
}
.toast-s{background:#1a3326;border:1px solid var(--accent);color:var(--accent);}
.toast-e{background:#3a1a1a;border:1px solid var(--danger);color:var(--danger);}
.toast-i{background:#1a2536;border:1px solid var(--accent2);color:var(--accent2);}
@keyframes toastIn{from{opacity:0;transform:translateX(20px)}to{opacity:1;transform:none}}

/* ── EMPTY STATE ── */
.empty{text-align:center;padding:3rem;color:var(--muted);}
.empty .empty-icon{font-size:3rem;opacity:.4;margin-bottom:.8rem;}

/* ── STOCK WARNING ── */
.stock-low{color:var(--danger);font-weight:700;}

/* scrollbar */
::-webkit-scrollbar{width:6px;height:6px;}
::-webkit-scrollbar-track{background:transparent;}
::-webkit-scrollbar-thumb{background:var(--border);border-radius:3px;}
</style>
</head>
<body>

<!-- ── HEADER ── -->
<header class="hdr">
  <div class="hdr-left">
    <div class="brand"><span class="brand-dot"></span>🛍️ Tienda Manager</div>
  </div>
  <div class="hdr-right">
    <div class="user-chip">
      <span>👤</span>
      <span><?= htmlspecialchars($nombre) ?></span>
      <span class="role-badge"><?= htmlspecialchars($rol) ?></span>
    </div>
    <button class="btn-logout" onclick="logout()">⏏ Salir</button>
  </div>
</header>

<!-- ── LAYOUT ── -->
<div class="layout">

  <!-- SIDEBAR -->
  <nav class="sidebar">
    <div class="nav-section">Principal</div>
    <div class="nav-item active" data-view="dashboard" onclick="nav(this)"><span class="icon">📊</span> Dashboard</div>

    <div class="nav-section">Operaciones</div>
    <div class="nav-item" data-view="ventas-new" onclick="nav(this)"><span class="icon">🛒</span> Nueva Venta</div>
    <div class="nav-item" data-view="ventas-list" onclick="nav(this)"><span class="icon">📋</span> Ventas</div>
    <div class="nav-item" data-view="clientes" onclick="nav(this)"><span class="icon">👥</span> Clientes</div>
    <div class="nav-item" data-view="productos" onclick="nav(this)"><span class="icon">📦</span> Productos</div>

    <?php if ($isAdmin): ?>
    <div class="nav-section">Administración</div>
    <div class="nav-item" data-view="usuarios" onclick="nav(this)"><span class="icon">🔑</span> Usuarios</div>
    <?php endif; ?>

    <div class="nav-section">Cuenta</div>
    <div class="nav-item" data-view="mi-perfil" onclick="nav(this)"><span class="icon">⚙️</span> Mi Perfil</div>
  </nav>

  <!-- MAIN -->
  <main class="main">

    <!-- ══ DASHBOARD ══════════════════════════════════════ -->
    <section class="view active" id="view-dashboard">
      <div class="page-hdr">
        <div><h2>Dashboard</h2><p>Resumen del sistema en tiempo real</p></div>
      </div>
      <div class="kpi-grid" id="kpi-grid">
        <div class="kpi"><span class="kpi-icon">📦</span><div class="kpi-label">Productos</div><div class="kpi-val" id="kpi-prod">—</div><div class="kpi-sub">En catálogo activo</div></div>
        <div class="kpi"><span class="kpi-icon">👥</span><div class="kpi-label">Clientes</div><div class="kpi-val" id="kpi-cli">—</div><div class="kpi-sub">Registrados</div></div>
        <div class="kpi"><span class="kpi-icon">🛒</span><div class="kpi-label">Ventas Hoy</div><div class="kpi-val" id="kpi-ven">—</div><div class="kpi-sub">Transacciones</div></div>
        <div class="kpi"><span class="kpi-icon">💰</span><div class="kpi-label">Ingresos Hoy</div><div class="kpi-val" id="kpi-ing">—</div><div class="kpi-sub">Total del día</div></div>
      </div>
      <div class="tbl-wrap">
        <div class="tbl-toolbar"><strong style="font-size:.88rem">⚠️ Productos con stock bajo</strong></div>
        <table><thead><tr><th>Código</th><th>Producto</th><th>Stock</th><th>Mínimo</th></tr></thead>
        <tbody id="tbl-stock-bajo"><tr><td colspan="4" class="empty">Cargando...</td></tr></tbody></table>
      </div>
    </section>

    <!-- ══ NUEVA VENTA ════════════════════════════════════ -->
    <section class="view" id="view-ventas-new">
      <div class="page-hdr">
        <div><h2>🛒 Nueva Venta</h2><p>Registra una transacción</p></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.2rem;align-items:start">
        <!-- Panel izquierdo: cliente + buscar producto -->
        <div>
          <div class="tbl-wrap" style="padding:1rem;margin-bottom:1rem;">
            <h3 style="font-size:.9rem;margin-bottom:.8rem;">1. Cliente</h3>
            <div class="fg">
              <label>Buscar cliente</label>
              <div style="display:flex;gap:.5rem">
                <input type="text" id="cli-search" placeholder="Nombre o teléfono…">
                <button class="btn btn-secondary" onclick="searchClientes()">🔍</button>
              </div>
            </div>
            <div id="cli-results" style="max-height:140px;overflow-y:auto;"></div>
            <div id="cli-selected" style="margin-top:.6rem;display:none" class="badge badge-green">✅ <span id="cli-name"></span></div>
            <hr style="border-color:var(--border);margin:.8rem 0">
            <details>
              <summary style="cursor:pointer;font-size:.83rem;color:var(--accent2)">➕ Registrar cliente nuevo</summary>
              <div style="margin-top:.6rem">
                <div class="row-2">
                  <div class="fg"><label>Nombre</label><input type="text" id="nc-nombre" placeholder="Ana"></div>
                  <div class="fg"><label>Apellido</label><input type="text" id="nc-apellido" placeholder="García"></div>
                </div>
                <div class="fg"><label>Teléfono</label><input type="text" id="nc-tel" placeholder="722…"></div>
                <button class="btn btn-secondary btn-sm" onclick="saveNewCliente()">💾 Guardar</button>
              </div>
            </details>
          </div>

          <div class="tbl-wrap" style="padding:1rem;">
            <h3 style="font-size:.9rem;margin-bottom:.8rem;">2. Agregar Producto</h3>
            <div style="display:flex;gap:.5rem;margin-bottom:.5rem">
              <input type="text" id="prod-search" placeholder="Buscar por nombre o código…" style="flex:1;padding:.5rem .7rem;background:var(--bg);border:1px solid var(--border);border-radius:7px;color:var(--text);outline:none;">
              <button class="btn btn-secondary" onclick="searchProductos()">🔍</button>
            </div>
            <div id="prod-results" style="max-height:180px;overflow-y:auto;"></div>
          </div>
        </div>

        <!-- Panel derecho: carrito -->
        <div class="tbl-wrap" style="padding:1rem;">
          <h3 style="font-size:.9rem;margin-bottom:.8rem;">3. Carrito</h3>
          <div id="cart-items"><div class="empty"><div class="empty-icon">🛒</div>Sin productos</div></div>
          <div class="cart-total">Total: <span id="cart-total-val">$0.00</span></div>
          <div class="fg" style="margin-top:.8rem">
            <label>Método de Pago</label>
            <select id="metodo-pago">
              <option>Efectivo</option><option>Tarjeta</option>
              <option>Transferencia</option><option>Otro</option>
            </select>
          </div>
          <div class="fg">
            <label>Notas (opcional)</label>
            <textarea id="venta-notas" placeholder="Observaciones…" style="min-height:55px"></textarea>
          </div>
          <button class="btn btn-primary" style="width:100%;justify-content:center;font-size:.9rem" onclick="confirmarVenta()">
            ✅ Confirmar Venta
          </button>
        </div>
      </div>
    </section>

    <!-- ══ LISTA DE VENTAS ════════════════════════════════ -->
    <section class="view" id="view-ventas-list">
      <div class="page-hdr">
        <div><h2>📋 Ventas</h2><p>Historial de transacciones</p></div>
      </div>
      <div class="tbl-wrap">
        <div class="tbl-toolbar">
          <div class="search-box"><span>🔍</span><input type="text" id="search-ventas" placeholder="Buscar folio o cliente…" oninput="filterVentas()"></div>
        </div>
        <table><thead><tr><th>Folio</th><th>Fecha</th><th>Cliente</th><th>Empleado</th><th>Total</th><th>Pago</th><th>Estado</th></tr></thead>
        <tbody id="tbl-ventas"><tr><td colspan="7" class="empty">Cargando…</td></tr></tbody></table>
      </div>
    </section>

    <!-- ══ CLIENTES ═══════════════════════════════════════ -->
    <section class="view" id="view-clientes">
      <div class="page-hdr">
        <div><h2>👥 Clientes</h2><p>Directorio de clientes</p></div>
        <button class="btn btn-primary" onclick="openModal('modal-cliente')">➕ Nuevo Cliente</button>
      </div>
      <div class="tbl-wrap">
        <div class="tbl-toolbar">
          <div class="search-box"><span>🔍</span><input type="text" id="search-cli" placeholder="Nombre, email, teléfono…" oninput="loadClientes(this.value)"></div>
        </div>
        <table><thead><tr><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Dirección</th><?php if($isAdmin):?><th>Acciones</th><?php endif;?></tr></thead>
        <tbody id="tbl-clientes"><tr><td colspan="5" class="empty">Cargando…</td></tr></tbody></table>
      </div>
    </section>

    <!-- ══ PRODUCTOS ══════════════════════════════════════ -->
    <section class="view" id="view-productos">
      <div class="page-hdr">
        <div><h2>📦 Productos</h2><p>Catálogo e inventario</p></div>
        <?php if($isAdmin):?><button class="btn btn-primary" onclick="openModal('modal-prod')">➕ Nuevo Producto</button><?php endif;?>
      </div>
      <div class="tbl-wrap">
        <div class="tbl-toolbar">
          <div class="search-box"><span>🔍</span><input type="text" id="search-prod" placeholder="Nombre o código…" oninput="loadProductos(this.value)"></div>
        </div>
        <table><thead><tr><th>Código</th><th>Nombre</th><th>Categoría</th><th>Precio</th><th>Stock</th><?php if($isAdmin):?><th>Estado</th><th>Acciones</th><?php endif;?></tr></thead>
        <tbody id="tbl-productos"><tr><td colspan="7" class="empty">Cargando…</td></tr></tbody></table>
      </div>
    </section>

    <?php if ($isAdmin): ?>
    <!-- ══ USUARIOS ════════════════════════════════════════ -->
    <section class="view" id="view-usuarios">
      <div class="page-hdr">
        <div><h2>🔑 Usuarios</h2><p>Gestión de accesos al sistema</p></div>
        <button class="btn btn-primary" onclick="openModal('modal-user')">➕ Nuevo Usuario</button>
      </div>
      <div class="tbl-wrap">
        <table><thead><tr><th>Nombre</th><th>Username</th><th>Email</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody id="tbl-usuarios"><tr><td colspan="6" class="empty">Cargando…</td></tr></tbody></table>
      </div>
    </section>
    <?php endif; ?>

    <!-- ══ MI PERFIL ══════════════════════════════════════ -->
    <section class="view" id="view-mi-perfil">
      <div class="page-hdr"><div><h2>⚙️ Mi Perfil</h2><p>Actualiza tus datos personales</p></div></div>
      <div class="tbl-wrap" style="max-width:420px;padding:1.4rem">
        <div class="fg"><label>Nombre</label><input type="text" id="p-nombre"></div>
        <div class="fg"><label>Apellido</label><input type="text" id="p-apellido"></div>
        <div class="fg"><label>Email</label><input type="email" id="p-email"></div>
        <div class="fg"><label>Nueva Contraseña <span style="color:var(--muted)">(dejar vacío = sin cambios)</span></label><input type="password" id="p-pass" placeholder="••••••"></div>
        <button class="btn btn-primary" onclick="savePerfil()">💾 Guardar Cambios</button>
      </div>
    </section>

  </main>
</div><!-- /layout -->

<!-- ════════ MODALES ════════════════════════════════════ -->

<!-- MODAL: Producto -->
<div class="modal-overlay" id="modal-prod">
  <div class="modal">
    <div class="modal-hdr"><h3 id="modal-prod-title">Nuevo Producto</h3><button class="modal-close" onclick="closeModal('modal-prod')">✕</button></div>
    <div class="modal-body">
      <input type="hidden" id="prod-id">
      <div class="row-2">
        <div class="fg"><label>Código</label><input type="text" id="prod-codigo" placeholder="ELEC-001"></div>
        <div class="fg"><label>Categoría</label>
          <select id="prod-cat">
            <option value="1">Electrónica</option><option value="2">Ropa</option>
            <option value="3">Hogar</option><option value="4">Alimentos</option><option value="5">Papelería</option>
          </select>
        </div>
      </div>
      <div class="fg"><label>Nombre</label><input type="text" id="prod-nombre" placeholder="Audífonos Bluetooth"></div>
      <div class="fg"><label>Descripción</label><textarea id="prod-desc" placeholder="Descripción opcional…"></textarea></div>
      <div class="row-2">
        <div class="fg"><label>Precio ($)</label><input type="number" id="prod-precio" min="0" step="0.01" placeholder="0.00"></div>
        <div class="fg"><label>Stock</label><input type="number" id="prod-stock" min="0" placeholder="0"></div>
      </div>
      <div class="fg"><label>Stock Mínimo</label><input type="number" id="prod-smin" min="0" placeholder="5"></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('modal-prod')">Cancelar</button>
      <button class="btn btn-primary" onclick="saveProd()">💾 Guardar</button>
    </div>
  </div>
</div>

<!-- MODAL: Cliente -->
<div class="modal-overlay" id="modal-cliente">
  <div class="modal">
    <div class="modal-hdr"><h3 id="modal-cli-title">Nuevo Cliente</h3><button class="modal-close" onclick="closeModal('modal-cliente')">✕</button></div>
    <div class="modal-body">
      <input type="hidden" id="cli-id">
      <div class="row-2">
        <div class="fg"><label>Nombre</label><input type="text" id="cli-nombre"></div>
        <div class="fg"><label>Apellido</label><input type="text" id="cli-apellido"></div>
      </div>
      <div class="fg"><label>Email</label><input type="email" id="cli-email"></div>
      <div class="fg"><label>Teléfono</label><input type="text" id="cli-tel"></div>
      <div class="fg"><label>Dirección</label><input type="text" id="cli-dir"></div>
      <div class="fg"><label>RFC (opcional)</label><input type="text" id="cli-rfc"></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('modal-cliente')">Cancelar</button>
      <button class="btn btn-primary" onclick="saveCli()">💾 Guardar</button>
    </div>
  </div>
</div>

<!-- MODAL: Usuario (solo admin) -->
<?php if ($isAdmin): ?>
<div class="modal-overlay" id="modal-user">
  <div class="modal">
    <div class="modal-hdr"><h3 id="modal-user-title">Nuevo Usuario</h3><button class="modal-close" onclick="closeModal('modal-user')">✕</button></div>
    <div class="modal-body">
      <input type="hidden" id="user-id">
      <div class="row-2">
        <div class="fg"><label>Nombre</label><input type="text" id="user-nombre"></div>
        <div class="fg"><label>Apellido</label><input type="text" id="user-apellido"></div>
      </div>
      <div class="fg"><label>Email</label><input type="email" id="user-email"></div>
      <div class="fg"><label>Username</label><input type="text" id="user-username"></div>
      <div class="fg"><label>Contraseña</label><input type="password" id="user-pass" placeholder="Dejar vacío para no cambiar"></div>
      <div class="fg"><label>Rol</label>
        <select id="user-rol"><option value="1">Administrador</option><option value="2" selected>Empleado</option></select>
      </div>
      <div class="fg"><label>Estado</label>
        <select id="user-activo"><option value="1">Activo</option><option value="0">Inactivo</option></select>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('modal-user')">Cancelar</button>
      <button class="btn btn-primary" onclick="saveUser()">💾 Guardar</button>
    </div>
  </div>
</div>
<?php endif; ?>

<div id="toast"></div>

<script>
// ─────────────────────────────────────────────────────────
//  ESTADO GLOBAL
// ─────────────────────────────────────────────────────────
const IS_ADMIN = <?= $isAdmin ? 'true' : 'false' ?>;
const MY_ID    = <?= (int)$_SESSION['user_id'] ?>;
let cart = [];       // {id_producto, nombre, precio, cantidad}
let selectedCli = null;
let allVentas = [];

// ─────────────────────────────────────────────────────────
//  NAVEGACIÓN
// ─────────────────────────────────────────────────────────
function nav(el) {
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  el.classList.add('active');
  const view = el.dataset.view;
  document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
  document.getElementById('view-' + view)?.classList.add('active');
  // Cargar datos según la sección
  const loaders = {
    'dashboard':   loadDashboard,
    'ventas-list': loadVentas,
    'clientes':    () => loadClientes(),
    'productos':   () => loadProductos(),
    'usuarios':    loadUsuarios,
    'mi-perfil':   loadPerfil,
  };
  loaders[view]?.();
}

// ─────────────────────────────────────────────────────────
//  TOAST
// ─────────────────────────────────────────────────────────
function toast(msg, type='i') {
  const icons = {s:'✅',e:'❌',i:'ℹ️'};
  const t = document.createElement('div');
  t.className = `toast-item toast-${type}`;
  t.innerHTML = `${icons[type]} ${msg}`;
  document.getElementById('toast').appendChild(t);
  setTimeout(() => { t.style.opacity='0'; t.style.transition='.3s'; setTimeout(()=>t.remove(),300); }, 3500);
}

// ─────────────────────────────────────────────────────────
//  MODALES
// ─────────────────────────────────────────────────────────
function openModal(id) {
  document.getElementById(id).classList.add('open');
}
function closeModal(id) {
  document.getElementById(id).classList.remove('open');
}
// Cerrar al click fuera
document.querySelectorAll('.modal-overlay').forEach(o => {
  o.addEventListener('click', e => { if (e.target === o) o.classList.remove('open'); });
});

// ─────────────────────────────────────────────────────────
//  LOGOUT
// ─────────────────────────────────────────────────────────
function logout() {
  window.location.href = 'auth/auth.php?action=logout';
}

// ─────────────────────────────────────────────────────────
//  FETCH HELPER
// ─────────────────────────────────────────────────────────
async function api(url, opts={}) {
  const res = await fetch(url, opts);
  return res.json();
}

// ─────────────────────────────────────────────────────────
//  DASHBOARD
// ─────────────────────────────────────────────────────────
async function loadDashboard() {
  const [prods, clis, ventas] = await Promise.all([
    api('api/productos.php'),
    api('api/clientes.php'),
    api('api/ventas.php'),
  ]);

  document.getElementById('kpi-prod').textContent = prods.length;
  document.getElementById('kpi-cli').textContent  = clis.length;

  const today = new Date().toISOString().slice(0,10);
  const hoy   = ventas.filter(v => v.fecha?.slice(0,10) === today);
  document.getElementById('kpi-ven').textContent = hoy.length;
  const total = hoy.reduce((s,v) => s + parseFloat(v.total||0), 0);
  document.getElementById('kpi-ing').textContent = '$' + total.toLocaleString('es-MX', {minimumFractionDigits:2});

  // Stock bajo
  const bajo = prods.filter(p => parseInt(p.stock) <= parseInt(p.stock_minimo));
  const tbody = document.getElementById('tbl-stock-bajo');
  tbody.innerHTML = bajo.length
    ? bajo.map(p => `<tr><td>${p.codigo}</td><td>${p.nombre}</td><td class="stock-low">${p.stock}</td><td>${p.stock_minimo}</td></tr>`).join('')
    : `<tr><td colspan="4" class="empty">✅ Sin alertas de inventario</td></tr>`;
}

// ─────────────────────────────────────────────────────────
//  PRODUCTOS
// ─────────────────────────────────────────────────────────
async function loadProductos(q='') {
  const url = 'api/productos.php' + (q ? '?q=' + encodeURIComponent(q) : '');
  const data = await api(url);
  const tbody = document.getElementById('tbl-productos');
  if (!data.length) { tbody.innerHTML = `<tr><td colspan="7"><div class="empty"><div class="empty-icon">📦</div>Sin productos</div></td></tr>`; return; }

  tbody.innerHTML = data.map(p => `
    <tr>
      <td><code>${p.codigo}</code></td>
      <td>${p.nombre}</td>
      <td>${p.categoria}</td>
      <td>$${parseFloat(p.precio).toFixed(2)}</td>
      <td class="${parseInt(p.stock) <= parseInt(p.stock_minimo) ? 'stock-low' : ''}">${p.stock}</td>
      ${IS_ADMIN ? `<td><span class="badge ${p.activo=='1'?'badge-green':'badge-red'}">${p.activo=='1'?'Activo':'Inactivo'}</span></td>
      <td>
        <button class="btn btn-warn btn-sm" onclick='editProd(${JSON.stringify(p)})'>✏️</button>
        <button class="btn btn-danger btn-sm" onclick="delProd(${p.id_producto},'${p.nombre}')">🗑️</button>
      </td>` : ''}
    </tr>`).join('');
}

function openNewProd() {
  document.getElementById('modal-prod-title').textContent = 'Nuevo Producto';
  ['prod-id','prod-codigo','prod-nombre','prod-desc','prod-precio','prod-stock','prod-smin'].forEach(id => {
    const el = document.getElementById(id);
    if(el) el.value = '';
  });
  openModal('modal-prod');
}

function editProd(p) {
  document.getElementById('modal-prod-title').textContent = 'Editar Producto';
  document.getElementById('prod-id').value     = p.id_producto;
  document.getElementById('prod-codigo').value  = p.codigo;
  document.getElementById('prod-nombre').value  = p.nombre;
  document.getElementById('prod-desc').value    = p.descripcion||'';
  document.getElementById('prod-precio').value  = p.precio;
  document.getElementById('prod-stock').value   = p.stock;
  document.getElementById('prod-smin').value    = p.stock_minimo;
  document.getElementById('prod-cat').value     = p.id_categoria;
  openModal('modal-prod');
}

async function saveProd() {
  const id = document.getElementById('prod-id').value;
  const payload = {
    id_producto:  id||null,
    codigo:       document.getElementById('prod-codigo').value,
    nombre:       document.getElementById('prod-nombre').value,
    descripcion:  document.getElementById('prod-desc').value,
    precio:       document.getElementById('prod-precio').value,
    stock:        document.getElementById('prod-stock').value,
    stock_minimo: document.getElementById('prod-smin').value,
    id_categoria: document.getElementById('prod-cat').value,
    activo:       1,
  };
  const action = id ? 'editar' : 'crear';
  const res = await api(`api/productos.php?action=${action}`, {
    method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload)
  });
  toast(res.msg, res.ok ? 's' : 'e');
  if (res.ok) { closeModal('modal-prod'); loadProductos(); }
}

async function delProd(id, nombre) {
  if (!confirm(`¿Eliminar "${nombre}"?`)) return;
  const res = await api('api/productos.php?action=eliminar', {
    method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id_producto:id})
  });
  toast(res.msg, res.ok ? 's' : 'e');
  if (res.ok) loadProductos();
}

// ─────────────────────────────────────────────────────────
//  CLIENTES
// ─────────────────────────────────────────────────────────
async function loadClientes(q='') {
  const url = 'api/clientes.php' + (q ? '?q=' + encodeURIComponent(q) : '');
  const data = await api(url);
  const tbody = document.getElementById('tbl-clientes');
  if (!data.length) { tbody.innerHTML = `<tr><td colspan="5"><div class="empty"><div class="empty-icon">👥</div>Sin clientes</div></td></tr>`; return; }
  tbody.innerHTML = data.map(c => `
    <tr>
      <td>${c.nombre} ${c.apellido}</td>
      <td>${c.email||'—'}</td>
      <td>${c.telefono||'—'}</td>
      <td>${c.direccion||'—'}</td>
      ${IS_ADMIN ? `<td><button class="btn btn-warn btn-sm" onclick='editCli(${JSON.stringify(c)})'>✏️</button></td>` : ''}
    </tr>`).join('');
}

function editCli(c) {
  document.getElementById('modal-cli-title').textContent = 'Editar Cliente';
  document.getElementById('cli-id').value       = c.id_cliente;
  document.getElementById('cli-nombre').value   = c.nombre;
  document.getElementById('cli-apellido').value = c.apellido;
  document.getElementById('cli-email').value    = c.email||'';
  document.getElementById('cli-tel').value      = c.telefono||'';
  document.getElementById('cli-dir').value      = c.direccion||'';
  document.getElementById('cli-rfc').value      = c.rfc||'';
  openModal('modal-cliente');
}

async function saveCli() {
  const id = document.getElementById('cli-id').value;
  const payload = {
    id_cliente: id||null,
    nombre:     document.getElementById('cli-nombre').value,
    apellido:   document.getElementById('cli-apellido').value,
    email:      document.getElementById('cli-email').value,
    telefono:   document.getElementById('cli-tel').value,
    direccion:  document.getElementById('cli-dir').value,
    rfc:        document.getElementById('cli-rfc').value,
  };
  const action = id ? 'editar' : 'crear';
  const res = await api(`api/clientes.php?action=${action}`, {
    method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload)
  });
  toast(res.msg, res.ok ? 's' : 'e');
  if (res.ok) { closeModal('modal-cliente'); loadClientes(); }
}

// ─────────────────────────────────────────────────────────
//  VENTAS
// ─────────────────────────────────────────────────────────
async function loadVentas() {
  allVentas = await api('api/ventas.php');
  renderVentas(allVentas);
}

function renderVentas(data) {
  const tbody = document.getElementById('tbl-ventas');
  if (!data.length) { tbody.innerHTML = `<tr><td colspan="7"><div class="empty">Sin ventas registradas</div></td></tr>`; return; }
  tbody.innerHTML = data.map(v => `
    <tr>
      <td><code>${v.folio}</code></td>
      <td>${new Date(v.fecha).toLocaleString('es-MX')}</td>
      <td>${v.cliente}</td>
      <td>${v.empleado}</td>
      <td><strong>$${parseFloat(v.total).toFixed(2)}</strong></td>
      <td>${v.metodo_pago}</td>
      <td><span class="badge ${v.estado==='Completada'?'badge-green':v.estado==='Cancelada'?'badge-red':'badge-warn'}">${v.estado}</span></td>
    </tr>`).join('');
}

function filterVentas() {
  const q = document.getElementById('search-ventas').value.toLowerCase();
  renderVentas(allVentas.filter(v => v.folio.toLowerCase().includes(q) || v.cliente.toLowerCase().includes(q)));
}

// ─────────────────────────────────────────────────────────
//  VENTA — BUSCAR CLIENTES
// ─────────────────────────────────────────────────────────
async function searchClientes() {
  const q = document.getElementById('cli-search').value;
  const data = await api('api/clientes.php?q=' + encodeURIComponent(q));
  const div = document.getElementById('cli-results');
  div.innerHTML = data.map(c => `
    <div style="padding:.4rem .6rem;cursor:pointer;border-radius:6px;margin:.15rem 0;background:var(--surf2)"
      onmouseover="this.style.background='var(--border)'" onmouseout="this.style.background='var(--surf2)'"
      onclick="selectCliente(${c.id_cliente},'${c.nombre} ${c.apellido}')">
      ${c.nombre} ${c.apellido} — ${c.telefono||c.email||'Sin contacto'}
    </div>`).join('') || '<div style="padding:.4rem;color:var(--muted)">Sin resultados</div>';
}

function selectCliente(id, nombre) {
  selectedCli = id;
  document.getElementById('cli-results').innerHTML = '';
  document.getElementById('cli-selected').style.display = '';
  document.getElementById('cli-name').textContent = nombre;
}

async function saveNewCliente() {
  const payload = {
    nombre:   document.getElementById('nc-nombre').value,
    apellido: document.getElementById('nc-apellido').value,
    telefono: document.getElementById('nc-tel').value,
  };
  const res = await api('api/clientes.php?action=crear', {
    method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload)
  });
  toast(res.msg, res.ok ? 's' : 'e');
  if (res.ok) selectCliente(res.id, `${payload.nombre} ${payload.apellido}`);
}

// ─────────────────────────────────────────────────────────
//  VENTA — BUSCAR PRODUCTOS
// ─────────────────────────────────────────────────────────
async function searchProductos() {
  const q = document.getElementById('prod-search').value;
  const data = await api('api/productos.php?q=' + encodeURIComponent(q));
  const div = document.getElementById('prod-results');
  div.innerHTML = data.filter(p=>p.activo=='1'&&parseInt(p.stock)>0).map(p => `
    <div style="padding:.4rem .6rem;cursor:pointer;border-radius:6px;margin:.15rem 0;background:var(--surf2);display:flex;justify-content:space-between;align-items:center"
      onmouseover="this.style.background='var(--border)'" onmouseout="this.style.background='var(--surf2)'"
      onclick='addToCart(${JSON.stringify(p)})'>
      <span>${p.nombre} <small style="color:var(--muted)">${p.codigo}</small></span>
      <span style="font-weight:700;color:var(--accent)">$${parseFloat(p.precio).toFixed(2)} <small style="color:var(--muted)">x${p.stock}</small></span>
    </div>`).join('') || '<div style="padding:.4rem;color:var(--muted)">Sin resultados o sin stock</div>';
}

// ─────────────────────────────────────────────────────────
//  CARRITO
// ─────────────────────────────────────────────────────────
function addToCart(p) {
  const idx = cart.findIndex(i => i.id_producto == p.id_producto);
  if (idx >= 0) {
    if (cart[idx].cantidad < parseInt(p.stock)) cart[idx].cantidad++;
    else { toast('Stock máximo alcanzado','e'); return; }
  } else {
    cart.push({id_producto:p.id_producto, nombre:p.nombre, precio:parseFloat(p.precio), cantidad:1, max:parseInt(p.stock)});
  }
  renderCart();
}

function removeFromCart(idx) {
  cart.splice(idx, 1);
  renderCart();
}

function changeQty(idx, val) {
  const qty = parseInt(val);
  if (qty < 1) return;
  if (qty > cart[idx].max) { toast(`Máximo disponible: ${cart[idx].max}`,'e'); return; }
  cart[idx].cantidad = qty;
  renderCart();
}

function renderCart() {
  const div = document.getElementById('cart-items');
  if (!cart.length) { div.innerHTML = '<div class="empty"><div class="empty-icon">🛒</div>Sin productos</div>'; }
  else {
    div.innerHTML = cart.map((item,i) => `
      <div class="cart-item">
        <span class="cart-item-name">${item.nombre}</span>
        <span style="color:var(--muted);font-size:.8rem">$${item.precio.toFixed(2)}</span>
        <div class="cart-item-qty">
          <input type="number" value="${item.cantidad}" min="1" max="${item.max}"
            onchange="changeQty(${i},this.value)"
            style="width:55px;text-align:center;padding:.3rem;background:var(--bg);border:1px solid var(--border);border-radius:5px;color:var(--text)">
        </div>
        <span style="font-weight:700;min-width:60px;text-align:right">$${(item.precio*item.cantidad).toFixed(2)}</span>
        <button onclick="removeFromCart(${i})" style="background:none;border:none;color:var(--danger);cursor:pointer;font-size:1rem">✕</button>
      </div>`).join('');
  }
  const total = cart.reduce((s,i) => s + i.precio * i.cantidad, 0);
  document.getElementById('cart-total-val').textContent = '$' + total.toLocaleString('es-MX',{minimumFractionDigits:2});
}

async function confirmarVenta() {
  if (!selectedCli)  return toast('Selecciona un cliente.','e');
  if (!cart.length)  return toast('El carrito está vacío.','e');

  const payload = {
    id_cliente:  selectedCli,
    metodo_pago: document.getElementById('metodo-pago').value,
    notas:       document.getElementById('venta-notas').value,
    items:       cart.map(i => ({id_producto: i.id_producto, cantidad: i.cantidad})),
  };

  const res = await api('api/ventas.php?action=registrar', {
    method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload)
  });
  toast(res.msg, res.ok ? 's' : 'e');
  if (res.ok) {
    cart = [];
    selectedCli = null;
    renderCart();
    document.getElementById('cli-selected').style.display = 'none';
    document.getElementById('cli-search').value = '';
    document.getElementById('venta-notas').value = '';
  }
}

// ─────────────────────────────────────────────────────────
//  USUARIOS (admin)
// ─────────────────────────────────────────────────────────
async function loadUsuarios() {
  const data = await api('api/usuarios.php');
  const tbody = document.getElementById('tbl-usuarios');
  if (!data.length) { tbody.innerHTML = `<tr><td colspan="6"><div class="empty">Sin usuarios</div></td></tr>`; return; }
  tbody.innerHTML = data.map(u => `
    <tr>
      <td>${u.nombre} ${u.apellido}</td>
      <td><code>${u.username}</code></td>
      <td>${u.email}</td>
      <td><span class="badge ${u.id_rol==1?'badge-warn':'badge-blue'}">${u.rol}</span></td>
      <td><span class="badge ${u.activo=='1'?'badge-green':'badge-red'}">${u.activo=='1'?'Activo':'Inactivo'}</span></td>
      <td>
        <button class="btn btn-warn btn-sm" onclick='editUser(${JSON.stringify(u)})'>✏️</button>
        ${u.id_usuario != MY_ID ? `<button class="btn btn-danger btn-sm" onclick="delUser(${u.id_usuario})">🗑️</button>` : ''}
      </td>
    </tr>`).join('');
}

function editUser(u) {
  document.getElementById('modal-user-title').textContent = 'Editar Usuario';
  document.getElementById('user-id').value       = u.id_usuario;
  document.getElementById('user-nombre').value   = u.nombre;
  document.getElementById('user-apellido').value = u.apellido;
  document.getElementById('user-email').value    = u.email;
  document.getElementById('user-username').value = u.username;
  document.getElementById('user-pass').value     = '';
  document.getElementById('user-rol').value      = u.id_rol;
  document.getElementById('user-activo').value   = u.activo;
  openModal('modal-user');
}

async function saveUser() {
  const id = document.getElementById('user-id').value;
  const payload = {
    id_usuario: id||null,
    nombre:     document.getElementById('user-nombre').value,
    apellido:   document.getElementById('user-apellido').value,
    email:      document.getElementById('user-email').value,
    username:   document.getElementById('user-username').value,
    password:   document.getElementById('user-pass').value,
    id_rol:     document.getElementById('user-rol').value,
    activo:     document.getElementById('user-activo').value,
  };

  if (!id) {
    // Nuevo usuario: usar auth
    payload.confirm = payload.password;
    payload.action  = 'register';
    const fd = new FormData();
    Object.entries(payload).forEach(([k,v]) => fd.append(k,v));
    const res = await api('auth/auth.php', {method:'POST', body:fd});
    toast(res.msg, res.ok ? 's' : 'e');
    if (res.ok) { closeModal('modal-user'); loadUsuarios(); }
  } else {
    const res = await api('api/usuarios.php?action=editar', {
      method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload)
    });
    toast(res.msg, res.ok ? 's' : 'e');
    if (res.ok) { closeModal('modal-user'); loadUsuarios(); }
  }
}

async function delUser(id) {
  if (!confirm('¿Desactivar este usuario?')) return;
  const res = await api('api/usuarios.php?action=eliminar', {
    method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id_usuario:id})
  });
  toast(res.msg, res.ok ? 's' : 'e');
  if (res.ok) loadUsuarios();
}

// ─────────────────────────────────────────────────────────
//  MI PERFIL
// ─────────────────────────────────────────────────────────
async function loadPerfil() {
  const data = await api('api/usuarios.php?id=' + MY_ID);
  // Populate (reutilizamos el endpoint de lista y filtramos)
  const all = await api('api/usuarios.php');
  const me  = all?.find?.(u => u.id_usuario == MY_ID);
  if (!me) return;
  document.getElementById('p-nombre').value   = me.nombre;
  document.getElementById('p-apellido').value = me.apellido;
  document.getElementById('p-email').value    = me.email;
}

async function savePerfil() {
  const payload = {
    id_usuario: MY_ID,
    nombre:     document.getElementById('p-nombre').value,
    apellido:   document.getElementById('p-apellido').value,
    email:      document.getElementById('p-email').value,
    password:   document.getElementById('p-pass').value,
  };
  const res = await api('api/usuarios.php?action=editar', {
    method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload)
  });
  toast(res.msg, res.ok ? 's' : 'e');
}

// ─────────────────────────────────────────────────────────
//  INIT
// ─────────────────────────────────────────────────────────
loadDashboard();
</script>
</body>
</html>
