<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    .muteado {
        background-color: #e9ecef !important;
        /* gris claro */
        color: #6c757d !important;
        /* gris oscuro */
        text-decoration: line-through;
        /* rayitas */
    }

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
<div class="row">
    <div class="col-md-12">

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h4>Rendición del motorista: <?= esc($motoristaNombre) ?></h4>
            </div>
            <div class="card-body">
                <form method="post"
                    action="<?= base_url('tracking-rendicion/save') ?>"
                    onsubmit="return bloquearEnvio();">


                    <input type="hidden" name="tracking_id" value="<?= $tracking->id ?>">

                    <h5>Seleccionar estado de los paquetes</h5>

                    <table class="table table-bordered table-sm">
                        <thead class="thead">
                            <tr class="col-md-12">
                                <th class="col-md-1">No exitoso</th>

                                <th class="col-md-1 text-center">Solo Recolectado</th>

                                <th class="col-md-1">ID Paquete</th>
                                <th class="col-md-3">Vendedor → Cliente</th>
                                <th class="col-md-3">Destino / Tipo</th>
                                <th class="col-md-1">Monto</th>
                                <th class="col-md-1">Aporte Rendición</th>
                                <th class="col-md-1">Cuenta</th>
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
                                    data-tipo="<?= $p->tipo_servicio ?>"
                                    data-destinos="<?= $destinoCount ?>"
                                    data-monto="<?= $p->monto ?>"
                                    data-toggle="<?= $p->toggle_pago_parcial ?>"
                                    data-flete-total="<?= $p->flete_total ?>"
                                    data-flete-pagado="<?= $p->flete_pagado ?>"
                                    data-flete-rendido="<?= (int) $p->flete_rendido ?>">

                                    <td class="text-center aporte-monto">
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
                                    <td class="text-center paquete-monto-celda">
                                        <?php
                                        // Condición inicial para el paquete (asumiendo que 'recolectado' aplica a "Solo Recolectado")
                                        $isRecolectadoSolo = ($p->status == 'recolectado' && $isRecolectaMultiple);
                                        $muteClass = $isRecolectadoSolo ? 'muteado' : '';
                                        ?>
                                        <strong class="paquete-monto-total <?= $muteClass ?>">
                                            $<?= number_format($p->monto ?? 0, 2) ?>
                                        </strong>
                                    </td>
                                    <td class="aporte-rendicion">
                                        <?php if ($p->tipo_servicio == 3): ?>

                                            <?php if ($p->flete_rendido): ?>
                                                <span class="muteado">
                                                    <?= '$' . number_format(
                                                        $p->toggle_pago_parcial == 1 ? $p->flete_pagado : $p->flete_total,
                                                        2
                                                    ) ?>
                                                </span>
                                                <br>
                                                <small class="text-muted">Flete ya fue recolectado</small>
                                            <?php else: ?>
                                                <?= '$' . number_format(
                                                    $p->toggle_pago_parcial == 1 ? $p->flete_pagado : $p->flete_total,
                                                    2
                                                ) ?>
                                            <?php endif; ?>

                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <select name="cuenta_asignada[<?= $p->id ?>]"
                                            class="form-control select2-account">
                                        </select>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="card bg-light p-3 mb-3 shadow-sm">
                        <h5 class="mb-0">Total a entregar: <strong id="total-entregar">$0.00</strong></h5>
                        <small class="text-muted">Solo se suman los paquetes exitosos (no marcados como no
                            entregados/regresados)</small>
                    </div>

                    <button type="submit" id="btnRendir" class="btn btn-success">
                        Guardar rendición
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    function bloquearEnvio() {

        const btn = document.getElementById('btnRendir');

        // 🛑 Si ya fue bloqueado, cancelamos submit
        if (btn.disabled) {
            return false;
        }

        // 🔒 Bloqueo inmediato
        btn.disabled = true;

        Swal.fire({
            title: 'Procesando rendición',
            text: 'Por favor espera…',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        return true; // ✔ permite enviar el form
    }
</script>

<script>
    $(document).ready(function() {

        // Inicializar Select2 para selección de cuentas
        $('.select2-account').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Buscar cuenta...',
            allowClear: true,
            minimumInputLength: 1,
            language: {
                inputTooShort: function() {
                    return 'Ingrese 1 o más caracteres';
                }
            },
            ajax: {
                url: "<?= base_url('accounts-list') ?>",
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.map(item => ({
                            id: item.id,
                            text: item.name
                        }))
                    };
                }
            }
        });

        // 🟢 Obtener desde el servidor la cuenta con ID 1
        $.ajax({
            url: "<?= base_url('accounts-list') ?>",
            data: {
                q: "efectivo"
            }, // cualquier valor, el backend lo ignora si devuelves siempre la lista
            dataType: "json",
            success: function(data) {

                // buscar cuenta ID = 1
                const cuenta = data.find(item => item.id == 1);

                if (!cuenta) return; // si no existe, no ponemos nada

                // Colocar como selección inicial en todos los select2
                $('.select2-account').each(function() {
                    let option = new Option(cuenta.name, cuenta.id, true, true);
                    $(this).append(option).trigger('change');
                });
            }
        });

    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        // Función para sincronizar los checkboxes y actualizar el total
        function actualizarEstadoYTotal() {
            let total = 0;

            document.querySelectorAll('.paquete-row').forEach(row => {

                const cbRegresado = row.querySelector('.regresado-checkbox');
                const cbRecolectadoSolo = row.querySelector('.recolectado-solo-checkbox');
                const strongMonto = row.querySelector('.paquete-monto-total');
                const fleteRendido = parseInt(row.dataset.fleteRendido) === 1;


                const tipo = parseInt(row.dataset.tipo);
                const destinos = parseInt(row.dataset.destinos);

                const montoPaquete = Number(row.dataset.monto) || 0;
                const togglePago = parseInt(row.dataset.toggle);
                const fleteTotal = Number(row.dataset.fleteTotal) || 0;
                const fletePagado = Number(row.dataset.fletePagado) || 0;

                // Determinar cuál flete usar
                const montoVendedor = (togglePago === 0) ?
                    fleteTotal // Pago completo
                    :
                    fletePagado; // Pago parcial

                // =======================================================
                // A. SINCRONIZACIÓN (Regresado vs Solo Recolectado)
                // =======================================================
                if (cbRegresado && cbRecolectadoSolo) {
                    if (cbRegresado.checked) {
                        cbRecolectadoSolo.disabled = true;
                        cbRecolectadoSolo.checked = false;
                    } else {
                        cbRecolectadoSolo.disabled = false;
                    }
                }

                // Limpieza de clases
                row.classList.remove('bg-warning', 'bg-danger-light', 'bg-success-light', 'bg-info-light');

                // B. REGLA 1: Si está regresado → NO SUMA NADA
                if (cbRegresado.checked) {

                    if (tipo === 3 && destinos === 1) {
                        row.classList.add('bg-danger-light'); // Falló la recolección única
                    } else {
                        row.classList.add('bg-warning'); // No retirado general
                    }

                    return;
                }

                // 🟢 SERVICIO DE RECOLECCIÓN
                if (tipo === 3) {

                    // Caso: Solo recolectado (sin entrega final)
                    if (cbRecolectadoSolo && cbRecolectadoSolo.checked) {

                        // 🚀 Aplicar Muteado 🚀
                        if (strongMonto) strongMonto.classList.add('muteado');

                        if (!fleteRendido) {
                            total += montoVendedor;
                        }

                        row.classList.add('bg-info-light'); // pendiente de entrega

                    } else {

                        // 🛑 Quitar Muteado (Se asume Recolectado + Entregado Exitoso) 🛑
                        if (strongMonto) strongMonto.classList.remove('muteado');

                        // Caso: recolectado + entregado
                        total += montoPaquete;

                        if (!fleteRendido) {
                            total += montoVendedor;
                        }

                        row.classList.add('bg-success-light');
                    }

                } else {

                    // 🟦 SERVICIO NORMAL (1 y 2)

                    // 🛑 Quitar Muteado (No aplica para servicios normales) 🛑
                    if (strongMonto) strongMonto.classList.remove('muteado');

                    total += montoPaquete;
                    row.classList.add('bg-success-light');
                }
            });

            document.getElementById('total-entregar').innerText = '$' + total.toFixed(2);
        }

        actualizarEstadoYTotal();

        document.querySelectorAll('.regresado-checkbox').forEach(cb => {
            cb.addEventListener('change', actualizarEstadoYTotal);
        });

        document.querySelectorAll('.recolectado-solo-checkbox').forEach(cb => {
            cb.addEventListener('change', actualizarEstadoYTotal);
        });
    });
</script>

<?= $this->endSection() ?>