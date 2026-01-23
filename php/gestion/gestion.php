<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Stock y Compras</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <style>
        body {
            background: linear-gradient(to right, #fdbb2d, #2297c3);
            color: #333;
            font-family: Arial, sans-serif;
        }
        .container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .total-suma {
            font-weight: bold;
            font-size: 1.2rem;
            text-align: right;
        }
        #listaProveedores {
            position: absolute;
            z-index: 1000;
            background-color: #ffffff;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            max-height: 200px;
            overflow-y: auto;
            display: none;
        }
        #listaProveedores .dropdown-item {
            padding: 10px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        #listaProveedores .dropdown-item:hover {
            background-color: #f0f0f0;
        }
        .form-group {
        margin-bottom: 10px; /* Reduce el espacio entre los campos del formulario */
    }
    .form-control {
        padding: 5px; /* Reduce el padding de los campos del formulario */
    }
    .btn {
        margin-top: 10px; /* Reduce el margen superior del botón */
    }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center">Gestión de Stock y Compras</h2>
    <ul class="nav nav-tabs">
    <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#compras">Compras</a>
        </li>
        <li class="nav-item">
            <!-- Eliminada la clase 'disabled' -->
            <a class="nav-link" data-toggle="tab" href="#productos" id="productos-tab">Productos</a>
        </li>
        <li class="nav-item">
            <!-- Eliminada la clase 'disabled' -->
            <a class="nav-link" data-toggle="tab" href="#detalles" id="detalles-tab">Detalles de Compra</a>
        </li>
    </ul>

    <div class="tab-content">
       <!-- Compras -->
<div id="compras" class="tab-pane active">
    <h3>Registrar Compra</h3>
    <form id="agregarCompraForm">
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="fecha_com">Fecha de Compra:</label>
            <input required type="date" class="form-control" id="fecha_com" name="fecha_com" required>
        </div>
        <div class="form-group col-md-6">
            <label for="id_proveedor">Proveedor:</label>
            <select class="form-control" id="id_proveedor" name="id_proveedor" required>
                <option value="">Seleccione un proveedor</option>
            </select>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="id_mon">Moneda:</label>
            <select class="form-control" id="id_mon" name="id_mon" required>
                <option value="">Seleccione una moneda</option>
            </select>
        </div>
        <div class="form-group col-md-6">
            <label for="timbrado_com">Timbrado:</label>
            <input type="text" class="form-control" id="timbrado_com" name="timbrado_com" required>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="documento_com">Documento:</label>
            <input type="text" class="form-control" id="documento_com" name="documento_com" required>
        </div>
        <div class="form-group col-md-6">
            <label for="fecha_emision_comp">Fecha de Emisión:</label>
            <input type="date" class="form-control" id="fecha_emision_comp" name="fecha_emision_comp" required>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="historico_com">Histórico:</label>
            <textarea class="form-control" id="historico_com" name="historico_com"></textarea>
        </div>
        <div class="form-group col-md-6">
            <label for="valor_documento_com">Valor Documento:</label>
            <input type="number" step="0.01" class="form-control" id="valor_documento_com" name="valor_documento_com" required>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Guardar Compra</button>
</form>

</div>

