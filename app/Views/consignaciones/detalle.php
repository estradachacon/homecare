<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    .badge-info {
        background-color: #d1ecf1;
        color: #0c5460;
        font-size: 0.8rem;
        padding: 5px 10px;
    }

    .badge-emergencia {
        background: #7b1a1a;
        color: #fff;
        font-size: .68rem;
        font-weight: 700;
        padding: .25rem .5rem;
        border-radius: .3rem;
        vertical-align: middle;
        letter-spacing: .03em;
    }

    .lotes-panel {
        background: #f8f9fa;
        border-left: 3px solid #0d6efd;
        padding: 8px 12px;
        margin-top: 6px;
        font-size: 12px;
    }

    .lotes-panel .lote-row {
        display: flex;
        gap: 8px;
        align-items: center;
        margin-bottom: 4px;
    }

    .table-productos-compacta td,
    .table-productos-compacta th {
        padding: .35rem .45rem !important;
        vertical-align: middle !important;
        font-size: 16px;
    }

    .table-productos-compacta .td-descripcion {
        max-width: 360px;
        white-space: normal;
        line-height: 1.2;
    }

    .table-productos-compacta .td-lotes {
        width: 90px !important;
        white-space: nowrap;
    }

    .table-productos-compacta .btn-xs {
        padding: .12rem .35rem;
        font-size: 16px;
        line-height: 1.2;
    }

    .lotes-wrap {
        gap: 6px;
    }

    .lote-chip {
        border: 1px solid #dee2e6;
        border-radius: 6px;
        background: #fff;
        padding: 4px 7px;
        font-size: 11px;
        line-height: 1.15;
        min-width: 135px;
    }

    .lote-chip span {
        display: block;
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
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-start flex-wrap">
                    <div>
                        <h4 class="header-title mb-0">Nota de Envío <strong><?= esc($consignacion->numero) ?></strong></h4>
                        <small class="text-muted d-block mb-2">Generada: <?= date('d/m/Y H:i', strtotime($consignacion->fecha_generacion)) ?></small>

                        <!-- Estados (badges) -->
                        <div class="mb-2">
                            <?php if (($consignacion->origen ?? 'normal') === 'emergencia'): ?>
                                <span class="badge-emergencia mr-1">
                                    <i class="fa-solid fa-bolt"></i> Stock de Emergencia
                                </span>
                            <?php endif; ?>
                            <?php if ($consignacion->estado === 'abierta'): ?>
                                <span class="badge badge-success">ACTIVA</span>
                            <?php elseif ($consignacion->estado === 'cerrada'): ?>
                                <span class="badge badge-secondary">CERRADA</span>
                            <?php else: ?>
                                <span class="badge badge-danger">ANULADA</span>
                            <?php endif; ?>

                            <?php
                                $apEst = $consignacion->aprobacion_estado ?? 'pendiente';
                                $apBadge = match ($apEst) {
                                    'aprobada'  => ['badge-success', 'fa-check-circle', 'Aprobada'],
                                    'rechazada' => ['badge-danger',  'fa-times-circle', 'Rechazada'],
                                    default     => ['badge-warning text-dark', 'fa-clock', 'Pendiente aprobación del despacho'],
                                };
                            ?>
                            <span class="badge <?= $apBadge[0] ?>">
                                <i class="fa-solid <?= $apBadge[1] ?> mr-1"></i><?= $apBadge[2] ?>
                            </span>
                        </div>

                        <!-- Botones de aprobación / autorización (debajo de los badges) -->
                        <?php
                            $puedeGestionarLotes = tienePermiso('gestionar_lotes_consignaciones');
                            $puedeEditarLotes    = $puedeGestionarLotes && !empty($consignacion->lotes_autorizados_por);
                        ?>
                        <div class="d-flex gap-2 mb-1">
                            <?php if ($consignacion->estado === 'abierta' && tienePermiso('aprobar_consignaciones')): ?>
                                <?php if ($apEst !== 'aprobada'): ?>
                                    <button class="btn btn-sm btn-success mr-1" id="btnAprobar">
                                        <i class="fa-solid fa-check mr-1"></i> Aprobar despacho
                                    </button>
                                <?php endif; ?>
                                <?php if ($apEst !== 'rechazada'): ?>
                                    <button class="btn btn-sm btn-outline-danger" id="btnRechazar">
                                        <i class="fa-solid fa-times mr-1"></i> Rechazar lotes
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if (!empty($consignacion->lotes_autorizados_por)): ?>
                                <span class="badge badge-success ml-1">
                                    <i class="fa-solid fa-unlock mr-1 mt-1"></i> Autorizado para selección de lotes
                                </span>
                            <?php elseif ($consignacion->estado === 'abierta' && tienePermiso('autorizar_lotes_consignacion')): ?>
                                <button class="btn btn-sm btn-warning ml-1" id="btnAutorizarLotes">
                                    <i class="fa-solid fa-unlock mr-1"></i> Autorizar edición de lotes
                                </button>
                            <?php elseif ($consignacion->estado === 'abierta' && $puedeGestionarLotes): ?>
                                <span class="badge badge-secondary ml-1">
                                    <i class="fa-solid fa-lock mr-1"></i> Lotes pendientes de autorización
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="text-right">
                        <div class="mb-2">
                            <a href="<?= base_url('consignaciones/' . $consignacion->id . '/imprimir') ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                                <i class="fa-solid fa-print mr-1"></i> Imprimir
                            </a>
                            <a href="<?= base_url('consignaciones') ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="fa-solid fa-arrow-left mr-1"></i> Volver
                            </a>
                        </div>

                        <div class="d-flex flex-wrap gap-2 justify-content-end">
                            <?php if ($consignacion->estado === 'abierta' && tienePermiso('crear_consignaciones')): ?>
                                <a href="<?= base_url('consignaciones/' . $consignacion->id . '/editar') ?>" class="btn btn-info btn-sm mr-1"> <i class="fa-solid fa-edit mr-1"></i> Editar</a>
                            <?php endif; ?>

                            <?php if ($consignacion->estado === 'abierta' && tienePermiso('cerrar_consignaciones')): ?>
                                <a href="<?= base_url('consignaciones/' . $consignacion->id . '/cerrar') ?>" class="btn btn-warning btn-sm mr-1"> <i class="fa-solid fa-lock mr-1"></i> Cerrar</a>
                            <?php endif; ?>

                            <?php if ($consignacion->estado === 'abierta' && tienePermiso('anular_consignaciones')): ?>
                                <button class="btn btn-danger btn-sm" id="btnAnular"> <i class="fa-solid fa-ban mr-1"></i> Anular</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">

                <?php if ($apEst === 'rechazada' && !empty($consignacion->rechazo_motivo)): ?>
                    <div class="alert alert-danger py-2 mb-3" style="font-size:13px;">
                        <i class="fa-solid fa-exclamation-triangle mr-1"></i>
                        <strong>Motivo de rechazo:</strong> <?= esc($consignacion->rechazo_motivo) ?>
                    </div>
                <?php endif; ?>

                <!-- Info principal -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <p class="mb-1 text-muted small">Vendedor / Representante</p>
                        <p class="font-weight-bold"><?= esc($consignacion->vendedor_nombre) ?></p>
                    </div>
                    <div class="col-md-3">
                        <p class="mb-1 text-muted small">Paciente</p>
                        <p class="font-weight-bold">
                            <?= esc($consignacion->paciente_nombre ?? $consignacion->nombre ?: '—') ?>
                        </p>
                    </div>
                    <div class="col-md-3">
                        <p class="mb-1 text-muted small">Fecha</p>
                        <p class="font-weight-bold"><?= date('d/m/Y', strtotime($consignacion->fecha)) ?></p>
                    </div>
                    <div class="col-md-3">
                        <p class="mb-1 text-muted small">Hora</p>
                        <p class="font-weight-bold"><?= $consignacion->hora ? substr($consignacion->hora, 0, 5) : '—' ?></p>
                    </div>
                </div>

                <?php if (!empty($consignacion->doctor_nombre) || !empty($consignacion->cliente_nombre)): ?>
                    <div class="row mb-3">
                        <?php if (!empty($consignacion->doctor_nombre)): ?>
                            <div class="col-md-4">
                                <p class="mb-1 text-muted small">Doctor</p>
                                <p class="font-weight-bold"><?= esc($consignacion->doctor_nombre) ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($consignacion->cliente_nombre)): ?>
                            <div class="col-md-4">
                                <p class="mb-1 text-muted small">Cliente a facturar</p>
                                <p class="font-weight-bold"><?= esc($consignacion->cliente_nombre) ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($consignacion->concepto)): ?>
                            <div class="col-md-4">
                                <p class="mb-1 text-muted small">Concepto</p>
                                <p class="font-weight-bold"><?= esc($consignacion->concepto) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php elseif ($consignacion->concepto): ?>
                    <div class="mb-3">
                        <p class="mb-1 text-muted small">Concepto</p>
                        <p><?= esc($consignacion->concepto) ?></p>
                    </div>
                <?php endif; ?>

                <!-- Tabla productos -->
                <h6>Productos consignados</h6>
                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm table-productos-compacta">
                        <thead class="thead-light">
                            <tr>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-right">Precio Unit.</th>
                                <th class="text-right">Subtotal</th>
                                <th class="text-center" style="width:120px">Lotes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalles as $d):
                                $lotesDetalle = $lotesPorDetalle[$d->id] ?? [];
                                $totalLotes   = count($lotesDetalle);
                            ?>
                                <tr id="fila-det-<?= $d->id ?>">
                                    <td><?= esc($d->producto_codigo) ?></td>
                                    <td class="td-descripcion"><?= esc($d->producto_nombre) ?></td>
                                    <td class="text-center"><?= number_format($d->cantidad, 2) ?></td>
                                    <td class="text-right">$<?= number_format($d->precio_unitario, 2) ?></td>
                                    <td class="text-right">$<?= number_format($d->subtotal, 2) ?></td>
                                    <td class="text-center">
                                        <!-- Toggle ver lotes -->
                                        <button class="btn btn-xs <?= $totalLotes ? 'btn-info' : 'btn-outline-secondary' ?> btn-toggle-lotes"
                                            data-target="lotes-panel-<?= $d->id ?>"
                                            title="<?= $totalLotes ? 'Ver lotes asignados' : 'Sin lotes asignados' ?>">
                                            <i class="fa-solid fa-boxes-stacked mr-1"></i><?= $totalLotes ?>
                                        </button>
                                        <?php if ($consignacion->estado === 'abierta'): ?>
                                            <?php if ($puedeEditarLotes): ?>
                                                <button class="btn btn-xs btn-outline-primary btn-lotes mt-1"
                                                    data-id="<?= $d->id ?>"
                                                    data-producto-id="<?= $d->producto_id ?>"
                                                    data-nombre="<?= esc($d->producto_nombre) ?>"
                                                    data-cantidad="<?= $d->cantidad ?>"
                                                    title="Editar lotes">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-xs btn-outline-secondary mt-1"
                                                    disabled title="Requiere autorización para editar lotes">
                                                    <i class="fa-solid fa-lock"></i>
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>

                                <!-- Lotes panel (pre-cargado del servidor) -->
                                <tr id="lotes-panel-<?= $d->id ?>" style="display:none;">
                                    <td colspan="6" class="p-0">
                                        <div class="lotes-panel">
                                            <?php if (empty($lotesDetalle)): ?>
                                                <em class="text-muted">Sin lotes asignados.</em>
                                            <?php else: ?>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <?php foreach ($lotesDetalle as $lote): ?>
                                                        <div class="border rounded px-2 py-1 bg-white" style="font-size:11px; min-width:160px;">
                                                            <div class="fw-bold text-primary"><?= esc($lote->numero_lote) ?></div>
                                                            <?php if (!empty($lote->fecha_vencimiento)): ?>
                                                                <div class="text-muted">Vence: <?= esc($lote->fecha_vencimiento) ?></div>
                                                            <?php endif; ?>
                                                            <?php if (!empty($lote->manufactura)): ?>
                                                                <div class="text-muted">Mfr: <?= esc($lote->manufactura) ?></div>
                                                            <?php endif; ?>
                                                            <div>Cant: <strong><?= number_format($lote->cantidad, 2) ?></strong></div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>

                                <?php if (!empty($facturasPorDetalle[$d->id])): ?>
                                    <tr>
                                        <td colspan="6" class="bg-light">
                                            <small class="text-muted">Facturas:</small><br>
                                            <?php foreach ($facturasPorDetalle[$d->id] as $f): ?>
                                                <span class="badge badge-info mr-1"><?= esc($f->numero_control) ?></span>
                                            <?php endforeach; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>

                                <?php if (isset($mapCierreDetalle[$d->id])):
                                    $cd = $mapCierreDetalle[$d->id];
                                    $lotesCierre = $lotesCierrePorDetalle[$d->id] ?? [];
                                ?>
                                    <?php if ($cd->cantidad_facturada > 0 || $cd->cantidad_devuelta > 0 || $cd->cantidad_stock_vendedor > 0 || $cd->doc_devolucion || $cd->comentario_devolucion): ?>
                                        <tr>
                                            <td colspan="6" class="bg-light">
                                                <small class="text-muted">Resultado del cierre:</small><br>
                                                <?php if ($cd->cantidad_facturada > 0): ?>
                                                    <span class="badge badge-success mr-1">Facturado: <?= number_format($cd->cantidad_facturada, 2) ?></span>
                                                <?php endif; ?>
                                                <?php if ($cd->cantidad_devuelta > 0): ?>
                                                    <span class="badge badge-warning mr-1">Devuelto: <?= number_format($cd->cantidad_devuelta, 2) ?></span>
                                                <?php endif; ?>
                                                <?php if ($cd->cantidad_stock_vendedor > 0): ?>
                                                    <span class="badge badge-info mr-1">Stock vendedor: <?= number_format($cd->cantidad_stock_vendedor, 2) ?></span>
                                                <?php endif; ?>
                                                <?php if ($cd->doc_devolucion): ?>
                                                    <div class="mt-1"><strong>Doc devolución:</strong> <?= esc($cd->doc_devolucion) ?></div>
                                                <?php endif; ?>
                                                <?php if ($cd->comentario_devolucion): ?>
                                                    <div><strong>Comentario:</strong> <?= esc($cd->comentario_devolucion) ?></div>
                                                <?php endif; ?>
                                                <?php if ($cd->foto_devolucion): ?>
                                                    <div class="mt-2">
                                                        <img src="<?= base_url('upload/devoluciones/' . $cd->foto_devolucion) ?>"
                                                            style="max-height:80px; border-radius:5px;">
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (!empty($lotesCierre)): ?>
                                                    <div class="mt-2">
                                                        <?php foreach (['facturado' => 'Lotes facturados', 'stock_vendedor' => 'Lotes stock vendedor'] as $tipo => $titulo): ?>
                                                            <?php if (!empty($lotesCierre[$tipo])): ?>
                                                                <div class="small mt-1">
                                                                    <strong><?= esc($titulo) ?>:</strong>
                                                                    <?php foreach ($lotesCierre[$tipo] as $loteCierre): ?>
                                                                        <span class="badge badge-light border mr-1">
                                                                            <?= esc($loteCierre->numero_lote ?? 'Lote') ?>:
                                                                            <?= number_format($loteCierre->cantidad, 2) ?>
                                                                        </span>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
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
                                <td colspan="5" class="text-right font-weight-bold">Subtotal Total:</td>
                                <td class="text-right font-weight-bold text-primary">$<?= number_format($consignacion->subtotal, 2) ?></td>
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
                            <a href="<?= base_url('consignaciones/' . $cierre->nueva_consignacion_id) ?>">Ver nueva nota</a>
                        </p>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($consignacion->anulada): ?>
                    <hr>
                    <div class="alert alert-danger">
                        Nota anulada el <?= date('d/m/Y H:i', strtotime($consignacion->fecha_anulacion)) ?>
                    </div>
                <?php endif; ?>

                <!-- Notas de Pedido asociadas -->
                <?php if (!empty($notasPedido)): ?>
                    <hr>
                    <?php
                        $npVivas     = array_filter($notasPedido, fn($np) => $np->estado === 'pendiente');
                        $npCerradas  = array_filter($notasPedido, fn($np) => $np->estado !== 'pendiente');
                        $todasCerradas = empty($npVivas);
                    ?>
                    <div class="d-flex justify-content-between mb-2">
                        <h6 class="mb-0">
                            <i class="fa-solid fa-file-invoice mr-1 text-primary"></i>
                            Notas de Pedido asociadas
                            <span class="badge badge-primary ml-1"><?= count($notasPedido) ?></span>
                        </h6>
                        <?php if ($todasCerradas): ?>
                            <span class="badge badge-success" style="font-size:.75rem;">
                                <i class="fa-solid fa-check mr-1"></i> Todas facturadas — apta para cerrar
                            </span>
                        <?php else: ?>
                            <span class="badge badge-warning text-dark" style="font-size:.75rem;">
                                <i class="fa-solid fa-clock mr-1"></i> <?= count($npVivas) ?> pendiente<?= count($npVivas) !== 1 ? 's' : '' ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0" style="font-size:.82rem;">
                            <thead class="thead-light">
                                <tr>
                                    <th>NP</th>
                                    <th>Cliente</th>
                                    <th>Documento</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($notasPedido as $np): ?>
                                    <?php $esPendiente = $np->estado === 'pendiente'; ?>
                                    <tr class="<?= $esPendiente ? '' : 'text-muted' ?>">
                                        <td>
                                            <a href="<?= base_url('pedidos/' . $np->id) ?>" target="_blank">
                                                <strong><?= esc($np->numero) ?></strong>
                                            </a>
                                        </td>
                                        <td><?= esc($np->cliente_nombre) ?></td>
                                        <td><?= esc(ucfirst(str_replace('_', ' ', $np->tipo_documento))) ?></td>
                                        <td class="text-end">$<?= number_format($np->total, 2) ?></td>
                                        <td class="text-center">
                                            <?php if ($np->estado === 'pendiente'): ?>
                                                <span class="badge badge-warning text-dark">Pendiente</span>
                                            <?php elseif ($np->estado === 'facturada'): ?>
                                                <span class="badge badge-success">Facturada</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary"><?= esc(ucfirst($np->estado)) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><?= date('d/m/Y', strtotime($np->created_at)) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <!-- Historial de actividad -->
                <?php if (!empty($log)): ?>
                    <hr>
                    <div class="mb-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                            data-toggle="collapse" data-target="#logSection" aria-expanded="false">
                            <i class="fa-solid fa-history mr-1"></i>
                            Historial de actividad (<?= count($log) ?>)
                        </button>
                    </div>
                    <div class="collapse" id="logSection">
                        <div class="card card-body p-2" style="font-size:12px; max-height:280px; overflow-y:auto;">
                            <?php foreach ($log as $entry): ?>
                                <div class="d-flex align-items-start py-1 border-bottom">
                                    <small class="text-muted mr-3" style="white-space:nowrap; min-width:115px;">
                                        <?= date('d/m/Y H:i', strtotime($entry->created_at)) ?>
                                    </small>
                                    <div>
                                        <strong><?= esc($entry->user_nombre) ?></strong>
                                        <span class="text-muted"> — <?= esc($entry->accion) ?></span>
                                        <?php if (!empty($entry->detalle)): ?>
                                            <div class="text-muted"><?= esc($entry->detalle) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<!-- Modal lotes por detalle -->
