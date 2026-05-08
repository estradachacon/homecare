<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<?= csrf_field() ?>
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex">
                <h4 class="mb-0">
                    <?= esc($cliente->nombre) ?>
                </h4>
            </div>
            <div class="card-body">
                <div class="row">

                    <div class="col-md-4">
                        <small class="text-muted">Documento</small>
                        <div class="fw-semibold">
                            <?= esc($cliente->numero_documento) ?>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted">NRC</small>
                        <div class="fw-semibold">
                            <?= esc($cliente->nrc ?? 'N/D') ?>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted">Teléfono</small>
                        <div class="fw-semibold">
                            <?= esc($cliente->telefono ?? 'N/D') ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted">Cuenta Contable</small>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span id="cuentaLabel" class="fw-semibold">
                                <?php if (!empty($cliente->cuenta_codigo)): ?>
                                    <?= esc($cliente->cuenta_codigo . ' - ' . $cliente->cuenta_nombre) ?>
                                <?php else: ?>
                                    <span class="text-danger">Sin cuenta</span>
                                <?php endif; ?>
                            </span>
                            <button class="btn btn-sm btn-outline-primary py-0 px-2"
                                    data-bs-toggle="modal" data-bs-target="#modalCuentaCliente">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

<!-- Modal Cuenta Contable -->
<div class="modal fade" id="modalCuentaCliente" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cuenta Contable del Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label fw-semibold">Seleccionar subcuenta existente</label>
                <div class="input-group mb-3">
                    <select id="selectCuentaCliente" class="form-select"></select>
                    <button class="btn btn-success" type="button" id="btnCrearSubcuenta">
                        <i class="fa-solid fa-plus"></i> Nueva
                    </button>
                </div>
                <div id="formCrearSubcuenta" class="d-none">
                    <label class="form-label fw-semibold small">Nombre para la nueva subcuenta</label>
                    <div class="input-group">
                        <input type="text" id="inputNombreSubcuenta" class="form-control"
                               value="<?= esc($cliente->nombre) ?>">
                        <button class="btn btn-primary" type="button" id="btnConfirmarSubcuenta">
                            Crear y asignar
                        </button>
                    </div>
                    <small class="text-muted">Se creará como subcuenta de 110201 CLIENTES LOCALES</small>
                </div>
                <div id="alertaCuenta" class="mt-2"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarCuenta">Guardar</button>
            </div>
        </div>
    </div>
