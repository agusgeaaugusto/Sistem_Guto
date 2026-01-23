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
  <style>
    :root{
      --bg:#0c0d12;
      --card:#13141b;
      --muted:#a5adcb;
      --text:#e6e9f5;
      --primary:#6ee7ff;
      --primary-2:#7c5cff;
      --border:rgba(255,255,255,.08);
      --shadow:0 12px 34px rgba(255, 255, 255, 1);
      --radius:18px;
      --top:64px; --side:260px;
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0;
      font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      background:
        radial-gradient(1200px 600px at 110% -10%, rgba(255, 255, 255, 1), transparent 50%),
        radial-gradient(1000px 500px at -10% 110%, rgba(255, 255, 255, 0.95), transparent 50%),
        var(--bg);
      color:var(--text);
    }
    /* Topbar */
    .topbar{
      position:fixed; inset:0 0 auto 0; height:var(--top);
      display:flex; align-items:center; justify-content:space-between; gap:12px;
      padding:0 16px; background:rgba(19,20,27,.75); backdrop-filter: blur(10px);
      border-bottom:1px solid var(--border); z-index:20;
    }
    .brand{ display:flex; align-items:center; gap:12px }
    /* LOGO: contenedor fijo + imagen contenida */
    .logo{
      width:36px; height:36px;
      border-radius:10px;
      box-shadow: var(--shadow);
      overflow:hidden;               /* que no se salga */
      background:none;               /* sin gradiente detrás */
      display:flex; align-items:center; justify-content:center;
    }
    .logo img{
      width:100%; height:100%;
      object-fit:contain;            /* o 'cover' si preferís llenar */
      display:block;
    }

    .title{ font-weight:800; letter-spacing:.3px; }
    .user{ color:var(--muted); font-size:14px }
    .iconbtn{
      display:inline-flex; align-items:center; justify-content:center;
      width:38px; height:38px; border-radius:10px; border:1px solid var(--border);
      background:#0b0c11; color:var(--text); cursor:pointer;
    }
    /* Layout */
    .layout{ display:flex; height:100%; padding-top:var(--top) }
    .sidebar{
      width:var(--side); flex:0 0 var(--side);
      border-right:1px solid var(--border);
      background:rgba(19,20,27,.96);
      backdrop-filter: blur(8px);
      position:fixed; top:var(--top); bottom:0; left:0; z-index:30;
      transform: translateX(-100%);            /* oculto por defecto */
      transition: transform .25s ease;
          overflow-y:auto;
      overscroll-behavior:contain;
    }
    .sidebar.open{ transform: translateX(0) }   /* visible cuando tiene .open */

    .menu{ padding:14px; padding-bottom:24px }
    .section-title{ color:var(--muted); font-size:12px; letter-spacing:.4px; text-transform:uppercase; margin:12px 8px }
    .navbtn{
      width:100%; text-align:left; padding:10px 12px; margin:6px 0;
      border-radius:12px; border:1px solid var(--border); background:#0b0c11; color:var(--text);
      cursor:pointer; display:flex; gap:10px; align-items:center; transition:.2s background;
    }
    .navbtn.active{ outline:2px solid rgba(110,231,255,.2); background:#101117 }
    .content{
      margin-left:var(--side); width:calc(100% - var(--side)); height:calc(100vh - var(--top));
      padding:16px;
      overflow:hidden;
      transition: margin-left .25s ease, width .25s ease;
    }
    .content.full{ margin-left:0; width:100% }
    .card{
      background: var(--card); border:1px solid var(--border); border-radius: var(--radius);
      box-shadow: var(--shadow); overflow:hidden; height:100%;
      display:flex; flex-direction:column;
    }
    .tabbar{ display:flex; gap:8px; padding:12px; border-bottom:1px solid var(--border); background:#0b0c11; flex-wrap:wrap }
    .tab{
      display:flex; align-items:center; gap:8px; padding:8px 10px; border-radius:10px;
      background:#13141b; border:1px solid var(--border); cursor:pointer; user-select:none;
    }
    .tab.active{ outline:2px solid rgba(124,92,255,.25) }
    .tab .x{ width:20px; height:20px; display:inline-grid; place-items:center; border-radius:8px; background:#0b0c11; border:1px solid var(--border) }
    .frame{
      border:0; width:100%; height:100%; background:#0b0c11;
    }
    /* Frames cache (1 iframe por pestaña, sin recargar al cambiar) */
    .frames{ flex:1; position:relative; }
    .frames .frame{ position:absolute; inset:0; width:100%; height:100%; display:none; }
    .frames .frame.active{ display:block; }

    /* Backdrop para cerrar al click fuera */
    .backdrop{
      position:fixed; inset:0; background:rgba(0,0,0,.45);
      display:none; z-index:25;
    }
    .backdrop.show{ display:block }

    /* Mobile: solo ajustamos contenido; el toggle de sidebar funciona igual */
    @media (max-width: 900px){
      .content{ margin-left:0; width:100% }
    }
  </style>
</head>
<body>
  <header class="topbar">
    <div class="brand">
      <button id="btnMenu" class="iconbtn" title="Menú" aria-label="Abrir menú">☰</button>
      <div class="logo" aria-hidden="true"><img src="logo.png" alt="Logo"></div>
      <div>
        <div class="title">Panel Principal</div>
        <div class="user">Hola, <?php echo $usuario; ?></div>
      </div>
    </div>
    <div>
  <a class="iconbtn" href="logout.php" title="Salir" aria-label="Cerrar sesión">Salir</a>
</div>

  </header>

  <div class="layout">
    <!-- Arranca visible con .open -->
    <aside id="sidebar" class="sidebar open">
      <div class="menu">
        <button class="navbtn" data-url="inicio.php">🏠 Inicio</button>

        <button class="navbtn" data-url="cargos/cargo.php">👤 Cargos</button>
        <button class="navbtn" data-url="facturas/factura_listado.php">👤 Listado de Factura</button>
        <button class="navbtn" data-url="categoria/categoria.php">🏷️ Categoría</button>
        <button class="navbtn" data-url="ventas/ventas.php">🧾 Ventas</button>
        <button class="navbtn" data-url="comprovante/comprovante.php">🧾 Comprobante</button>
        <button class="navbtn" data-url="persona/persona.php">🧍 Persona</button>
        <button class="navbtn" data-url="proveedor/proveedor.php">🚚 Proveedor</button>
        <button class="navbtn" data-url="usuario/usuario.php">👥 Usuario</button>
        <button class="navbtn" data-url="rol/rol.php">⚙️ Rol</button>
        <button class="navbtn" data-url="gestion/gestion.php">📑 Gestión de Compra</button>
        <button class="navbtn" data-url="compra/compra.php">🛒 Compra</button>
        <button class="navbtn" data-url="producto/producto.php">📦 Producto</button>
        <button class="navbtn" data-url="producto_det/producto.php">🧪 Producto tester</button>
        <button class="navbtn" data-url="producto_det/producto_det.php">📦 Producto Detalle</button>
        <button class="navbtn" data-url="moneda/moneda.php">💵 Moneda</button>
        <button class="navbtn" data-url="compra_detalle/compra_detalle.php">📦 Compra Detalle</button>
        <button class="navbtn" data-url="portaforlio/admin.php">🗂️ Administrador</button>
        <button class="navbtn" data-url="portaforlio/clientes.php">👤 Cliente</button>
        <button class="navbtn" data-url="acerca.php">📘 Acerca de</button>
      </div>
    </aside>

    <!-- Backdrop para cerrar sidebar al click fuera -->
    <div id="backdrop" class="backdrop" aria-hidden="true"></div>

    <main id="content" class="content">
      <div class="card">
        <div id="tabs" class="tabbar"></div>
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
    const frames = document.getElementById('frames');
    const navButtons = Array.from(document.querySelectorAll('.navbtn'));
    const backdrop = document.getElementById('backdrop');

    // Estado
    let openTabs = [{ url: 'inicio.php', title: '🏠 Inicio' }]; // [{url,title}]
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

    function addTab(url, title){
      // Si me voy de la actual con datos sin guardar, aviso
      if(url !== activeUrl){
        const currentTitle = openTabs.find(t => t.url === activeUrl)?.title || 'esta pestaña';
        if(!confirmIfDirty(activeUrl, currentTitle)) return;
      }

      if(!openTabs.find(t => t.url === url)){
        openTabs.push({url, title});
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
        alert('La pestaña Inicio no se puede cerrar 😉');
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
        const next = openTabs[openTabs.length - 1] || {url:'inicio.php', title:'🏠 Inicio'};
        activeUrl = next.url;
        activateFrame(activeUrl);
        setActiveNav(activeUrl);
      }
      renderTabs();
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
        addTab(url, title);
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

</body>
</html>
