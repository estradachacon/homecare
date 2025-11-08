<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between">
                <h5 class=" header-title mb-0">Registrar nuevo paquete</h5>
                <a href="<?= base_url('packages') ?>" class="btn btn-light btn-sm">Volver a la lista</a>
            </div>
            <div class="card-body">
                <form action="<?= base_url('packages/store') ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>

                    <div class="row g-3">
                        <!-- Vendedor -->
                        <div class="col-md-6">
                            <label class="form-label">Vendedor</label>
                            <input type="text" name="vendedor" class="form-control" required>
                        </div>

                        <!-- Cliente -->
                        <div class="col-md-6">
                            <label class="form-label">Cliente</label>
                            <input type="text" name="cliente" class="form-control" required>
                        </div>

                        <!-- Tipo de servicio -->
                        <div class="col-md-6">
                            <label class="form-label">Tipo de servicio</label>
                            <select name="tipo_servicio" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <option value="Entrega a domicilio">Entrega a domicilio</option>
                                <option value="Retiro en sucursal">Retiro en sucursal</option>
                                <option value="Envío express">Envío express</option>
                            </select>
                        </div>

                        <!-- Punto de retiro -->
                        <div class="col-md-6">
                            <label class="form-label">Retiro del paquete</label>
                            <input type="text" name="retiro_paquete" class="form-control" placeholder="Lugar de recogida" required>
                        </div>

                        <!-- Destino -->
                        <div class="col-md-6">
                            <label class="form-label">Destino</label>
                            <input type="text" name="destino" class="form-control" placeholder="Ciudad o sucursal destino" required>
                        </div>

                        <!-- Punto fijo -->
                        <div class="col-md-6">
                            <label class="form-label">Punto fijo</label>
                            <select name="id_puntofijo" class="form-select">
                                <option value="">Seleccione un punto fijo</option>
                                <!-- Llenar dinámicamente -->
                            </select>
                        </div>

                        <!-- Dirección -->
                        <div class="col-12">
                            <label class="form-label">Dirección</label>
                            <textarea name="direccion" class="form-control" rows="2" required></textarea>
                        </div>

                        <!-- Fechas -->
                        <div class="col-md-6">
                            <label class="form-label">Fecha de ingreso</label>
                            <input type="date" name="fecha_ingreso" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha de entrega</label>
                            <input type="date" name="fecha_entrega" class="form-control">
                        </div>

                        <!-- Teléfonos -->
                        <div class="col-md-6">
                            <label class="form-label">Teléfono primario</label>
                            <input type="tel" name="tel_primario" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono opcional</label>
                            <input type="tel" name="tel_opcional" class="form-control">
                        </div>

                        <!-- Fletes -->
                        <div class="col-md-4">
                            <label class="form-label">Flete total ($)</label>
                            <input type="number" step="0.01" name="flete_total" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Flete pagado ($)</label>
                            <input type="number" step="0.01" name="flete_pagado" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Flete pendiente ($)</label>
                            <input type="number" step="0.01" name="flete_pendiente" class="form-control" readonly>
                        </div>

                        <!-- Monto declarado -->
                        <div class="col-md-6">
                            <label class="form-label">Monto declarado ($)</label>
                            <input type="number" step="0.01" name="monto" class="form-control">
                        </div>

                        <!-- Foto -->
                        <div class="col-md-6">
                            <label class="form-label">Foto del paquete</label>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                        </div>

                        <!-- Comentarios -->
                        <div class="col-12">
                            <label class="form-label">Comentarios</label>
                            <textarea name="comentarios" class="form-control" rows="2"></textarea>
                        </div>

                        <!-- Fragil -->
                        <div class="col-md-6">
                            <label class="form-label">¿Es frágil?</label>
                            <select name="fragil" class="form-select" required>
                                <option value="0">No</option>
                                <option value="1">Sí</option>
                            </select>
                        </div>

                        <!-- Estatus -->
                        <div class="col-md-6">
                            <label class="form-label">Estatus</label>
                            <select name="estatus" class="form-select" required>
                                <option value="Pendiente">Pendiente</option>
                                <option value="En tránsito">En tránsito</option>
                                <option value="Entregado">Entregado</option>
                                <option value="Cancelado">Cancelado</option>
                            </select>
                        </div>

                        <!-- Usuario -->
                        <div class="col-md-12">
                            <input type="hidden" name="user_id" value="<?= session('user_id') ?>">
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-success px-4">Guardar paquete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Calcular flete pendiente en tiempo real
    document.addEventListener('input', function () {
        const total = parseFloat(document.querySelector('[name="flete_total"]').value) || 0;
        const pagado = parseFloat(document.querySelector('[name="flete_pagado"]').value) || 0;
        document.querySelector('[name="flete_pendiente"]').value = (total - pagado).toFixed(2);
    });
</script>

<?= $this->endSection() ?>