</div>
<script>
$(function () {
    const clienteId  = <?= (int)$cliente->id ?>;
    const csrfName   = '<?= csrf_token() ?>';
    let   csrfHash   = '<?= csrf_hash() ?>';
    let   cuentaIdSeleccionada = <?= !empty($cliente->cuenta_contable_id) ? (int)$cliente->cuenta_contable_id : 'null' ?>;

    $('#selectCuentaCliente').select2({
        dropdownParent: $('#modalCuentaCliente'),
        placeholder: 'Buscar subcuenta...',
        allowClear: true,
        minimumInputLength: 0,
        ajax: {
            url: '<?= base_url('clientes/cuentas-contables-select2') ?>',
            dataType: 'json',
            delay: 200,
            data: p => ({ q: p.term ?? '' }),
            processResults: d => d,
            cache: true,
        }
    });

    // Precargar cuenta actual
    <?php if (!empty($cliente->cuenta_contable_id) && !empty($cliente->cuenta_codigo)): ?>
    const optActual = new Option(
        '<?= esc($cliente->cuenta_codigo . ' - ' . $cliente->cuenta_nombre) ?>',
        <?= (int)$cliente->cuenta_contable_id ?>, true, true
    );
    $('#selectCuentaCliente').append(optActual).trigger('change');
    <?php endif; ?>

    $('#selectCuentaCliente').on('select2:select', function (e) {
        cuentaIdSeleccionada = e.params.data.id;
    }).on('select2:clear', function () {
        cuentaIdSeleccionada = null;
    });

    // Toggle formulario crear
    $('#btnCrearSubcuenta').on('click', function () {
        $('#formCrearSubcuenta').toggleClass('d-none');
    });

    // Crear subcuenta + asignar directamente
    $('#btnConfirmarSubcuenta').on('click', function () {
        const nombre = $('#inputNombreSubcuenta').val().trim();
        if (!nombre) return;

        $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');

        $.post('<?= base_url('clientes/cuentas-contables-crear') ?>', {
            [csrfName]: csrfHash,
            nombre: nombre
        }, null, 'json')
        .done(function (r) {
            csrfHash = r.csrf ?? csrfHash;
            if (!r.success) {
                $('#alertaCuenta').html('<div class="alert alert-danger">' + r.message + '</div>');
                return;
            }
            const opt = new Option(r.cuenta.text, r.cuenta.id, true, true);
            $('#selectCuentaCliente').append(opt).trigger('change');
            cuentaIdSeleccionada = r.cuenta.id;
            $('#formCrearSubcuenta').addClass('d-none');
            $('#alertaCuenta').html('<div class="alert alert-success">Subcuenta creada: ' + r.cuenta.text + '</div>');
        })
        .always(function () {
            $('#btnConfirmarSubcuenta').prop('disabled', false).html('Crear y asignar');
        });
    });

    // Guardar asignación
    $('#btnGuardarCuenta').on('click', function () {
        $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i>');

        $.ajax({
            url: '<?= base_url('clientes/asignar-cuenta/') ?>' + clienteId,
            type: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            data: { [csrfName]: csrfHash, cuenta_contable_id: cuentaIdSeleccionada ?? '' },
            dataType: 'json'
        })
        .done(function (r) {
            csrfHash = r.csrf ?? csrfHash;
            if (!r.success) {
                $('#alertaCuenta').html('<div class="alert alert-danger">' + (r.message ?? 'Error') + '</div>');
                return;
            }
            const texto = r.cuenta ? r.cuenta.text : '<span class="text-danger">Sin cuenta</span>';
            $('#cuentaLabel').html('<span class="fw-semibold">' + texto + '</span>');
            $('#modalCuentaCliente').modal('hide');
        })
        .always(function () {
            $('#btnGuardarCuenta').prop('disabled', false).html('Guardar');
        });
    });
});
</script>

        <!-- FACTURAS -->

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Facturas del cliente</h5>
            </div>

            <div class="card-body">
                <form method="get" class="mb-3">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label>Desde</label>
                            <input type="date" name="desde" class="form-control"
                                value="<?= esc($desde ?? '') ?>">
                        </div>

                        <div class="col-md-3">
                            <label>Hasta</label>
                            <input type="date" name="hasta" class="form-control"
                                value="<?= esc($hasta ?? '') ?>">
                        </div>

                        <div class="col-md-3">
                            <button class="btn btn-primary">
                                <i class="fa-solid fa-filter"></i> Filtrar
                            </button>

                            <a href="<?= base_url('clientes/show/' . $cliente->id) ?>" class="btn btn-light">
                                Limpiar
                            </a>
                        </div>
                        <div class="col-md-3 text-end">
                            <a href="<?= base_url('clientes/exportar-excel/' . $cliente->id . '?desde=' . ($desde ?? '') . '&hasta=' . ($hasta ?? '')) ?>"
                                class="btn btn-success">
                                <i class="fa-solid fa-file-excel"></i> Exportar Excel
                            </a>
                        </div>
                    </div>
                </form>
                <div class="table-responsive">

                    <table class="table table-bordered table-hover align-middle">

                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th class="col-2">Correlativo</th>
                                <th>Fecha y hora de emisión</th>
                                <th>Total</th>
                                <th>Saldo</th>
                                <th class="text-center">Estado</th>
                                <th style="width:80px">Acción</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if ($facturas): ?>

                                <?php foreach ($facturas as $f): ?>

                                    <tr class="<?= ($f->anulada ?? 0) == 1 ? 'table-danger' : '' ?>">
                                        <td><?= $f->id ?></td>

                                        <td>
                                            <span class="badge bg-info text-white badge-lg">
                                                <?= substr($f->numero_control, -6) ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?= date('d/m/Y', strtotime($f->fecha_emision)) ?>
                                            <br>
                                            <small class="text-muted">
                                                <?= date('H:i:s', strtotime($f->hora_emision)) ?>
                                        </td>

                                        <td class="text-end fw-bold">
                                            $<?= number_format($f->total_pagar, 2) ?>
                                        </td>
                                        <td>
                                            $<?= number_format($f->saldo, 2) ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (($f->anulada ?? 0) == 1): ?>
                                                <span class="badge bg-danger text-white">
                                                    Anulada
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-success text-white">
                                                    Activa
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= base_url('facturas/' . $f->id) ?>"
                                                class="btn btn-sm btn-info">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>

                                <?php endforeach ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        Este cliente no tiene facturas.
                                    </td>
                                </tr>

                            <?php endif ?>

                        </tbody>

                    </table>

                </div>
                <div id="pagerContainer" class="d-flex mt-3">
                    <?= $pager->only(['desde', 'hasta'])->links('default', 'bootstrap_full') ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>