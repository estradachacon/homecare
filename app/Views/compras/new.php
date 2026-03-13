<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="container-fluid">

    <div class="card shadow-sm mb-3">
        <div class="card-header">
            <h4>Nueva Compra</h4>
        </div>

        <div class="card-body">

            <div class="row mb-3">

                <div class="col-md-3">
                    <label class="form-label">Fecha de compra</label>
                    <input type="date" class="form-control" name="fecha_compra">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Fecha documento</label>
                    <input type="date" class="form-control" name="fecha_documento">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Proveedor</label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="proveedor" placeholder="Seleccione proveedor">
                        <button class="btn btn-outline-secondary" type="button">+</button>
                    </div>
                    <small class="text-muted">Datos del proveedor</small>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Tipo Documento</label>
                    <select class="form-control" name="tipo_documento">
                        <option value="">Seleccione</option>
                        <option>Factura</option>
                        <option>Crédito Fiscal</option>
                        <option>Ticket</option>
                    </select>
                </div>

            </div>

            <div class="row mb-3">

                <div class="col-md-3">
                    <label class="form-label">Código de Generación</label>
                    <input type="text" class="form-control" name="codigo_generacion">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Número de Control</label>
                    <input type="text" class="form-control" name="numero_control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Sello de Recepción</label>
                    <input type="text" class="form-control" name="sello_recepcion">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Condición de Pago</label>
                    <select class="form-control" name="condicion_pago">
                        <option value="contado">Contado</option>
                        <option value="credito">Crédito</option>
                    </select>
                </div>

            </div>

            <div class="row mb-3">

                <div class="col-md-2">
                    <label class="form-label">Días Crédito</label>
                    <input type="number" class="form-control" name="dias_credito">
                </div>

                <div class="col-md-3">
                    <label class="form-label">No. Quedan</label>
                    <input type="text" class="form-control" name="numero_quedan">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Fecha Quedan</label>
                    <input type="date" class="form-control" name="fecha_quedan">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Cargar JSON</label>
                    <input type="file" class="form-control" id="jsonInput">
                </div>

            </div>

        </div>
    </div>


    <div class="card shadow-sm">

        <div class="card-header">
            <h5>Detalle de Compra</h5>
        </div>

        <div class="card-body">

            <table class="table table-bordered table-sm" id="tablaDetalles">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cantidad</th>
                        <th>Cod. Proveedor</th>
                        <th>Descripción</th>
                        <th>Unidad</th>
                        <th>Precio</th>
                        <th>Total</th>
                        <th>Lote</th>
                        <th>Obs</th>
                    </tr>
                </thead>

                <tbody>

                    <tr>
                        <td>1</td>
                        <td><input class="form-control cantidad"></td>
                        <td><input class="form-control codigo"></td>
                        <td><input class="form-control descripcion"></td>
                        <td><input class="form-control unidad"></td>
                        <td><input class="form-control precio"></td>
                        <td><input class="form-control total" readonly></td>
                        <td>
                            <button class="btn btn-sm btn-outline-secondary">
                                Lote
                            </button>
                        </td>
                        <td><input class="form-control"></td>
                    </tr>

                </tbody>

            </table>

            <button class="btn btn-outline-primary btn-sm" id="addRow">
                Agregar línea
            </button>

        </div>
    </div>


    <div class="row mt-3">

        <div class="col-md-6">

            <div class="card">
                <div class="card-body">

                    <h6>Estadísticas</h6>

                    <p>Líneas: <span id="lineCount">1</span></p>

                </div>
            </div>

        </div>


        <div class="col-md-6">

            <div class="card">

                <div class="card-body">

                    <h6>Totales</h6>

                    <div class="mb-2">
                        <label>Subtotal</label>
                        <input class="form-control" id="subtotal" readonly>
                    </div>

                    <div class="mb-2">
                        <label>IVA</label>
                        <input class="form-control" id="iva" readonly>
                    </div>

                    <div class="mb-2">
                        <label>Total Documento</label>
                        <input class="form-control" id="totalDocumento" readonly>
                    </div>

                    <hr>

                    <h6>Resumen a pagar</h6>

                    <input class="form-control" id="totalPagar" readonly>

                </div>

            </div>

        </div>

    </div>


</div>

<script>
    document.getElementById('addRow').addEventListener('click', function() {

        let table = document.getElementById('tablaDetalles').getElementsByTagName('tbody')[0]

        let rowCount = table.rows.length + 1

        let row = table.insertRow()

        row.innerHTML = `
<td>${rowCount}</td>
<td><input class="form-control cantidad"></td>
<td><input class="form-control codigo"></td>
<td><input class="form-control descripcion"></td>
<td><input class="form-control unidad"></td>
<td><input class="form-control precio"></td>
<td><input class="form-control total" readonly></td>
<td><button class="btn btn-sm btn-outline-secondary">Lote</button></td>
<td><input class="form-control"></td>
`

        document.getElementById("lineCount").innerText = rowCount

    })
</script>

<?= $this->endSection() ?>