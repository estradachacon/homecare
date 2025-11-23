<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-12">

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h4>Rendición del motorista: <?= esc($motoristaNombre) ?></h4>
            </div>
            <div class="card-body">
                <form method="post" action="<?= site_url('tracking-rendicion/save') ?>">
                    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                    <h5>Seleccionar paquetes no exitosos</h5>
                    <input type="hidden" name="tracking_id" value="<?= $tracking->id ?>">

                    <table class="table table-bordered table-sm">
                        <thead class="thead">
                            <tr class="col-md-12">
                                <th class="col-md-1">No exitoso</th>
                                <th class="col-md-1">ID Paquete</th>
                                <th class="col-md-3">Vendedor → Cliente</th>
                                <th class="col-md-5">Destino / Tipo</th>
                                <th class="col-md-2">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($paquetes as $p): ?>
                                <?php
                                $destino = '';
                                $destinoPartes = [];

                                switch ((int) $p->tipo_servicio) {
                                    case 1:
                                        $destino = 'Punto fijo → ' . ($p->punto_fijo_nombre ?? 'Sin info');
                                        $destinoPartes[] = $destino;
                                        break;
                                    case 2:
                                        $destino = 'Personalizado → ' . ($p->destino_personalizado ?? 'Sin info');
                                        $destinoPartes[] = $destino;
                                        break;
                                    case 3:
                                        $destino = 'Recolección → ' . ($p->lugar_recolecta_paquete ?? 'Sin info');
                                        $destinoPartes[] = $destino;
                                        if (!empty($p->destino_personalizado)) {
                                            $destino .= ' → Entregar en: ' . $p->destino_personalizado;
                                            $destinoPartes[] = 'Entrega';
                                        }
                                        if (!empty($p->punto_fijo_nombre)) {
                                            $destino .= ' → Punto fijo: ' . $p->punto_fijo_nombre;
                                            $destinoPartes[] = 'PuntoFijo';
                                        }
                                        break;
                                    default:
                                        $destino = 'No definido';
                                }

                                // Clase inicial y tooltip
                                $rowClass = '';
                                $tooltip = '';
                                if ($p->status == 'regresado') {
                                    if ($p->tipo_servicio == 3) {
                                        $rowClass = (count($destinoPartes) > 1) ? 'bg-warning' : 'bg-danger-light';
                                        $tooltip = (count($destinoPartes) > 1) ? 'No retirado' : 'Recolección fallida';
                                    } else {
                                        $rowClass = 'bg-warning';
                                        $tooltip = 'No retirado';
                                    }
                                }

                                // Badge tipo entrega
                                $tipoBadge = '';
                                $badgeColor = '';
                                if ($p->tipo_servicio == 3) {
                                    if (count($destinoPartes) === 1) {
                                        $tipoBadge = 'Recolección';
                                        $badgeColor = 'bg-danger-light';
                                    } else {
                                        $tipoBadge = 'Entrega';
                                        $badgeColor = 'bg-warning-light';
                                    }
                                } elseif ($p->tipo_servicio == 1) {
                                    $tipoBadge = 'Punto fijo';
                                    $badgeColor = 'bg-info-light';
                                } elseif ($p->tipo_servicio == 2) {
                                    $tipoBadge = 'Personalizado';
                                    $badgeColor = 'bg-info-light';
                                }
                                ?>
                                <tr class="paquete-row <?= $rowClass ?>"
                                    title="<?= $tooltip ?>"
                                    data-tipo="<?= $p->tipo_servicio ?>"
                                    data-destinos="<?= count($destinoPartes) ?>">
                                    <td class="text-center">
                                        <input type="checkbox" class="regresado-checkbox" name="regresados[]"
                                               value="<?= $p->id ?>"
                                               data-monto="<?= $p->monto ?? 0 ?>"
                                               <?= ($p->status == 'regresado' ? 'checked' : '') ?>>
                                    </td>
                                    <td><?= $p->id ?></td>
                                    <td><?= esc($p->vendedor . ' → ' . $p->cliente) ?></td>
                                    <td>
                                        <?= esc($destino) ?>
                                        <?php if (!empty($tipoBadge)): ?>
                                            <span class="badge-pill <?= $badgeColor ?>"><?= $tipoBadge ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><strong>$<?= number_format($p->monto ?? 0, 2) ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- DIV TOTALIZADOR BONITO -->
                    <div class="card bg-light p-3 mb-3 shadow-sm">
                        <h5 class="mb-0">Total a entregar: <strong id="total-entregar">$0.00</strong></h5>
                        <small class="text-muted">Solo se suman los paquetes exitosos (no marcados como no entregados)</small>
                    </div>

                    <button class="btn btn-primary">Guardar Rendición</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function actualizarTotal() {
        let total = 0;
        document.querySelectorAll('.regresado-checkbox').forEach(cb => {
            const row = cb.closest('tr');
            const tipo = parseInt(row.dataset.tipo);
            const destinos = parseInt(row.dataset.destinos);

            if (cb.checked) {
                if (tipo === 3 && destinos === 1) {
                    row.classList.add('bg-danger-light');
                    row.classList.remove('bg-warning');
                } else {
                    row.classList.add('bg-warning');
                    row.classList.remove('bg-danger-light');
                }
            } else {
                row.classList.remove('bg-warning', 'bg-danger-light');
                total += Number(cb.dataset.monto) || 0;
            }
        });
        document.getElementById('total-entregar').innerText = '$' + total.toFixed(2);
    }

    // Ejecutar al cargar la página
    actualizarTotal();

    // Ejecutar al cambiar cualquier checkbox
    document.querySelectorAll('.regresado-checkbox').forEach(cb => {
        cb.addEventListener('change', actualizarTotal);
    });
</script>

<style>
    .bg-danger-light { background-color: #ffe5e5 !important; }
    .bg-warning-light { background-color: #fff3cd !important; }
    .bg-info-light { background-color: #d1e7dd !important; color: #0f5132; }

    .badge-pill {
        display: inline-block;
        padding: 0.25em 0.6em;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 999px;
        margin-left: 0.5rem;
    }
</style>

<?= $this->endSection() ?>
