<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
    .badge-estado {
        font-size: 0.75rem;
        padding: 5px 12px;
        border-radius: 10px;
        font-weight: 500;
    }
        .select2-container .select2-selection--single {
        height: 40px !important;
        /* altura estándar Bootstrap */
        border: 1px solid #ced4da;
        border-radius: .375rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        /* centra texto */
        padding-left: .75rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }

    /* focus igual que form-control */
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #86b7fe;
        box-shadow: 0 0 0 .25rem rgba(13, 110, 253, .25);
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
                <form method="get" class="mb-3">
                    <div class="row g-2">

                        <div class="col-md-4">
                            <small class="text-muted">Cliente</small>
                            <select name="cliente_id" id="clienteFiltro" class="form-control"></select>
                        </div>

                    <div class="col-md-2">
                        <small class="text-muted">Estado</small>
                        <select name="estado_calc" class="form-control">
                            <option value="">Todos</option>
                                <option value="pagado" <?= ($_GET['estado_calc'] ?? '') == 'pagado' ? 'selected' : '' ?>>Pagado</option>
                                <option value="pendiente" <?= ($_GET['estado_calc'] ?? '') == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                <option value="vencido" <?= ($_GET['estado_calc'] ?? '') == 'vencido' ? 'selected' : '' ?>>Vencido</option>
                                <option value="anulado" <?= ($_GET['estado_calc'] ?? '') == 'anulado' ? 'selected' : '' ?>>Anulado</option>
                        </select>
                    </div>

                        <div class="col-md-3">
                            <small class="text-muted">Fecha inicio</small>
                            <input type="date" name="fecha_inicio" class="form-control"
                                value="<?= esc($_GET['fecha_inicio'] ?? '') ?>">
                        </div>

                        <div class="col-md-3">
                            <small class="text-muted">Fecha fin</small>
                            <input type="date" name="fecha_inicio" class="form-control"
                                value="<?= esc($_GET['fecha_inicio'] ?? '') ?>">
                        </div>

                    </div>

                    <div class="mt-2">
                        <button class="btn btn-primary btn-sm">Filtrar</button>
                        <a href="<?= base_url('quedans') ?>" class="btn btn-secondary btn-sm">Limpiar</a>
                    </div>
                </form>
                <table class="table table-striped table-bordered table-hover">

                    <thead>
                        <colgroup>
                            <col style="width: 5%;"> <!-- ID -->
                            <col style="width: 15%;"> <!-- Quedan -->
                            <col style="width: 25%;"> <!-- Cliente -->
                            <col style="width: 12%;"> <!-- Fecha emisión -->
                            <col style="width: 12%;"> <!-- Fecha pago -->
                            <col style="width: 10%;"> <!-- Total -->
                            <col style="width: 10%;"> <!-- Estado -->
                            <col style="width: 6%;"> <!-- Menú -->
                        </colgroup>
                        <tr>
                            <th>ID</th>
                            <th class="col-1">Quedan</th>
                            <th class="col-5">Cliente</th>
                            <th class="col-1">Fecha emisión</th>
                            <th class="col-1">Fecha pago</th>
                            <th class="col-1">Total</th>
                            <th class="col-1">Estado</th>
                            <th class="col-1">Menú</th>

                        </tr>
                    </thead>

                    <tbody>

                        <?php if (!empty($quedans)): ?>

                            <?php foreach ($quedans as $q): ?>

                                <tr class="<?= $q->anulado ? 'table-danger text-muted' : '' ?>">
                                    <td>
                                        <?= $q->id ?>
                                    </td>
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
                                        <?php switch ($q->estado_calculado):

                                            case 'anulado': ?>
                                                <span class="badge bg-danger badge-estado">Anulado</span>
                                                <?php break; ?>

                                            <?php
                                            case 'pagado': ?>
                                                <span class="badge bg-success badge-estado">Pagado</span>
                                                <?php break; ?>

                                            <?php
                                            case 'vencido': ?>
                                                <span class="badge bg-danger badge-estado">Vencido</span>
                                                <?php break; ?>

                                            <?php
                                            default: ?>
                                                <span class="badge bg-warning text-dark badge-estado">Pendiente</span>

                                        <?php endswitch; ?>
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
                                <td colspan="8" class="text-center">
                                    No hay quedan registrados
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>
                <div class="mt-3">
                    <?= $pager->links('default', 'bootstrap_full') ?>
                </div>
            </div>

        </div>

    </div>
</div>
<script>
    $(document).ready(function() {

    let clienteId = "<?= $_GET['cliente_id'] ?? '' ?>";

    if (clienteId) {
        fetch("<?= base_url('clientes/buscar') ?>?q=")
            .then(r => r.json())
            .then(data => {

                let cliente = data.find(c => c.id == clienteId);

                if (cliente) {
                    let option = new Option(cliente.text, cliente.id, true, true);
                    $('#clienteFiltro').append(option).trigger('change');
                }
            });
    }


        $('#clienteFiltro').select2({
            placeholder: 'Buscar cliente...',
            minimumInputLength: 2,
            ajax: {
                url: "<?= base_url('clientes/buscar') ?>",
                dataType: 'json',
                delay: 250,
                data: params => ({
                    q: params.term
                }),
                processResults: function(data) {
                    return {
                        results: data
                    };
                }
            }
        });

    });
</script>
<?= $this->endSection() ?>