<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    #dropZoneExcel {
        border: 2px dashed #6c757d;
        border-radius: 12px;
        padding: 32px 24px;
        text-align: center;
        cursor: pointer;
        transition: border-color .2s, background .2s;
        background: #f8f9fa;
    }
    #dropZoneExcel.drag-over { border-color: #198754; background: #d1e7dd; }
    #globalDropOverlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(25, 135, 84, 0.08); backdrop-filter: blur(3px);
        z-index: 9999; display: flex; justify-content: center; align-items: center;
    }
    #globalDropOverlay .overlay-content {
        border: 2px dashed #198754; padding: 40px 60px; border-radius: 12px;
        text-align: center; background: white; color: #198754;
        box-shadow: 0 10px 30px rgba(0,0,0,.1);
    }
    .badge-nuevo     { background: #fd7e14; color: #fff; font-size: 11px; }
    .badge-duplicado { background: #dc3545; color: #fff; font-size: 11px; }
    .badge-nocuadra  { background: #ffc107; color: #000; font-size: 11px; }
    .row-danger td  { background: #f8d7da !important; }
    .row-warning td { background: #fff3cd !important; }
    .expand-row td  { background: #f8f9fb; }
    .stat-box { border: 1px solid #dee2e6; border-radius: 8px; padding: 10px 14px; min-width: 90px; }
</style>

<div id="globalDropOverlay" class="d-none">
    <div class="overlay-content">
        <i class="fas fa-file-excel fa-3x mb-3"></i>
        <h5>Suelta el Excel aquí</h5>
        <small class="text-muted">Plantilla de facturas tradicionales (.xlsx)</small>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between flex-wrap gap-2">
                <div>
                    <h4 class="header-title mb-0">
                        <i class="fas fa-file-excel me-2 text-success"></i>
                        Carga Masiva — Facturas Tradicionales de Venta
                    </h4>
                    <small class="text-muted">Facturas pre-electrónicas cargadas desde plantilla Excel</small>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-outline-secondary btn-sm" id="btnCrearMacro" title="Genera plantilla .xlsm con formulario VBA (requiere Excel instalado en el servidor)">
                        <i class="fas fa-magic me-1"></i> Generar plantilla con Macro
                    </button>
                </div>
            </div>

            <div class="card-body">

                <!-- INSTRUCCIONES -->
                <div class="alert alert-info py-2 mb-3" style="font-size:13px;">
                    <i class="fas fa-info-circle me-1"></i>
                    <strong>Instrucciones:</strong>
                    Descarga la plantilla y llena las filas <code>CAB</code> (cabecera) y <code>DET</code> (detalles).
                    En <strong>DET</strong>: ingresa el <strong>Código</strong> del producto — la descripción, precio y venta gravada se calculan solos.
                    <strong>CCF</strong>: precios sin IVA, el subtotal (H) debe coincidir con la suma de líneas.
                    <strong>FAC</strong>: precios con IVA incluido, el total (H) debe coincidir con la suma de líneas.
                </div>

                <!-- DROP ZONE -->
                <div id="dropZoneExcel" class="mb-4">
                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                    <p class="mb-1 fw-semibold">Arrastra o haz clic para cargar la plantilla Excel</p>
                    <small class="text-muted">Solo archivos .xlsx — Sin límite de filas</small>
                    <input type="file" id="excelFile" accept=".xlsx,.xlsm" hidden>
                </div>

                <!-- ESTADO -->
                <div id="statusArea" class="d-none mb-3">
                    <div class="d-flex gap-2 text-success">
                        <div class="spinner-border spinner-border-sm"></div>
                        <span id="statusText">Procesando archivo…</span>
                    </div>
                </div>

                <!-- PREVIEW -->
                <div id="previewArea" class="d-none">

                    <div id="statsRow" class="row mb-3 g-2"></div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle" id="facturasTable">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width:40px;">#</th>
                                    <th style="width:120px;">Correlativo</th>
                                    <th>Cliente</th>
                                    <th style="width:110px;">Fecha</th>
                                    <th style="width:85px;" class="text-center">Tipo</th>
                                    <th style="width:115px;" class="text-end">Total</th>
                                    <th style="width:90px;" class="text-center">Estado</th>
                                    <th style="width:40px;"></th>
                                </tr>
                            </thead>
                            <tbody id="facturasBody"></tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <button class="btn btn-outline-secondary btn-sm" id="btnLimpiar">
                            <i class="fas fa-times me-1"></i> Limpiar
                        </button>
                        <button class="btn btn-success px-4" id="btnProcesar" disabled>
                            <i class="fas fa-check me-1"></i> Procesar Facturas
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
const BASE = '<?= base_url() ?>';
let facturasData   = [];
let clientesNuevos = [];
let dragCounter    = 0;

// ── Drop zone ──────────────────────────────────────────────
const dropZone = document.getElementById('dropZoneExcel');
const fileInput = document.getElementById('excelFile');

dropZone.addEventListener('click', () => fileInput.click());
fileInput.addEventListener('change', () => { if (fileInput.files[0]) uploadExcel(fileInput.files[0]); });

window.addEventListener('dragenter', e => { e.preventDefault(); dragCounter++; document.getElementById('globalDropOverlay').classList.remove('d-none'); });
window.addEventListener('dragover',  e => e.preventDefault());
window.addEventListener('dragleave', () => { if (--dragCounter === 0) document.getElementById('globalDropOverlay').classList.add('d-none'); });
window.addEventListener('drop', e => {
    e.preventDefault();
    document.getElementById('globalDropOverlay').classList.add('d-none');
    dragCounter = 0;
    const f = e.dataTransfer.files[0];
    if (f && (f.name.toLowerCase().endsWith('.xlsx') || f.name.toLowerCase().endsWith('.xlsm'))) uploadExcel(f);
    else if (f) Swal.fire('Archivo inválido', 'Solo se aceptan archivos .xlsx o .xlsm', 'warning');
});

dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
dropZone.addEventListener('drop', e => { e.preventDefault(); dropZone.classList.remove('drag-over'); const f = e.dataTransfer.files[0]; if (f) uploadExcel(f); });

// ── Upload → preview ──────────────────────────────────────
async function uploadExcel(file) {
    document.getElementById('statusArea').classList.remove('d-none');
    document.getElementById('statusText').textContent = 'Leyendo y validando el Excel…';
    document.getElementById('previewArea').classList.add('d-none');

    const fd = new FormData();
    fd.append('excel', file);

    try {
        const res  = await fetch(BASE + 'facturas/manual/preview', { method: 'POST', body: fd });
        const data = await res.json();

        document.getElementById('statusArea').classList.add('d-none');

        if (!data.success) { Swal.fire('Error al leer el Excel', data.message ?? 'Error desconocido', 'error'); return; }

        facturasData      = data.facturas;
        clientesNuevos    = data.clientes_nuevos ?? [];
        renderPreview();

    } catch (err) {
        document.getElementById('statusArea').classList.add('d-none');
        Swal.fire('Error de red', err.message, 'error');
    }
}

// ── Render ────────────────────────────────────────────────
function renderPreview() {
    const body = document.getElementById('facturasBody');
    body.innerHTML = '';

    if (!facturasData.length) { document.getElementById('previewArea').classList.add('d-none'); return; }

    const TIPO = { '03': 'CCF', '01': 'Factura' };

    facturasData.forEach((fac, idx) => {

        const detId  = 'det_' + idx;
        const badges = [];
        if (fac.duplicado)              badges.push('<span class="badge badge-duplicado ms-1">Duplicado</span>');
        if (fac.cliente_nuevo)          badges.push('<span class="badge badge-nuevo ms-1">Cliente nuevo</span>');
        if (fac.tiene_productos_nuevos) badges.push('<span class="badge bg-warning text-dark ms-1">Prod. nuevo</span>');
        if (!fac.cuadra)                badges.push('<span class="badge badge-nocuadra ms-1">No cuadra</span>');

        const rowClass = fac.duplicado ? 'row-danger' : (!fac.cuadra ? 'row-warning' : '');

        const estadoIcon = fac.cuadra
            ? '<i class="fas fa-check-circle text-success" title="Totales cuadran"></i>'
            : '<i class="fas fa-exclamation-triangle text-warning" title="Totales no cuadran"></i>';

        body.innerHTML += `
            <tr class="main-row ${rowClass}" data-idx="${idx}" style="cursor:pointer;">
                <td>${idx + 1}
                    <button class="btn btn-sm btn-outline-danger ms-1 btn-remove" data-idx="${idx}">×</button>
                </td>
                <td><code>${esc(fac.correlativo)}</code></td>
                <td>
                    <strong>${esc(fac.cliente_nombre)}</strong>${badges.join('')}
                    ${fac.cliente_nuevo && fac.nit_dui ? `<br><small class="text-muted">NIT/DUI: ${esc(fac.nit_dui)}</small>` : ''}
                    ${fac.cliente_nuevo && !fac.nit_dui ? `<br><small class="text-danger"><i class="fas fa-exclamation-circle"></i> Sin NIT/DUI — se creará con datos mínimos</small>` : ''}
                </td>
                <td>${formatFecha(fac.fecha)}</td>
                <td class="text-center"><span class="badge bg-secondary text-white">${TIPO[fac.tipo_dte] ?? fac.tipo_dte}</span></td>
                <td class="text-end fw-bold">
                    $${parseFloat(fac.subtotal).toFixed(2)}
                    ${fac.tipo_dte === '03' ? `<small class="d-block text-muted" style="font-size:10px;">+IVA $${parseFloat(fac.iva_declarado).toFixed(2)}</small>` : ''}
                </td>
                <td class="text-center">${estadoIcon}</td>
                <td class="text-center">
                    <i class="fas fa-chevron-down text-muted toggle-icon" data-target="${detId}"></i>
                </td>
            </tr>
            <tr id="${detId}" class="expand-row d-none">
                <td colspan="8"><div class="p-2">${renderLineas(fac)}</div></td>
            </tr>`;
    });

    // Expandir/colapsar
    body.querySelectorAll('.main-row').forEach(tr => {
        tr.addEventListener('click', e => {
            if (e.target.closest('.btn-remove')) return;
            const idx = tr.dataset.idx;
            document.getElementById('det_' + idx).classList.toggle('d-none');
            tr.querySelector('.toggle-icon').classList.toggle('fa-chevron-down');
            tr.querySelector('.toggle-icon').classList.toggle('fa-chevron-up');
        });
    });

    // Quitar fila
    body.querySelectorAll('.btn-remove').forEach(btn => {
        btn.addEventListener('click', e => {
            e.stopPropagation();
            facturasData.splice(parseInt(btn.dataset.idx), 1);
            renderPreview();
        });
    });

    renderStats();
    document.getElementById('previewArea').classList.remove('d-none');
    actualizarBotonProcesar();
}

function actualizarBotonProcesar() {
    const btn       = document.getElementById('btnProcesar');
    const dupCount  = facturasData.filter(f => f.duplicado).length;
    const ncCount   = facturasData.filter(f => !f.cuadra).length;
    const bloqueado = facturasData.length === 0 || dupCount > 0 || ncCount > 0;

    btn.disabled = bloqueado;

    if (dupCount > 0 && ncCount > 0) {
        btn.title = `${dupCount} duplicado(s) y ${ncCount} descuadre(s) — corrige antes de procesar`;
    } else if (dupCount > 0) {
        btn.title = `${dupCount} factura(s) duplicada(s) — elimina las filas rojas`;
    } else if (ncCount > 0) {
        btn.title = `${ncCount} factura(s) con totales que no cuadran — revisa las filas amarillas`;
    } else {
        btn.title = '';
    }
}

function renderLineas(fac) {
    if (!fac.lineas?.length) return '<small class="text-muted">Sin líneas</small>';

    const esCCF = fac.tipo_dte === '03';

    const rows = fac.lineas.map((l, i) => {
        const badge = l.producto_nuevo ? '<span class="badge bg-warning text-dark ms-1" style="font-size:10px;">nuevo</span>' : '';
        const codCell = l.codigo ? `<code class="text-secondary" style="font-size:10px;">${esc(l.codigo)}</code> ` : '';
        return `<tr>
            <td class="text-muted text-end" style="font-size:12px;">${i+1}</td>
            <td>${codCell}${esc(l.descripcion)}${badge}</td>
            <td class="text-end">${parseFloat(l.cantidad).toFixed(2)}</td>
            <td class="text-end">$${parseFloat(l.precio_unitario).toFixed(4)}</td>
            <td class="text-end">$${parseFloat(l.venta_gravada).toFixed(2)}</td>
        </tr>`;
    }).join('');

    const suma       = fac.lineas.reduce((a, l) => a + parseFloat(l.venta_gravada || 0), 0);
    const subtotal   = parseFloat(fac.subtotal   || 0);
    const ivaDec     = parseFloat(fac.iva_declarado || 0);
    const totalPagar = esCCF ? subtotal + ivaDec : subtotal;
    const diff       = Math.abs(suma - subtotal);
    const cls        = diff <= 0.02 ? 'text-success' : 'text-danger fw-bold';

    const footerLabel = esCCF ? '∑ Líneas vs Subtotal' : '∑ Líneas vs Total (c/IVA)';
    const footerVal   = esCCF
        ? `$${suma.toFixed(2)} / $${subtotal.toFixed(2)} <small class="text-muted ms-2">Total a pagar: $${totalPagar.toFixed(2)}</small>`
        : `$${suma.toFixed(2)} / $${subtotal.toFixed(2)}`;

    return `
        <table class="table table-sm table-bordered mb-1" style="font-size:12.5px;">
            <thead class="table-light"><tr>
                <th style="width:30px;">#</th>
                <th>Descripción</th>
                <th class="text-end" style="width:80px;">Cantidad</th>
                <th class="text-end" style="width:100px;">Precio Unit.</th>
                <th class="text-end" style="width:110px;">${esCCF ? 'V. Gravada (s/IVA)' : 'V. Gravada (c/IVA)'}</th>
            </tr></thead>
            <tbody>${rows}</tbody>
            <tfoot class="table-light"><tr>
                <td colspan="4" class="text-end text-muted">${footerLabel}</td>
                <td class="text-end ${cls}">${footerVal}</td>
            </tr></tfoot>
        </table>`;
}

function renderStats() {
    const total = facturasData.length;
    const monto = facturasData.reduce((a, f) => {
        const sub = parseFloat(f.subtotal || 0);
        const iva = parseFloat(f.iva_declarado || 0);
        return a + (f.tipo_dte === '03' ? sub + iva : sub);
    }, 0);
    const nuevosCli   = facturasData.filter(f => f.cliente_nuevo).length;
    const noCuadra    = facturasData.filter(f => !f.cuadra).length;
    const duplicados  = facturasData.filter(f => f.duplicado).length;

    document.getElementById('statsRow').innerHTML = `
        <div class="col-auto"><div class="stat-box text-center"><div class="fw-bold fs-5">${total}</div><small class="text-muted">Facturas</small></div></div>
        <div class="col-auto"><div class="stat-box text-center"><div class="fw-bold fs-5">$${monto.toFixed(2)}</div><small class="text-muted">Monto total</small></div></div>
        ${nuevosCli  > 0 ? `<div class="col-auto"><div class="stat-box text-center border-warning"><div class="fw-bold fs-5 text-warning">${nuevosCli}</div><small class="text-muted">Clientes nuevos</small></div></div>` : ''}
        ${noCuadra   > 0 ? `<div class="col-auto"><div class="stat-box text-center border-danger" title="Bloquea el procesamiento"><div class="fw-bold fs-5 text-danger"><i class="fas fa-lock me-1" style="font-size:13px;"></i>${noCuadra}</div><small class="text-danger fw-semibold">No cuadran</small></div></div>` : ''}
        ${duplicados > 0 ? `<div class="col-auto"><div class="stat-box text-center border-danger" title="Bloquea el procesamiento"><div class="fw-bold fs-5 text-danger"><i class="fas fa-lock me-1" style="font-size:13px;"></i>${duplicados}</div><small class="text-danger fw-semibold">Duplicados</small></div></div>` : ''}`;
}

// ── Procesar ──────────────────────────────────────────────
document.getElementById('btnProcesar').addEventListener('click', async () => {

    if (!facturasData.length) return;

    // Doble-check por si alguien llama directamente (el botón ya debería estar bloqueado)
    const dupList = facturasData.filter(f => f.duplicado);
    const ncList  = facturasData.filter(f => !f.cuadra);

    if (dupList.length || ncList.length) {
        const lineas = [];
        if (dupList.length) lineas.push(`<b>${dupList.length} duplicada(s):</b> ${dupList.map(f => esc(f.correlativo)).join(', ')}`);
        if (ncList.length)  lineas.push(`<b>${ncList.length} sin cuadrar:</b> ${ncList.map(f => esc(f.correlativo)).join(', ')}`);
        Swal.fire({ icon: 'error', title: 'No se puede procesar',
            html: `<div style="text-align:left;font-size:13px;">${lineas.join('<br>')}<br><br>Elimina estas filas o corrígelas antes de continuar.</div>` });
        return;
    }

    let confirmHtml = buildResumenHtml();
    if (noCuadra.length) {
        confirmHtml += `<div class="alert alert-warning mt-2 mb-0" style="font-size:12px;">
            <i class="fas fa-exclamation-triangle me-1"></i>
            <b>${noCuadra.length}</b> factura(s) con totales que no cuadran. Se procesarán con los valores ingresados.</div>`;
    }

    const ok = await Swal.fire({
        title: 'Confirmar carga', html: confirmHtml, icon: 'info',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-check me-1"></i> Procesar',
        cancelButtonText: 'Cancelar', confirmButtonColor: '#198754',
    });
    if (!ok.isConfirmed) return;

    // Lotes de 50
    const BATCH  = 50;
    const lotes  = [];
    for (let i = 0; i < facturasData.length; i += BATCH)
        lotes.push(facturasData.slice(i, i + BATCH));

    let totalProc = 0, todasSaltadas = [], todosErrores = [], totalAsientos = 0;

    Swal.fire({ title: 'Procesando…', html: `<small>Lote 1 de ${lotes.length}…</small>`,
        allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    for (let i = 0; i < lotes.length; i++) {
        Swal.update({ html: `<small>Lote ${i+1} de ${lotes.length} — ${lotes[i].length} facturas…</small>` });
        try {
            const res  = await fetch(BASE + 'facturas/manual/process', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ facturas: lotes[i], clientes_nuevos: clientesNuevos }),
            });
            const data = await res.json();
            if (data.success) {
                totalProc      += data.total ?? 0;
                totalAsientos  += data.asientos_creados ?? 0;
                if (data.saltadas?.length)          todasSaltadas.push(...data.saltadas);
                if (data.errores?.length)           todosErrores.push(...data.errores);
                if (data.asientos_omitidos?.length) todosErrores.push(...data.asientos_omitidos.map(m => '⚠ Asiento: ' + m));
            } else {
                todosErrores.push(`Lote ${i+1}: ${data.message ?? 'error'}`);
            }
        } catch (err) {
            todosErrores.push(`Lote ${i+1}: error de red (${err.message})`);
        }
    }

    Swal.close();

    const resultHtml = `
        <div style="text-align:left;font-size:14px;">
            <table class="table table-sm table-borderless">
                <tbody>
                    <tr><td class="text-muted">Facturas registradas:</td><td><b>${totalProc}</b></td></tr>
                    ${totalAsientos  > 0 ? `<tr><td class="text-muted">Asientos contables:</td><td><b class="text-success">${totalAsientos}</b></td></tr>` : ''}
                    ${todasSaltadas.length ? `<tr><td class="text-muted">Duplicadas omitidas:</td><td><b class="text-warning">${todasSaltadas.length}</b></td></tr>` : ''}
                    ${todosErrores.length  ? `<tr><td class="text-muted">Observaciones:</td><td><b class="text-danger">${todosErrores.length}</b></td></tr>` : ''}
                </tbody>
            </table>
            ${todosErrores.length ? `<hr><small class="text-danger"><b>Detalle:</b><br>${todosErrores.join('<br>')}</small>` : ''}
        </div>`;

    await Swal.fire({
        icon: todosErrores.some(e => !e.startsWith('⚠')) ? 'warning' : 'success',
        title: '¡Carga completada!', html: resultHtml, confirmButtonColor: '#198754',
    });

    facturasData = []; fileInput.value = ''; renderPreview();
});

document.getElementById('btnLimpiar').addEventListener('click', () => {
    facturasData = []; fileInput.value = '';
    document.getElementById('previewArea').classList.add('d-none');
});

function esc(s) { return s ? String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') : ''; }
function formatFecha(f) { if (!f) return '—'; const p = f.split('-'); return p.length === 3 ? `${p[2]}/${p[1]}/${p[0]}` : f; }

function buildResumenHtml() {
    const total     = facturasData.length;
    const monto     = facturasData.reduce((a, f) => {
        const sub = parseFloat(f.subtotal || 0);
        const iva = parseFloat(f.iva_declarado || 0);
        return a + (f.tipo_dte === '03' ? sub + iva : sub);
    }, 0);
    const nuevosCli = facturasData.filter(f => f.cliente_nuevo).length;
    const TIPO = { '03': 'CCF', '01': 'FAC' };
    return `<div style="text-align:left;font-size:14px;">
        <table class="table table-sm table-borderless mb-2"><tbody>
            <tr><td class="text-muted">Facturas:</td><td><b>${total}</b></td></tr>
            <tr><td class="text-muted">Total a pagar:</td><td><b>$${monto.toFixed(2)}</b></td></tr>
            ${nuevosCli > 0 ? `<tr><td class="text-muted">Clientes nuevos:</td><td><b class="text-warning">${nuevosCli}</b></td></tr>` : ''}
        </tbody></table>
        <hr class="my-2">
        <div style="max-height:180px;overflow-y:auto;">
            ${facturasData.map(f => {
                const sub = parseFloat(f.subtotal || 0);
                const iva = parseFloat(f.iva_declarado || 0);
                const tot = f.tipo_dte === '03' ? sub + iva : sub;
                return `<div class="d-flex justify-content-between">
                    <small class="text-muted">${esc(f.correlativo)} — ${esc(f.cliente_nombre)} <span class="badge bg-secondary text-white" style="font-size:9px;">${TIPO[f.tipo_dte]??f.tipo_dte}</span></small>
                    <small><b>$${tot.toFixed(2)}</b></small>
                </div>`;
            }).join('')}
        </div>
    </div>`;
}

// ── Generar plantilla macro (solo admins) ─────────────────────────────────
const btnMacro = document.getElementById('btnCrearMacro');
if (btnMacro) {
    btnMacro.addEventListener('click', async () => {
        const ok = await Swal.fire({
            title: 'Generar plantilla con Macro VBA',
            html: `<div style="text-align:left;font-size:13px;">
                <p>Este proceso:</p>
                <ul>
                  <li>Ejecuta un script PowerShell en el servidor</li>
                  <li>Crea <code>plantilla_base.xlsm</code> con formulario VBA integrado</li>
                  <li><strong>Requiere Microsoft Excel instalado en el servidor</strong></li>
                  <li>Una vez generada, "Descargar Plantilla" la usará automáticamente con los datos actuales</li>
                </ul>
                <div class="alert alert-warning py-1 mt-2" style="font-size:12px;">
                  <i class="fas fa-clock me-1"></i> Puede tardar 15–30 segundos.
                </div>`,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Generar ahora',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#198754',
        });
        if (!ok.isConfirmed) return;

        btnMacro.disabled = true;
        btnMacro.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Generando…';

        try {
            const res  = await fetch(BASE + 'facturas/manual/crear-macro');
            const data = await res.json();
            if (data.success) {
                const result = await Swal.fire({
                    icon: 'success',
                    title: '¡Plantilla con macro lista!',
                    html: `<div style="font-size:13px;">
                        <p>El formulario VBA fue creado con los datos actuales de clientes y productos.</p>
                        <p class="mb-0"><strong>Ahora descarga la plantilla para usarla en Excel:</strong></p>
                    </div>`,
                    confirmButtonText: '<i class="fas fa-download me-1"></i> Descargar ahora',
                    showCancelButton: true,
                    cancelButtonText: 'Cerrar',
                    confirmButtonColor: '#198754',
                });
                if (result.isConfirmed) {
                    window.location.href = BASE + 'facturas/manual/template';
                }
            } else {
                await Swal.fire({
                    icon: 'error', title: 'Error al generar',
                    html: `<p>${data.message}</p>${data.output ? `<pre style="font-size:10px;text-align:left;max-height:180px;overflow:auto;">${esc(data.output)}</pre>` : ''}`,
                });
            }
        } catch (err) {
            Swal.fire('Error de red', err.message, 'error');
        } finally {
            btnMacro.disabled = false;
            btnMacro.innerHTML = '<i class="fas fa-magic me-1"></i> Generar plantilla con Macro';
        }
    });
}
</script>

<?= $this->endSection() ?>
