<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Inicio</title>
  <style>
    :root{
      --bg:#0c0d12; --card:#13141b; --muted:#a5adcb; --text:#e6e9f5;
      --primary:#6ee7ff; --primary-2:#7c5cff; --border:rgba(255,255,255,.08);
      --shadow:0 12px 34px rgba(0,0,0,.4); --radius:22px;
    }
    *{box-sizing:border-box} html,body{height:100%}
    body{
      margin:0; font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;
      background:
        radial-gradient(1200px 600px at 110% -10%, rgba(124,92,255,.12), transparent 50%),
        radial-gradient(1000px 500px at -10% 110%, rgba(110,231,255,.12), transparent 50%),
        var(--bg);
      color:var(--text);
      display:flex; align-items:center; justify-content:center; padding:24px;
    }
    .wrap{ width:min(1100px, 100%); }
    .hero{
      display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:18px;
    }
    .title{ font-weight:900; letter-spacing:.3px; font-size: clamp(22px, 3vw, 32px) }
    .muted{ color:var(--muted); font-size:14px }
    .grid{
      display:grid; gap:16px;
      grid-template-columns: repeat( auto-fit, minmax(260px, 1fr) );
    }
    .card{
      background: var(--card); border:1px solid var(--border); border-radius: var(--radius);
      box-shadow: var(--shadow); padding:22px; display:flex; flex-direction:column; gap:14px;
    }
    .bigbtn{
      display:flex; align-items:center; justify-content:center; gap:12px;
      padding:22px 18px; border-radius:18px; border:1px solid var(--border);
      background: linear-gradient(90deg, var(--primary-2), var(--primary));
      color:#0b0c11; font-weight:900; font-size: clamp(18px, 2.4vw, 24px);
      cursor:pointer; text-decoration:none; box-shadow: 0 10px 24px rgba(124,92,255,.28);
      transition: transform .06s ease, box-shadow .2s ease, filter .2s ease;
      text-align:center;
    }
    .bigbtn:active{ transform: translateY(1px) }
    .bigbtn.secondary{
      background:#0b0c11; color:var(--text); border-color:var(--border);
      box-shadow:none;
    }
    .kpis{ display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:12px }
    .kpi{
      background:#0b0c11; border:1px solid var(--border); border-radius:16px; padding:14px;
    }
    .kpi .label{ color:var(--muted); font-size:12px; text-transform:uppercase; letter-spacing:.3px }
    .kpi .value{ font-weight:900; font-size:22px; margin-top:6px }
    .row{ display:grid; grid-template-columns: 1.2fr .8fr; gap:16px }
    @media (max-width: 900px){ .row{ grid-template-columns: 1fr } }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="hero">
      <div>
        <div class="title">Bienvenido</div>
        <div class="muted">Accesos rápidos fijados en la pantalla principal</div>
      </div>
    </div>

    <div class="row">
      <div class="card">
        <div class="muted">Acciones rápidas</div>
        <div class="grid">
          <!-- IMPORTANTE: ajusta la ruta de ventas si tu carpeta es distinta -->
          <a class="bigbtn" href="ventas/ventas.php" onclick="parent.addTab && parent.addTab('ventas/ventas.php','Ventas'); return false;">🧾 Ventas</a>
          <a class="bigbtn secondary" href="compra/compra.php" onclick="parent.addTab && parent.addTab('compra/compra.php','Compra'); return false;">🛒 Compra</a>
        </div>
      </div>

      <div class="card">
        <div class="muted">Atajos</div>
        <div class="grid">
          <a class="bigbtn secondary" href="producto/producto.php" onclick="parent.addTab && parent.addTab('producto/producto.php','Producto'); return false;">📦 Producto</a>
          <a class="bigbtn secondary" href="cargos/cargo.php" onclick="parent.addTab && parent.addTab('cargos/cargo.php','Cargos'); return false;">👤 Cargos</a>
        </div>
      </div>
    </div>

    <div style="height:10px"></div>

   
  </div>
</body>
</html>
