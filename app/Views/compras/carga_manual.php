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
    #dropZoneExcel.drag-over {
        border-color: #0d6efd;
        background: #e8f0fe;
    }
    #globalDropOverlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(13, 110, 253, 0.08); backdrop-filter: blur(3px);
        z-index: 9999; display: flex; justify-content: center; align-items: center;
    }
    #globalDropOverlay .overlay-content {
        border: 2px dashed #0d6efd; padding: 40px 60px; border-radius: 12px;
        text-align: center; background: white; color: #0d6efd;
        box-shadow: 0 10px 30px rgba(0,0,0,.1);
    }
    .badge-nuevo    { background: #fd7e14; color: #fff; }
    .badge-duplicado { background: #dc3545; color: #fff; }
    .badge-nocuadra { background: #ffc107; color: #000; }
    .row-warning td { background: #fff3cd !important; }
    .row-danger td  { background: #f8d7da !important; }
    .row-muted td   { background: #f1f3f5 !important; color: #6c757d; }
    .expand-row td  { background: #f8f9fb; }
    .stat-box { border: 1px solid #dee2e6; border-radius: 8px; padding: 12px 16px; }
</style>

<div id="globalDropOverlay" class="d-none">
    <div class="overlay-content">
        <i class="fas fa-file-excel fa-3x mb-3 text-success"></i>
        <h5>Suelta el Excel aquí</h5>
        <small class="text-muted">Plantilla de compras tradicionales (.xlsx)</small>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between flex-wrap gap-2">
                <div>
                    <h4 class="header-title mb-0">
                        <i class="fas fa-file-excel me-2 text-success"></i>
                        Carga Masiva — Facturas Tradicionales
                    </h4>
                    <small class="text-muted">Carga facturas pre-electrónicas desde plantilla Excel</small>
                </div>
                <a href="<?= base_url('purchases/manual/template') ?>" class="btn btn-outline-success">
                    <i class="fas fa-download me-1"></i> Descargar Plantilla
                </a>
            </div>

            <div class="card-body">

                <!-- DROP ZONE -->
                <div id="dropZoneExcel" class="mb-4">
                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                    <p class="mb-1 fw-semibold">Arrastra o haz clic para cargar la plantilla Excel</p>
                    <small class="text-muted">Solo archivos .xlsx — Sin límite de filas</small>
                    <input type="file" id="excelFile" accept=".xlsx" hidden>
                </div>

                <!-- ESTADO DE CARGA -->
                <div id="statusArea" class="d-none mb-3">
                    <div class="d-flex gap-2 text-primary">
                        <div class="spinner-border spinner-border-sm"></div>
                        <span id="statusText">Procesando archivo…</span>
                    </div>
                </div>

                <!-- TABLA PREVIEW -->
                <div id="previewArea" class="d-none">
                    <div id="statsRow" class="row mb-3 g-2"></div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle" id="facturasTable">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width:40px;">#</th>
                                    <th style="width:110px;">Correlativo</th>
                                    <th>Proveedor</th>
                                    <th style="width:110px;">Fecha</th>
                                    <th style="width:90px;" class="text-center">Tipo</th>
                                    <th style="width:120px;" class="text-end">Total</th>
                                    <th style="width:90px;" class="text-center">Estado</th>
                                    <th style="width:50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="facturasBody"></tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <button class="btn btn-outline-secondary btn-sm" id="btnLimpiar">
                            <i class="fas fa-times me-1"></i> Limpiar
                        </button>
                        <button class="btn btn-success" id="btnProcesar" disabled>
                            <i class="fas fa-check me-1"></i> Procesar Facturas
                        </button>
                    </div>
                </div>

            </div><!-- card-body -->
        </div>
    </div>
</div>

<script>
const BASE = '<?= base_url() ?>';
let facturasData = [];   // parsed from server
let dragCounter = 0;

// ── Drop zone ──────────────────────────────────────────────
const dropZone = document.getElementById('dropZoneExcel');
const fileInput = document.getElementById('excelFile');

dropZone.addEventListener('click', () => fileInput.click());
fileInput.addEventListener('change', () => {
    if (fileInput.files[0]) uploadExcel(fileInput.files[0]);
});

// Global drag & drop
window.addEventListener('dragenter', e => {
    e.preventDefault();
    dragCounter++;
    document.getElementById('globalDropOverlay').classList.remove('d-none');
});
window.addEventListener('dragover', e => e.preventDefault());
window.addEventListener('dragleave', () => {
    if (--dragCounter === 0)
        document.getElementById('globalDropOverlay').classList.add('d-none');
});
window.addEventListener('drop', e => {
    e.preventDefault();
    document.getElementById('globalDropOverlay').classList.add('d-none');
    dragCounter = 0;
    const f = e.dataTransfer.files[0];
    if (f && f.name.toLowerCase().endsWith('.xlsx')) uploadExcel(f);
    else if (f) Swal.fire('Archivo inválido', 'Solo se aceptan archivos .xlsx', 'warning');
});

// Local drop zone highlight
dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('drag-over');
    const f = e.dataTransfer.files[0];
    if (f) uploadExcel(f);
});

// ── Upload & preview ──────────────────────────────────────
function setStatus(msg) {
    document.getElementById('statusArea').classList.remove('d-none');
    document.getElementById('statusText').textContent = msg;
}
function hideStatus() {
    document.getElementById('statusArea').classList.add('d-none');
}

async function uploadExcel(file) {
    setStatus('Leyendo y validando el Excel…');
    document.getElementById('previewArea').classList.add('d-none');

    const fd = new FormData();
    fd.append('excel', file);

    try {
        const res  = await fetch(BASE + 'purchases/manual/preview', { method: 'POST', body: fd });
        const data = await res.json();

        hideStatus();

        if (!data.success) {
            Swal.fire('Error al leer el Excel', data.message ?? 'Error desconocido', 'error');
            return;
        }

        facturasData = data.facturas;
        renderPreview();

    } catch (err) {
        hideStatus();
        Swal.fire('Error de red', err.message, 'error');
    }
}

// ── Render preview table ──────────────────────────────────
function renderPreview() {
    const body = document.getElementById('facturasBody');
    body.innerHTML = '';

    if (!facturasData.length) {
        document.getElementById('previewArea').classList.add('d-none');
        return;
    }

    const TIPO_LABEL = { '03': 'CCF', '01': 'Factura' };

    facturasData.forEach((fac, idx) => {

        const detId = 'det_' + idx;
        const badges = [];
        if (fac.duplicado)               badges.push('<span class="badge badge-duplicado ms-1">Duplicado</span>');
        if (fac.proveedor_nuevo)         badges.push('<span class="badge badge-nuevo ms-1">Prov. nuevo</span>');
        if (fac.tiene_productos_nuevos)  badges.push('<span class="badge bg-warning text-dark ms-1">Prod. nuevo</span>');
        if (!fac.cuadra)                 badges.push('<span class="badge badge-nocuadra ms-1">No cuadra</span>');

        const rowClass = fac.duplicado ? 'row-danger' : (!fac.cuadra ? 'row-warning' : '');

        const estadoIcon = fac.cuadra
            ? '<i class="fas fa-check-circle text-success" title="Totales cuadran"></i>'
            : '<i class="fas fa-exclamation-triangle text-warning" title="Totales no cuadran"></i>';

        body.innerHTML += `
            <tr class="main-row ${rowClass}" data-idx="${idx}" style="cursor:pointer;">
                <td>${idx + 1}
                    <button class="btn btn-sm btn-outline-danger ms-1 btn-remove" data-idx="${idx}" title="Quitar">×</button>
                </td>
                <td><code>${esc(fac.correlativo)}</code></td>
                <td>
                    <strong>${esc(fac.proveedor_nombre)}</strong>
                    ${badges.join('')}
                    ${fac.proveedor_nuevo && fac.nit_ruc ? `<br><small class="text-muted">NIT/NRC: ${esc(fac.nit_ruc)}</small>` : ''}
                    ${fac.proveedor_nuevo && !fac.nit_ruc ? `<br><small class="text-danger"><i class="fas fa-exclamation-circle"></i> Sin NIT/NRC — se creará sin datos fiscales</small>` : ''}
                </td>
                <td>${formatFecha(fac.fecha)}</td>
                <td class="text-center"><span class="badge bg-secondary">${TIPO_LABEL[fac.tipo_dte] ?? fac.tipo_dte}</span></td>
                <td class="text-end fw-bold">$${parseFloat(fac.total_declarado).toFixed(2)}</td>
                <td class="text-center">${estadoIcon}</td>
                <td class="text-center">
                    <i class="fas fa-chevron-down text-muted toggle-icon" data-target="${detId}"></i>
                </td>
            </tr>
            <tr id="${detId}" class="expand-row d-none">
                <td colspan="8">
                    <div class="p-2">
                        ${renderLineas(fac)}
                    </div>
                </td>
            </tr>
        `;
    });

    // Click to expand
    body.querySelectorAll('.main-row').forEach(tr => {
        tr.addEventListener('click', e => {
            if (e.target.closest('.btn-remove')) return;
            const idx = tr.dataset.idx;
            const detRow = document.getElementById('det_' + idx);
            const icon = tr.querySelector('.toggle-icon');
            detRow.classList.toggle('d-none');
            icon.classList.toggle('fa-chevron-down');
            icon.classList.toggle('fa-chevron-up');
        });
    });

    // Remove buttons
    body.querySelectorAll('.btn-remove').forEach(btn => {
        btn.addEventListener('click', e => {
            e.stopPropagation();
            facturasData.splice(parseInt(btn.dataset.idx), 1);
            renderPreview();
        });
    });

    renderStats();
    document.getElementById('previewArea').classList.remove('d-none');
    document.getElementById('btnProcesar').disabled = facturasData.length === 0;
}

function renderLineas(fac) {
    if (!fac.lineas?.length) return '<small class="text-muted">Sin líneas de detalle</small>';

    const rows = fac.lineas.map((l, i) => {
        const badge = l.producto_nuevo ? '<span class="badge bg-warning text-dark ms-1" style="font-size:10px;">nuevo</span>' : '';
        return `<tr>
            <td class="text-end text-muted" style="font-size:12px;">${i + 1}</td>
            <td>${esc(l.descripcion)}${badge}</td>
            <td class="text-end">${parseFloat(l.cantidad).toFixed(2)}</td>
            <td class="text-end">$${parseFloat(l.precio_unitario).toFixed(4)}</td>
            <td class="text-end">$${parseFloat(l.venta_gravada).toFixed(2)}</td>
        </tr>`;
    }).join('');

    const suma = fac.lineas.reduce((acc, l) => acc + parseFloat(l.venta_gravada || 0), 0);
    const diff = Math.abs(fac.total_declarado - (suma + fac.iva_declarado));
    const cuadraColor = diff <= 0.02 ? 'text-success' : 'text-danger fw-bold';

    return `
        <table class="table table-sm table-bordered mb-1" style="font-size:12.5px;">
            <thead class="table-light">
                <tr>
                    <th style="width:30px;">#</th>
                    <th>Descripción</th>
                    <th class="text-end" style="width:80px;">Cantidad</th>
                    <th class="text-end" style="width:100px;">Precio Unit</th>
                    <th class="text-end" style="width:110px;">Venta Gravada</th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
            <tfoot class="table-light">
                <tr>
                    <td colspan="4" class="text-end text-muted">∑ Venta Gravada + IVA ($${parseFloat(fac.iva_declarado).toFixed(2)})</td>
                    <td class="text-end ${cuadraColor}">$${(suma + fac.iva_declarado).toFixed(2)} / $${parseFloat(fac.total_declarado).toFixed(2)}</td>
                </tr>
            </tfoot>
        </table>
    `;
}

function renderStats() {
    const total    = facturasData.length;
    const monto    = facturasData.reduce((a, f) => a + parseFloat(f.total_declarado || 0), 0);
    const nuevosProv = facturasData.filter(f => f.proveedor_nuevo).length;
    const noCuadra = facturasData.filter(f => !f.cuadra).length;
    const duplicados = facturasData.filter(f => f.duplicado).length;

    document.getElementById('statsRow').innerHTML = `
        <div class="col-auto"><div class="stat-box text-center"><div class="fw-bold fs-5">${total}</div><small class="text-muted">Facturas</small></div></div>
        <div class="col-auto"><div class="stat-box text-center"><div class="fw-bold fs-5">$${monto.toFixed(2)}</div><small class="text-muted">Monto total</small></div></div>
        ${nuevosProv  > 0 ? `<div class="col-auto"><div class="stat-box text-center border-warning"><div class="fw-bold fs-5 text-warning">${nuevosProv}</div><small class="text-muted">Prov. nuevos</small></div></div>` : ''}
        ${noCuadra    > 0 ? `<div class="col-auto"><div class="stat-box text-center border-warning"><div class="fw-bold fs-5 text-warning">${noCuadra}</div><small class="text-muted">No cuadran</small></div></div>` : ''}
        ${duplicados  > 0 ? `<div class="col-auto"><div class="stat-box text-center border-danger"><div class="fw-bold fs-5 text-danger">${duplicados}</div><small class="text-muted">Duplicados</small></div></div>` : ''}
    `;
}

// ── Procesar ──────────────────────────────────────────────
document.getElementById('btnProcesar').addEventListener('click', async () => {

    if (!facturasData.length) return;

    // Bloquear si hay duplicados
    const duplicados = facturasData.filter(f => f.duplicado);
    if (duplicados.length) {
        Swal.fire({
            icon: 'warning',
            title: 'Hay facturas duplicadas',
            html: `<small>Quita las filas marcadas en rojo antes de procesar:<br><b>${duplicados.map(f => f.correlativo).join(', ')}</b></small>`,
        });
        return;
    }

    // Aviso si no cuadran
    const noCuadra = facturasData.filter(f => !f.cuadra);
    let confirmHtml = buildResumenHtml();
    if (noCuadra.length) {
        confirmHtml += `<div class="alert alert-warning mt-2 mb-0" style="font-size:12px;">
            <i class="fas fa-exclamation-triangle me-1"></i>
            <b>${noCuadra.length}</b> factura(s) con totales que no cuadran. Se procesarán igual con los valores ingresados.
        </div>`;
    }

    const ok = await Swal.fire({
        title: 'Confirmar carga',
        html: confirmHtml,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-check me-1"></i> Procesar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#198754',
    });
    if (!ok.isConfirmed) return;

    // Process in batches of 50 (JSON payload)
    const BATCH = 50;
    const lotes = [];
    for (let i = 0; i < facturasData.length; i += BATCH)
        lotes.push(facturasData.slice(i, i + BATCH));

    let totalProcesadas = 0, todasSaltadas = [], todosErrores = [];

    Swal.fire({
        title: 'Procesando…',
        html: `<small class="text-muted">Lote 1 de ${lotes.length}…</small>`,
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    for (let i = 0; i < lotes.length; i++) {
        Swal.update({ html: `<small class="text-muted">Lote ${i + 1} de ${lotes.length} — ${lotes[i].length} facturas…</small>` });

        try {
            const res  = await fetch(BASE + 'purchases/manual/process', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ facturas: lotes[i] }),
            });
            const data = await res.json();

            if (data.success) {
                totalProcesadas += data.total ?? 0;
                if (data.saltadas?.length) todasSaltadas.push(...data.saltadas);
                if (data.errores?.length)  todosErrores.push(...data.errores);
            } else {
                todosErrores.push(`Lote ${i + 1}: ${data.message ?? 'error desconocido'}`);
            }
        } catch (err) {
            todosErrores.push(`Lote ${i + 1}: error de red (${err.message})`);
        }
    }

    Swal.close();

    const resultHtml = `
        <div style="text-align:left; font-size:14px;">
            <table class="table table-sm table-borderless">
                <tbody>
                    <tr><td class="text-muted">Compras registradas:</td><td><b>${totalProcesadas}</b></td></tr>
                    ${todasSaltadas.length ? `<tr><td class="text-muted">Duplicadas omitidas:</td><td><b class="text-warning">${todasSaltadas.length}</b></td></tr>` : ''}
                    ${todosErrores.length  ? `<tr><td class="text-muted">Con errores:</td><td><b class="text-danger">${todosErrores.length}</b></td></tr>` : ''}
                </tbody>
            </table>
            ${todosErrores.length ? `<hr><small class="text-danger"><b>Errores:</b><br>${todosErrores.join('<br>')}</small>` : ''}
        </div>`;

    await Swal.fire({
        icon: todosErrores.length ? 'warning' : 'success',
        title: '¡Carga completada!',
        html: resultHtml,
        confirmButtonColor: '#198754',
    });

    facturasData = [];
    fileInput.value = '';
    renderPreview();
});

// ── Limpiar ───────────────────────────────────────────────
document.getElementById('btnLimpiar').addEventListener('click', () => {
    facturasData = [];
    fileInput.value = '';
    document.getElementById('previewArea').classList.add('d-none');
});

// ── Helpers ───────────────────────────────────────────────
function esc(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function formatFecha(fecha) {
    if (!fecha) return '—';
    const p = fecha.split('-');
    if (p.length === 3) return `${p[2]}/${p[1]}/${p[0]}`;
    return fecha;
}

function buildResumenHtml() {
    const total = facturasData.length;
    const monto = facturasData.reduce((a, f) => a + parseFloat(f.total_declarado || 0), 0);
    const nuevosProv = facturasData.filter(f => f.proveedor_nuevo).length;

    return `
        <div style="text-align:left; font-size:14px;">
            <table class="table table-sm table-borderless mb-2">
                <tbody>
                    <tr><td class="text-muted">Facturas:</td><td><b>${total}</b></td></tr>
                    <tr><td class="text-muted">Monto total:</td><td><b>$${monto.toFixed(2)}</b></td></tr>
                    ${nuevosProv > 0 ? `<tr><td class="text-muted">Proveedores nuevos:</td><td><b class="text-warning">${nuevosProv}</b></td></tr>` : ''}
                </tbody>
            </table>
            <hr class="my-2">
            <div style="max-height:180px; overflow-y:auto;">
                ${facturasData.map(f => `
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">${esc(f.correlativo)} — ${esc(f.proveedor_nombre)}</small>
                        <small><b>$${parseFloat(f.total_declarado).toFixed(2)}</b></small>
                    </div>`).join('')}
            </div>
        </div>`;
}
</script>

<?= $this->endSection() ?>