<!-- Pestaña de Productos -->
<div id="productos" class="tab-pane">
    <h3>Agregar Productos</h3>
    <div class="row">
        <div class="col-md-12">
            <!-- Formulario de Agregar Producto -->
            <form id="agregarProductoForm">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="id_com">ID Compra:</label>
                        <input type="text" class="form-control" id="id_com" name="id_com" readonly>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="id_pro">ID Producto:</label>
                        <input type="text" class="form-control" id="id_pro" name="id_pro" readonly>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="codigo_barra_pro">Código de Barra:</label>
                        <input type="text" class="form-control" id="codigo_barra_pro" name="codigo_barra_pro" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="nombre_pro">Nombre del Producto:</label>
                        <input type="text" class="form-control" id="nombre_pro" name="nombre_pro" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="uni_caja_pro">Unidades por Caja:</label>
                        <input type="number" class="form-control" id="uni_caja_pro" name="uni_caja_pro" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="cantidad_caja_pro">Cantidad de Cajas:</label>
                        <input type="number" class="form-control" id="cantidad_caja_pro" name="cantidad_caja_pro" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="cantidad_uni_pro">Cantidad Total Unidades:</label>
                        <input type="number" class="form-control" id="cantidad_uni_pro" name="cantidad_uni_pro" readonly>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="fecha_ven_pro">Fecha de Vencimiento:</label>
                        <input type="date" class="form-control" id="fecha_ven_pro" name="fecha_ven_pro">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="costo_caja_pro">Costo por Caja:</label>
                        <input type="number" class="form-control" id="costo_caja_pro" name="costo_caja_pro" step="0.01" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="costo_uni_pro">Costo por Unidad:</label>
                        <input type="number" class="form-control" id="costo_uni_pro" name="costo_uni_pro" readonly>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="porcen_pro">Margen de Ganancia (%):</label>
                        <input type="number" class="form-control" id="porcen_pro" name="porcen_pro" step="0.01" required>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="iva_pro">IVA (%):</label>
                        <input type="number" class="form-control" id="iva_pro" name="iva_pro" step="0.01" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="precio1_pro">Precio Venta 1:</label>
                        <input type="number" class="form-control" id="precio1_pro" name="precio1_pro" step="0.01" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="precio2_pro">Precio Venta 2:</label>
                        <input type="number" class="form-control" id="precio2_pro" name="precio2_pro" step="0.01" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="precio3_pro">Precio Venta 3:</label>
                        <input type="number" class="form-control" id="precio3_pro" name="precio3_pro" step="0.01" required>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <button type="submit" class="btn btn-primary btn-lg">Agregar Producto</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Productos -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead class="thead-dark">
                            <tr>
                            <th>ID Compra</th>
                                <th>Nombre del Producto</th>
                                <th>Cantidad por Unidad</th>
                                <th>Costo por Unidad</th>
                                <th>Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-productos"></tbody>
                    </table>
                    <div id="subtotal">Subtotal: 0</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pestaña de Detalles -->
<div id="detalles" class="tab-pane">
    <h3>Detalles de Compra</h3>
    <form id="agregarCompraDetalleForm">
        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="timbre_comp_det">Timbre:</label>
                <input type="number" class="form-control" id="timbre_comp_det" name="timbre_comp_det" required>
            </div>
            <div class="form-group col-md-4">
                <label for="subtotal_comp_det">Subtotal:</label>
                <input type="number" class="form-control" id="subtotal_comp_det" name="subtotal_comp_det" readonly>
            </div>
            <div class="form-group col-md-4">
                <label for="iva_comp_det">IVA %:</label>
                <select class="form-control" id="iva_comp_det" name="iva_comp_det" required>
                    <option value="10" selected>10</option>
                    <option value="5">5</option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Agregar Detalle</button>
    </form>

    <!-- Tabla de Detalles -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID Compra</th>
                                <th>ID Producto</th>
                                <th>ID Comprobante</th>
                                <th>Timbre</th>
                                <th>Subtotal</th>
                                <th>IVA</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-detalles"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para agregar producto -->
