<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title mb-0"><i class="fa-solid fa-cog me-2"></i>Configuración Contable</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fa-solid fa-circle-info me-2"></i>
                    Configura las cuentas contables que se usarán automáticamente en los procesos del sistema.
                    Solo se muestran cuentas que aceptan movimientos.
                </div>

                <form id="formConfig">
                    <div class="row g-3">
                        <div class="col-12"><h6 class="fw-bold text-muted border-bottom pb-1">CUENTAS DE BALANCE</h6></div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Cuenta de Caja</label>
                            <select name="cuenta_caja_id" class="form-select config-select" data-val="<?= $config->cuenta_caja_id ?? '' ?>"></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Cuenta de Bancos</label>
                            <select name="cuenta_banco_id" class="form-select config-select" data-val="<?= $config->cuenta_banco_id ?? '' ?>"></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Cuentas por Cobrar (CxC)</label>
                            <select name="cuenta_cxc_id" class="form-select config-select" data-val="<?= $config->cuenta_cxc_id ?? '' ?>"></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Cuentas por Pagar (CxP)</label>
                            <select name="cuenta_cxp_id" class="form-select config-select" data-val="<?= $config->cuenta_cxp_id ?? '' ?>"></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Inventario</label>
                            <select name="cuenta_inventario_id" class="form-select config-select" data-val="<?= $config->cuenta_inventario_id ?? '' ?>"></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Capital</label>
                            <select name="cuenta_capital_id" class="form-select config-select" data-val="<?= $config->cuenta_capital_id ?? '' ?>"></select>
                        </div>

                        <div class="col-12"><h6 class="fw-bold text-muted border-bottom pb-1 mt-2">CUENTAS DE RESULTADOS</h6></div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Ventas (Ingresos)</label>
                            <select name="cuenta_ventas_id" class="form-select config-select" data-val="<?= $config->cuenta_ventas_id ?? '' ?>"></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Costo de Ventas</label>
                            <select name="cuenta_costos_id" class="form-select config-select" data-val="<?= $config->cuenta_costos_id ?? '' ?>"></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Gastos de Administración</label>
                            <select name="cuenta_gastos_admin_id" class="form-select config-select" data-val="<?= $config->cuenta_gastos_admin_id ?? '' ?>"></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Gastos de Venta</label>
                            <select name="cuenta_gastos_venta_id" class="form-select config-select" data-val="<?= $config->cuenta_gastos_venta_id ?? '' ?>"></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Cuenta de Resultado (Utilidad/Pérdida)</label>
                            <select name="cuenta_resultado_id" class="form-select config-select" data-val="<?= $config->cuenta_resultado_id ?? '' ?>"></select>
                        </div>

                        <div class="col-12"><h6 class="fw-bold text-muted border-bottom pb-1 mt-2">CUENTAS DE RETENCIONES</h6></div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">IVA Débito Fiscal</label>
                            <small class="text-muted d-block mb-1">IVA generado en ventas (pasivo)</small>
                            <select name="cuenta_iva_debito_id" class="form-select config-select" data-val="<?= $config->cuenta_iva_debito_id ?? '' ?>"></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Retención IVA 1% por Cobrar</label>
                            <small class="text-muted d-block mb-1">Retención del 1% aplicada a clientes (activo)</small>
                            <select name="cuenta_retencion_cobrar_id" class="form-select config-select" data-val="<?= $config->cuenta_retencion_cobrar_id ?? '' ?>"></select>
                        </div>

                        <div class="col-12"><h6 class="fw-bold text-muted border-bottom pb-1 mt-2">CUENTAS DE SERVICIOS</h6></div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Cuenta Servicios 1 — Etiqueta</label>
                            <input type="text" name="cuenta_ventas_servicio1_label" class="form-control form-control-sm mb-1"
                                placeholder="Ej: Ingresos por Servicios Médicos"
                                value="<?= esc($config->cuenta_ventas_servicio1_label ?? '') ?>">
                            <label class="form-label small fw-semibold">Cuenta Servicios 1</label>
                            <select name="cuenta_ventas_servicio1_id" class="form-select config-select" data-val="<?= $config->cuenta_ventas_servicio1_id ?? '' ?>"></select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Cuenta Servicios 2 — Etiqueta</label>
                            <input type="text" name="cuenta_ventas_servicio2_label" class="form-control form-control-sm mb-1"
                                placeholder="Ej: Ingresos por Servicios Domiciliarios"
                                value="<?= esc($config->cuenta_ventas_servicio2_label ?? '') ?>">
                            <label class="form-label small fw-semibold">Cuenta Servicios 2</label>
                            <select name="cuenta_ventas_servicio2_id" class="form-select config-select" data-val="<?= $config->cuenta_ventas_servicio2_id ?? '' ?>"></select>
                        </div>

                        <div class="col-12"><h6 class="fw-bold text-muted border-bottom pb-1 mt-2">TIPOS DE PARTIDA — PROCESOS AUTOMÁTICOS</h6></div>

                        <div class="col-12">
                            <div class="alert alert-light border small mb-1">
                                <i class="fa-solid fa-circle-info me-1 text-primary"></i>
                                Selecciona el tipo de partida que se asignará automáticamente a los asientos generados por cada proceso.
                                Si no seleccionas ninguno, el asiento se creará sin tipo de partida.
                                <a href="<?= base_url('contabilidad/mantenimientos/tipos-partida') ?>" class="ms-2">
                                    <i class="fa-solid fa-tags"></i> Administrar tipos de partida
                                </a>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Carga de Facturas (JSON / Ventas automáticas)</label>
                            <small class="text-muted d-block mb-1">Tipo de partida para asientos generados al cargar CCF / FAC</small>
                            <select name="tipo_partida_ventas_id" class="form-select">
                                <option value="">— Sin tipo de partida —</option>
                                <?php foreach ($tiposPartida as $tp): ?>
                                    <option value="<?= $tp->id ?>" <?= ($config->tipo_partida_ventas_id ?? null) == $tp->id ? 'selected' : '' ?>>
                                        <?= esc($tp->nombre) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Carga de Pagos de Clientes</label>
                            <small class="text-muted d-block mb-1">Tipo de partida para asientos generados al registrar pagos</small>
                            <select name="tipo_partida_pagos_id" class="form-select">
                                <option value="">— Sin tipo de partida —</option>
                                <?php foreach ($tiposPartida as $tp): ?>
                                    <option value="<?= $tp->id ?>" <?= ($config->tipo_partida_pagos_id ?? null) == $tp->id ? 'selected' : '' ?>>
                                        <?= esc($tp->nombre) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Remesas Contables</label>
                            <small class="text-muted d-block mb-1">Tipo de partida que se pre-selecciona al crear una remesa (cuadre de caja grande)</small>
                            <select name="tipo_partida_remesas_id" class="form-select">
                                <option value="">— Sin tipo de partida —</option>
                                <?php foreach ($tiposPartida as $tp): ?>
                                    <option value="<?= $tp->id ?>" <?= ($config->tipo_partida_remesas_id ?? null) == $tp->id ? 'selected' : '' ?>>
                                        <?= esc($tp->nombre) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12"><h6 class="fw-bold text-muted border-bottom pb-1 mt-2">OPCIONES GENERALES</h6></div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Moneda</label>
                            <select name="moneda" class="form-select">
                                <option value="USD" <?= ($config->moneda ?? 'USD') === 'USD' ? 'selected' : '' ?>>USD ($)</option>
                                <option value="GTQ" <?= ($config->moneda ?? '') === 'GTQ' ? 'selected' : '' ?>>GTQ (Q)</option>
                                <option value="HNL" <?= ($config->moneda ?? '') === 'HNL' ? 'selected' : '' ?>>HNL (L)</option>
                                <option value="NIO" <?= ($config->moneda ?? '') === 'NIO' ? 'selected' : '' ?>>NIO (C$)</option>
                                <option value="CRC" <?= ($config->moneda ?? '') === 'CRC' ? 'selected' : '' ?>>CRC (₡)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Dígitos Decimales</label>
                            <select name="digitos_decimales" class="form-select">
                                <option value="2" <?= ($config->digitos_decimales ?? 2) == 2 ? 'selected' : '' ?>>2</option>
                                <option value="4" <?= ($config->digitos_decimales ?? 2) == 4 ? 'selected' : '' ?>>4</option>
                            </select>
                        </div>
                    </div>

                    <div id="erroresConfig" class="alert alert-danger mt-3 d-none"></div>

                    <div class="mt-4 d-flex gap-2">
                        <a href="<?= base_url('contabilidad') ?>" class="btn btn-outline-secondary mr-1">
                            <i class="fa-solid fa-arrow-left"></i> Volver
                        </a>
                        <button type="button" class="btn btn-success ms-auto" onclick="guardarConfig()">
                            <i class="fa-solid fa-save"></i> Guardar Configuración
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    // Mapa de cuentas precargadas para mostrar el valor guardado sin necesidad de buscar
    const cuentasPrecargadas = <?= json_encode(array_map(fn($c) => ['id' => $c->id, 'text' => $c->codigo . ' - ' . $c->nombre], $cuentas)) ?>;
    const mapaId = {};
    cuentasPrecargadas.forEach(c => { mapaId[String(c.id)] = c; });

    document.querySelectorAll('.config-select').forEach(function (el) {
        const $el = $(el);

        $el.select2({
            width: '100%',
            placeholder: 'Seleccionar cuenta...',
            allowClear: true,
            minimumInputLength: 2,
            language: 'es',
            ajax: {
                url: '<?= base_url('contabilidad/plan-cuentas/search') ?>',
                dataType: 'json',
                delay: 200,
                data: p => ({ q: p.term }),
                processResults: d => d,
                cache: true
            }
        });

        // Precargar el valor ya guardado en BD
        const val = String(el.dataset.val || '');
        if (val && mapaId[val]) {
            const opt = new Option(mapaId[val].text, mapaId[val].id, true, true);
            $el.append(opt).trigger('change');
        }
    });

    window.guardarConfig = function () {
        const form = document.getElementById('formConfig');
        const data = new FormData(form);

        fetch('<?= base_url('contabilidad/configuracion/guardar') ?>', { method: 'POST', body: data })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    Swal.fire('Guardado', d.message, 'success');
                } else {
                    const msgs = d.errors ? Object.values(d.errors).join('<br>') : d.message;
                    document.getElementById('erroresConfig').classList.remove('d-none');
                    document.getElementById('erroresConfig').innerHTML = msgs;
                    Swal.fire('Error', d.message, 'error');
                }
            });
    };
});
</script>

<?= $this->endSection() ?>
