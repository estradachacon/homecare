<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<script src="/backend/assets/js/scripts_newtracking.js"></script>
<div class="row">
    <div class="col-md-12">

        <!-- ===============================
          ENCABEZADO DEL TRACKING
    ================================ -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Nuevo Tracking</h5>
            </div>
            <div class="card-body">

                <div class="form-row">
                    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                    <div class="form-group col-md-4">
                        <label>Asignar Motorista</label>
                        <select id="motorista" class="form-control">
                            <option value="">Seleccione</option>
                            <?php foreach ($motoristas as $m): ?>
                                <option value="<?= $m['id'] ?>"><?= $m['user_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group col-md-4">
                        <label>Asignar fecha de seguimiento</label>
                        <input type="text" id="fecha_tracking" class="form-control">

                    </div>
                </div>

                <hr>

                <button class="btn btn-primary" data-toggle="modal" data-target="#modalRutas">
                    <i class="fa fa-route"></i> Agregar paquetes por ruta
                </button>

                <button class="btn btn-secondary ml-2" data-toggle="modal" data-target="#modalEspeciales">
                    <i class="fa fa-box"></i> Agregar personalizados / recolecciones
                </button>

            </div>
        </div>

        <!-- ===============================
            TABLA DE PACKETS SELECCIONADOS
    ================================ -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white">
                <h6 class="mb-0">Paquetes Seleccionados</h6>
            </div>
            <div class="card-body table-responsive">

                <table class="table table-bordered table-sm" id="tablaTracking">
                    <thead>
                        <tr>
                            <th>Tipo</th>
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

            </div>
        </div>

        <!-- ===============================
                BOTÓN FINAL
    ================================ -->
        <div class="text-right mb-5">
            <button id="btnGuardar" class="btn btn-success btn-lg">
                <i class="fa fa-save"></i> Guardar Tracking
            </button>
        </div>
    </div>
</div>



<!-- ============================================================
                      MODAL 1 – PAQUETES POR RUTA
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
                        <label>Ruta</label>
                        <select id="ruta_select" class="form-control">
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
                <h5 class="modal-title">Agregar personalizados y recolecciones</h5>
                <button class="close text-white" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">

                <!-- Filtro -->
                <div class="form-group">
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
                            <th>Cliente</th>
                            <th>Destino / Recolección</th>
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
<?= $this->endSection() ?>