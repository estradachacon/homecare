<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
    .consignaciones-filtros {
        background: #f8f9fa;
        border: 1px solid #e3e6ea;
        border-radius: .25rem;
        padding: .85rem;
    }

    .consignaciones-filtros label {
        color: #495057;
        font-size: .74rem;
        font-weight: 600;
        margin-bottom: .25rem;
    }

    .consignaciones-filtros .form-control,
    .consignaciones-filtros .btn {
        height: calc(1.5em + .5rem + 2px);
    }

    .consignaciones-filtros .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }

    .consignaciones-filtros .acciones-filtro {
        display: flex;
    }

    .consignaciones-filtros .acciones-filtro .btn + .btn {
        margin-left: .35rem;
    }

    .estado-consignacion-badge {
        font-size: .72rem;
        font-weight: 600;
        line-height: 1;
        padding: .28rem .48rem;
    }

    .estado-aprobacion-badge {
        display: inline-block;
        font-size: .68rem;
        font-weight: 600;
        margin-top: .22rem;
        padding: .24rem .42rem;
    }

    @media (max-width: 767.98px) {
        .consignaciones-filtros .acciones-filtro {
            flex-direction: column;
        }

        .consignaciones-filtros .acciones-filtro .btn {
            width: 100%;
        }

        .consignaciones-filtros .acciones-filtro .btn + .btn {
            margin-left: 0;
            margin-top: .35rem;
        }
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
            <div class="card-header d-flex justify-content-between">
                <h4 class="header-title mb-0">Notas de Envío</h4>
                <?php if (tienePermiso('crear_consignaciones')): ?>
                    <a href="<?= base_url('consignaciones/crear') ?>" class="btn btn-success btn-sm">
                        <i class="fa-solid fa-plus"></i> Nueva Nota
                    </a>
                <?php endif; ?>
            </div>

            <div class="card-body">
                <!-- Filtros -->
                <form method="GET" action="<?= base_url('consignaciones') ?>" class="consignaciones-filtros mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fa-solid fa-filter text-primary mr-2"></i>
                        <span class="font-weight-bold text-muted small text-uppercase">Filtros</span>
                    </div>
                    <div class="row no-gutters align-items-end">
                        <?php if ($puede_ver_todos): ?>
                        <div class="col-lg-3 col-md-6 pr-md-2 mb-2">
                            <label for="filtro_vendedor">Vendedor</label>
                            <select name="vendedor_id" id="filtro_vendedor" class="form-control form-control-sm">
                                <option value="">Todos los vendedores</option>
                                <?php foreach ($vendedores as $v): ?>
                                    <option value="<?= $v->id ?>" <?= ($filtros['vendedor_id'] == $v->id) ? 'selected' : '' ?>>
                                        <?= esc($v->seller) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php else: ?>
                        <div class="col-lg-3 col-md-6 pr-md-2 mb-2 d-flex align-items-end">
                            <div class="form-control form-control-sm bg-light text-muted d-flex align-items-center" style="height:calc(1.5em + .5rem + 2px);">
                                <i class="fa-solid fa-user-tie mr-1 text-secondary"></i>
                                <?= esc($seller_usuario->seller ?? 'Sin vendedor asignado') ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <div class="col-lg-2 col-md-6 pr-lg-2 mb-2">
                            <label for="filtro_estado">Estado</label>
                            <select name="estado" id="filtro_estado" class="form-control form-control-sm">
                                <option value="">Todos los estados</option>
                                <option value="abierta" <?= ($filtros['estado'] === 'abierta')  ? 'selected' : '' ?>>Abierta</option>
                                <option value="cerrada" <?= ($filtros['estado'] === 'cerrada')  ? 'selected' : '' ?>>Cerrada</option>
                                <option value="anulada" <?= ($filtros['estado'] === 'anulada')  ? 'selected' : '' ?>>Anulada</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6 pr-md-2 mb-2">
                            <label for="filtro_fecha_inicio">Desde</label>
                            <input type="date" name="fecha_inicio" id="filtro_fecha_inicio" value="<?= esc($filtros['fecha_inicio'] ?? '') ?>" class="form-control form-control-sm">
                        </div>
                        <div class="col-lg-2 col-md-6 pr-lg-2 mb-2">
                            <label for="filtro_fecha_fin">Hasta</label>
                            <input type="date" name="fecha_fin" id="filtro_fecha_fin" value="<?= esc($filtros['fecha_fin'] ?? '') ?>" class="form-control form-control-sm">
                        </div>
                        <div class="col-lg-2 col-md-6 pr-md-2 mb-2">
                            <label for="filtro_lotes">Lotes</label>
                            <select name="lote_estado" id="filtro_lotes" class="form-control form-control-sm">
                                <option value="">Estado de lotes</option>
                                <option value="sin_autorizar"  <?= ($filtros['lote_estado'] === 'sin_autorizar')  ? 'selected' : '' ?>>Sin autorizar</option>
                                <option value="pendiente_lotes" <?= ($filtros['lote_estado'] === 'pendiente_lotes') ? 'selected' : '' ?>>Pendiente asignación</option>
                                <option value="lotes_ok"       <?= ($filtros['lote_estado'] === 'lotes_ok')       ? 'selected' : '' ?>>Lotes asignados</option>
                            </select>
                        </div>
                        <div class="col-lg-1 col-md-6 mb-2">
                            <label class="d-none d-lg-block">&nbsp;</label>
                            <div class="acciones-filtro">
                                <button type="submit" class="btn btn-primary btn-sm" title="Filtrar">
                                    <i class="fa-solid fa-filter"></i>
                                </button>
                                <a href="<?= base_url('consignaciones') ?>" class="btn btn-outline-secondary btn-sm" title="Limpiar">
                                    <i class="fa-solid fa-times"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Número</th>
                                <th>Vendedor</th>
                                <th>Nombre</th>
                                <th>Fecha</th>
                                <th class="text-end">Subtotal</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Lotes</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($consignaciones)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        No hay notas de envío registradas.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($consignaciones as $c): ?>
                                    <tr>
                                        <td><?= $c->id ?></td>
                                        <td>
                                            <a href="<?= base_url('consignaciones/' . $c->id) ?>">
                                                <strong><?= esc($c->numero) ?></strong>
                                            </a>
                                        </td>
                                        <td><?= esc($c->vendedor_nombre) ?></td>
                                        <td><?= esc($c->nombre) ?></td>
                                        <td><?= date('d/m/Y', strtotime($c->fecha)) ?></td>
                                        <td class="text-end">$<?= number_format($c->subtotal, 2) ?></td>
                                        <td class="text-center">
                                            <?php if ($c->estado === 'abierta'): ?>
                                                <span class="badge badge-success estado-consignacion-badge">Abierta</span>
                                            <?php elseif ($c->estado === 'cerrada'): ?>
                                                <span class="badge badge-secondary estado-consignacion-badge">Cerrada</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger estado-consignacion-badge">Anulada</span>
                                            <?php endif; ?>
                                            <?php
                                                $apEst = $c->aprobacion_estado ?? 'pendiente';
                                                $apBadge = [
                                                    'aprobada'  => ['badge-success', 'Aprobada'],
                                                    'rechazada' => ['badge-danger', 'Rechazada'],
                                                    'pendiente' => ['badge-warning text-dark', 'Pendiente aprobación'],
                                                ][$apEst] ?? ['badge-warning text-dark', ucfirst($apEst)];
                                            ?>
                                            <br>
                                            <span class="badge <?= $apBadge[0] ?> estado-aprobacion-badge"
                                                  title="<?= $apEst === 'rechazada' ? esc($c->rechazo_motivo ?? 'Nota rechazada') : '' ?>">
                                                <?= esc($apBadge[1]) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($c->estado === 'abierta'): ?>
                                                <?php if (empty($c->lotes_autorizados_por)): ?>
                                                    <span class="badge badge-warning" title="Pendiente autorización de lotes">
                                                        <i class="fa-solid fa-clock"></i> Sin autorizar
                                                    </span>
                                                <?php elseif ((int)$c->lotes_asignados_count === 0): ?>
                                                    <span class="badge badge-info" title="Autorizado — sin lotes asignados aún">
                                                        <i class="fa-solid fa-boxes-stacked"></i> Pendiente lotes
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-success" title="<?= (int)$c->lotes_asignados_count ?> lote(s) asignado(s)">
                                                        <i class="fa-solid fa-check"></i> <?= (int)$c->lotes_asignados_count ?> lote(s)
                                                    </span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= base_url('consignaciones/' . $c->id) ?>" class="btn btn-info btn-sm" title="Ver">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                            <?php if ($c->estado === 'abierta' && tienePermiso('cerrar_consignaciones')): ?>
                                                <a href="<?= base_url('consignaciones/' . $c->id . '/cerrar') ?>" class="btn btn-warning btn-sm" title="Cerrar">
                                                    <i class="fa-solid fa-lock"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($c->estado === 'abierta' && tienePermiso('anular_consignaciones')): ?>
                                                <button class="btn btn-danger btn-sm btn-anular" data-id="<?= $c->id ?>" data-numero="<?= esc($c->numero) ?>" title="Anular">
                                                    <i class="fa-solid fa-ban"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?= $pager->links('default', 'bootstrap_full') ?>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $(document).on('click', '.btn-anular', function() {
            const id = $(this).data('id');
            const numero = $(this).data('numero');

            Swal.fire({
                title: '¿Anular nota?',
                html: `¿Está seguro de anular la nota <strong>${numero}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, anular',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc3545',
            }).then(result => {
                if (!result.isConfirmed) return;

                fetch(`<?= base_url('consignaciones') ?>/${id}/anular`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
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
    });
</script>

<?= $this->endSection() ?>