<div class="modal fade" id="agregarProductoModal" tabindex="-1" role="dialog" aria-labelledby="agregarProductoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="agregarProductoModalLabel">Agregar Producto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formAgregarProducto">
                    <div class="form-group">
                        <label for="nombre_pro">Nombre del Producto:</label>
                        <input type="text" class="form-control" id="nombre_pro_modal" name="nombre_pro" required>
                    </div>
                    <div class="form-group">
                        <label for="codigo_barra_pro">Código de Barra:</label>
                        <input type="text" class="form-control" id="codigo_barra_pro_modal" name="codigo_barra_pro" required>
                    </div>
                    <div class="form-group">
                        <label for="uni_caja_pro">Unidades por Caja:</label>
                        <input type="number" class="form-control" id="uni_caja_pro_modal" name="uni_caja_pro" required>
                    </div>
                    <div class="form-group">
                        <label for="iva_pro">IVA:</label>
                        <input type="number" step="0.01" class="form-control" id="iva_pro_modal" name="iva_pro" required>
                    </div>
                    <div class="form-group">
                        <label for="id_cat">ID Categoría:</label>
                        <input type="number" class="form-control" id="id_cat_modal" name="id_cat" required>
                    </div>
                    <button type="submit" class="btn btn-success">Agregar Producto</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {

// 🔹 Cargar lista de proveedores
function cargarProveedores() {
    $.ajax({
        url: 'get_proveedores.php',
        method: 'GET',
        dataType: 'json',
        success: function (data) {
            let opciones = '<option value="">Seleccione un proveedor</option>';
            data.forEach(function (proveedor) {
                opciones += <option value="${proveedor.id_proveedor}">${proveedor.nombre_prove}</option>;
            });
            $('#id_proveedor').html(opciones);
        },
        error: function (xhr) {
            console.error('Error al cargar proveedores:', xhr.responseText);
        }
    });
}

// 🔹 Cargar lista de monedas
function cargarMonedas() {
    $.ajax({
        url: 'get_monedas.php',
        method: 'GET',
        dataType: 'json',
        success: function (data) {
            let opciones = '<option value="">Seleccione una moneda</option>';
            data.forEach(function (moneda) {
                opciones += <option value="${moneda.id_mon}">${moneda.nombre_mon}</option>;
            });
            $('#id_mon').html(opciones);
        },
        error: function (xhr) {
            console.error('Error al cargar monedas:', xhr.responseText);
        }
    });
}

// 🔹 Cargar lista de compras
function cargarCompras() {
    $.ajax({
        url: 'get_compras.php',
        method: 'GET',
        dataType: 'json',
        success: function (data) {
            let tablaCompras = '';
            data.forEach(function (compra) {
                tablaCompras += <tr>
                    <td>${compra.id_com}</td>
                    <td>${compra.fecha_com}</td>
                    <td>${compra.nombre_prove}</td>
                    <td>${compra.nombre_mon}</td>
                    <td>${compra.timbrado_com}</td>
                    <td>${compra.documento_com}</td>
                    <td>${compra.fecha_emision_comp}</td>
                    <td>${compra.historico_com}</td>
                    <td>${compra.valor_documento_com}</td>
                    <td>
                        <button class="btn btn-warning btn-sm editar-compra" data-id="${compra.id_com}">Editar</button>
                        <button class="btn btn-danger btn-sm eliminar-compra" data-id="${compra.id_com}">Eliminar</button>
                    </td>
                </tr>;
            });
            $('#tabla-compras').html(tablaCompras);
        },
        error: function (xhr) {
            console.error('Error al obtener compras:', xhr.responseText);
        }
    });
}

// 🔹 Registrar una nueva compra
$('#agregarCompraForm').on('submit', function (e) {
    e.preventDefault();

    let datosFormulario = $(this).serialize();

    $.ajax({
        url: 'register_compra_bi.php',
        method: 'POST',
        data: datosFormulario,
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                alert('Compra registrada correctamente. ID: ' + response.id_com);
                
                // Pasar automáticamente el ID de compra al formulario de productos
                $('#id_com').val(response.id_com);
                
                // Actualizar la lista de compras
                cargarCompras();
                
                // Activar la pestaña del formulario de productos
                $('#productos-tab').removeClass('disabled').tab('show');
            } else {
                alert('Error al guardar la compra: ' + response.message);
            }
        },
        error: function (xhr) {
            console.error('Error al agregar compra:', xhr.responseText);
        }
    });
});
// Función para cargar el último ID de compra y pasarlo al siguiente formulario
function cargarUltimoIdCompra() {
        $.ajax({
            url: 'get_last_compra.php', // Endpoint para obtener el último id_com
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    // Completar el campo id_com en el siguiente formulario
                    $('#id_com').val(response.id_com); // Campo en el formulario de productos
                    console.log('Último ID de compra cargado:', response.id_com);
                } else {
                    console.error('Error al cargar el último ID de compra:', response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error al obtener el último ID de compra:', status, error);
                console.log(xhr.responseText);
            }
        });
    }

    // Llamar a las funciones al cargar la página
   
    cargarUltimoIdCompra();


