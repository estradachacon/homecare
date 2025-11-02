<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h4 class="header-title">Lista de sucursales</h4>
                <a class="btn btn-primary btn-sm ml-auto" href="<?= base_url('branches/create') ?>"><i
                        class="fa-solid fa-plus"></i> Crear sucursal</a>
            </div>
            <div class="card-body">
                <table class="table table-bordered" id="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre de Sucursal</th>
                            <th>Dirección</th>
                            <th class="col-1">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($branches)): ?>
                            <tr>
                                <td colspan="5" class="text-center">No hay sucursales registradas.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($branches as $branch): ?>
                                <tr>
                                    <td><?= esc($branch->id) ?></td>
                                    <td><?= esc($branch->branch_name) ?></td>
                                    <td><?= esc($branch->branch_direction) ?></td>
                                    <td class="text-center">
                                        <?php
                                        $status = esc($branch->status);
                                        $style = []; // Array para definir las clases

                                        // Definimos el estilo basado en el estado
                                        switch ($status) {
                                            case '1':
                                                $style['class'] = 'bg-dark text-white'; // Oscuro y potente
                                                break;
                                            case '0':
                                                $style['class'] = 'bg-secondary text-white'; // Advertencia/Atención, texto oscuro para contraste
                                                break;
                                            default:
                                                $style['class'] = 'bg-light text-warning border border-secondary'; // Para cualquier cosa que se escape
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?= $style['class'] ?> rounded-pill px-3 py-2">
                                            <?php if($status == 1) : ?><span>Activo</span><?php else : ?><span>Inactivo</span><?php endif; ?>
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <a href="<?= base_url('branches/edit/' . $branch->id) ?>" class="btn btn-sm btn-info"
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