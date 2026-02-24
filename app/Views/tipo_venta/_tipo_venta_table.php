<table class="table table-striped table-bordered table-hover" id="sellers-table">
    <thead>
        <tr>
            <th class="col-1">ID</th>
            <th class="col-7">Tipo venta</th>
            <th class="col-2">Fecha de creación</th>
            <th class="col-1">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($tipo_ventas)): ?>
            <?php foreach ($tipo_ventas as $tipo_venta): ?>
                <tr>
                    <td class="text-center"><?= esc($tipo_venta->id) ?></td>
                    <td><?= esc($tipo_venta->nombre_tipo_venta) ?></td>
                    <td class="text-center"><?= esc($tipo_venta->created_at) ?></td>
                    <td>
                        <?php if (tienePermiso('editar_tipo_venta')): ?>
                            <a href="<?= base_url('tipo_venta/edit/' . $tipo_venta->id) ?>" class="btn btn-sm btn-info"><i
                                    class="fa-solid fa-edit"></i></a>
                        <?php endif; ?>
                        <?php if (tienePermiso('eliminar_tipo_venta')): ?>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $tipo_venta->id ?>">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4" class="text-center">No hay tipos de venta registrados<?= !empty($q) ? ' para la búsqueda "' . esc($q) . '"' : '' ?></td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
<div class="mt-3" id="pagination-links">
    <?= $pager->links('default', 'bitacora_pagination') ?>
</div>