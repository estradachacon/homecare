<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex">
                <h5><?= esc($title) ?></h4>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <div>
                        <form method="get" id="perPageForm">
                            <label for="per_page" class="me-2">Mostrar:</label>
                            <select name="per_page" id="per_page" class="form-select d-inline-block w-auto"
                                onchange="document.getElementById('perPageForm').submit();">
                                <option value="5" <?= $perPage == 5 ? 'selected' : '' ?>>5</option>
                                <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10</option>
                                <option value="25" <?= $perPage == 25 ? 'selected' : '' ?>>25</option>
                                <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50</option>
                                <option value="100" <?= $perPage == 100 ? 'selected' : '' ?>>100</option>
                            </select>
                            <span>registros por página</span>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Usuario</th>
                                <th>Acción</th>
                                <th>Módulo</th>
                                <th>Descripción</th>
                                <th>IP</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($bitacoras)): ?>
                                <?php foreach ($bitacoras as $index => $b): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= esc($b['usuario'] ?? 'Sistema') ?></td>
                                        <td><?= esc($b['accion']) ?></td>
                                        <td><?= esc($b['modulo']) ?></td>
                                        <td><?= esc($b['descripcion']) ?></td>
                                        <td><?= esc($b['ip_address']) ?></td>
                                        <td><?= esc($b['created_at']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No hay registros en la bitácora.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    <?= $pager->links('default', 'bitacora_pagination') ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>