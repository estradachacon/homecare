<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

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
            <div class="card-header d-flex justify-content-between flex-wrap gap-2">
                <h4 class="mb-0"><i class="fa-solid fa-file-invoice me-1"></i> DTEs Emitidos</h4>
                <?php if (tienePermiso('emitir_dte')): ?>
                    <a href="<?= base_url('emision-dte/nuevo') ?>" class="btn btn-success btn-sm">
                        <i class="fa-solid fa-plus me-1"></i> Nuevo DTE
                    </a>
                <?php endif; ?>
            </div>

            <!-- Filtros -->
            <?php $req = service('request'); ?>
            <div class="card-body pb-0">
                <form method="GET" action="" id="filtroForm" class="row g-2 mb-2">
                    <div class="col-md-2">
                        <select name="tipo_dte" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">Todos los tipos</option>
                            <option value="01" <?= $req->getGet('tipo_dte') === '01' ? 'selected' : '' ?>>Factura (01)</option>
                            <option value="03" <?= $req->getGet('tipo_dte') === '03' ? 'selected' : '' ?>>CCF (03)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="estado" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">Todos</option>
                            <option value="activa"  <?= $req->getGet('estado') === 'activa'  ? 'selected' : '' ?>>Activos</option>
                            <option value="anulada" <?= $req->getGet('estado') === 'anulada' ? 'selected' : '' ?>>Anulados</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="fecha" value="<?= esc($req->getGet('fecha') ?? '') ?>"
                            class="form-control form-control-sm" onchange="this.form.submit()">
                    </div>
                    <div class="col-md-3">
                        <div class="input-group input-group-sm">
                            <input type="text" name="numero" value="<?= esc($req->getGet('numero') ?? '') ?>"
                                class="form-control" placeholder="Buscar número control...">
                            <button class="btn btn-outline-secondary" type="submit"><i class="fa-solid fa-search"></i></button>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <a href="<?= base_url('emision-dte') ?>" class="btn btn-outline-secondary btn-sm w-100">Limpiar</a>
                    </div>
                </form>
            </div>

            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Número de control</th>
                                <th>Tipo</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th class="text-end">Total</th>
                                <th class="text-center">Estado MH</th>
                                <th class="text-center">Estado</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($dtEs)): ?>
                                <?php foreach ($dtEs as $dte): ?>
                                    <?php
                                        $estadoMh = strtolower($dte->estado_mh ?? '');
                                        $badgeMh  = match($estadoMh) {
                                            'procesado'   => ['class' => 'bg-success', 'label' => 'Procesado'],
                                            'recibido'    => ['class' => 'bg-primary', 'label' => 'Recibido'],
                                            'rechazado'   => ['class' => 'bg-danger',  'label' => 'Rechazado'],
                                            'contingencia'=> ['class' => 'bg-warning text-dark', 'label' => 'Contingencia'],
                                            default       => ['class' => 'bg-secondary', 'label' => ucfirst($estadoMh ?: 'Pendiente')],
                                        };
                                    ?>
                                    <tr class="<?= $dte->anulada ? 'table-danger' : '' ?>">
                                        <td>
                                            <a href="<?= base_url('emision-dte/' . $dte->id) ?>">
                                                <?= esc($dte->numero_control) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge <?= $dte->tipo_dte === '01' ? 'bg-info text-dark' : 'bg-primary' ?>">
                                                <?= $dte->tipo_dte === '01' ? 'Factura' : 'CCF' ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($dte->fecha_emision)) ?></td>
                                        <td><?= esc($dte->cliente_nombre ?? 'Consumidor Final') ?></td>
                                        <td class="text-end fw-semibold">$<?= number_format($dte->total_pagar, 2) ?></td>
                                        <td class="text-center">
                                            <span class="badge <?= $badgeMh['class'] ?>"><?= $badgeMh['label'] ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($dte->anulada): ?>
                                                <span class="badge bg-danger">Anulado</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= base_url('emision-dte/' . $dte->id) ?>" class="btn btn-sm btn-outline-primary" title="Ver detalle">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        No se encontraron DTEs emitidos.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if (isset($pager)): ?>
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2">
                        <div>
                            <select class="form-control form-control-sm d-inline w-auto" onchange="location.href=this.value">
                                <?php foreach ([10, 15, 25, 50, 100] as $pp): ?>
                                    <option value="?<?= http_build_query(array_merge($req->getGet() ?? [], ['per_page' => $pp])) ?>"
                                        <?= ($perPage ?? 25) == $pp ? 'selected' : '' ?>>
                                        <?= $pp ?> por página
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?= $pager->links('default', 'bootstrap_full') ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