// 🔹 Cargar listas al iniciar la página
cargarProveedores();
cargarMonedas();
cargarCompras();
});



</script>












<script>
$(document).ready(function() {
    function formatearNumero(valor, decimales = 0) {
    return new Intl.NumberFormat('es-PY', {
        minimumFractionDigits: decimales,
        maximumFractionDigits: decimales,
        useGrouping: true
    }).format(valor);
}
$('#costo_caja_pro').on('input', function() {
    let valor = parseFloat($(this).val().replace(/\./g, '')) || 0;
    $(this).val(formatearNumero(valor));
    calcularCostoPorUnidad();
    calcularPrecio1();
    calcularPrecio2();
});

$('#costo_caja_pro, #costo_uni_pro, #precio1_pro, #precio2_pro, #precio3_pro').on('input', function() {
    let valor = parseFloat($(this).val().replace(/\./g, '')) || 0;
    $(this).val(formatearGuaranies(valor));
});


// 🔹 Evento al presionar Enter en el código de barras
$('#codigo_barra_pro').keypress(function (event) {
    if (event.which === 13) { // Detectar tecla Enter
        event.preventDefault();
        let codigoBarra = $(this).val().trim();

        if (codigoBarra === "") {
            alert("⚠ Ingrese un código de barras válido.");
            return;
        }

        // 🔹 Deshabilitar el input temporalmente para evitar múltiples consultas
        $('#codigo_barra_pro').prop('disabled', true);

        // 🔹 Consultar si el producto ya existe
        $.ajax({
            url: 'get_producto_completo.php',
            method: 'GET',
            data: { codigo_barra: codigoBarra },
            dataType: 'json',
            success: function (data) {
                if (data.existe) {
                    console.log("✅ Producto encontrado:", data);
                    $('#id_pro').val(data.id_pro || '');
                    $('#nombre_pro').val(data.nombre_pro || '');
                    $('#uni_caja_pro').val(data.uni_caja_pro || '');
                    $('#cantidad_caja_pro').val('');
                    $('#cantidad_uni_pro').val('');
                    $('#costo_caja_pro').val('');
                    $('#costo_uni_pro').val('');
                    $('#porcen_pro').val('');
                    $('#precio1_pro').val('');
                    $('#precio2_pro').val('');
                    $('#precio3_pro').val(0);

                    // 🔹 Habilitar el input de código de barras nuevamente
                    $('#codigo_barra_pro').prop('disabled', false);
                } else {
                    console.log("❌ Producto no encontrado. Abriendo modal de registro.");
                    $('#codigo_barra_pro_modal').val(codigoBarra); // Inserta el código en el modal
                    $('#agregarProductoModal').modal('show'); // Abre el modal
                }
            },
            error: function (xhr) {
                console.error("❌ Error al buscar el producto:", xhr.responseText);
                alert("Error al buscar el producto.");
            },
            complete: function () {
                // 🔹 Habilitar el input después de la consulta
                $('#codigo_barra_pro').prop('disabled', false);
            }
        });
    }
});

// 🔹 Guardar nuevo producto desde el modal
$('#formAgregarProducto').submit(function (e) {
    e.preventDefault();
    let datosFormulario = $(this).serialize();

    $.ajax({
        url: 'register_producto_bi.php',
        method: 'POST',
        data: datosFormulario,
        dataType: 'json',
        success: function (response) {
            console.log("✅ Respuesta del servidor:", response);

            if (response && response.success) {
                alert("✅ Producto registrado correctamente.");
                $('#agregarProductoModal').modal('hide');
                $('#formAgregarProducto')[0].reset();

                // Rellenar los datos en el formulario principal
                $('#codigo_barra_pro').val(response.codigo_barra_pro);
                $('#id_pro').val(response.id_pro);
                $('#nombre_pro').val(response.nombre_pro);
                $('#uni_caja_pro').val(response.uni_caja_pro);

                cargarProductos();
            } else {
                alert("❌ Error al registrar el producto.");
                console.error("⚠ Error en la respuesta del servidor:", response);
            }
        },
        error: function (xhr, status, error) {
            console.error("❌ Error en la solicitud AJAX:", xhr.responseText);
            alert("❌ Error inesperado al registrar el producto.");
        }
    });
});




// 🔹 Evento para registrar un nuevo producto
$('#agregarProductoForm').submit(function(e) {
    e.preventDefault(); // Prevenir el envío normal del formulario
    
    let datosFormulario = $(this).serialize();
    console.log("📤 Enviando datos del producto:", datosFormulario); // Depuración

    $.ajax({
        url: 'register_producto_detalle_bi.php',
        method: 'POST',
        data: datosFormulario,
        dataType: 'json',
        success: function(response) {
            console.log("✅ Respuesta del servidor:", response);
            if (response.success) {
                alert('Producto registrado correctamente.');
                $('#agregarProductoModal').modal('hide');
                $('#agregarProductoForm')[0].reset();
                cargarProductos(); // Recargar lista
            } else {
                alert('Error al registrar producto: ' + response.error);
            }
        },
        error: function(xhr) {
            console.error("❌ Error en la solicitud AJAX:", xhr.responseText);
            alert("Error al registrar producto. Revisa la consola.");
        }
    });
});

// 🔹 Evento para actualizar un producto existente
$('#editarProductoForm').submit(function(e) {
    e.preventDefault(); // Evitar recarga de página
    
    let datosFormulario = $(this).serialize();
    console.log("📤 Enviando datos de edición:", datosFormulario); // Depuración

    $.ajax({
        url: 'editar.php',
        method: 'POST',
        data: datosFormulario,
        dataType: 'json',
        success: function(response) {
            console.log("✅ Respuesta del servidor:", response);
            if (response.success) {
                alert('Producto actualizado correctamente.');
                $('#editarProductoModal').modal('hide');
                cargarProductos(); // Recargar lista
            } else {
                alert('Error al actualizar producto: ' + response.error);
            }
        },
        error: function(xhr) {
            console.error("❌ Error en la solicitud AJAX:", xhr.responseText);
            alert("Error al actualizar producto. Revisa la consola.");
        }
    });
});


     // 🔹 Función para cargar la lista de compras
     function cargarCompras() {
        $.ajax({
            url: 'get_compra.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                let opciones = '<option value="">Seleccione una compra</option>';
                data.forEach(function(compra) {
                    opciones += <option value="${compra.id_com}">${compra.id_com} - ${compra.fecha_com}</option>;
                });
                $('#id_com, #editar_id_com').html(opciones);
            },
            error: function(xhr) {
                console.error('Error al cargar compras:', xhr.responseText);
            }
        });
    }

    function cargarProductos(id_com) {
    if (!id_com) {
        console.warn("⚠ No hay ID de compra asignado. La lista de productos se mantendrá vacía.");
        $('#tabla-productos').html('');
        return;
    }

    let url = lista_producto_detalle.php?id_com=${id_com};

    $.ajax({
        url: url,
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            if (!data.success || !Array.isArray(data.data) || data.data.length === 0) {
                console.warn("⚠ No hay productos registrados para esta compra.");
                $('#tabla-productos').html('<tr><td colspan="6" class="text-center">No hay productos para esta compra.</td></tr>');
                return;
            }

            let tablaProductos = '';
            data.data.forEach(function(producto) {
                let total = producto.cantidad_uni_pro * producto.costo_uni_pro;
                
                tablaProductos += <tr>
                    <td>${producto.id_com}</td>
                    <td>${producto.codigo_barra_pro} - ${producto.nombre_pro}</td>
                    <td>${formatearNumero(producto.cantidad_uni_pro)}</td>
                    <td>${formatearNumero(producto.costo_uni_pro, 2)}</td>
                    <td class="total-fila">${formatearNumero(total, 2)}</td>
                    <td>
                        <button class="btn btn-warning btn-sm editar-producto" data-id="${producto.id_det_pro}">Editar</button>
                        <button class="btn btn-danger btn-sm eliminar-producto" data-id="${producto.id_det_pro}">Eliminar</button>
                    </td>
                </tr>;
            });

            $('#tabla-productos').html(tablaProductos);
        },
        error: function(xhr) {
            console.error('❌ Error al obtener productos:', xhr.responseText);
            alert('Error al obtener productos. Revisa la consola.');
        }
    });
}

    function formatearNumero(valor, decimales = 0) {
        return new Intl.NumberFormat('es-ES', {
            minimumFractionDigits: decimales,
            maximumFractionDigits: decimales
        }).format(valor);
    }

    function calcularCantidadTotal() {
        let unidadesPorCaja = parseFloat($('#uni_caja_pro').val()) || 0;
        let cantidadCajas = parseFloat($('#cantidad_caja_pro').val()) || 0;
        $('#cantidad_uni_pro').val(unidadesPorCaja * cantidadCajas);
    }

    function calcularCostoPorUnidad() {
        let costoCaja = parseFloat($('#costo_caja_pro').val()) || 0;
        let unidadesPorCaja = parseFloat($('#uni_caja_pro').val()) || 1;
        $('#costo_uni_pro').val((costoCaja / unidadesPorCaja).toFixed(2));
    }

    function calcularPrecio1() {
        let costoUnitario = parseFloat($('#costo_uni_pro').val()) || 0;
        let porcentaje = parseFloat($('#porcen_pro').val()) || 0;
        $('#precio1_pro').val((costoUnitario * (1 + (porcentaje / 100))).toFixed(2));
    }

    function calcularPrecio2() {
        let costoCaja = parseFloat($('#costo_caja_pro').val()) || 0;
        let porcentaje = parseFloat($('#porcen_pro').val()) || 0;
        $('#precio2_pro').val((costoCaja * (1 + (porcentaje / 100))).toFixed(2));
    }

    function inicializarPrecio3() {
        if (!$('#precio3_pro').val()) {
            $('#precio3_pro').val(0);
        }
    }

    function cargarDatosProducto(codigoBarra) {
        if (!codigoBarra.trim()) {
            alert("Ingrese un código de barras válido.");
            return;
        }

        $.ajax({
            url: 'get_producto_completo.php',
            method: 'GET',
            data: { codigo_barra: codigoBarra },
            dataType: 'json',
            success: function(data) {
                if (data && data.existe) {
                    $('#id_pro').val(data.id_pro || '');
                    $('#nombre_pro').val(data.nombre_pro || '');
                    $('#uni_caja_pro').val(data.uni_caja_pro || '');
                    $('#cantidad_caja_pro').val(data.cantidad_caja_pro || '');
                    $('#cantidad_uni_pro').val(data.cantidad_uni_pro || '');
                    $('#costo_caja_pro').val(data.costo_caja_pro || '');
                    $('#costo_uni_pro').val(data.costo_uni_pro || '');
                    $('#iva_pro').val(data.iva_pro || '');
                    $('#porcen_pro').val(data.porcen_pro || '');
                    $('#precio1_pro').val(data.precio1_pro || '');
                    $('#precio2_pro').val(data.precio2_pro || '');
                    $('#precio3_pro').val(data.precio3_pro || 0);
                } else {
// ❌ Producto NO encontrado - Abre el modal con el código prellenado
console.log("❌ Producto no encontrado. Abriendo modal de registro.");
                        $('#codigo_barra_pro').val(codigoBarra);
                        $('#agregarProductoModal').modal('show');
                }
            },
            error: function(xhr) {
                console.error("Error al buscar el producto:", xhr.responseText);
                alert("Error al buscar el producto. Revisa la consola para más detalles.");
            }
        });
    }

    $('#cantidad_caja_pro, #uni_caja_pro').on('input', calcularCantidadTotal);
    $('#costo_caja_pro').on('input', function() {
        calcularCostoPorUnidad();
        calcularPrecio1();
        calcularPrecio2();
    });
    $('#porcen_pro').on('input', function() {
        calcularPrecio1();
        calcularPrecio2();
    });
    $('#precio3_pro').on('focus', inicializarPrecio3);
    $('#codigo_barra_pro').keypress(function(event) {
        if (event.which === 13) {
            event.preventDefault();
            cargarDatosProducto($(this).val());
        }
    });

    cargarProductos();
    cargarCompras();
});



