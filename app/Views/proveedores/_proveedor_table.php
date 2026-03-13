<table class="table table-striped table-bordered table-hover" id="proveedores-table">
    <thead>
        <tr>
            <th class="col-1">ID</th>
            <th class="col-5">Proveedor</th>
            <th class="col-2">Teléfono</th>
            <th class="col-3">Email</th>
            <th class="col-1">Acciones</th>
        </tr>
    </thead>

    <tbody>

        <?php if (!empty($proveedores)): ?>

            <?php foreach ($proveedores as $proveedor): ?>

                <tr>

                    <td class="text-center">
                        <?= esc($proveedor->id) ?>
                    </td>

                    <td>
                        <?= esc($proveedor->nombre) ?>
                    </td>

                    <td class="text-center">
                        <?= esc($proveedor->telefono) ?>
                    </td>

                    <td>
                        <?= esc($proveedor->email) ?>
                    </td>

                    <td class="text-center">

                        <?php if (tienePermiso('editar_proveedor')): ?>

                            <a href="<?= base_url('proveedores/edit/' . $proveedor->id) ?>"
                                class="btn btn-sm btn-info">

                                <i class="fa-solid fa-edit"></i>

                            </a>

                        <?php endif; ?>


                        <?php if (tienePermiso('eliminar_proveedor')): ?>

                            <button class="btn btn-sm btn-danger delete-btn"
                                data-id="<?= $proveedor->id ?>">

                                <i class="fa-solid fa-trash"></i>

                            </button>

                        <?php endif; ?>

                    </td>

                </tr>

            <?php endforeach; ?>

        <?php else: ?>

            <tr>

                <td colspan="5" class="text-center">

                    No hay proveedores registrados
                    <?= !empty($q) ? ' para la búsqueda "' . esc($q) . '"' : '' ?>

                </td>

            </tr>

        <?php endif; ?>

    </tbody>
</table>


<div class="mt-3" id="pagination-links">
    <?= $pager->links('default', 'bitacora_pagination') ?>
</div>