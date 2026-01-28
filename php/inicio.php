<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/uicons-regular-rounded/css/uicons-regular-rounded.css">
  <title>Inicio</title>
  <style>
/* =========================================================
   INICIO — TEMA CLARO (Amarillo / Rojo / Blanco)
========================================================= */

:root{
  --bg:#f9fafb;              /* fondo blanco suave */
  --card:#ffffff;            /* tarjetas */
  --card2:#fff7e6;           /* leve amarillo */
  --text:#0f172a;            /* texto principal */
  --muted:#475569;

  --yellow:#fde047;
  --yellow2:#facc15;

  --red:#fb7185;
  --red2:#e11d48;

  --border:rgba(15,23,42,.12);
  --shadow:0 16px 36px rgba(15,23,42,.12);
  --radius:22px;

  --focus:0 0 0 4px rgba(250,204,21,.35);
}

*{ box-sizing:border-box }
html,body{ height:100% }

body{
  margin:0;
  font-family: Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;
  color:var(--text);
  display:flex;
  align-items:center;
  justify-content:center;
  padding:24px;

  background:
    radial-gradient(900px 520px at 105% -10%, rgba(250,204,21,.18), transparent 55%),
    radial-gradient(900px 520px at -10% 110%, rgba(225,29,72,.14), transparent 55%),
    var(--bg);
}

/* Layout */
.wrap{ width:min(1100px, 100%); }

.hero{
  display:flex;
  align-items:flex-end;
  justify-content:space-between;
  gap:16px;
  margin-bottom:18px;
}

.title{
  font-weight:900;
  letter-spacing:.3px;
  font-size: clamp(22px, 3vw, 34px);
}

.muted{ color:var(--muted); font-size:14px }

/* Grid & Cards */
.grid{
  display:grid;
  gap:16px;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
}

.card{
  background: linear-gradient(180deg,var(--card) 0%,var(--card2) 100%);
  border:1px solid var(--border);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding:22px;
  display:flex;
  flex-direction:column;
  gap:14px;
}

/* Botones */
.bigbtn{
  display:flex;
  align-items:center;
  justify-content:center;
  gap:12px;

  padding:18px 16px;
  border-radius:18px;
  border:1px solid var(--border);

  font-weight:900;
  font-size: clamp(18px, 2.2vw, 22px);
  cursor:pointer;
  text-decoration:none;
  text-align:center;

  transition: transform .12s ease, box-shadow .2s ease, filter .2s ease;
  box-shadow: 0 10px 26px rgba(15,23,42,.14);
}

.bigbtn:active{ transform: translateY(1px) }
.bigbtn:focus-visible{ outline:none; box-shadow: var(--focus), 0 10px 26px rgba(15,23,42,.14) }

/* Variantes */
.bigbtn.primary{
  background: linear-gradient(90deg,var(--yellow2),var(--yellow));
  color:#0f172a;
}

.bigbtn.secondary{
  background:#ffffff;
  color:var(--text);
}

.bigbtn.danger{
  background: linear-gradient(90deg,var(--red2),var(--red));
  color:#ffffff;
}

/* Botón gigante */
.bigbtn.big{
  gap:22px;
  height:140px;
  padding:0 28px;
  width:100%;
  font-size:34px;
  border-radius:34px;
}

.bigbtn.big i{
  font-size:50px;
  line-height:1;
}

/* Hover (solo con mouse) */
@media (hover:hover){
  .bigbtn:hover{
    transform: translateY(-2px);
    filter: brightness(1.02);
    box-shadow: 0 18px 40px rgba(15,23,42,.18);
  }
}

/* KPIs */
.kpis{
  display:grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap:12px;
}

.kpi{
  background:#ffffff;
  border:1px solid var(--border);
  border-radius:16px;
  padding:14px;
}

.kpi .label{
  color:var(--muted);
  font-size:12px;
  text-transform:uppercase;
  letter-spacing:.3px;
}

.kpi .value{
  font-weight:900;
  font-size:22px;
  margin-top:6px;
}

/* Filas */
.row{
  display:grid;
  grid-template-columns: 1.2fr .8fr;
  gap:16px;
}

/* Responsive */
@media (max-width: 900px){
  .row{ grid-template-columns: 1fr }
}

@media (max-width: 480px){
  body{ padding:14px }
  .bigbtn.big{
    height:120px;
    font-size:28px;
    border-radius:28px;
    gap:16px;
  }
  .bigbtn.big i{ font-size:40px }
}


  </style>
</head>
<body>
  <div class="wrap">
    <div class="hero">
      <div>
        
      </div>
    </div>

    <div class="row" style="display:grid; gap:18px">

  <!-- ACCIONES RÁPIDAS -->
  <div class="card" style="padding:20px">
    <div class="muted" style="font-size:14px; font-weight:900; margin-bottom:14px">
      Acciones rápidas
    </div>

    <div class="grid" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:16px">

      <a class="bigbtn big primary"
         href="ventas/ventas.php"
         onclick="parent.addTab && parent.addTab('ventas/ventas.php','Ventas'); return false;">
        <i class="fi fi-rr-receipt"></i>
        <span>Ventas</span>
      </a>

      <a class="bigbtn big secondary"
         href="compra/compra.php"
         onclick="parent.addTab && parent.addTab('compra/compra.php','Compra'); return false;">
        <i class="fi fi-rr-shopping-cart"></i>
        <span>Compra</span>
      </a>

    </div>
  </div>

  <!-- ATAJOS -->
  <div class="card" style="padding:20px">
    <div class="muted" style="font-size:14px; font-weight:900; margin-bottom:14px">
      Atajos
    </div>

    <div class="grid" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:16px">

      <a class="bigbtn big secondary"
         href="producto/producto.php"
         onclick="parent.addTab && parent.addTab('producto/producto.php','Producto'); return false;">
        <i class="fi fi-rr-box"></i>
        <span>Producto</span>
      </a>

      <a class="bigbtn big secondary"
         href="cargos/cargo.php"
         onclick="parent.addTab && parent.addTab('cargos/cargo.php','Cargos'); return false;">
        <i class="fi fi-rr-id-badge"></i>
        <span>Cargos</span>
      </a>

    </div>
  </div>

</div>

    <div style="height:10px"></div>

   
  </div>
</body>
</html>
