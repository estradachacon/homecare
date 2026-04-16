<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    .icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }

    /* Opcional: mejorar texto secundario */
    .card-body .text-muted {
        font-size: 0.9rem;
    }
</style>
<div class="card border-0 shadow-sm">

    <!-- HEADER PRINCIPAL -->
    <div class="card-header bg-white">
        <h4 class="mb-0">
            <i class="fa-solid fa-book-open-reader me-2"></i>
            Contabilidad — Dashboard
        </h4>
        <small class="text-muted">Resumen del módulo contable</small>
    </div>

    <div class="card-body">

        <!-- TARJETAS RESUMEN -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">

                        <!-- ICONO -->
                        <div class="icon-box bg-primary bg-opacity-10 text-white mr-3">
                            <i class="fa-solid fa-sitemap"></i>
                        </div>

                        <!-- TEXTO -->
                        <div>
                            <div class="fw-bold fs-3"><?= $stats['total_cuentas'] ?></div>
                            <div class="text-muted">Cuentas Contables</div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">

                        <!-- ICONO -->
                        <div class="icon-box bg-primary bg-opacity-10 text-white mr-3">
                            <i class="fa-solid fa-calendar-check text-white"></i>
                        </div>

                        <div>
                            <div class="fw-bold fs-4"><?= $stats['total_periodos'] ?></div>
                            <div class="text-muted small">Períodos Registrados</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">

                        <!-- ICONO -->
                        <div class="icon-box bg-primary bg-opacity-10 text-white mr-3">
                            <i class="fa-solid fa-file-pen text-white"></i>
                        </div>

                        <div>
                            <div class="fw-bold fs-4"><?= $stats['asientos_borrador'] ?></div>
                            <div class="text-muted small">Asientos en Borrador</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">

                        <!-- ICONO -->
                        <div class="icon-box bg-primary bg-opacity-10 text-white mr-3">
                            <i class="fa-solid fa-check-double text-white"></i>
                        </div>
                        <div>
                            <div class="fw-bold fs-4"><?= $stats['asientos_aprobados'] ?></div>
                            <div class="text-muted small">Asientos Aprobados</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- PERÍODO ACTUAL -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white fw-semibold"><i class="fa-solid fa-calendar me-2 text-primary mr-1"></i>Período Actual</div>
                    <div class="card-body">
                        <?php if ($periodoActual): ?>
                            <?php
                            $meses = [
                                1 => 'Enero',
                                2 => 'Febrero',
                                3 => 'Marzo',
                                4 => 'Abril',
                                5 => 'Mayo',
                                6 => 'Junio',
                                7 => 'Julio',
                                8 => 'Agosto',
                                9 => 'Septiembre',
                                10 => 'Octubre',
                                11 => 'Noviembre',
                                12 => 'Diciembre'
                            ];
                            ?>
                            <h5 class="mb-1"><?= $meses[$periodoActual->mes] ?> <?= $periodoActual->anio ?></h5>
                            <span class="badge bg-success">ABIERTO</span>
                            <hr>
                            <div class="d-flex justify-content-between small">
                                <span>Asientos del período</span>
                                <strong><?= $stats['asientos_mes'] ?></strong>
                            </div>
                            <div class="d-flex justify-content-between small mt-1">
                                <span>Aprobados</span>
                                <strong class="text-success"><?= $stats['asientos_aprobados'] ?></strong>
                            </div>
                            <div class="d-flex justify-content-between small mt-1">
                                <span>Borradores</span>
                                <strong class="text-warning"><?= $stats['asientos_borrador'] ?></strong>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-3">
                                <i class="fa-solid fa-calendar-xmark fa-2x mb-2"></i>
                                <p class="mb-0 small">No hay período abierto</p>
                                <a href="<?= base_url('contabilidad/periodos') ?>" class="btn btn-sm btn-primary mt-2">Crear período</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- SALDOS POR TIPO -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white fw-semibold"><i class="fa-solid fa-scale-balanced me-2 text-success mr-1"></i>Saldos por Tipo (Período Actual)</div>
                    <div class="card-body p-0">
                        <?php if (!empty($saldosPorTipo)): ?>
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tipo</th>
                                        <th class="text-end">Saldo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $colores = ['ACTIVO' => 'primary', 'PASIVO' => 'danger', 'CAPITAL' => 'warning', 'INGRESO' => 'success', 'COSTO' => 'secondary', 'GASTO' => 'dark'];
                                    foreach ($saldosPorTipo as $tipo => $s): ?>
                                        <tr>
                                            <td><span class="badge bg-<?= $colores[$tipo] ?? 'secondary' ?>"><?= $tipo ?></span></td>
                                            <td class="text-end fw-semibold">$ <?= number_format($s->saldo_final, 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="text-center text-muted py-4 small">Sin movimientos en el período</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ÚLTIMOS ASIENTOS -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                        <span><i class="fa-solid fa-clock-rotate-left me-2 text-info mr-1"></i>Últimos Asientos</span>
                        <a href="<?= base_url('contabilidad/asientos') ?>" class="btn btn-sm btn-outline-secondary">Ver todos</a>
                    </div>
                    <div class="card-body p-0">
                        <?php if (!empty($ultimosAsientos)): ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($ultimosAsientos as $a): ?>
                                    <li class="list-group-item px-3 py-2 mr-1">
                                        <div class="d-flex justify-content-between">
                                            <span class="fw-semibold small">AST-<?= str_pad($a->numero_asiento, 5, '0', STR_PAD_LEFT) ?></span>
                                            <span class="badge bg-<?= $a->estado === 'APROBADO' ? 'success' : ($a->estado === 'BORRADOR' ? 'warning' : 'danger') ?>"><?= $a->estado ?></span>
                                        </div>
                                        <div class="text-muted" style="font-size:0.78rem"><?= esc(substr($a->descripcion, 0, 40)) ?>...</div>
                                        <div class="text-muted" style="font-size:0.75rem"><?= date('d/m/Y', strtotime($a->fecha)) ?> — $ <?= number_format($a->total_debe, 2) ?></div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="text-center text-muted py-4 small">Sin asientos recientes</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ACCESOS RÁPIDOS -->
        <div class="row g-3 mt-1">
            <div class="col-12 card-header">
                <h4 class="text-muted fw-semibold">ACCESOS RÁPIDOS</h4>
            </div>
            <?php
            $accesos = [
                ['url' => 'contabilidad/asientos/nuevo',          'icon' => 'fa-plus-circle',      'label' => 'Nuevo Asiento',       'color' => 'primary'],
                ['url' => 'contabilidad/plan-cuentas',             'icon' => 'fa-sitemap',           'label' => 'Plan de Cuentas',     'color' => 'success'],
                ['url' => 'contabilidad/periodos',                 'icon' => 'fa-calendar-days',     'label' => 'Períodos',            'color' => 'info'],
                ['url' => 'contabilidad/reportes/diario',          'icon' => 'fa-book',              'label' => 'Libro Diario',        'color' => 'warning'],
                ['url' => 'contabilidad/reportes/mayor',           'icon' => 'fa-book-bookmark',     'label' => 'Libro Mayor',         'color' => 'secondary'],
                ['url' => 'contabilidad/procesos/cierre-mes',      'icon' => 'fa-lock',              'label' => 'Cierre de Mes',       'color' => 'danger'],
            ];
            foreach ($accesos as $a): ?>
                <div class="col-md-2 col-6">
                    <a href="<?= base_url($a['url']) ?>" class="card border-0 shadow-sm text-decoration-none h-100">
                        <div class="card-body text-center py-3">
                            <i class="fa-solid <?= $a['icon'] ?> fa-2x text-<?= $a['color'] ?> mb-2"></i>
                            <div class="small fw-semibold text-dark"><?= $a['label'] ?></div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>


<?= $this->endSection() ?>