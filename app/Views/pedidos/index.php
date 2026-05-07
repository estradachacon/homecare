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
            <div class="card-header d-flex justify-content-between">
                <h4 class="header-title mb-0">Notas de Pedido</h4>
                <?php if (tienePermiso('crear_pedidos')): ?>
                    <a href="<?= base_url('pedidos/crear') ?>" class="btn btn-success btn-sm">
                        <i class="fa-solid fa-plus"></i> Nueva Nota
                    </a>
                <?php endif; ?>
            </div>

            <div class="card-body">
                <!-- Filtros -->
                <form method="GET" action="<?= base_url('pedidos') ?>" class="mb-3">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <input type="text" name="q" value="<?= esc($filtros['q'] ?? '') ?>"
                                class="form-control form-control-sm" placeholder="Buscar número o cliente...">
                        </div>
                        <div class="col-md-2">
                            <select name="vendedor_id" class="form-control form-control-sm">
                                <option value="">Todos los vendedores</option>
                                <?php foreach ($sellers as $s): ?>
                                    <option value="<?= $s->id ?>" <?= ($filtros['vendedor_id'] == $s->id) ? 'selected' : '' ?>>
                                        <?= esc($s->seller) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="estado" class="form-control form-control-sm">
                                <option value="">Todos los estados</option>
                                <option value="pendiente"  <?= ($filtros['estado'] === 'pendiente')  ? 'selected' : '' ?>>Pendiente</option>
                                <option value="facturada"  <?= ($filtros['estado'] === 'facturada')  ? 'selected' : '' ?>>Facturada</option>
                                <option value="anulada"    <?= ($filtros['estado'] === 'anulada')    ? 'selected' : '' ?>>Anulada</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="tipo_documento" class="form-control form-control-sm">
                                <option value="">Tipo documento</option>
                                <option value="factura"        <?= ($filtros['tipo_documento'] === 'factura')        ? 'selected' : '' ?>>Factura</option>
                                <option value="credito_fiscal" <?= ($filtros['tipo_documento'] === 'credito_fiscal') ? 'selected' : '' ?>>Crédito Fiscal</option>
                                <option value="nota_remision"  <?= ($filtros['tipo_documento'] === 'nota_remision')  ? 'selected' : '' ?>>Nota de Remisión</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <input type="date" name="fecha_inicio" value="<?= esc($filtros['fecha_inicio'] ?? '') ?>" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-1">
                            <input type="date" name="fecha_fin" value="<?= esc($filtros['fecha_fin'] ?? '') ?>" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-1 d-flex gap-1">
                            <button type="submit" class="btn btn-primary btn-sm mr-1" title="Filtrar">
                                <i class="fa-solid fa-filter"></i>
                            </button>
                            <a href="<?= base_url('pedidos') ?>" class="btn btn-secondary btn-sm" title="Limpiar">
                                <i class="fa-solid fa-times"></i>
                            </a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Número</th>
                                <th>Cliente</th>
                                <th>Vendedor</th>
                                <th>Documento</th>
                                <th>Pago</th>
                                <th class="text-end">Total</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pedidos)): ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">No hay notas de pedido registradas.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pedidos as $p): ?>
                                    <tr>
                                        <td><?= $p->id ?></td>
                                        <td>
                                            <a href="<?= base_url('pedidos/' . $p->id) ?>">
                                                <strong><?= esc($p->numero) ?></strong>
                                            </a>
                                        </td>
                                        <td><?= esc($p->cliente_nombre) ?></td>
                                        <td><?= esc($p->vendedor_nombre) ?></td>
                                        <td>
                                            <?php
                                            $labels = [
                                                'factura'        => '<span class="badge badge-info">Factura</span>',
                                                'credito_fiscal' => '<span class="badge badge-warning">Créd. Fiscal</span>',
                                                'nota_remision'  => '<span class="badge badge-secondary">Nota Rem.</span>',
                                            ];
                                            echo $labels[$p->tipo_documento] ?? esc($p->tipo_documento);
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($p->tipo_pago === 'credito'): ?>
                                                <span class="badge badge-primary"><?= $p->dias_credito ?>d</span>
                                            <?php else: ?>
                                                <span class="badge badge-light">Contado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">$<?= number_format($p->total, 2) ?></td>
                                        <td class="text-center">
                                            <?php
                                            $estadoBadge = [
                                                'pendiente' => 'badge-warning',
                                                'facturada' => 'badge-success',
                                                'anulada'   => 'badge-danger',
                                            ];
                                            $estadoLabel = [
                                                'pendiente' => 'Pendiente',
                                                'facturada' => 'Facturada',
                                                'anulada'   => 'Anulada',
                                            ];
                                            ?>
                                            <span class="badge <?= $estadoBadge[$p->estado] ?? 'badge-secondary' ?> px-2 py-1">
                                                <?= $estadoLabel[$p->estado] ?? esc($p->estado) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= base_url('pedidos/' . $p->id) ?>" class="btn btn-info btn-xs" title="Ver">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                            <?php if ($p->estado === 'pendiente' && tienePermiso('editar_pedidos')): ?>
                                                <a href="<?= base_url('pedidos/' . $p->id . '/editar') ?>" class="btn btn-primary btn-xs" title="Editar">
                                                    <i class="fa-solid fa-pen"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($p->estado !== 'anulada' && tienePermiso('anular_pedidos')): ?>
                                                <button class="btn btn-danger btn-xs btn-anular"
                                                    data-id="<?= $p->id ?>"
                                                    data-numero="<?= esc($p->numero) ?>"
                                                    title="Anular">
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
$(document).ready(function () {
    $(document).on('click', '.btn-anular', function () {
        const id     = $(this).data('id');
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

            fetch(`<?= base_url('pedidos') ?>/${id}/anular`, {
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
