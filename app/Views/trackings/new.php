<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    /* ===== SELECT2 + BOOTSTRAP FIX ===== */
.select2-container .select2-selection--single {
    height: calc(2.25rem + 5px);
    padding: .375rem .75rem;
    border: 1px solid #ced4da;
    border-radius: .25rem;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 1.5;
    padding-left: 0;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: calc(2.25rem + 2px);
}

.select2-container {
    width: 100% !important;
}

</style>
<script src="/backend/assets/js/scripts_newtracking.js"></script>
<div class="row">
    <div class="col-md-12">

        <!-- ENCABEZADO DEL TRACKING -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Nuevo Tracking</h5>
            </div>
            <div class="card-body">

                <div class="form-row">
                    <!-- MOTORISTA -->
                    <div class="form-group col-md-4">
                        <label>Asignar Motorista</label>
                        <select id="motorista" name="motorista" class="form-control select2" required>
                            <option value="">Seleccione</option>
                            <?php foreach ($motoristas as $m): ?>
                                <option value="<?= $m['id'] ?>">
                                    <?= esc($m['user_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- FECHA -->
                    <div class="form-group col-md-4">
                        <label>Asignar fecha de seguimiento</label>
                        <input type="text" id="fecha_tracking" name="fecha_tracking" class="form-control" required>
                    </div>
                </div>

                <hr>
                <button id="btnRutas" class="btn btn-primary">
                    <i class="fa fa-route"></i> Agregar paquetes por ruta
                </button>

                <button id="btnEspeciales" class="btn btn-secondary ml-2">
                    <i class="fa fa-box"></i> Agregar personalizados
                </button>

                <button id="btnPendientes3" class="btn btn-secondary ml-2">
                    <i class="fa fa-clock"></i> Agregar pendientes de recolecta
                </button>

            </div>
        </div>

        <!-- ===============================
            TABLA DE PACKETES SELECCIONADOS
            ================================ -->
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0">Paquetes Seleccionados</h5>
            </div>
            <div class="card-body table-responsive">

                <table class="table table-bordered table-sm" id="tablaTracking">
                    <thead>
                        <tr>
                            <th>Tipo de servicio</th>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Destino</th>
                            <th>Monto</th>
                            <th style="width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody id="tracking-body">
                        <!-- se llena con JS -->
                    </tbody>
                </table>
                <div class="text-right mt-3 pb-2">
                    <h4>Total: <span id="totalTracking" class="text-success">$0.00</span></h4>
                </div>
            </div>
        </div>

        <!-- BOTÓN FINAL -->
        <div class="text-right mb-5">
            <button id="btnGuardar" class="btn btn-success btn-lg">
                <i class="fa fa-save"></i> Guardar Tracking
            </button>
        </div>
    </div>
</div>



<!-- ============================================================
            MODAL 1 – PAQUETES POR RUTA CON SELECT2
=============================================================== -->
<div class="modal fade" id="modalRutas" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Seleccionar paquetes por ruta</h5>
                <button class="close text-white" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="ruta_select">Ruta</label>
                        <!-- Contenedor Select2 -->
                        <select id="ruta_select" class="form-control">
                            <option value="">Seleccione una ruta</option>
                            <?php foreach ($rutas as $r): ?>
                                <option value="<?= $r->id ?>"><?= $r->route_name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <button class="btn btn-sm btn-outline-primary mb-2" id="selectAllRuta">
                    Seleccionar / Quitar todos
                </button>

                <table class="table table-hover table-sm">
                    <thead>
                        <tr>
                            <th style="width: 40px;"></th>
                            <th>ID</th>
                            <th>Vendedor</th>
                            <th>Cliente</th>
                            <th>Punto fijo</th>
                            <th>Monto</th>
                        </tr>
                    </thead>
                    <tbody id="tablaPaquetesRuta">
                        <!-- lleno por AJAX/JS -->
                    </tbody>
                </table>

            </div>

            <div class="modal-footer">
                <button class="btn btn-primary" id="agregarPorRuta">Agregar Seleccionados</button>
                <button class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
                 MODAL 2 – PERSONALIZADOS / RECOLECTAS
=============================================================== -->
<div class="modal fade" id="modalEspeciales" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title">Agregar personalizados</h5>
                <button class="close text-white" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">

                <!-- Municipios involucrados -->
                <div class="form-group">
                    <label class="font-weight-bold">Municipio</label>
                    <select id="municipioEspecial" class="form-control">
                        <option value="">Todos los municipios</option>
                    </select>
                </div>

                <!-- Filtro -->
                <div class="form-group" hidden>
                    <label>Filtrar por tipo</label>
                    <select id="filtro_tipo" class="form-control">
                        <option value="">Todos</option>
                        <option value="2">Personalizados</option>
                        <option value="3">Recolecciones</option>
                    </select>
                </div>
                <button class="btn btn-sm btn-outline-secondary mb-2" id="selectAllEspeciales">
                    Seleccionar / Quitar todos
                </button>
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th style="width: 40px;"></th>
                            <th>ID</th>
                            <th>Vendedor</th>
                            <th>Cliente</th>
                            <th>Destino personalizado</th>
                            <th>Monto</th>
                        </tr>
                    </thead>
                    <tbody id="tablaEspeciales">
                        <!-- se llena con JS -->
                    </tbody>
                </table>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" id="agregarEspeciales">Agregar Seleccionados</button>
                <button class="btn btn-light" data-dismiss="modal">Cerrar</button>
            </div>

        </div>
    </div>
</div>

<!-- ============================================================
                 MODAL 3 – PAQUETES EN ESTATUS 3 (SIN FECHA)
=============================================================== -->
<div class="modal fade" id="modalPendientes3" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Paquetes pendientes de recolecta</h5>
                <button class="close text-white" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">

                <button class="btn btn-sm btn-outline-success mb-2" id="selectAllPendientes3">
                    Seleccionar / Quitar todos
                </button>

                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th style="width: 40px;"></th>
                            <th>ID</th>
                            <th>Vendedor</th>
                            <th>Cliente</th>
                            <th>Destino</th>
                            <th>Monto</th>
                        </tr>
                    </thead>
                    <tbody id="tablaPendientes3">
                        <!-- Se llena con JS -->
                    </tbody>
                </table>
            </div>

            <div class="modal-footer">
                <button class="btn btn-success" id="agregarPendientes3">Agregar Seleccionados</button>
                <button class="btn btn-light" data-dismiss="modal">Cerrar</button>
            </div>

        </div>
    </div>
</div>

<?= $this->endSection() ?>