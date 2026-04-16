<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-md-10">
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
                        <a href="<?= base_url('contabilidad') ?>" class="btn btn-outline-secondary">
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
// Precargar cuentas configuradas
const cuentasPrecargadas = <?= json_encode(array_map(fn($c) => ['id'=>$c->id,'text'=>$c->codigo.' - '.$c->nombre], $cuentas)) ?>;
const mapaId = {};
cuentasPrecargadas.forEach(c => { mapaId[c.id] = c; });

document.querySelectorAll('.config-select').forEach(el => {
    const $el = $(el);
    $el.select2({
        width: '100%',
        placeholder: 'Seleccionar cuenta...',
        allowClear: true,
        minimumInputLength: 2,
        language: 'es',
        ajax: {
            url: '<?= base_url('contabilidad/plan-cuentas/search') ?>',
            dataType: 'json', delay: 200,
            data: p => ({q: p.term}),
            processResults: d => d,
            cache: true
        }
    });

    const val = el.dataset.val;
    if (val && mapaId[val]) {
        const opt = new Option(mapaId[val].text, mapaId[val].id, true, true);
        $el.append(opt).trigger('change');
    }
});

function guardarConfig() {
    const form = document.getElementById('formConfig');
    const data = new FormData(form);

    fetch('<?= base_url('contabilidad/configuracion/guardar') ?>', { method:'POST', body: data })
        .then(r => r.json()).then(d => {
            if (d.success) {
                Swal.fire('Guardado', d.message, 'success');
            } else {
                const msgs = d.errors ? Object.values(d.errors).join('<br>') : d.message;
                document.getElementById('erroresConfig').classList.remove('d-none');
                document.getElementById('erroresConfig').innerHTML = msgs;
                Swal.fire('Error', d.message, 'error');
            }
        });
}
</script>

<?= $this->endSection() ?>
