<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    .badge-info {
        background-color: #d1ecf1;
        color: #0c5460;
        font-size: 0.8rem;
        padding: 5px 10px;
    }
</style>
<div class="row">
    <div class="col-md-12">

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= session()->getFlashdata('success') ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= session()->getFlashdata('error') ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header d-flex flex-wrap justify-content-between gap-2">

                <!-- 🧾 Info -->
                <div>
                    <h4 class="header-title mb-0">
                        Nota de Envío <strong><?= esc($consignacion->numero) ?></strong>
                    </h4>
                    <small class="text-muted">
                        Generada: <?= date('d/m/Y H:i', strtotime($consignacion->fecha_generacion)) ?>
                    </small>
                </div>

                <!-- 🎯 Acciones -->
                <div class="align-items-center gap-2">

                    <!-- Grupo secundario -->
                    <div class="btn-group btn-group-sm">
                        <a href="<?= base_url('consignaciones/' . $consignacion->id . '/imprimir') ?>"
                            target="_blank"
                            class="btn btn-outline-secondary">
                            <i class="fa-solid fa-print me-1"></i> Imprimir
                        </a>

                        <a href="<?= base_url('consignaciones') ?>"
                            class="btn btn-outline-secondary">
                            <i class="fa-solid fa-arrow-left me-1"></i> Volver
                        </a>
                    </div>

                    <!-- Acciones importantes -->
                    <?php if ($consignacion->estado === 'abierta' && tienePermiso('cerrar_consignaciones')): ?>
                        <a href="<?= base_url('consignaciones/' . $consignacion->id . '/cerrar') ?>"
                            class="btn btn-warning btn-sm px-3">
                            <i class="fa-solid fa-lock me-1"></i> Cerrar
                        </a>
                    <?php endif; ?>

                    <?php if ($consignacion->estado === 'abierta' && tienePermiso('anular_consignaciones')): ?>
                        <button class="btn btn-danger btn-sm px-3" id="btnAnular">
                            <i class="fa-solid fa-ban me-1"></i> Anular
                        </button>
                    <?php endif; ?>

                </div>
            </div>

            <div class="card-body">
                <!-- Estado badge -->
                <div class="mb-3">
                    <?php if ($consignacion->estado === 'abierta'): ?>
                        <span class="badge bg-success fs-6 text-white">ABIERTA</span>
                    <?php elseif ($consignacion->estado === 'cerrada'): ?>
                        <span class="badge bg-secondary fs-6 text-white">CERRADA</span>
                    <?php else: ?>
                        <span class="badge bg-danger fs-6 text-white">ANULADA</span>
                    <?php endif; ?>
                </div>

                <!-- Info principal -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <p class="mb-1 text-muted small">Vendedor / Representante</p>
                        <p class="fw-bold"><?= esc($consignacion->vendedor_nombre) ?></p>
                    </div>
                    <div class="col-md-3">
                        <p class="mb-1 text-muted small">Nombre / Referencia</p>
                        <p class="fw-bold"><?= esc($consignacion->nombre ?: '—') ?></p>
                    </div>
                    <div class="col-md-3">
                        <p class="mb-1 text-muted small">Fecha</p>
                        <p class="fw-bold"><?= date('d/m/Y', strtotime($consignacion->fecha)) ?></p>
                    </div>
                    <div class="col-md-3">
                        <p class="mb-1 text-muted small">Hora</p>
                        <p class="fw-bold"><?= $consignacion->hora ? substr($consignacion->hora, 0, 5) : '—' ?></p>
                    </div>
                </div>

                <?php if ($consignacion->concepto): ?>
                    <div class="mb-3">
                        <p class="mb-1 text-muted small">Concepto</p>
                        <p><?= esc($consignacion->concepto) ?></p>
                    </div>
                <?php endif; ?>

                <!-- Tabla productos -->
                <h6>Productos consignados</h6>
                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">Precio Unit.</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalles as $d): ?>
                                <tr>
                                    <td><?= esc($d->producto_codigo) ?></td>
                                    <td><?= esc($d->producto_nombre) ?></td>
                                    <td class="text-center"><?= number_format($d->cantidad, 2) ?></td>
                                    <td class="text-end">$<?= number_format($d->precio_unitario, 2) ?></td>
                                    <td class="text-end">$<?= number_format($d->subtotal, 2) ?></td>
                                </tr>

                                <?php if (!empty($facturasPorDetalle[$d->id])): ?>
                                    <tr>
                                        <td colspan="5" class="bg-light">
                                            <small class="text-muted">Facturas:</small><br>

                                            <?php foreach ($facturasPorDetalle[$d->id] as $f): ?>
                                                <span class="badge badge-info mr-1">
                                                    <?= esc($f->numero_control) ?>
                                                </span>
                                            <?php endforeach; ?>

                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <?php if (isset($mapCierreDetalle[$d->id])):
                                    $cd = $mapCierreDetalle[$d->id];
                                ?>

                                    <?php if (
                                        $cd->cantidad_devuelta > 0 ||
                                        $cd->cantidad_stock_vendedor > 0 ||
                                        $cd->doc_devolucion ||
                                        $cd->comentario_devolucion
                                    ): ?>
                                        <tr>
                                            <td colspan="5" class="bg-light">

                                                <small class="text-muted">Resultado del cierre:</small><br>

                                                <?php if ($cd->cantidad_devuelta > 0): ?>
                                                    <span class="badge badge-warning mr-1">
                                                        Devuelto: <?= number_format($cd->cantidad_devuelta, 2) ?>
                                                    </span>
                                                <?php endif; ?>

                                                <?php if ($cd->cantidad_stock_vendedor > 0): ?>
                                                    <span class="badge badge-info mr-1">
                                                        Stock vendedor: <?= number_format($cd->cantidad_stock_vendedor, 2) ?>
                                                    </span>
                                                <?php endif; ?>

                                                <?php if ($cd->doc_devolucion): ?>
                                                    <div class="mt-1">
                                                        <strong>Doc devolución:</strong>
                                                        <?= esc($cd->doc_devolucion) ?>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if ($cd->comentario_devolucion): ?>
                                                    <div>
                                                        <strong>Comentario:</strong>
                                                        <?= esc($cd->comentario_devolucion) ?>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if ($cd->foto_devolucion): ?>
                                                    <div class="mt-2">
                                                        <img src="<?= base_url('uploads/devoluciones/' . $cd->foto_devolucion) ?>"
                                                            style="max-height: 80px; border-radius: 5px;">
                                                    </div>
                                                <?php endif; ?>

                                            </td>
                                        </tr>
                                    <?php endif; ?>

                                <?php endif; ?>
                            <?php endforeach; ?>

                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end fw-bold">Subtotal Total:</td>
                                <td class="text-end fw-bold text-primary">$<?= number_format($consignacion->subtotal, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <?php if ($consignacion->observaciones): ?>
                    <div class="border rounded p-2 bg-light">
                        <small class="text-muted">Observaciones:</small>
                        <p class="mb-0"><?= esc($consignacion->observaciones) ?></p>
                    </div>
                <?php endif; ?>

                <!-- Datos del cierre -->
                <?php if ($cierre): ?>
                    <hr>
                    <h6>Datos del Cierre</h6>
                    <p class="text-muted small">Cerrada el: <?= date('d/m/Y H:i', strtotime($cierre->created_at)) ?></p>
                    <?php if ($cierre->observaciones): ?>
                        <p><?= esc($cierre->observaciones) ?></p>
                    <?php endif; ?>
                    <?php if ($cierre->nueva_consignacion_id): ?>
                        <p>
                            Nota de traslado generada:
                            <a href="<?= base_url('consignaciones/' . $cierre->nueva_consignacion_id) ?>">
                                Ver nueva nota
                            </a>
                        </p>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($consignacion->anulada): ?>
                    <hr>
                    <div class="alert alert-danger">
                        Nota anulada el <?= date('d/m/Y H:i', strtotime($consignacion->fecha_anulacion)) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        <?php if ($consignacion->estado === 'abierta' && tienePermiso('anular_consignaciones')): ?>
            document.getElementById('btnAnular')?.addEventListener('click', function() {
                Swal.fire({
                    title: '¿Anular nota?',
                    html: '¿Desea anular la nota <strong><?= esc($consignacion->numero) ?></strong>? Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, anular',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#dc3545',
                }).then(result => {
                    if (!result.isConfirmed) return;

                    fetch('<?= base_url('consignaciones/' . $consignacion->id . '/anular') ?>', {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Anulada', data.message, 'success').then(() => location.reload());
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        });
                });
            });
        <?php endif; ?>
    </script>

    <?= $this->endSection() ?>