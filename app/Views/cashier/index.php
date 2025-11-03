<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h4 class="header-title">Lista de cajas</h4>
                <a class="btn btn-primary btn-sm ml-auto" href="<?= base_url('cashiers/new') ?>"><i
                        class="fa-solid fa-plus"></i> Crear caja</a>
            </div>
            <div class="card-body">
                <table class="table table-bordered" id="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre de caja</th>
                            <th>Sucursal</th>
                            <th>Monto inicial</th>
                            <th>Monto actual</th>
                            <th class="col-1">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cashiers)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No hay cajas registradas.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($cashiers as $cashier): ?>
                                <tr>
                                    <td><?= esc($cashier->id) ?></td>
                                    <td><?= esc($cashier->name) ?></td>

                                    <td><?= esc($cashier->branch_name) ?></td>

                                    <td><?= esc($cashier->initial_balance) ?></td>
                                    <td><?= esc($cashier->current_balance) ?></td>
                                    <td class="text-center">
                                        <?php
                                        $status = esc($cashier->is_open);

                                        // Definimos el estilo basado en el estado
                                        switch ($status) {
                                            case '1':
                                                $style['class'] = 'bg-info text-white'; // Oscuro y potente
                                                break;
                                            case '0':
                                                $style['class'] = 'bg-secondary text-white'; // Advertencia/Atención, texto oscuro para contraste
                                                break;
                                            default:
                                                $style['class'] = 'bg-light text-danger border border-secondary'; // Para cualquier cosa que se escape
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?= $style['class'] ?> rounded-pill px-3 py-2">
                                            <?php if ($status == 1): ?><span>Caja abierta</span><?php else: ?><span>Caja
                                                    cerrada</span><?php endif; ?>
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <a href="<?= base_url('cashier/show/' . $cashier->id) ?>" class="btn btn-sm btn-primary"
                                            title="Ver"><i class="fa-solid fa-eye"></i></a>
                                        <a href="<?= base_url('cashier/edit/' . $cashier->id) ?>" class="btn btn-sm btn-info"
                                            title="Editar"><i class="fa-solid fa-edit"></i></a>
                                        <button class="btn btn-sm btn-danger" title="Eliminar"><i
                                                class="fa-solid fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>