</script>




<script>
   $(document).ready(function() {
    function cargarCompraDetalles() {
        $.ajax({
            url: 'register_compra_detalle_bi.php',
            method: 'GET',
            dataType: 'json',
            success: function(data) {
                var tablaCompraDetalle = '';
                if (data.length > 0) {
                    data.forEach(function(detalle) {
                        tablaCompraDetalle += '<tr>';
                        tablaCompraDetalle += '<td>' + detalle.id_comp_det + '</td>';
                        tablaCompraDetalle += '<td>' + detalle.subtotal_comp_det + '</td>';
                        tablaCompraDetalle += '<td>' + detalle.id_com + '</td>';
                        tablaCompraDetalle += '<td>' + detalle.id_pro + '</td>';
                        tablaCompraDetalle += '<td>';
                        tablaCompraDetalle += '<button class="btn btn-warning btn-sm editar-compra-detalle" data-id="' + detalle.id_comp_det + '">Editar</button> ';
                        tablaCompraDetalle += '<button class="btn btn-danger btn-sm eliminar-compra-detalle" data-id="' + detalle.id_comp_det + '">Eliminar</button>';
                        tablaCompraDetalle += '</td>';
                        tablaCompraDetalle += '</tr>';
                    });
                } else {
                    tablaCompraDetalle = '<tr><td colspan="5" class="text-center">No hay detalles de compra.</td></tr>';
                }
                $('#tabla-compra-detalle').html(tablaCompraDetalle);
            },
            error: function(xhr, status, error) {
                console.error('Error al obtener detalles de compra:', status, error);
            }
        });
    }
    cargarCompraDetalles();

    $('#buscarCompraDetalle').on('input', function() {
        var textoBusqueda = $(this).val().toLowerCase();
        $('#tabla-compra-detalle tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(textoBusqueda) > -1);
        });
    });

    $('#agregarCompraDetalleForm').submit(function(e) {
        e.preventDefault();
        var subtotalCompDet = $('#subtotal_comp_det').val();
        var idCom = $('#id_com').val();
        var idPro = $('#id_pro').val();

        $.ajax({
            url: 'register_compra_detalle_bi.php',
            method: 'POST',
            dataType: 'json',
            data: {
                subtotal_comp_det: subtotalCompDet,
                id_com: idCom,
                id_pro: idPro
            },
            success: function(response) {
                if (response.status === "success") {
                    cargarCompraDetalles();
                    $('#agregarCompraDetalleModal').modal('hide');
                    $('#agregarCompraDetalleForm')[0].reset();
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al agregar detalle de compra:', status, error);
            }
        });
    });
});