<div class="modal fade" id="modalLotes" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lotes — <span id="modalLotesNombre"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modalDetalleId">
                <input type="hidden" id="modalProductoId">
                <input type="hidden" id="modalCantidadEsperada">

                <div class="alert alert-info py-2 mb-2">
                    Cantidad requerida:
                    <strong id="cantidadEsperadaText">0.00</strong>
                    |
                    Cantidad asignada:
                    <strong id="cantidadAsignadaText">0.00</strong>
                    |
                    Pendiente:
                    <strong id="cantidadPendienteText">0.00</strong>
                </div>
                <table class="table table-sm table-bordered" id="tblLotesModal">
                    <thead class="thead-light">
                        <tr>
                            <th>Lote</th>
                            <th style="width:130px">Cantidad</th>
                            <th style="width:50px"></th>
                        </tr>
                    </thead>
                    <tbody id="bodyLotesModal"></tbody>
                </table>

                <div class="d-flex gap-2 mt-1">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnAgregarLoteModal">
                        <i class="fa-solid fa-plus mr-1"></i> Agregar lote existente
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success" id="btnToggleCrearLote">
                        <i class="fa-solid fa-layer-group mr-1"></i> Crear nuevo lote
                    </button>
                </div>

                <!-- Panel crear nuevo lote (inline) -->
                <div id="panelCrearLote" style="display:none;" class="border rounded p-3 mt-3 bg-light">
                    <h6 class="mb-3 text-success">
                        <i class="fa-solid fa-layer-group mr-1"></i> Nuevo lote para este producto
                    </h6>
                    <div class="row g-2 mb-2">
                        <div class="col-md-4">
                            <label class="form-label small mb-1">Lote <span class="text-danger">*</span></label>
                            <input type="text" id="nuevoLoteNumero" class="form-control form-control-sm"
                                placeholder="Ej: L-2024-001">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1">Vencimiento</label>
                            <input type="text" id="nuevoLoteVence" class="form-control form-control-sm"
                                placeholder="Ej: Jun 2026, 12/2025…">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1">Manufactura</label>
                            <input type="text" id="nuevoLoteManufactura" class="form-control form-control-sm"
                                placeholder="Ej: Ene 2025, 01/2025…">
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-success" id="btnGuardarNuevoLote">
                            <i class="fa-solid fa-save mr-1"></i> Crear y agregar
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary" id="btnCancelarNuevoLote">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarLotesModal">
                    <i class="fa-solid fa-save mr-1"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    $(function() {
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

        // ── Anular ──────────────────────────────────────────────
        <?php if ($consignacion->estado === 'abierta' && tienePermiso('anular_consignaciones')): ?>
            $('#btnAnular').on('click', function() {
                Swal.fire({
                    title: '¿Anular nota?',
                    html: '¿Desea anular la nota <strong><?= esc($consignacion->numero) ?></strong>? Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, anular',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#dc3545',
                }).then(r => {
                    if (!r.isConfirmed) return;
                    fetch('<?= base_url('consignaciones/' . $consignacion->id . '/anular') ?>', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': CSRF
                        },
                    }).then(r => r.json()).then(data => {
                        if (data.success) Swal.fire('Anulada', data.message, 'success').then(() => location.reload());
                        else Swal.fire('Error', data.message, 'error');
                    });
                });
            });
        <?php endif; ?>

        // ── Aprobar ─────────────────────────────────────────────
        <?php if ($consignacion->estado === 'abierta' && tienePermiso('aprobar_consignaciones')): ?>
            $('#btnAprobar')?.on('click', function() {

                let hayError = false;

                <?php foreach ($detalles as $d): ?>
                    <?php $totalLotes = count($lotesPorDetalle[$d->id] ?? []); ?>

                    <?php if ($totalLotes === 0): ?>
                        hayError = true;
                    <?php endif; ?>

                <?php endforeach; ?>

                if (hayError) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Lotes incompletos',
                        text: 'Todos los productos deben tener lotes asignados antes de aprobar la nota.'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Aprobar nota',
                    text: '¿Confirma que la mercadería fue validada físicamente?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, aprobar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#198754',
                }).then(r => {
                    if (!r.isConfirmed) return;

                    fetch('<?= base_url('consignaciones/' . $consignacion->id . '/aprobar') ?>', {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': CSRF
                            },
                        })
                        .then(r => r.json())
                        .then(d => {
                            if (d.success) {
                                Swal.fire('Aprobada', d.message, 'success')
                                    .then(() => location.reload());
                            } else {
                                Swal.fire('Error', d.message, 'error');
                            }
                        });
                });
            });

            $('#btnRechazar')?.on('click', function() {
                Swal.fire({
                    title: 'Rechazar nota',
                    input: 'textarea',
                    inputLabel: 'Motivo del rechazo',
                    inputPlaceholder: 'Indique el motivo...',
                    inputAttributes: {
                        required: true
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Rechazar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#dc3545',
                    preConfirm: (val) => {
                        if (!val.trim()) {
                            Swal.showValidationMessage('Debe indicar un motivo.');
                            return false;
                        }
                        return val;
                    },
                }).then(r => {
                    if (!r.isConfirmed) return;
                    fetch('<?= base_url('consignaciones/' . $consignacion->id . '/rechazar') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': CSRF,
                        },
                        body: JSON.stringify({
                            motivo: r.value
                        }),
                    }).then(r => r.json()).then(d => {
                        if (d.success) Swal.fire('Rechazada', d.message, 'success').then(() => location.reload());
                        else Swal.fire('Error', d.message, 'error');
                    });
                });
            });
        <?php endif; ?>

        // ── Autorizar lotes ──────────────────────────────────────
        <?php if ($consignacion->estado === 'abierta' && tienePermiso('autorizar_lotes_consignacion')): ?>
            $('#btnAutorizarLotes')?.on('click', function() {
                Swal.fire({
                    title: 'Autorizar edición de lotes',
                    text: '¿Confirma que autoriza la edición de lotes para esta nota? Quedará registrado su nombre y la fecha.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, autorizar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#fd7e14',
                }).then(r => {
                    if (!r.isConfirmed) return;
                    fetch('<?= base_url('consignaciones/' . $consignacion->id . '/autorizar-lotes') ?>', {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CSRF },
                    }).then(r => r.json()).then(d => {
                        if (d.success) Swal.fire('Autorizado', d.message, 'success').then(() => location.reload());
                        else Swal.fire('Error', d.message, 'error');
                    });
                });
            });
        <?php endif; ?>

        // ── Toggle panel lotes (siempre activo) ──────────────────
        $(document).on('click', '.btn-toggle-lotes', function() {
            const targetId = $(this).data('target');
            $('#' + targetId).toggle();
        });

        // ── Lotes modal ──────────────────────────────────────────
        <?php if ($consignacion->estado === 'abierta'): ?>
            let loteSelectIdx = 0;

            function initLoteSelect(sel, productoId) {
                $(sel).select2({
                    dropdownParent: $('#modalLotes'),
                    language: 'es',
                    placeholder: 'Seleccionar lote...',
                    allowClear: true,
                    ajax: {
                        url: '<?= base_url('consignaciones/lotes-por-producto') ?>',
                        dataType: 'json',
                        delay: 200,
                        data: () => ({
                            producto_id: productoId
                        }),
                        processResults: d => ({
                            results: d.results
                        }),
                        cache: false,
                    },
                });
            }

            function agregarFilaLote(lote) {
                const idx = loteSelectIdx++;
                const html = `<tr class="fila-lote-modal">
                    <td>
                        <select class="form-control form-control-sm sel-lote" name="lotes[${idx}][lote_id]"></select>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm input-lote-cant"
                            name="lotes[${idx}][cantidad]" min="0.01" step="0.01" value="${lote ? lote.cantidad : 1}">
                    </td>
                    <td class="text-center td-lotes">
                        <button type="button" class="btn btn-xs btn-outline-danger btn-rm-lote"><i class="fa-solid fa-times"></i></button>
                    </td>
                </tr>`;
                const $tr = $(html);
                $('#bodyLotesModal').append($tr);

                const $sel = $tr.find('.sel-lote');
                const prodId = parseInt($('#modalProductoId').val());
                initLoteSelect($sel[0], prodId);

                if (lote && lote.lote_id) {
                    const opt = new Option(lote.numero_lote + (lote.fecha_vencimiento ? ' (vence: ' + lote.fecha_vencimiento + ')' : ''), lote.lote_id, true, true);
                    $sel.append(opt).trigger('change');
                }

                $tr.find('.btn-rm-lote').on('click', function() {
                    $tr.remove();
                    actualizarResumenLotes();
                });
            }

            $(document).on('click', '.btn-lotes', function() {
                const $btn = $(this);
                const detId = $btn.data('id');
                const prodId = $btn.data('producto-id');
                const nombre = $btn.data('nombre');
                const cantidad = parseFloat($btn.data('cantidad')) || 0;

                $('#modalDetalleId').val(detId);
                $('#modalProductoId').val(prodId);
                $('#modalCantidadEsperada').val(cantidad);
                $('#cantidadEsperadaText').text(cantidad.toFixed(2));
                actualizarResumenLotes();
                $('#modalLotesNombre').text(nombre);
                $('#bodyLotesModal').empty();
                loteSelectIdx = 0;
                $('#panelCrearLote').hide();
                $('#nuevoLoteNumero, #nuevoLoteVence, #nuevoLoteManufactura').val('');

                fetch('<?= base_url('consignaciones/detalle-lotes') ?>/' + detId)
                    .then(r => r.json())
                    .then(d => {
                        if (d.success && d.lotes.length) {
                            d.lotes.forEach(l => agregarFilaLote(l));
                        }
                        $('#modalLotes').modal('show');
                    });
            });

            $('#btnAgregarLoteModal').on('click', function() {
                agregarFilaLote(null);
            });

            // ── Toggle panel crear lote ──────────────────────────────
            $('#btnToggleCrearLote').on('click', function() {
                const $panel = $('#panelCrearLote');
                $panel.toggle();
                if ($panel.is(':visible')) {
                    $('#nuevoLoteNumero').focus();
                }
            });

            $('#btnCancelarNuevoLote').on('click', function() {
                $('#panelCrearLote').hide();
                $('#nuevoLoteNumero, #nuevoLoteVence, #nuevoLoteManufactura').val('');
            });

            $('#btnGuardarNuevoLote').on('click', function() {
                const numero = $('#nuevoLoteNumero').val().trim();
                if (!numero) {
                    $('#nuevoLoteNumero').addClass('is-invalid').focus();
                    return;
                }
                $('#nuevoLoteNumero').removeClass('is-invalid');

                const productoId = parseInt($('#modalProductoId').val());
                const fd = new FormData();
                fd.append('producto_id', productoId);
                fd.append('numero_lote', numero);
                fd.append('fecha_vencimiento', $('#nuevoLoteVence').val());
                fd.append('manufactura', $('#nuevoLoteManufactura').val());

                const $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin mr-1"></i> Creando...');

                fetch('<?= base_url('consignaciones/lotes/guardar') ?>', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': CSRF
                        },
                        body: fd,
                    })
                    .then(r => r.json())
                    .then(d => {
                        if (!d.success) {
                            Swal.fire('Error', d.message ?? 'No se pudo crear el lote.', 'error');
                            return;
                        }

                        // Fetch the fresh lot list to get the new ID
                        return fetch('<?= base_url('consignaciones/lotes-por-producto') ?>?producto_id=' + productoId)
                            .then(r => r.json())
                            .then(res => {
                                // Find the newly created lot (matches numero)
                                const nuevo = res.results?.find(l => l.text.startsWith(numero));
                                if (nuevo) {
                                    agregarFilaLoteConOpcion(nuevo.id, nuevo.text);
                                }
                                // Reset panel
                                $('#panelCrearLote').hide();
                                $('#nuevoLoteNumero, #nuevoLoteVence, #nuevoLoteManufactura').val('');
                            });
                    })
                    .catch(() => Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error'))
                    .finally(() => {
                        $btn.prop('disabled', false).html('<i class="fa-solid fa-save mr-1"></i> Crear y agregar');
                    });
            });

            // Agregar fila con opción ya conocida (no requiere AJAX al abrir el select)
            function agregarFilaLoteConOpcion(loteId, loteTexto) {
                const idx = loteSelectIdx++;
                const html = `<tr class="fila-lote-modal">
                        <td>
                            <select class="form-control form-control-sm sel-lote" name="lotes[${idx}][lote_id]"></select>
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm input-lote-cant"
                                name="lotes[${idx}][cantidad]" min="0.01" step="0.01" value="1">
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-xs btn-outline-danger btn-rm-lote"><i class="fa-solid fa-times"></i></button>
                        </td>
                    </tr>`;
                const $tr = $(html);
                $('#bodyLotesModal').append($tr);

                const $sel = $tr.find('.sel-lote');
                const prodId = parseInt($('#modalProductoId').val());
                initLoteSelect($sel[0], prodId);

                const opt = new Option(loteTexto, loteId, true, true);
                $sel.append(opt).trigger('change');

                $tr.find('.btn-rm-lote').on('click', function() {
                    $tr.remove();
                    actualizarResumenLotes();
                });
                actualizarResumenLotes();
            }

            function actualizarResumenLotes() {
                const esperada = parseFloat($('#modalCantidadEsperada').val()) || 0;
                let asignada = 0;

                $('#bodyLotesModal .input-lote-cant').each(function() {
                    asignada += parseFloat($(this).val()) || 0;
                });

                const pendiente = esperada - asignada;

                $('#cantidadAsignadaText').text(asignada.toFixed(2));
                $('#cantidadPendienteText').text(pendiente.toFixed(2));

                $('#cantidadPendienteText')
                    .toggleClass('text-success', Math.abs(pendiente) < 0.001)
                    .toggleClass('text-danger', Math.abs(pendiente) >= 0.001);
            }
            $(document).on('input', '.input-lote-cant', function() {
                actualizarResumenLotes();
            });

            function actualizarLotesEnPantalla(detId, lotes) {
                const total = lotes.length;

                const $fila = $('#fila-det-' + detId);
                const $btnToggle = $fila.find('.btn-toggle-lotes');

                $btnToggle
                    .removeClass('btn-outline-secondary btn-info')
                    .addClass(total > 0 ? 'btn-info' : 'btn-outline-secondary')
                    .html(`<i class="fa-solid fa-boxes-stacked mr-1"></i>${total}`)
                    .attr('title', total > 0 ? 'Ver lotes asignados' : 'Sin lotes asignados');

                let html = '';

                if (total === 0) {
                    html = `<em class="text-muted">Sin lotes asignados.</em>`;
                } else {
                    html = `<div class="d-flex flex-wrap lotes-wrap">`;

                    lotes.forEach(lote => {
                        html += `
                <div class="lote-chip">
                    <strong class="text-primary">${lote.numero_lote}</strong>
                    ${lote.fecha_vencimiento ? `<span>Vence: ${lote.fecha_vencimiento}</span>` : ''}
                    ${lote.manufactura ? `<span>Mfr: ${lote.manufactura}</span>` : ''}
                    <span>Cant: <b>${parseFloat(lote.cantidad).toFixed(2)}</b></span>
                </div>
            `;
                    });

                    html += `</div>`;
                }

                $('#lotes-panel-' + detId + ' .lotes-panel').html(html);
                $('#lotes-panel-' + detId).show();
            }
            $('#btnGuardarLotesModal').on('click', function() {
                const detId = $('#modalDetalleId').val();
                const lotes = [];
                let ok = true;

                $('#bodyLotesModal .fila-lote-modal').each(function() {
                    const loteId = $(this).find('.sel-lote').val();
                    const cant = parseFloat($(this).find('.input-lote-cant').val()) || 0;
                    if (!loteId || cant <= 0) {
                        ok = false;
                        return;
                    }
                    const loteText = $(this).find('.sel-lote option:selected').text();

                    lotes.push({
                        lote_id: parseInt(loteId),
                        numero_lote: loteText,
                        fecha_vencimiento: '',
                        manufactura: '',
                        cantidad: cant
                    });
                });

                if (!ok) {
                    Swal.fire('Datos incompletos', 'Seleccione lote y cantidad en todas las filas.', 'warning');
                    return;
                }
                const esperada = parseFloat($('#modalCantidadEsperada').val()) || 0;
                const asignada = lotes.reduce((sum, l) => sum + l.cantidad, 0);

                if (Math.abs(asignada - esperada) > 0.001) {
                    Swal.fire(
                        'Cantidad incorrecta',
                        `Debe asignar exactamente ${esperada.toFixed(2)} unidades. Actualmente asignó ${asignada.toFixed(2)}.`,
                        'warning'
                    );
                    return;
                }
                fetch('<?= base_url('consignaciones/detalle-lotes') ?>/' + detId + '/guardar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': CSRF,
                    },
                    body: JSON.stringify({
                        lotes
                    }),
                }).then(r => r.json()).then(d => {
                    if (d.success) {
                        actualizarLotesEnPantalla(detId, lotes);

                        $('#modalLotes').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: 'Lotes guardados',
                            timer: 1000,
                            showConfirmButton: false
                        }).then(() => {
                            // Recargar la página completa para que se muestre el log actualizado
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', d.message, 'error');
                    }
                });
            });
        <?php endif; ?>
    });
</script>

<?= $this->endSection() ?>
