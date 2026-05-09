<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
    .linea-asiento td { vertical-align: middle; }
    .linea-asiento input { border: 1px solid #dee2e6; }
    .table-responsive { overflow: visible !important; }
    table { overflow: visible !important; }
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="header-title mb-0">
                    <i class="fa-solid fa-pen-to-square me-2 mr-1"></i>Editar Asiento Contable
                    <span class="badge bg-secondary ms-2 text-white mr-1">AST-<?= str_pad($asiento->numero_asiento, 5, '0', STR_PAD_LEFT) ?></span>
                    <span class="badge bg-success text-white ms-1"><?= esc($asiento->estado) ?></span>
                </h4>
                <div class="ms-auto">
                    <a href="<?= base_url('contabilidad/asientos/' . $asiento->id) ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
            <div class="card-body">

                <!-- Cabecera -->
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Período <span class="text-danger">*</span></label>
                        <select id="periodoId" class="form-select">
                            <option value="">Seleccionar período</option>
                            <?php
                            $mesesN = [
                                1=>'Enero', 2=>'Febrero', 3=>'Marzo', 4=>'Abril',
                                5=>'Mayo', 6=>'Junio', 7=>'Julio', 8=>'Agosto',
                                9=>'Septiembre', 10=>'Octubre', 11=>'Noviembre', 12=>'Diciembre'
                            ];
                            foreach ($periodos as $p): ?>
                                <option value="<?= $p->id ?>"
                                        data-mes="<?= $p->mes ?>"
                                        data-anio="<?= $p->anio ?>"
                                        <?= $p->id == $asiento->periodo_id ? 'selected' : '' ?>>
                                    <?= $mesesN[$p->mes] ?> <?= $p->anio ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Fecha <span class="text-danger">*</span></label>
                        <input type="date" id="fechaAsiento" class="form-control" value="<?= esc($asiento->fecha) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Tipo</label>
                        <select id="tipoAsiento" class="form-select">
                            <option value="DIARIO"   <?= $asiento->tipo === 'DIARIO'   ? 'selected' : '' ?>>DIARIO</option>
                            <option value="AJUSTE"   <?= $asiento->tipo === 'AJUSTE'   ? 'selected' : '' ?>>AJUSTE</option>
                            <option value="APERTURA" <?= $asiento->tipo === 'APERTURA' ? 'selected' : '' ?>>APERTURA</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Tipo de Partida</label>
                        <select id="tipoPartidaId" class="form-select" onchange="toggleNumeroPartida()">
                            <option value="">— Ninguno —</option>
                            <?php foreach ($tiposPartida as $tp): ?>
                                <option value="<?= $tp->id ?>" <?= $asiento->tipo_partida_id == $tp->id ? 'selected' : '' ?>>
                                    <?= esc($tp->nombre) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-1" id="colNumeroPartida" style="<?= empty($asiento->tipo_partida_id) ? 'display:none' : '' ?>">
                        <label class="form-label small fw-semibold">Correlativo</label>
                        <input type="number" id="numeroPartida" class="form-control" min="1" step="1"
                               value="<?= $asiento->numero_partida ?? '' ?>" placeholder="—">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Descripción <span class="text-danger">*</span></label>
                        <input type="text" id="descAsiento" class="form-control" value="<?= esc($asiento->descripcion) ?>" placeholder="Descripción del asiento">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Referencia</label>
                        <input type="text" id="refAsiento" class="form-control" value="<?= esc($asiento->referencia ?? '') ?>" placeholder="Opcional">
                    </div>
                </div>

                <!-- Tabla de líneas -->
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px">#</th>
                                <th>Cuenta</th>
                                <th style="width:350px">Descripción línea</th>
                                <th class="text-end" style="width:140px">Debe ($)</th>
                                <th class="text-end" style="width:140px">Haber ($)</th>
                                <th style="width:40px"></th>
                            </tr>
                        </thead>
                        <tbody id="lineasBody">
                            <!-- líneas generadas por JS -->
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td colspan="3" class="text-end fw-bold">TOTALES:</td>
                                <td class="text-end fw-bold text-primary" id="totalDebe">$ 0.00</td>
                                <td class="text-end fw-bold text-success" id="totalHaber">$ 0.00</td>
                                <td></td>
                            </tr>
                            <tr id="rowDiferencia" class="d-none">
                                <td colspan="3" class="text-end fw-semibold text-danger">Diferencia:</td>
                                <td colspan="2" class="text-end fw-bold text-danger" id="diferencia"></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="d-flex gap-2 mt-3">
                    <button class="btn btn-outline-secondary btn-sm mr-2" onclick="agregarLinea()">
                        <i class="fa-solid fa-plus"></i> Agregar línea
                    </button>
                    <button class="btn btn-success ms-auto" onclick="guardarAsiento()">
                        <i class="fa-solid fa-save"></i> Guardar cambios
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
let contadorLineas = 0;

// Líneas existentes precargadas desde PHP
const lineasExistentes = <?= json_encode(array_map(function($l) {
    return [
        'cuenta_id'   => $l->cuenta_id,
        'cuenta_text' => $l->codigo . ' - ' . $l->cuenta_nombre,
        'descripcion' => $l->descripcion ?? '',
        'debe'        => (float)$l->debe,
        'haber'       => (float)$l->haber,
    ];
}, $lineas)) ?>;

function agregarLinea(opts = {}) {
    contadorLineas++;
    const i = contadorLineas;

    const row = `
    <tr class="linea-asiento" id="linea-${i}">
        <td class="text-center text-muted small">${i}</td>
        <td>
            <select class="form-control form-control-sm" id="cuenta-${i}"></select>
        </td>
        <td><input type="text" class="form-control form-control-sm" id="desc-${i}" value="${escHtml(opts.descripcion ?? '')}"></td>
        <td><input type="number" class="form-control form-control-sm monto-debe" id="debe-${i}" value="${(opts.debe ?? 0).toFixed(2)}" oninput="recalcular()"></td>
        <td><input type="number" class="form-control form-control-sm monto-haber" id="haber-${i}" value="${(opts.haber ?? 0).toFixed(2)}" oninput="recalcular()"></td>
        <td>
            <button class="btn btn-sm btn-outline-danger" onclick="eliminarLinea(${i})">X</button>
        </td>
    </tr>`;

    $('#lineasBody').append(row);

    const select = document.getElementById(`cuenta-${i}`);
    if (select.tomselect) select.tomselect.destroy();

    const ts = new TomSelect(select, {
        valueField: 'id',
        labelField: 'text',
        searchField: 'text',
        placeholder: 'Buscar cuenta...',
        dropdownParent: 'body',
        preload: true,
        load: function(query, callback) {
            fetch(`<?= base_url('contabilidad/plan-cuentas/search') ?>?q=${encodeURIComponent(query || '')}`)
                .then(r => r.json())
                .then(data => callback(data.results))
                .catch(() => callback());
        }
    });

    // Precargar cuenta si viene de una línea existente
    if (opts.cuenta_id) {
        ts.addOption({ id: opts.cuenta_id, text: opts.cuenta_text });
        ts.setValue(opts.cuenta_id, true);
    }

    recalcular();
}

function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

function eliminarLinea(i) {
    document.getElementById('linea-' + i)?.remove();
    recalcular();
}

function recalcular() {
    let debe = 0, haber = 0;
    document.querySelectorAll('.monto-debe').forEach(el => debe  += parseFloat(el.value) || 0);
    document.querySelectorAll('.monto-haber').forEach(el => haber += parseFloat(el.value) || 0);
    document.getElementById('totalDebe').textContent  = '$ ' + debe.toFixed(2);
    document.getElementById('totalHaber').textContent = '$ ' + haber.toFixed(2);
    const diff = Math.abs(debe - haber);
    const rowDiff = document.getElementById('rowDiferencia');
    if (diff > 0.005) {
        rowDiff.classList.remove('d-none');
        document.getElementById('diferencia').textContent = '$ ' + diff.toFixed(2);
    } else {
        rowDiff.classList.add('d-none');
    }
}

function guardarAsiento() {
    const periodoId     = $('#periodoId').val();
    const fecha         = $('#fechaAsiento').val();
    const descripcion   = $('#descAsiento').val().trim();
    const tipo          = $('#tipoAsiento').val();
    const tipoPartidaId = $('#tipoPartidaId').val() || null;
    const numeroPartida = $('#numeroPartida').val() !== '' ? parseInt($('#numeroPartida').val()) : null;
    const referencia    = $('#refAsiento').val().trim();

    if (!periodoId || !fecha || !descripcion) {
        Swal.fire('Faltan datos', 'Completa período, fecha y descripción', 'warning');
        return;
    }

    const lineas = [];
    document.querySelectorAll('.linea-asiento').forEach(row => {
        const id         = row.id.replace('linea-', '');
        const el         = document.getElementById(`cuenta-${id}`);
        const cuentaId   = el?.tomselect ? el.tomselect.getValue() : el?.value;
        const cuentaText = el?.tomselect
            ? (el.tomselect.options[cuentaId]?.text ?? 'Cuenta no visible')
            : ($(`#cuenta-${id} option:selected`).text() || 'Cuenta no visible');
        const desc  = document.getElementById(`desc-${id}`)?.value ?? '';
        const debe  = parseFloat(document.getElementById(`debe-${id}`)?.value)  || 0;
        const haber = parseFloat(document.getElementById(`haber-${id}`)?.value) || 0;

        if (cuentaId && (debe > 0 || haber > 0)) {
            lineas.push({ cuenta_id: cuentaId, cuenta: cuentaText, descripcion: desc, debe, haber });
        }
    });

    if (lineas.length < 2) {
        Swal.fire('Error', 'Se requieren al menos 2 líneas con montos', 'error');
        return;
    }

    const totalDebe  = lineas.reduce((s, l) => s + l.debe,  0);
    const totalHaber = lineas.reduce((s, l) => s + l.haber, 0);
    const diferencia = Math.abs(totalDebe - totalHaber);

    const advertencias = [];

    const [anioF, mesF, diaF] = fecha.split('-').map(Number);
    const selected    = $('#periodoId option:selected');
    const periodoMes  = parseInt(selected.data('mes'));
    const periodoAnio = parseInt(selected.data('anio'));

    if (mesF !== periodoMes || anioF !== periodoAnio) {
        advertencias.push(`📆 La fecha <strong>${diaF}/${mesF}/${anioF}</strong> no corresponde al período <strong>${selected.text()}</strong>`);
    }

    lineas.forEach(l => {
        const texto = l.cuenta.toUpperCase();
        const esDeudora  = texto.includes('CAJA') || texto.includes('BANCOS') || texto.includes('CLIENTES') || texto.includes('INVENTARIO') || texto.includes('GASTO') || texto.includes('COSTO') || texto.includes('ACTIVO');
        const esAcreedora = texto.includes('VENTAS') || texto.includes('IVA DÉBITO') || texto.includes('PAGAR') || texto.includes('CAPITAL') || texto.includes('INGRESO') || texto.includes('PASIVO');
        if (esDeudora  && l.haber > 0 && l.debe  === 0) advertencias.push(`⚠️ <strong>${l.cuenta}</strong> normalmente va en Debe`);
        if (esAcreedora && l.debe > 0  && l.haber === 0) advertencias.push(`⚠️ <strong>${l.cuenta}</strong> normalmente va en Haber`);
    });

    if (totalDebe === 0 || totalHaber === 0) advertencias.push('⚠️ El asiento solo tiene Debe o solo Haber');

    let html = `
        <div style="text-align:left; font-size:13px">
        <strong>Descripción:</strong> ${descripcion}<br>
        <strong>Fecha:</strong> ${diaF}/${mesF}/${anioF} &nbsp;|&nbsp; <strong>Período:</strong> ${selected.text()}<br><br>
        <table style="width:100%; border-collapse:collapse;">
            <thead><tr>
                <th style="text-align:left; border-bottom:1px solid #ccc; padding:4px">Cuenta</th>
                <th style="text-align:right; border-bottom:1px solid #ccc; padding:4px">Debe</th>
                <th style="text-align:right; border-bottom:1px solid #ccc; padding:4px">Haber</th>
            </tr></thead>
            <tbody>`;

    lineas.forEach(l => {
        html += `<tr>
            <td style="padding:3px 4px">${l.cuenta}</td>
            <td style="text-align:right; padding:3px 4px">${l.debe  > 0 ? '$ ' + l.debe.toFixed(2)  : ''}</td>
            <td style="text-align:right; padding:3px 4px">${l.haber > 0 ? '$ ' + l.haber.toFixed(2) : ''}</td>
        </tr>`;
    });

    html += `</tbody></table>
        <hr style="margin:8px 0">
        <strong>Total Debe:</strong> $ ${totalDebe.toFixed(2)}&nbsp;&nbsp;
        <strong>Total Haber:</strong> $ ${totalHaber.toFixed(2)}`;

    if (diferencia > 0.01) html += `<br><span style="color:red"><strong>⚠️ Diferencia: $ ${diferencia.toFixed(2)}</strong></span>`;

    if (advertencias.length > 0) {
        html += `<div style="margin-top:10px; color:#92400e; background:#fff3cd; padding:10px; border-radius:6px; font-size:12px">
            <strong>Advertencias:</strong><br>
            ${advertencias.map(a => `• ${a}`).join('<br>')}
        </div>`;
    }
    html += `</div>`;

    const icon  = diferencia > 0.01 || advertencias.length > 0 ? 'warning' : 'question';
    const title = diferencia > 0.01 ? '⚠️ Asiento NO cuadra' : 'Confirmar cambios';

    Swal.fire({
        title,
        html,
        width: 620,
        icon,
        showCancelButton: true,
        confirmButtonText: diferencia > 0.01 ? 'Guardar de todos modos' : 'Guardar cambios',
        cancelButtonText: 'Revisar'
    }).then(result => {
        if (!result.isConfirmed) return;

        if (diferencia > 0.01) {
            Swal.fire('Error', 'El asiento no está balanceado. Corrígelo antes de guardar.', 'error');
            return;
        }

        fetch('<?= base_url('contabilidad/asientos/actualizar/') ?><?= $asiento->id ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ periodo_id: periodoId, fecha, descripcion, tipo, tipo_partida_id: tipoPartidaId, numero_partida: numeroPartida, referencia, lineas })
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                Swal.fire('Guardado', d.message, 'success').then(() => {
                    window.location = '<?= base_url('contabilidad/asientos/') ?>' + d.id;
                });
            } else {
                Swal.fire('Error', d.message, 'error');
            }
        });
    });
}

function toggleNumeroPartida() {
    const col = document.getElementById('colNumeroPartida');
    if ($('#tipoPartidaId').val()) {
        col.style.display = '';
    } else {
        col.style.display = 'none';
        $('#numeroPartida').val('');
    }
}

// Cargar líneas existentes al iniciar
lineasExistentes.forEach(l => agregarLinea(l));
</script>

<?= $this->endSection() ?>
