<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}
$usuario = htmlspecialchars($_SESSION['usuario'], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Panel Principal</title>
  
<link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css">

<style>
  /* =========================================================
   CARVALLO APP SHELL — LIGHT POP
   Claro · moderno · llamativo (amarillo/rojo como acento)
   Topbar · Sidebar · Tabs · Frames · Botón Salir
========================================================= */

:root{
  /* Fondo y superficies */
  --bg:#fbf7ee;
  --bg2:#fffdf6;
  --card:#ffffff;
  --card2:#fff7e6;

  /* Texto */
  --text:#0b0f19;
  --muted:#44516a;

  /* Acentos */
  --yellow:#facc15;
  --yellow2:#f59e0b;
  --red:#e11d48;
  --red2:#b91c1c;

  /* Bordes y sombras */
  --border:rgba(11,15,25,.12);
  --border2:rgba(11,15,25,.18);
  --shadow:0 14px 30px rgba(11,15,25,.12);

  --radius:18px;
  --top:64px;
  --side:260px;
}

*{ box-sizing:border-box }
html,body{ height:100% }

body{
  margin:0;
  font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
  color:var(--text);
  background:
    radial-gradient(1200px 620px at 10% 0%, rgba(250,204,21,.18), transparent 62%),
    radial-gradient(900px 520px at 100% 10%, rgba(225,29,72,.10), transparent 60%),
    linear-gradient(180deg, var(--bg2) 0%, var(--bg) 100%);
}

/* =========================================================
   TOPBAR
========================================================= */
.topbar{
  position:fixed;
  inset:0 0 auto 0;
  height:var(--top);
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  padding:0 16px;

  background:rgba(255,255,255,.78);
  backdrop-filter: blur(12px);
  border-bottom:1px solid var(--border);
  z-index:20;
}

.brand{ display:flex; align-items:center; gap:12px }

.logo{
  width:38px;
  height:38px;
  border-radius:12px;
  overflow:hidden;
  box-shadow: 0 10px 22px rgba(11,15,25,.14);
  background:#fff;
  display:flex;
  align-items:center;
  justify-content:center;
}
.logo img{
  width:100%;
  height:100%;
  object-fit:contain;
  display:block;
}

.title{
  font-weight:950;
  letter-spacing:.3px;
  font-size:16px;
  background: linear-gradient(90deg, var(--yellow2), var(--red));
  -webkit-background-clip:text;
  background-clip:text;
  color:transparent;
}

.user{
  color:var(--muted);
  font-size:13px;
  font-weight:800;
}

/* Botones icono topbar */
.iconbtn{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  width:40px;
  height:40px;
  border-radius:12px;
  border:1px solid var(--border2);
  background:linear-gradient(180deg,#fff 0%, #f7f7f7 100%);
  color:var(--text);
  cursor:pointer;
  box-shadow: 0 10px 18px rgba(11,15,25,.10);
  transition: transform .12s ease, box-shadow .12s ease;
}
.iconbtn:hover{
  transform: translateY(-1px);
  box-shadow: 0 14px 24px rgba(11,15,25,.12);
}

/* Botón salir (rojo elegante) */
.iconbtn.logout{
  border-color:rgba(225,29,72,.35);
  background:linear-gradient(180deg, rgba(225,29,72,.10) 0%, rgba(185,28,28,.10) 100%);
  color:var(--red);
}
.iconbtn.logout:hover{
  background:linear-gradient(180deg, var(--red) 0%, var(--red2) 100%);
  color:#fff;
  border-color:rgba(185,28,28,.55);
}

/* =========================================================
   LAYOUT
========================================================= */
.layout{
  display:flex;
  height:100%;
  padding-top:var(--top);
}

/* =========================================================
   SIDEBAR
========================================================= */
.sidebar{
  width:var(--side);
  flex:0 0 var(--side);
  position:fixed;
  top:var(--top);
  bottom:0;
  left:0;
  z-index:30;

  background:rgba(255,255,255,.86);
  backdrop-filter: blur(10px);
  border-right:1px solid var(--border);

  transform: translateX(-100%);
  transition: transform .25s ease;
  overflow-y:auto;
  overscroll-behavior:contain;
}
.sidebar.open{ transform: translateX(0) }

.menu{ padding:14px 14px 24px }

.section-title{
  color:rgba(68,81,106,.9);
  font-size:11px;
  letter-spacing:.5px;
  text-transform:uppercase;
  margin:12px 8px 8px;
  font-weight:900;
}

/* Botones menú */
.navbtn{
  width:100%;
  text-align:left;
  padding:15px 20px;
  margin:6px 0;
  border-radius:14px;
  border:1px solid var(--border2);
  background:linear-gradient(180deg,#fff 0%, #fafafa 100%);
  color:var(--text);
  cursor:pointer;
  display:flex;
  align-items:center;
  gap:10px;
  font-weight:900;
  box-shadow: 0 10px 18px rgba(11,15,25,.08);
  transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease;
}
.navbtn:hover{
  transform: translateY(-1px);
  box-shadow: 0 14px 24px rgba(11,15,25,.10);
  border-color:rgba(250,204,21,.55);
}

/* Activo: borde amarillo + glow suave */
.navbtn.active{
  border-color:rgba(250,204,21,.85);
  box-shadow:
    0 14px 26px rgba(11,15,25,.12),
    0 0 0 4px rgba(250,204,21,.18);
  background: linear-gradient(180deg, rgba(250,204,21,.18) 0%, #fff 55%);
}

/* =========================================================
   CONTENT
========================================================= */
.content{
  margin-left:var(--side);
  width:calc(100% - var(--side));
  height:calc(100vh - var(--top));
  padding:16px;
  overflow:hidden;
  transition: margin-left .25s ease, width .25s ease;
}
.content.full{ margin-left:0; width:100% }

/* =========================================================
   CARD
========================================================= */
.card{
  background: linear-gradient(180deg, var(--card) 0%, var(--card2) 100%);
  border:1px solid var(--border);
  border-top:4px solid rgba(250,204,21,.95);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  overflow:hidden;
  height:100%;
  display:flex;
  flex-direction:column;
}

/* =========================================================
   TABBAR
========================================================= */
.tabbar{
  display:flex;
  gap:8px;
  padding:-3px;
  border-bottom:1px solid var(--border);
  background: linear-gradient(180deg, rgba(255,255,255,.92) 0%, rgba(255,247,230,.92) 100%);
  flex-wrap:wrap;
}

/* Pestaña */
.tab{
  display:flex;
  align-items:center;
  gap:8px;
  padding:3px 10px;
  border-radius:14px;
  border:1px solid var(--border2);
  cursor:pointer;
  user-select:none;
  font-weight:900;
  color:var(--text);
  background:linear-gradient(180deg,#fff 0%, #f7f7f7 100%);
  box-shadow: 0 10px 18px rgba(11,15,25,.08);
  transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease;
}
.tab:hover{
  transform: translateY(-1px);
  box-shadow: 0 14px 22px rgba(11,15,25,.10);
  border-color:rgba(250,204,21,.55);
}

/* Activa: glow amarillo */
.tab.active{
  border-color:rgba(250,204,21,.95);
  box-shadow:
    0 14px 24px rgba(11,15,25,.12),
    0 0 0 4px rgba(250,204,21,.18);
  background: linear-gradient(180deg, rgba(250,204,21,.16) 0%, #fff 60%);
}

/* Botón X en pestaña */
.tab .x{
  width:22px;
  height:22px;
  display:inline-grid;
  place-items:center;
  border-radius:10px;
  background:rgba(225,29,72,.10);
  border:1px solid rgba(225,29,72,.30);
  color:var(--red);
  transition: background .12s ease, color .12s ease, border-color .12s ease;
}
.tab .x:hover{
  background:linear-gradient(180deg,var(--red) 0%, var(--red2) 100%);
  color:#fff;
  border-color:rgba(185,28,28,.55);
}

/* =========================================================
   FRAMES
========================================================= */
.frames{
  flex:1;
  position:relative;
  background: transparent;
}

.frame{
  position:absolute;
  inset:0;
  width:100%;
  height:100%;
  border:0;
  background:#fff; /* dentro del iframe */
  display:none;
}
.frame.active{ display:block; }

/* =========================================================
   BACKDROP
========================================================= */
.backdrop{
  position:fixed;
  inset:0;
  background:rgba(11,15,25,.25);
  display:none;
  z-index:25;
}
.backdrop.show{ display:block }

/* =========================================================
   ICONOS (Flaticon)
========================================================= */
.fi{ font-size:16px; line-height:1; vertical-align:middle; color:var(--text); }
.navbtn .fi{ color:var(--yellow2); opacity:.95; }
.iconbtn .fi{ font-size:18px; }
.tab .fi{ color:rgba(11,15,25,.85); }
.tab .x .fi{ color:inherit; }

/* =========================================================
   RESPONSIVE
========================================================= */
@media (max-width:900px){
  .content{ margin-left:0; width:100% }
}





/* ================================
   AJUSTES FINOS EXTRA (LIMPIEZA UI)
================================ */

/* Reducir línea blanca superior (topbar más compacta) */
:root{
  --top:56px; /* antes 64px */
}

.topbar{
  padding:0 14px;
  border-bottom:1px solid rgba(11,15,25,.08); /* más fina */
}

/* Ajustar contenido al nuevo alto */
.layout{ padding-top:var(--top); }
.sidebar{ top:var(--top); }
.content{ height:calc(100vh - var(--top)); }

/* Quitar pelotita roja: dejar SOLO la X */
.tab .x{
  background:transparent !important;
  border:none !important;
  box-shadow:none !important;
  color:var(--red);
}

.tab .x:hover{
  background:transparent;
  color:var(--red2);
}

</style>
</head>
<body>
  <header class="topbar">
    <div class="brand">
      <button id="btnMenu" class="iconbtn" title="Menú" aria-label="Abrir menú"><i class="fi fi-rr-menu-burger" aria-hidden="true"></i></button>
      <div class="logo" aria-hidden="true"><img src="logo.png" alt="Logo"></div>
      <div>
        <div class="title">Panel Principal</div>
        <div class="user">Hola, <?php echo $usuario; ?></div>
      </div>
    </div>
    <div class="actions">
  <a class="btn danger" id="btnLogout" href="logout.php" title="Cerrar sesión" aria-label="Cerrar sesión">
    <i class="fi fi-rr-sign-out-alt" aria-hidden="true"></i>
   
  </a>
</div>

  </header>

  <div class="layout">
    <!-- Arranca visible con .open -->
    <aside id="sidebar" class="sidebar open">
      <div class="menu">
        <button class="navbtn" data-url="inicio.php">
  <i class="fi fi-rr-home"></i>
  Inicio
</button>
<button class="navbtn" data-url="ventas/ventas.php">
  <i class="fi fi-rr-receipt"></i>
  Ventas
</button>
<button class="navbtn" data-url="compra/compra.php">
  <i class="fi fi-rr-shopping-cart"></i>
  Compra
</button>
<button class="navbtn" data-url="producto_det/producto_det.php">
  <i class="fi fi-rr-box-open"></i>
  Producto Detalle
</button>
<button class="navbtn" data-url="categoria/categoria.php">
  <i class="fi fi-rr-tags"></i>
  Categoría
</button>
<button class="navbtn" data-url="persona/persona.php">
  <i class="fi fi-rr-user"></i>
  Persona
</button>
<button class="navbtn" data-url="proveedor/proveedor.php">
  <i class="fi fi-rr-truck-moving"></i>
  Proveedor
</button>
<button class="navbtn" data-url="cargos/cargo.php">
  <i class="fi fi-rr-id-badge"></i>
  Cargos
</button>
<button class="navbtn" data-url="rol/rol.php">
  <i class="fi fi-rr-settings"></i>
  Rol
</button>




<button class="navbtn" data-url="producto/producto.php">
  <i class="fi fi-rr-box"></i>
  Producto
</button>



<button class="navbtn" data-url="moneda/moneda.php">
  <i class="fi fi-rr-money-bill-wave"></i>
  Moneda
</button>
<button class="navbtn" data-url="usuario/usuario.php">
  <i class="fi fi-rr-users"></i>
  Usuario
</button>


      
      </div>
    </aside>

    <!-- Backdrop para cerrar sidebar al click fuera -->
    <div id="backdrop" class="backdrop" aria-hidden="true"></div>

    <main id="content" class="content">
      <div class="card">
        <div class="tabbar-head">
  <div id="tabs" class="tabbar" aria-label="Pestañas abiertas"></div>
 
</div>
        <div id="frames" class="frames">
          <iframe class="frame active" data-url="inicio.php" src="inicio.php" title="Contenido"></iframe>
        </div>
      </div>
    </main>
  </div>

  <script>
    // Panel principal (tabs sin recarga + sidebar scroll + protección anti-perdida)
    const btnMenu = document.getElementById('btnMenu');
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');
    const tabs = document.getElementById('tabs');
    const btnCloseTab = document.getElementById('btnCloseTab');
    const frames = document.getElementById('frames');
    const navButtons = Array.from(document.querySelectorAll('.navbtn'));
    const backdrop = document.getElementById('backdrop');

    // Estado
    let openTabs = [{ url: 'inicio.php', title: 'Inicio', icon: 'fi fi-rr-home' }]; // [{url,title,icon}]
    let activeUrl = 'inicio.php';

    // Dirty tracking por pestaña (si hay texto escrito sin "guardar")
    const dirtyByUrl = new Map(); // url -> boolean

    function hasDirty(){
      for (const v of dirtyByUrl.values()) if (v) return true;
      return false;
    }
    function setDirty(url, val){ dirtyByUrl.set(url, !!val); }
    function isDirty(url){ return !!dirtyByUrl.get(url); }

    function setActiveNav(url){
      navButtons.forEach(b => b.classList.toggle('active', b.dataset.url === url));
    }

    function getFrameByUrl(url){
      return frames ? frames.querySelector('.frame[data-url="' + url + '"]') : null;
    }

    function wireDirtyListeners(iframe, url){
      // Ojo: solo funciona si es mismo origen (tus .php lo son)
      const tryWire = () => {
        try{
          const doc = iframe.contentDocument || iframe.contentWindow?.document;
          if(!doc) return;

          // Evitar duplicados
          if(doc.__dirtyWired) return;
          doc.__dirtyWired = true;

          const mark = () => setDirty(url, true);

          doc.addEventListener('input', mark, true);
          doc.addEventListener('change', mark, true);

          // Si se envía un form, asumimos "guardado" (podés ajustar si tu backend valida)
          doc.addEventListener('submit', () => {
            setTimeout(() => setDirty(url, false), 500);
          }, true);

        } catch(e){
          // Si no se puede acceder (cross-origin), simplemente no podemos trackear dirty en ese iframe
        }
      };

      iframe.addEventListener('load', tryWire);
      // también intento inmediato (por si ya cargó)
      tryWire();
    }

    function ensureFrame(url){
      if(!frames) return null;
      let fr = getFrameByUrl(url);
      if(fr) return fr;

      fr = document.createElement('iframe');
      fr.className = 'frame';
      fr.setAttribute('data-url', url);
      fr.src = url;
      fr.title = 'Contenido';
      frames.appendChild(fr);

      // inicializa dirty en falso
      if(!dirtyByUrl.has(url)) setDirty(url, false);
      wireDirtyListeners(fr, url);

      return fr;
    }

    function activateFrame(url){
      if(!frames) return;
      // asegurar existencia
      ensureFrame(url);

      frames.querySelectorAll('.frame').forEach(f => {
        f.classList.toggle('active', f.getAttribute('data-url') === url);
      });
    }

    function confirmIfDirty(url, actionLabel){
      if(!isDirty(url)) return true;
      return confirm('Tenés datos sin guardar en "' + actionLabel + '".\n\n¿Querés continuar igual?');
    }

    function renderTabs(){
      if(!tabs) return;

      tabs.innerHTML = openTabs.map(t => {
        const isActive = t.url === activeUrl;
        const isD = isDirty(t.url);
        return (
          '<div class="tab' + (isActive ? ' active' : '') + '" data-url="' + t.url + '">' +
            '<i class="' + (t.icon || 'fi fi-rr-apps') + '" aria-hidden="true"></i> ' +
            '<span>' + t.title + (isD ? ' •' : '') + '</span>' +
            '<button class="x" data-close="' + t.url + '" title="Cerrar">✕</button>' +
          '</div>'
        );
      }).join('');

      // Click en tab (NO recarga: solo muestra iframe ya existente)
      tabs.querySelectorAll('.tab').forEach(el => {
        el.addEventListener('click', () => {
          const url = el.getAttribute('data-url');
          if(url === activeUrl) return;

          // Si la pestaña actual tiene cambios, avisar antes de salir
          if(!confirmIfDirty(activeUrl, openTabs.find(t => t.url === activeUrl)?.title || 'esta pestaña')) return;

          activeUrl = url;
          activateFrame(url);
          setActiveNav(url);
          renderTabs();
        });
      });

      // Cerrar pestaña
      tabs.querySelectorAll('.x').forEach(btn => {
        btn.addEventListener('click', (e) => {
          e.stopPropagation();
          const url = btn.getAttribute('data-close');
          closeTab(url);
        });
      });
    }

    function addTab(url, title, icon){
      // Si me voy de la actual con datos sin guardar, aviso
      if(url !== activeUrl){
        const currentTitle = openTabs.find(t => t.url === activeUrl)?.title || 'esta pestaña';
        if(!confirmIfDirty(activeUrl, currentTitle)) return;
      }

      if(!openTabs.find(t => t.url === url)){
        openTabs.push({url, title, icon: icon || 'fi fi-rr-apps'});
      }

      activeUrl = url;
      activateFrame(url);
      setActiveNav(url);
      renderTabs();

      // En móvil, cerrar sidebar al navegar
      closeSidebar();
    }

    function closeTab(url){
      if(url === 'inicio.php') {
        alert('La pestaña Inicio no se puede cerrar ');
        return;
      }

      const i = openTabs.findIndex(t => t.url === url);
      if(i < 0) return;

      const title = openTabs[i]?.title || 'esta pestaña';
      if(!confirmIfDirty(url, title)) return;

      openTabs.splice(i, 1);

      const fr = getFrameByUrl(url);
      if(fr) fr.remove();
      dirtyByUrl.delete(url);

      // Si cerré la activa, activar la última
      if(activeUrl === url){
        const next = openTabs[openTabs.length - 1] || {url:'inicio.php', title:'Inicio', icon:'fi fi-rr-home'};
        activeUrl = next.url;
        activateFrame(activeUrl);
        setActiveNav(activeUrl);
      }
      renderTabs();
    }

    
    // Botón ✕ (cierra pestaña actual)
    if(btnCloseTab){
      btnCloseTab.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        closeTab(activeUrl);
      });
    }

function openSidebar(){
      sidebar.classList.add('open');
      backdrop.classList.add('show');
      content.classList.remove('full');
    }
    function closeSidebar(){
      sidebar.classList.remove('open');
      backdrop.classList.remove('show');
      content.classList.add('full');
    }

    // NAV lateral
    navButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const url = btn.dataset.url;
        const title = btn.textContent.trim();
        const iconEl = btn.querySelector('i');
        const icon = iconEl ? iconEl.className : 'fi fi-rr-apps';
        addTab(url, title, icon);
      });
    });
    

    // Toggle sidebar
    btnMenu.addEventListener('click', () => {
      const isOpen = sidebar.classList.toggle('open');
      backdrop.classList.toggle('show', isOpen);
      if(isOpen){ content.classList.remove('full'); } else { content.classList.add('full'); }
    });

    // Cerrar sidebar al click fuera
    backdrop.addEventListener('click', closeSidebar);

    // Inicial
    ensureFrame('inicio.php');      // ya existe en HTML, pero lo dejo por seguridad
    activateFrame('inicio.php');
    setActiveNav('inicio.php');
    renderTabs();

    // ---------- Protección anti-perdida (F5 / Ctrl+R / cerrar pestaña navegador) ----------
    window.addEventListener('beforeunload', (e) => {
      if(!hasDirty()) return;
      e.preventDefault();
      e.returnValue = '';
    });

    window.addEventListener('keydown', (e) => {
      const isReload = (e.key === 'F5') || ((e.ctrlKey || e.metaKey) && (e.key.toLowerCase() === 'r'));
      if(!isReload) return;
      if(!hasDirty()) return;

      e.preventDefault();
      if(confirm('Hay datos sin guardar.\n\n¿Querés recargar igual?')){
        location.reload();
      }
    }, true);
  </script>
  <script>
document.addEventListener('DOMContentLoaded', () => {
  const killClose = () => {
    document.querySelectorAll('button, .tab-close, .close').forEach(btn => {
      if (
        btn.textContent.trim() === '×' ||
        btn.textContent.trim() === '✕'
      ) {
        btn.remove();
      }
    });
  };

  killClose();
  setTimeout(killClose, 300); // por si se crea después
});
</script>


</body>
</html>
