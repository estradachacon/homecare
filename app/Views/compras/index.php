<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">

        <div class="card">

            <div class="card-header d-flex">
                <h4 class="header-title">Listado de Compras</h4>

                <?php if (tienePermiso('ingresar_compras')): ?>
                    <a class="btn btn-primary btn-sm ml-auto"
                       href="<?= base_url('compras/new') ?>">

                        <i class="fa-solid fa-plus"></i> Nueva compra

                    </a>
                <?php endif; ?>
            </div>

            <div class="card-body">

                <table class="table table-striped table-bordered table-hover">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Proveedor</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr>
                            <td colspan="5" class="text-center">
                                No hay compras registradas
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>
</div>

<?= $this->endSection() ?>