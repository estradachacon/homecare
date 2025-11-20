<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-12">

        <!-- ===============================
          ENCABEZADO DEL TRACKING
    ================================ -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h4>Rendición del motorista: <?= esc($tracking->user_id) ?></h4>
            </div>
            <div class="card-body">
                <form method="post" action="<?= site_url('tracking-rendicion/save') ?>">
                    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                    <h5>Seleccionar paquetes no retirados</h5>
                    <input type="hidden" name="tracking_id" value="<?= $tracking->id ?>">
                    <table class="table table-bordered table-sm">
                        <thead class="thead">
                            <tr>
                                <th>Regresó</th>
                                <th>ID Paquete</th>
                                <th>Cliente</th>
                                <th>Monto</th>
                                <th>Estatus</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($paquetes as $p): ?>
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="regresados[]" value="<?= $p->id ?>"
                                            <?= ($p->status == 'REGRESADO' ? 'checked' : '') ?>>
                                    </td>
                                    <td><?= $p->id ?></td>
                                    <td><?= esc($p->cliente) ?></td>
                                    <td>$<?= number_format($p->monto, 2) ?></td>
                                    <td><?= statusBadge($p->status ?? 'N/A') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <button class="btn btn-primary">Guardar Rendición</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>