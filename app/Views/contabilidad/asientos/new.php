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
                    <i class="fa-solid fa-plus-circle me-2 mr-1"></i>Nuevo Asiento Contable
                    <span class="badge bg-secondary ms-2 text-white mr-1">AST-<?= str_pad($nextNumero, 5, '0', STR_PAD_LEFT) ?></span>
                </h4>
                <div class="ms-auto">
                    <a href="<?= base_url('contabilidad/asientos') ?>" class="btn btn-outline-secondary btn-sm">
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
                                        data-anio="<?= $p->anio ?>">
                                    <?= $mesesN[$p->mes] ?> <?= $p->anio ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Fecha <span class="text-danger">*</span></label>
                        <input type="date" id="fechaAsiento" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Tipo</label>
                        <select id="tipoAsiento" class="form-select">
                            <option value="DIARIO">DIARIO</option>
                            <option value="AJUSTE">AJUSTE</option>
                            <option value="APERTURA">APERTURA</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Descripción <span class="text-danger">*</span></label>
                        <input type="text" id="descAsiento" class="form-control" placeholder="Descripción del asiento">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Referencia</label>
                        <input type="text" id="refAsiento" class="form-control" placeholder="Opcional">
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
                        <i class="fa-solid fa-save"></i> Guardar como Borrador
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
let contadorLineas = 0;