</script>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<div class="mt-4">
    <h5>Productos agregados:</h5>
    <table class="table table-bordered" id="tablaProductos">
        <thead class="thead-dark">
            <tr>
                <th>Nombre</th>
                <th>Cantidad</th>
                <th>Precio Unitario (Gs)</th>
                <th>Subtotal (Gs)</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
    <div class="total-suma text-right">Total: <span id="subtotalGeneral">0</span> Gs</div>
</div>


<div class="mt-4">
    <h5>Productos agregados:</h5>
    <table class="table table-bordered" id="tablaProductos">
        <thead class="thead-dark">
            <tr>
                <th>ID Compra</th>
                <th>ID Producto</th>
                <th>ID Comprobante</th>
                <th>Timbre</th>
                <th>Subtotal (Gs)</th>
                <th>IVA (Gs)</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
    <div class="total-suma text-right">Total: <span id="subtotalGeneral">0</span> Gs</div>
</div>

</body>

<script>
let productos = [];

function formatearGs(numero) {
    return new Intl.NumberFormat('es-PY').format(numero);
}

function agregarProducto(nombre, cantidad, precio) {
    let subtotal = cantidad * precio;
    productos.push({ nombre, cantidad, precio, subtotal });

    let fila = <tr>
        <td>${nombre}</td>
        <td>${cantidad}</td>
        <td>${formatearGs(precio)}</td>
        <td>${formatearGs(subtotal)}</td>
    </tr>;

    $('#tablaProductos tbody').append(fila);

    let total = productos.reduce((sum, p) => sum + p.subtotal, 0);
    $('#subtotalGeneral').text(formatearGs(total));
}

