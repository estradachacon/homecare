<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
    .badge-estado {
        font-size: 0.75rem;
        padding: 5px 12px;
        border-radius: 10px;
        font-weight: 500;
    }
</style>

<div class="row">
    <div class="col-md-12">

        <div class="card">

            <div class="card-header d-flex">

                <h4 class="header-title">Control de Quedan</h4>

                <?php if (tienePermiso('crear_quedans')): ?>
                    <a href="<?= base_url('quedans/crear') ?>" class="btn btn-primary btn-sm ml-auto">
                        <i class="fa-solid fa-plus"></i> Nuevo Quedan
                    </a>
                <?php endif; ?>
            </div>

            <div class="card-body">

                <table class="table table-striped table-bordered table-hover">

                    <thead>
                        <tr>

                            <th class="col-1">Quedan</th>
                            <th class="col-3">Cliente</th>
                            <th class="col-2">Fecha emisión</th>
                            <th class="col-2">Fecha pago</th>
                            <th class="col-2">Total</th>
                            <th class="col-1">Menú</th>

                        </tr>
                    </thead>

                    <tbody>

                        <?php if (!empty($quedans)): ?>

                            <?php foreach ($quedans as $q): ?>

                                <tr class="<?= $q->anulado ? 'table-danger text-muted' : '' ?>">

                                    <td>

                                        <strong>
                                            <?= esc($q->numero_quedan) ?>
                                        </strong>


                                        <?php if ($q->anulado): ?>

                                            <span class="badge bg-danger ml-2">
                                                Anulado
                                            </span>

                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?= esc($q->cliente_nombre ?? 'Sin cliente') ?>
                                    </td>


                                    <td class="text-center">
                                        <?= date('d/m/Y', strtotime($q->fecha_emision)) ?>
                                    </td>


                                    <td class="text-center">
                                        <?= date('d/m/Y', strtotime($q->fecha_pago)) ?>
                                    </td>


                                    <td class="text-end">

                                        <?php
                                        $total = 0;

                                        if (isset($q->total_aplicado)) {
                                            $total = $q->total_aplicado;
                                        }
                                        ?>

                                        $ <?= number_format($total, 2) ?>

                                    </td>

                                    <td class="text-center">

                                        <a href="<?= base_url('quedans/' . $q->id) ?>"
                                            class="btn btn-sm btn-info">

                                            <i class="fa-solid fa-eye"></i>

                                        </a>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="7" class="text-center">
                                    No hay quedan registrados
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>
</div>

<?= $this->endSection() ?>