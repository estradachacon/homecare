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
                    <input type="hidden" name="tracking_id" value="<?= $tracking->id ?>">

                    <h5>Seleccionar estado de los paquetes</h5>

                    <table class="table table-bordered table-sm">
                        <thead class="thead">
                            <tr class="col-md-12">
                                <th class="col-md-1">No exitoso</th>

                                <th class="col-md-1 text-center">Solo Recolectado</th>

                                <th class="col-md-1">ID Paquete</th>
                                <th class="col-md-3">Vendedor → Cliente</th>
                                <th class="col-md-4">Destino / Tipo</th>
                                <th class="col-md-2">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalles as $p): ?>
                                <?php
                                $destino = '';
                                $destinoPartes = [];

                                switch ((int) $p->tipo_servicio) {
                                    case 1:
                                        $destino = 'Punto fijo → ' . ($p->puntofijo_nombre ?? 'Sin info');
                                        $destinoPartes[] = $destino;
                                        break;
                                    case 2:
                                        $destino = 'Personalizado → ' . ($p->destino_personalizado ?? 'Sin info');
                                        $destinoPartes[] = $destino;
                                        break;
                                    case 3:
                                        $destino = 'Recolección → ' . ($p->lugar_recolecta_paquete ?? 'Sin info');
                                        $destinoPartes[] = 'Recolección'; // Marcador de parte
                                        if (!empty($p->destino_personalizado)) {
                                            $destino .= ' → Entregar en: ' . $p->destino_personalizado;
                                            $destinoPartes[] = 'Entrega Personalizada'; // Marcador de parte
                                        }
                                        if (!empty($p->puntofijo_nombre)) {
                                            $destino .= ' → Punto fijo: ' . $p->puntofijo_nombre;
                                            $destinoPartes[] = 'Entrega Punto Fijo'; // Marcador de parte
                                        }
                                        break;
                                    default:
                                        $destino = 'No definido';
                                }

                                // Recalculamos el conteo de destinos aquí
                                $destinoCount = count($destinoPartes);

                                // Clase inicial y tooltip
                                $rowClass = '';
                                $tooltip = '';
                                if ($p->status == 'regresado') {
                                    if ($p->tipo_servicio == 3) {
                                        $rowClass = ($destinoCount > 1) ? 'bg-warning' : 'bg-danger-light';
                                        $tooltip = ($destinoCount > 1) ? 'No retirado' : 'Recolección fallida';
                                    } else {
                                        $rowClass = 'bg-warning';
                                        $tooltip = 'No retirado';
                                    }
                                }

                                // Badge tipo entrega
                                $tipoBadge = '';
                                $badgeColor = '';
                                if ($p->tipo_servicio == 3) {
                                    if ($destinoCount === 1) {
                                        $tipoBadge = 'Recolección Única';
                                        $badgeColor = 'bg-danger-light';
                                    } else {
                                        $tipoBadge = 'Recol. + Entrega';
                                        $badgeColor = 'bg-info-light'; // Cambio a éxito total
                                    }
                                } elseif ($p->tipo_servicio == 1) {
                                    $tipoBadge = 'Punto Fijo';
                                    $badgeColor = 'bg-info-light';
                                } elseif ($p->tipo_servicio == 2) {
                                    $tipoBadge = 'Personalizado';
                                    $badgeColor = 'bg-info-light';
                                }
                                ?>
                                <tr class="paquete-row <?= $rowClass ?>" title="<?= $tooltip ?>"
                                    data-tipo="<?= $p->tipo_servicio ?>" data-destinos="<?= $destinoCount ?>">
                                    <td class="text-center">
                                        <input type="checkbox" class="regresado-checkbox" name="regresados[]"
                                            value="<?= $p->id ?>" data-monto="<?= $p->monto ?? 0 ?>"
                                            <?= ($p->status == 'regresado' ? 'checked' : '') ?>>
                                    </td>

                                    <td class="text-center">
                                        <?php
                                        $isRecolectaMultiple = (
                                            $p->tipo_servicio == 3
                                            && $destinoCount >= 2
                                            && $p->package_status !== 'asignado_para_entrega'   // ✔ Aquí ya usas el status REAL del paquete
                                        );

                                        if ($isRecolectaMultiple):
                                            ?>
                                            <input type="checkbox" class="recolectado-solo-checkbox" name="recolectados_solo[]"
                                                value="<?= $p->id ?>" data-id="<?= $p->id ?>"
                                                title="Marcar si el paquete fue recolectado pero la entrega final está pendiente."
                                                <?= ($p->status == 'recolectado' ? 'checked' : '') ?>>
                                        <?php endif; ?>
                                    </td>


                                    <td><?= $p->package_id ?></td>
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

                    <div class="card bg-light p-3 mb-3 shadow-sm">
                        <h5 class="mb-0">Total a entregar: <strong id="total-entregar">$0.00</strong></h5>
                        <small class="text-muted">Solo se suman los paquetes exitosos (no marcados como no
                            entregados/regresados)</small>
                    </div>

                    <button class="btn btn-primary">Guardar Rendición</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        // Función para sincronizar los checkboxes y actualizar el total
        function actualizarEstadoYTotal() {
            let total = 0;

            // 1. Recorrer todos los paquetes
            document.querySelectorAll('.paquete-row').forEach(row => {
                const cbRegresado = row.querySelector('.regresado-checkbox');
                const cbRecolectadoSolo = row.querySelector('.recolectado-solo-checkbox');

                const tipo = parseInt(row.dataset.tipo);
                const destinos = parseInt(row.dataset.destinos);

                // =======================================================
                // A. LÓGICA DE SINCRONIZACIÓN (Regresado vs RecolectadoSolo)
                // =======================================================
                if (cbRegresado && cbRecolectadoSolo) {
                    // Si se marca 'No exitoso', se debe desmarcar 'Solo Recolectado'
                    if (cbRegresado.checked) {
                        cbRecolectadoSolo.disabled = true;
                        cbRecolectadoSolo.checked = false;
                    } else {
                        // Si es exitoso, se habilita el control de 'Solo Recolectado'
                        cbRecolectadoSolo.disabled = false;
                    }
                } else if (cbRegresado && tipo === 3 && destinos === 1) {
                    // Si es Recolección Única, el checkbox de "Solo Recolectado" no existe,
                    // y el checkbox "No exitoso" lo marca como Recolección Fallida o Recolectado.
                    // No hay estado intermedio.
                }

                // =======================================================
                // B. LÓGICA DE CÁLCULO DE TOTAL Y ESTILOS
                // =======================================================
                row.classList.remove('bg-warning', 'bg-danger-light', 'bg-success-light');

                if (cbRegresado && cbRegresado.checked) {
                    // Si está marcado como NO EXITOSO (Regresado)
                    if (tipo === 3 && destinos === 1) {
                        row.classList.add('bg-danger-light'); // Recolección fallida
                    } else {
                        row.classList.add('bg-warning'); // No Retirado
                    }
                } else {
                    // Si es EXITOSO
                    const monto = Number(cbRegresado.dataset.monto) || 0;
                    total += monto;

                    if (cbRecolectadoSolo && cbRecolectadoSolo.checked) {
                        // Recolectado pero pendiente de Entrega Final
                        row.classList.add('bg-info-light');
                    } else {
                        // Éxito Final (Entregado o Recolección Única Exitosa)
                        row.classList.add('bg-success-light');
                    }
                }
            });

            document.getElementById('total-entregar').innerText = '$' + total.toFixed(2);
        }

        // Ejecutar al cargar la página
        actualizarEstadoYTotal();

        // Ejecutar al cambiar cualquier checkbox de NO EXITOSO
        document.querySelectorAll('.regresado-checkbox').forEach(cb => {
            cb.addEventListener('change', actualizarEstadoYTotal);
        });

        // Ejecutar al cambiar cualquier checkbox de SOLO RECOLECTADO
        document.querySelectorAll('.recolectado-solo-checkbox').forEach(cb => {
            cb.addEventListener('change', actualizarEstadoYTotal);
        });
    });
</script>

<style>
    /* Estilos existentes */
    .bg-danger-light {
        background-color: #ffe5e5 !important;
    }

    .bg-warning-light {
        background-color: #fff3cd !important;
    }

    .bg-info-light {
        background-color: #d1e7dd !important;
        color: #0f5132;
    }

    .badge-pill {
        display: inline-block;
        padding: 0.25em 0.6em;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 999px;
        margin-left: 0.5rem;
    }

    /* Nuevo estilo para Recolectado + Entregado Exitoso */
    .bg-success-light {
        background-color: #d4edda !important;
    }

    /* Nuevo estilo para Recolectado (Pendiente de Entrega Final) */
    .bg-info-light {
        background-color: #cce5ff !important;
    }
</style>

<?= $this->endSection() ?>