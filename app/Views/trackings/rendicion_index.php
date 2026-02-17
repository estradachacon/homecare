<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    .muteado {
        background-color: #e9ecef !important;
        color: #6c757d !important;
        text-decoration: line-through;
    }

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

    .bg-success-light {
        background-color: #d4edda !important;
    }

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
                    onsubmit="return confirmarRendicion(event);">

                    <input type="hidden" name="tracking_id" value="<?= $tracking->id ?>">
                    <input type="hidden" name="total_efectivo" id="input-total-efectivo">
                    <input type="hidden" name="total_otras_cuentas" id="input-total-otras">


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
                    <div class="card bg-light p-3 mb-3 shadow-sm">
                        <h5 class="mb-0">Total otras cuentas: <strong id="total-otras">$0.00</strong></h5>
                        <small class="text-muted">Solo paquetes exitosos que NO estén en cuenta efectivo</small>
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
    function confirmarRendicion(e) {

        e.preventDefault(); // 🚫 detenemos envío automático

        // 🔎 Tomar los totales actuales
        const totalEfectivo = document.getElementById('total-entregar').innerText;
        const totalOtras = document.getElementById('total-otras').innerText;

        Swal.fire({
            title: 'Confirmar rendición',
            html: `
            <div style="text-align:left; font-size:15px;">
                <p><strong>Total en efectivo:</strong> ${totalEfectivo}</p>
                <p><strong>Total otras cuentas:</strong> ${totalOtras}</p>
                <hr>
                <p>¿Deseas continuar con la rendición?</p>
            </div>
        `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#dc3545',
            confirmButtonText: 'Sí, confirmar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {

            if (result.isConfirmed) {

                const btn = document.getElementById('btnRendir');
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

                const limpioEfectivo = totalEfectivo.replace('$', '');
                const limpioOtras = totalOtras.replace('$', '');

                // Asignar a los hidden
                document.getElementById('input-total-efectivo').value = limpioEfectivo;
                document.getElementById('input-total-otras').value = limpioOtras;

                e.target.submit(); 
            }
        });

        return false;
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

        function actualizarEstadoYTotal() {

            let totalEfectivo = 0;
            let totalOtras = 0;

            document.querySelectorAll('.paquete-row').forEach(row => {

                const cbRegresado = row.querySelector('.regresado-checkbox');
                const cbRecolectadoSolo = row.querySelector('.recolectado-solo-checkbox');
                const strongMonto = row.querySelector('.paquete-monto-total');
                const selectCuenta = row.querySelector('.select2-account');

                const cuentaSeleccionada = parseInt(selectCuenta?.value) || 0;

                const fleteRendido = parseInt(row.dataset.fleteRendido) === 1;
                const tipo = parseInt(row.dataset.tipo);
                const destinos = parseInt(row.dataset.destinos);

                const montoPaquete = Number(row.dataset.monto) || 0;
                const togglePago = parseInt(row.dataset.toggle);
                const fleteTotal = Number(row.dataset.fleteTotal) || 0;
                const fletePagado = Number(row.dataset.fletePagado) || 0;

                const montoVendedor = (togglePago === 0) ? fleteTotal : fletePagado;

                let subtotal = 0;

                // =========================
                // SINCRONIZACIÓN
                // =========================
                if (cbRegresado && cbRecolectadoSolo) {
                    if (cbRegresado.checked) {
                        cbRecolectadoSolo.disabled = true;
                        cbRecolectadoSolo.checked = false;
                    } else {
                        cbRecolectadoSolo.disabled = false;
                    }
                }

                row.classList.remove('bg-warning', 'bg-danger-light', 'bg-success-light', 'bg-info-light');

                // ❌ REGRESADO → NO SUMA
                if (cbRegresado.checked) {

                    if (tipo === 3 && destinos === 1) {
                        row.classList.add('bg-danger-light');
                    } else {
                        row.classList.add('bg-warning');
                    }

                    return;
                }

                // =========================
                // SERVICIO RECOLECCIÓN
                // =========================
                if (tipo === 3) {

                    if (cbRecolectadoSolo && cbRecolectadoSolo.checked) {

                        if (strongMonto) strongMonto.classList.add('muteado');

                        if (!fleteRendido) {
                            subtotal += montoVendedor;
                        }

                        row.classList.add('bg-info-light');

                    } else {

                        if (strongMonto) strongMonto.classList.remove('muteado');

                        subtotal += montoPaquete;

                        if (!fleteRendido) {
                            subtotal += montoVendedor;
                        }

                        row.classList.add('bg-success-light');
                    }

                } else {

                    if (strongMonto) strongMonto.classList.remove('muteado');

                    subtotal += montoPaquete;
                    row.classList.add('bg-success-light');
                }

                // =========================
                // 🔥 AQUÍ HACEMOS LA SEPARACIÓN POR CUENTA
                // =========================
                if (cuentaSeleccionada === 1) {
                    totalEfectivo += subtotal;
                } else if (cuentaSeleccionada > 1) {
                    totalOtras += subtotal;
                }

            });

            document.getElementById('total-entregar').innerText = '$' + totalEfectivo.toFixed(2);
            document.getElementById('total-otras').innerText = '$' + totalOtras.toFixed(2);
        }

        actualizarEstadoYTotal();

        // 🔥 Recalcular cuando cambie la cuenta (Select2)
        $(document).on('change', '.select2-account', function() {
            actualizarEstadoYTotal();
        });

        document.querySelectorAll('.regresado-checkbox').forEach(cb => {
            cb.addEventListener('change', actualizarEstadoYTotal);
        });

        document.querySelectorAll('.recolectado-solo-checkbox').forEach(cb => {
            cb.addEventListener('change', actualizarEstadoYTotal);
        });
    });
</script>

<?= $this->endSection() ?>