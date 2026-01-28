Módulo Compras (corregido)

Archivos:
- compra.php                (UI + JS)
- register_compra_bi.php    (API: meta + listar + insertar)
- eliminar.php              (API: eliminar compra por id_com)
- get_compra.php            (API: obtener compra por id) [opcional]
- conexion_bi.php           (tu conexión PostgreSQL)
- editar.php                (tu módulo de moneda, sin cambios) [opcional]

Notas importantes:
1) compra.php llamaba a archivos *_FIXED.php que no existían. Ya apunta a:
   - register_compra_bi.php
   - eliminar.php

2) Bug crítico: safeJson estaba declarado como 'const' DESPUÉS de usarse en loadMeta().
   En JS eso rompe todo (ReferenceError). Ahora safeJson es 'function' y funciona.

3) Select Moneda: por cada registro de 'moneda' aparecen 3 opciones:
   '#ID — Guaraní', '#ID — Real', '#ID — Dólar' (con su cotización).

4) Al guardar, se guarda id_mon en compra y además se añade snapshot en historico_com:
   'Moneda: DOLAR | Cotización: 7300 | id_mon: 5'