// Simular evento de agregar producto (reemplazar esto por los datos reales del formulario)
$('#btnAgregarProducto').on('click', function() {
    const nombre = $('#nombre_pro').val();
    const cantidad = parseInt($('#cantidad_uni_pro').val());
    const precio = parseInt($('#costo_uni_pro').val());

    if (nombre && cantidad > 0 && precio > 0) {
        agregarProducto(nombre, cantidad, precio);
        $('#nombre_pro').val('');
        $('#cantidad_uni_pro').val('');
        $('#costo_uni_pro').val('');
    } else {
        alert("Completa los campos correctamente.");
    }
});
</script>


<script>
let productos = [];

function formatearGs(numero) {
    return new Intl.NumberFormat('es-PY').format(numero);
}

function agregarProducto(idCompra, idProducto, idComprobante, timbre, subtotal, iva) {
    productos.push({ idCompra, idProducto, idComprobante, timbre, subtotal, iva });

    let fila = <tr>
        <td>${idCompra}</td>
        <td>${idProducto}</td>
        <td>${idComprobante}</td>
        <td>${timbre}</td>
        <td>${formatearGs(subtotal)}</td>
        <td>${formatearGs(iva)}</td>
        <td><button class="btn btn-sm btn-danger" onclick="eliminarProducto(${productos.length - 1})">Eliminar</button></td>
    </tr>;

    $('#tablaProductos tbody').append(fila);
    actualizarTotal();
}