function agregarLinea() {
    contadorLineas++;
    const i = contadorLineas;

    const row = `
    <tr class="linea-asiento" id="linea-${i}">
        <td class="text-center text-muted small">${i}</td>
        <td>
            <select class="form-control form-control-sm" id="cuenta-${i}"></select>
        </td>
        <td><input type="text" class="form-control form-control-sm" id="desc-${i}"></td>
        <td><input type="number" class="form-control form-control-sm monto-debe" id="debe-${i}" value="0.00" oninput="recalcular()"></td>
        <td><input type="number" class="form-control form-control-sm monto-haber" id="haber-${i}" value="0.00" oninput="recalcular()"></td>
        <td>
            <button class="btn btn-sm btn-outline-danger" onclick="eliminarLinea(${i})">X</button>
        </td>
    </tr>`;

    $('#lineasBody').append(row);

    const select = document.getElementById(`cuenta-${i}`);
    if (select.tomselect) select.tomselect.destroy();

    new TomSelect(select, {
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
    const periodoId   = $('#periodoId').val();
    const fecha       = $('#fechaAsiento').val();
    const descripcion = $('#descAsiento').val().trim();
    const tipo        = $('#tipoAsiento').val();
    const referencia  = $('#refAsiento').val().trim();

    if (!periodoId || !fecha || !descripcion) {
        Swal.fire('Faltan datos', 'Completa período, fecha y descripción', 'warning');
        return;
    }

    // ── 1. RECOLECTAR LÍNEAS PRIMERO ──────────────────────────────
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

    // ── 2. ADVERTENCIAS ───────────────────────────────────────────
    const advertencias = [];

    // 📆 Fecha fuera del período — parse manual para evitar desfase de zona horaria
    const [anioF, mesF, diaF] = fecha.split('-').map(Number);
    const selected    = $('#periodoId option:selected');
    const periodoMes  = parseInt(selected.data('mes'));
    const periodoAnio = parseInt(selected.data('anio'));

    if (mesF !== periodoMes || anioF !== periodoAnio) {
        advertencias.push(`📆 La fecha <strong>${diaF}/${mesF}/${anioF}</strong> no corresponde al período <strong>${selected.text()}</strong>`);
    }

    // ⚖️ Naturaleza de cuentas
    lineas.forEach(l => {
        const texto = l.cuenta.toUpperCase();

        const esDeudora =
            texto.includes('CAJA') || texto.includes('BANCOS') ||
            texto.includes('CLIENTES') || texto.includes('INVENTARIO') ||
            texto.includes('GASTO') || texto.includes('COSTO') ||
            texto.includes('ACTIVO');

        const esAcreedora =
            texto.includes('VENTAS') || texto.includes('IVA DÉBITO') ||
            texto.includes('PAGAR') || texto.includes('CAPITAL') ||
            texto.includes('INGRESO') || texto.includes('PASIVO');

        if (esDeudora && l.haber > 0 && l.debe === 0) {
            advertencias.push(`⚠️ <strong>${l.cuenta}</strong> normalmente va en Debe`);
        }
        if (esAcreedora && l.debe > 0 && l.haber === 0) {
            advertencias.push(`⚠️ <strong>${l.cuenta}</strong> normalmente va en Haber`);
        }
    });

    if (totalDebe === 0 || totalHaber === 0) {
        advertencias.push('⚠️ El asiento solo tiene Debe o solo Haber');
    }

    if (lineas.length > 10) {
        advertencias.push('🤔 Este asiento tiene muchas líneas, verifica si es correcto');
    }

    // ── 3. CONSTRUIR HTML DEL RESUMEN ─────────────────────────────
    let html = `
        <div style="text-align:left; font-size:13px">
        <strong>Descripción:</strong> ${descripcion}<br>
        <strong>Fecha:</strong> ${diaF}/${mesF}/${anioF} &nbsp;|&nbsp; <strong>Período:</strong> ${selected.text()}<br><br>
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr>
                    <th style="text-align:left; border-bottom:1px solid #ccc; padding:4px">Cuenta</th>
                    <th style="text-align:right; border-bottom:1px solid #ccc; padding:4px">Debe</th>
                    <th style="text-align:right; border-bottom:1px solid #ccc; padding:4px">Haber</th>
                </tr>
            </thead>
            <tbody>`;

    lineas.forEach(l => {
        html += `
            <tr>
                <td style="padding:3px 4px">${l.cuenta}</td>
                <td style="text-align:right; padding:3px 4px">${l.debe  > 0 ? '$ ' + l.debe.toFixed(2)  : ''}</td>
                <td style="text-align:right; padding:3px 4px">${l.haber > 0 ? '$ ' + l.haber.toFixed(2) : ''}</td>
            </tr>`;
    });

    html += `
            </tbody>
        </table>
        <hr style="margin:8px 0">
        <strong>Total Debe:</strong> $ ${totalDebe.toFixed(2)}&nbsp;&nbsp;
        <strong>Total Haber:</strong> $ ${totalHaber.toFixed(2)}`;

    if (diferencia > 0.01) {
        html += `<br><span style="color:red"><strong>⚠️ Diferencia: $ ${diferencia.toFixed(2)}</strong></span>`;
    }

    if (advertencias.length > 0) {
        html += `
        <div style="margin-top:10px; color:#92400e; background:#fff3cd; padding:10px; border-radius:6px; font-size:12px">
            <strong>Advertencias:</strong><br>
            ${advertencias.map(a => `• ${a}`).join('<br>')}
        </div>`;
    }

    html += `</div>`;

    // ── 4. CONFIRMAR Y GUARDAR ────────────────────────────────────
    const icon  = diferencia > 0.01 || advertencias.length > 0 ? 'warning' : 'question';
    const title = diferencia > 0.01 ? '⚠️ Asiento NO cuadra' : 'Confirmar asiento';

    Swal.fire({
        title,
        html,
        width: 620,
        icon,
        showCancelButton: true,
        confirmButtonText: diferencia > 0.01 ? 'Guardar de todos modos' : 'Guardar borrador',
        cancelButtonText: 'Revisar'
    }).then(result => {
        if (!result.isConfirmed) return;

        if (diferencia > 0.01) {
            Swal.fire('Error', 'El asiento no está balanceado. Corrígelo antes de guardar.', 'error');
            return;
        }

        fetch('<?= base_url('contabilidad/asientos/store') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ periodo_id: periodoId, fecha, descripcion, tipo, referencia, lineas })
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

// Agregar 2 líneas iniciales
agregarLinea();
agregarLinea();
</script>

<?= $this->endSection() ?>