function eliminarProducto(index) {
    productos.splice(index, 1);
    renderizarTabla();
}

function renderizarTabla() {
    let total = 0;
    let cuerpo = '';
    productos.forEach((p, i) => {
        total += p.subtotal;
        cuerpo += <tr>
            <td>${p.idCompra}</td>
            <td>${p.idProducto}</td>
            <td>${p.idComprobante}</td>
            <td>${p.timbre}</td>
            <td>${formatearGs(p.subtotal)}</td>
            <td>${formatearGs(p.iva)}</td>
            <td><button class="btn btn-sm btn-danger" onclick="eliminarProducto(${i})">Eliminar</button></td>
        </tr>;
    });
    $('#tablaProductos tbody').html(cuerpo);
    $('#subtotalGeneral').text(formatearGs(total));
}

function actualizarTotal() {
    let total = productos.reduce((sum, p) => sum + p.subtotal, 0);
    $('#subtotalGeneral').text(formatearGs(total));
}

// Evento simulado para agregar producto
$('#btnAgregarProducto').on('click', function() {
    const idCompra = $('#id_compra').val();
    const idProducto = $('#id_producto').val();
    const idComprobante = $('#id_comprobante').val();
    const timbre = $('#timbre').val();
    const subtotal = parseInt($('#subtotal').val());
    const iva = parseInt($('#iva').val());

    if (idCompra && idProducto && idComprobante && subtotal > 0) {
        agregarProducto(idCompra, idProducto, idComprobante, timbre, subtotal, iva);
        $('#id_compra, #id_producto, #id_comprobante, #timbre, #subtotal, #iva').val('');
    } else {
        alert("Todos los campos obligatorios deben completarse.");
    }
});
</script>

</html> 