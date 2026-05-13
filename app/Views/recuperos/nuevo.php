<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
/* ── Encabezado de tabla ──────────────────────────────────────── */
#tablaFacturas thead {
    background-color: #111827 !important;
}
#tablaFacturas thead th {
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #5c5c5c !important;
    padding: 9px 10px;
    border-color: #374151 !important;
    white-space: nowrap;
}
#tablaFacturas thead th input[type="checkbox"] { accent-color: #28a745; }

/* ── Estados de fila ─────────────────────────────────────────── */
.factura-row { transition: background .12s, opacity .12s; }

/* Sin seleccionar: gris suave, texto atenuado */
.factura-row.row-inactive {
    background: #f3f4f6;
    opacity: .78;
}
.factura-row.row-inactive td { color: #6b6f75; }
.factura-row.row-inactive .font-weight-bold { font-weight: 400 !important; color: #3a4c70 !important; }
.factura-row.row-inactive .badge { opacity: .55; }
.factura-row.row-inactive .saldo-bar-fill { background: #9ca3af; }

/* Seleccionada: verde suave, borde izquierdo destacado */
.factura-row.row-active {
    background: #f0fdf4 !important;
    border-left: 4px solid #16a34a;
}
.factura-row.row-active td { color: #14532d; }
.factura-row.row-active .monto-input { border-color: #16a34a !important; }

/* ── Campo monto ──────────────────────────────────────────────── */
.monto-input:disabled {
    background: #e5e7eb !important;
    border-color: #d1d5db !important;
    color: #9ca3af !important;
    cursor: not-allowed;
    font-weight: 400;
}
.monto-input:not(:disabled) {
    background: #fff !important;
    border: 2px solid #16a34a !important;
    color: #14532d !important;
    font-weight: 700;
    box-shadow: 0 0 0 2px #bbf7d0;
}

/* ── Botón detalle ────────────────────────────────────────────── */
.btn-detalle {
    background: none;
    border: none;
    padding: 0;
    color: #2563eb;
    font-size: .8rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    line-height: 1.3;
}
.btn-detalle:hover { color: #1d4ed8; text-decoration: underline; }

/* ── Saldo bar ───────────────────────────────────────────────── */
.saldo-bar { height: 4px; border-radius: 2px; background: #e5e7eb; overflow: hidden; margin-top: 4px; }
.saldo-bar-fill { height: 100%; background: #16a34a; border-radius: 2px; }
.badge-dias { font-size: .70rem; }

/* ── Fila con recupero pendiente ─────────────────────────────── */
.factura-row.row-recupero-pendiente {
    background: #fffbeb !important;
    opacity: 1;
}
.factura-row.row-recupero-pendiente td { color: #92400e; }
.factura-row.row-recupero-pendiente .chk-factura { cursor: not-allowed; }
.badge-recupero-pendiente {
    font-size: .62rem;
    background: #f59e0b;
    color: #fff;
    padding: 2px 5px;
    border-radius: 3px;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    white-space: nowrap;
}

/* ── Select2 sm ──────────────────────────────────────────────── */
#clienteId + .select2-container .select2-selection--single,
.select2-container .select2-selection--single {
    height: 31px !important;
    border: 1px solid #ced4da;
    border-radius: .2rem;
}
.select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 29px !important; padding-left: .5rem; font-size: .875rem; }
.select2-container--default .select2-selection--single .select2-selection__arrow  { height: 29px !important; }
.select2-container--default .select2-selection--single .select2-selection__placeholder { color: #6c757d; }

/* ── Modal detalle factura ───────────────────────────────────── */
#modalDetalleFactura .modal-header { background: #111827; color: #fff; padding: .6rem 1rem; }
#modalDetalleFactura .modal-title { font-size: .95rem; }
#modalDetalleFactura .close { color: #fff; opacity: .7; }
#modalDetalleFactura .close:hover { opacity: 1; }
#tablaDetalleModal thead { background: #f1f3f5 !important; }
#tablaDetalleModal thead th { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #212529 !important; padding: 7px 8px; border-color: #dee2e6 !important; }
</style>

<div class="row">

    <!-- ── COLUMNA IZQUIERDA: datos del recupero ─────────────────── -->
    <div class="col-lg-4 mb-3">
        <div class="card h-100">
            <div class="card-header py-2">
                <h5 class="header-title mb-0">
                    <i class="fa-solid fa-file-invoice-dollar text-success mr-2"></i>Datos del Recupero
                </h5>
            </div>
            <div class="card-body">

                <!-- N° Recupero (readonly) -->
                <div class="form-group mb-3">
                    <label class="font-weight-bold small">N° Recupero</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light">
                                <i class="fa-solid fa-hashtag text-muted"></i>
                            </span>
                        </div>
                        <input type="text" id="numeroRecupero"
                               class="form-control text-muted font-italic"
                               value="Se asignará al guardar" readonly>
                    </div>
                    <small class="text-muted">El correlativo se genera en el momento de guardar</small>
                </div>

                <!-- Cliente -->
                <div class="form-group mb-3">
                    <label class="font-weight-bold small">Cliente <span class="text-danger">*</span></label>
                    <select id="clienteId" class="form-control form-control-sm" style="width:100%;"></select>
                    <small class="text-muted">Escribe al menos 2 letras para buscar</small>
                </div>

                <!-- Fecha -->
                <div class="form-group mb-3">
                    <label class="font-weight-bold small">Fecha <span class="text-danger">*</span></label>
                    <input type="date" id="fecha" class="form-control form-control-sm"
                           value="<?= date('Y-m-d') ?>">
                </div>

                <!-- Forma de cobro -->
                <div class="form-group mb-3">
                    <label class="font-weight-bold small">Forma de cobro <span class="text-danger">*</span></label>
                    <select id="formaCobro" class="form-control form-control-sm" onchange="toggleReferencia()">
                        <option value="efectivo">💵 Efectivo</option>
                        <option value="cheque">🏦 Cheque</option>
                        <option value="transferencia">📲 Transferencia</option>
                        <option value="deposito">🏧 Depósito bancario</option>
                    </select>
                </div>

                <!-- Referencia (condicional) -->
                <div class="form-group mb-3" id="grupoReferencia" style="display:none;">
                    <label class="font-weight-bold small" id="labelReferencia">Referencia</label>
                    <input type="text" id="referencia" class="form-control form-control-sm"
                           placeholder="N° cheque / N° transferencia / N° depósito">
                </div>

                <!-- Observaciones -->
                <div class="form-group mb-3">
                    <label class="font-weight-bold small">Observaciones</label>
                    <textarea id="observaciones" class="form-control form-control-sm" rows="3"
                              placeholder="Notas adicionales del recupero..."></textarea>
                </div>

                <!-- Total acumulado -->
                <div class="alert alert-success py-2 mb-3" id="resumenTotal" style="display:none;">
                    <div class="d-flex justify-content-between">
                        <span class="small font-weight-bold">Total a recuperar:</span>
                        <span class="font-weight-bold" id="totalDisplay">$0.00</span>
                    </div>
                    <div class="small text-success mt-1">
                        <span id="cantFacturasDisplay">0</span> factura(s) seleccionada(s)
                    </div>
                </div>

                <div class="d-flex">
                    <a href="<?= base_url('recuperos') ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-arrow-left mr-1"></i>Cancelar
                    </a>
                    <button class="btn btn-success btn-sm ml-auto" onclick="guardarRecupero()">
                        <i class="fa-solid fa-floppy-disk mr-1"></i>Guardar Recupero
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- ── COLUMNA DERECHA: facturas pendientes ──────────────────── -->
    <div class="col-lg-8 mb-3">
        <div class="card h-100">
            <div class="card-header py-2 d-flex justify-content-between">
                <h5 class="header-title mb-0">
                    <i class="fa-solid fa-file-lines text-warning mr-2"></i>Facturas Pendientes
                </h5>
                <div id="badgeFacturas" class="d-none">
                    <span class="badge badge-warning" id="badgeCount">0 facturas</span>
                </div>
            </div>
            <div class="card-body p-0">

                <!-- Estado inicial: seleccionar cliente -->
                <div id="estadoInicial" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-hand-pointer fa-2x mb-2 d-block text-secondary"></i>
                    <strong>Selecciona un cliente</strong> para ver sus facturas pendientes de cobro.
                </div>

                <!-- Cargando -->
                <div id="cargando" class="text-center py-5 d-none">
                    <div class="spinner-border text-success" role="status"></div>
                    <div class="small mt-2 text-muted">Buscando facturas...</div>
                </div>

                <!-- Sin resultados -->
                <div id="sinFacturas" class="text-center py-5 text-muted d-none">
                    <i class="fa-solid fa-circle-check fa-2x mb-2 d-block text-success"></i>
                    <strong>¡Sin saldo pendiente!</strong><br>
                    <small>Este cliente no tiene facturas con saldo por cobrar.</small>
                </div>

                <!-- Tabla de facturas -->
                <div id="contenedorTabla" class="d-none">
                    <!-- Mini buscador -->
                    <div class="px-3 py-2 border-bottom bg-light">
                        <div class="input-group input-group-sm" style="max-width:280px;">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white">
                                    <i class="fa-solid fa-magnifying-glass text-muted" style="font-size:.75rem;"></i>
                                </span>
                            </div>
                            <input type="text" id="buscadorFacturas"
                                   class="form-control form-control-sm"
                                   placeholder="Buscar por N° documento..."
                                   oninput="filtrarFacturas(this.value)">
                            <div class="input-group-append" id="btnLimpiarBusqueda" style="display:none;">
                                <button class="btn btn-outline-secondary btn-sm" type="button"
                                        onclick="limpiarBusqueda()">
                                    <i class="fa-solid fa-xmark" style="font-size:.75rem;"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0" id="tablaFacturas">
                            <thead>
                                <tr>
                                    <th style="width:36px;">
                                        <input type="checkbox" id="chkTodos" title="Seleccionar todas"
                                               onchange="toggleTodas(this.checked)">
                                    </th>
                                    <th>Documento</th>
                                    <th style="width:42px;" class="text-center">Tipo</th>
                                    <th style="width:80px;">Fecha</th>
                                    <th class="text-right" style="width:90px;">Total</th>
                                    <th class="text-right" style="width:100px;">Saldo</th>
                                    <th class="text-right" style="width:130px;">Monto a aplicar</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyFacturas"></tbody>
                        </table>
                    </div>
                    <!-- Totales de la tabla -->
                    <div class="border-top px-3 py-2 bg-light d-flex justify-content-end align-items-center">
                        <small class="text-muted mr-3">
                            Saldo total del cliente:
                            <strong id="totalSaldoCliente" class="text-danger">$0.00</strong>
                        </small>
                        <small class="text-muted">
                            Recupero actual:
                            <strong id="totalRecuperoTabla" class="text-success">$0.00</strong>
                        </small>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

<script>
// ─── Estado global ────────────────────────────────────────────────
let facturasData = [];   // todas las facturas del cliente
let seleccionadas = {};  // { factura_id: monto }

// ─── Referencia dinámica ──────────────────────────────────────────
function toggleReferencia() {
    const forma = document.getElementById('formaCobro').value;
    const grupo = document.getElementById('grupoReferencia');
    const label = document.getElementById('labelReferencia');
    const labels = {
        cheque:        'N° de cheque',
        transferencia: 'N° de transferencia',
        deposito:      'N° de referencia bancaria',
    };
    if (forma === 'efectivo') {
        grupo.style.display = 'none';
    } else {
        grupo.style.display = 'block';
        label.textContent   = labels[forma] ?? 'Referencia';
    }
}

// ─── Select2 AJAX para clientes ──────────────────────────────────
$(function () {
    $('#clienteId').select2({
        theme:              'bootstrap4',
        width:              '100%',
        placeholder:        'Buscar cliente por nombre o documento...',
        allowClear:         true,
        minimumInputLength: 2,
        language:           'es',
        ajax: {
            url:      '<?= base_url('clientes/searchAjax') ?>',
            dataType: 'json',
            delay:    300,
            data:     p => ({ q: p.term }),
            processResults: d => ({ results: d.results ?? [] }),
            cache:    true,
        },
    });

    // Cargar facturas al seleccionar un cliente
    $('#clienteId').on('select2:select', function () {
        cargarFacturasCliente(this.value);
    });

    // Limpiar panel al borrar la selección
    $('#clienteId').on('select2:clear', function () {
        resetPanelFacturas();
    });
});

function cargarFacturasCliente(clienteId) {
    facturasData  = [];
    seleccionadas = {};
    resetPanelFacturas();

    document.getElementById('cargando').classList.remove('d-none');

    fetch(`<?= base_url('recuperos/facturas-pendientes/') ?>${clienteId}`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('cargando').classList.add('d-none');

            if (!data.length) {
                document.getElementById('sinFacturas').classList.remove('d-none');
                return;
            }

            facturasData = data;
            renderTabla(data);
            document.getElementById('contenedorTabla').classList.remove('d-none');
            document.getElementById('badgeFacturas').classList.remove('d-none');
            const conRecupero = data.filter(f => f.recupero_id).length;
            const disponibles = data.length - conRecupero;
            document.getElementById('badgeCount').textContent =
                disponibles + ' disponible(s)' + (conRecupero ? ' · ' + conRecupero + ' con recupero pendiente' : '');

            const totalSaldo = data.reduce((a, f) => a + parseFloat(f.saldo), 0);
            document.getElementById('totalSaldoCliente').textContent = '$' + totalSaldo.toFixed(2);
        })
        .catch(() => {
            document.getElementById('cargando').classList.add('d-none');
            Swal.fire('Error', 'No se pudieron cargar las facturas', 'error');
        });
}

function resetPanelFacturas() {
    ['sinFacturas', 'contenedorTabla', 'badgeFacturas', 'cargando']
        .forEach(id => document.getElementById(id).classList.add('d-none'));
    document.getElementById('estadoInicial').classList.remove('d-none');
    document.getElementById('chkTodos').checked = false;
    limpiarBusqueda();
    actualizarTotal();
}

// ─── Renderizar tabla ─────────────────────────────────────────────
function renderTabla(facturas) {
    const tbody     = document.getElementById('tbodyFacturas');
    const tipos     = { '01': 'FAC', '03': 'CCF', '05': 'N.C.', '06': 'N.D.' };
    const tipoBadge = { '01': 'secondary', '03': 'info', '05': 'warning', '06': 'danger' };

    tbody.innerHTML = facturas.map(f => {
        const saldo = parseFloat(f.saldo);
        const total = parseFloat(f.total_pagar);
        const pct   = Math.round((1 - saldo / total) * 100);
        const dias  = parseInt(f.dias_pendiente);

        const badgeDias = dias > 60
            ? `<span class="badge badge-danger badge-dias ml-1">${dias}d</span>`
            : dias > 30
                ? `<span class="badge badge-warning badge-dias ml-1">${dias}d</span>`
                : `<span class="badge badge-light border badge-dias ml-1">${dias}d</span>`;

        const tipoLabel = tipos[f.tipo_dte] ?? f.tipo_dte;
        const tipoColor = tipoBadge[f.tipo_dte] ?? 'secondary';

        // ── Factura con recupero activo pendiente de aplicar ──────────
        if (f.recupero_id) {
            const montoRec = parseFloat(f.monto_recuperado || 0);
            return `<tr class="factura-row row-recupero-pendiente" data-id="${f.id}" data-saldo="${saldo}" data-bloqueada="1">
                <td class="pl-2 text-center align-middle" style="width:36px;">
                    <input type="checkbox" class="chk-factura" value="${f.id}" disabled
                           title="Esta factura ya tiene un recupero pendiente de aplicar">
                </td>
                <td>
                    <button class="btn-detalle" onclick="verDetalleFactura(${f.id})" title="Ver productos facturados">
                        <i class="fa-solid fa-magnifying-glass-plus" style="font-size:.7rem;margin-right:3px;"></i>${f.numero_control}
                    </button>
                    ${badgeDias}
                    <div class="mt-1">
                        <a href="<?= base_url('recuperos/') ?>${f.recupero_id}" target="_blank"
                           class="badge-recupero-pendiente" title="Ver recupero">
                            <i class="fa-solid fa-clock"></i> Recupero pendiente: ${f.numero_recupero}
                        </a>
                    </div>
                </td>
                <td class="text-center">
                    <span class="badge badge-${tipoColor}" style="font-size:.65rem;">${tipoLabel}</span>
                </td>
                <td class="small">${formatDate(f.fecha_emision)}</td>
                <td class="text-right small">$${total.toFixed(2)}</td>
                <td class="text-right">
                    <strong>$${saldo.toFixed(2)}</strong>
                    <div class="saldo-bar">
                        <div class="saldo-bar-fill" style="width:${pct}%;background:#f59e0b;"></div>
                    </div>
                </td>
                <td class="text-right">
                    <input type="number" class="form-control form-control-sm text-right monto-input"
                           value="${montoRec.toFixed(2)}" disabled
                           title="Monto ya asignado en recupero ${f.numero_recupero}">
                    <small class="text-muted" style="font-size:.6rem;">remesado</small>
                </td>
            </tr>`;
        }

        // ── Factura normal seleccionable ───────────────────────────────
        return `<tr class="factura-row row-inactive" data-id="${f.id}" data-saldo="${saldo}">
            <td class="pl-2 text-center align-middle" style="width:36px;">
                <input type="checkbox" class="chk-factura" value="${f.id}"
                       onchange="onCheckFactura(this, ${saldo})">
            </td>
            <td>
                <button class="btn-detalle" onclick="verDetalleFactura(${f.id})" title="Ver productos facturados">
                    <i class="fa-solid fa-magnifying-glass-plus" style="font-size:.7rem;margin-right:3px;"></i>${f.numero_control}
                </button>
                ${badgeDias}
            </td>
            <td class="text-center">
                <span class="badge badge-${tipoColor}" style="font-size:.65rem;">${tipoLabel}</span>
            </td>
            <td class="small">${formatDate(f.fecha_emision)}</td>
            <td class="text-right small">$${total.toFixed(2)}</td>
            <td class="text-right">
                <strong>$${saldo.toFixed(2)}</strong>
                <div class="saldo-bar">
                    <div class="saldo-bar-fill" style="width:${pct}%"></div>
                </div>
            </td>
            <td class="text-right">
                <input type="number" class="form-control form-control-sm text-right monto-input"
                       id="monto_${f.id}" value="${saldo.toFixed(2)}"
                       min="0.01" max="${saldo.toFixed(2)}" step="0.01"
                       disabled
                       oninput="onMontoChange(${f.id}, this.value)">
            </td>
        </tr>`;
    }).join('');
}

// ─── Checkbox individual ──────────────────────────────────────────
function onCheckFactura(chk, saldo) {
    const id    = parseInt(chk.value);
    const input = document.getElementById('monto_' + id);

    if (chk.checked) {
        input.disabled = false;
        seleccionadas[id] = parseFloat(input.value) || saldo;
    } else {
        input.disabled = true;
        delete seleccionadas[id];
    }
    actualizarTotal();
}

// ─── Checkbox "seleccionar todas" (solo las no bloqueadas) ────────
function toggleTodas(checked) {
    document.querySelectorAll('.chk-factura:not(:disabled)').forEach(chk => {
        chk.checked = checked;
        const id    = parseInt(chk.value);
        const row   = chk.closest('tr');
        const saldo = parseFloat(row.dataset.saldo);
        const input = document.getElementById('monto_' + id);
        input.disabled = !checked;
        if (checked) {
            seleccionadas[id] = parseFloat(input.value) || saldo;
        } else {
            delete seleccionadas[id];
        }
    });
    actualizarTotal();
}

// ─── Cambio de monto ──────────────────────────────────────────────
function onMontoChange(id, valor) {
    const row   = document.querySelector(`tr[data-id="${id}"]`);
    const saldo = parseFloat(row.dataset.saldo);
    let   monto = parseFloat(valor) || 0;

    if (monto > saldo) {
        monto = saldo;
        document.getElementById('monto_' + id).value = saldo.toFixed(2);
    }
    seleccionadas[id] = monto;
    actualizarTotal();
}

// ─── Actualizar totales ───────────────────────────────────────────
function actualizarTotal() {
    const cant  = Object.keys(seleccionadas).length;
    const total = Object.values(seleccionadas).reduce((a, v) => a + v, 0);

    document.getElementById('totalRecuperoTabla').textContent = '$' + total.toFixed(2);

    const el = document.getElementById('resumenTotal');
    if (cant > 0) {
        el.style.display = 'block';
        document.getElementById('totalDisplay').textContent         = '$' + total.toFixed(2);
        document.getElementById('cantFacturasDisplay').textContent  = cant;
    } else {
        el.style.display = 'none';
    }
}

// ─── Guardar recupero ─────────────────────────────────────────────
function guardarRecupero() {
    const clienteId  = document.getElementById('clienteId').value;
    const fecha      = document.getElementById('fecha').value;
    const formaCobro = document.getElementById('formaCobro').value;
    const referencia = document.getElementById('referencia').value.trim();
    const obs        = document.getElementById('observaciones').value.trim();

    // Validaciones rápidas
    if (!clienteId) {
        Swal.fire('Requerido', 'Selecciona un cliente', 'warning'); return;
    }
    if (!fecha) {
        Swal.fire('Requerido', 'Ingresa la fecha del recupero', 'warning'); return;
    }
    if (formaCobro !== 'efectivo' && !referencia) {
        Swal.fire('Requerido', 'Ingresa la referencia para ' + formaCobro, 'warning'); return;
    }

    const cant = Object.keys(seleccionadas).length;
    if (cant === 0) {
        Swal.fire('Sin facturas', 'Selecciona al menos una factura del cliente', 'warning'); return;
    }

    // Construir lista de facturas
    const facturas = [];
    for (const [id, monto] of Object.entries(seleccionadas)) {
        const f = facturasData.find(x => x.id == id);
        facturas.push({
            factura_id:      parseInt(id),
            numero_control:  f?.numero_control ?? id,
            monto:           parseFloat(monto.toFixed(2)),
        });
    }

    const total = facturas.reduce((a, f) => a + f.monto, 0);
    const formaLabel = {
        efectivo: 'Efectivo', cheque: 'Cheque',
        transferencia: 'Transferencia', deposito: 'Depósito bancario'
    }[formaCobro] ?? formaCobro;

    // Construir HTML del resumen
    const clienteNombre = ($('#clienteId').select2('data')[0]?.text ?? 'Cliente seleccionado').split(' | ')[0];
    const fechaFmt      = document.getElementById('fecha').value.split('-').reverse().join('/');

    let filas = facturas.map(f =>
        `<tr>
            <td style="text-align:left;padding:4px 8px;font-size:.875rem;">${f.numero_control}</td>
            <td style="text-align:right;padding:4px 8px;font-size:.875rem;font-weight:600;">$${f.monto.toFixed(2)}</td>
         </tr>`
    ).join('');

    const htmlResumen = `
        <div style="text-align:left;font-size:.875rem;">
            <table style="width:100%;margin-bottom:10px;">
                <tr><td style="color:#6c757d;width:40%;">Cliente:</td><td><strong>${clienteNombre}</strong></td></tr>
                <tr><td style="color:#6c757d;">Fecha:</td><td><strong>${fechaFmt}</strong></td></tr>
                <tr><td style="color:#6c757d;">Forma de cobro:</td><td><strong>${formaLabel}</strong></td></tr>
                ${referencia ? `<tr><td style="color:#6c757d;">Referencia:</td><td><strong>${referencia}</strong></td></tr>` : ''}
            </table>
            <p style="margin:6px 0 4px;font-weight:700;color:#333;">Facturas a cobrar:</p>
            <table style="width:100%;border-top:1px solid #dee2e6;">
                <thead><tr>
                    <th style="text-align:left;padding:4px 8px;font-size:.75rem;color:#6c757d;">Documento</th>
                    <th style="text-align:right;padding:4px 8px;font-size:.75rem;color:#6c757d;">Monto</th>
                </tr></thead>
                <tbody>${filas}</tbody>
                <tfoot><tr style="border-top:2px solid #28a745;">
                    <td style="padding:6px 8px;font-weight:700;">TOTAL A RECUPERAR</td>
                    <td style="text-align:right;padding:6px 8px;font-weight:700;font-size:1.1rem;color:#28a745;">
                        $${total.toFixed(2)}
                    </td>
                </tr></tfoot>
            </table>
            <p style="margin-top:10px;color:#856404;background:#fff3cd;padding:8px;border-radius:4px;font-size:.8rem;">
                <strong>⚠ Verifica los datos antes de confirmar.</strong><br>
                Al guardar, el saldo de cada factura será reducido por el monto ingresado.
            </p>
        </div>`;

    Swal.fire({
        title: '¿Confirmar y guardar recupero?',
        html:  htmlResumen,
        icon:  'question',
        showCancelButton:    true,
        confirmButtonColor:  '#28a745',
        confirmButtonText:   '<i class="fa fa-save"></i> Sí, guardar recupero',
        cancelButtonText:    'Revisar',
        width: '550px',
    }).then(r => {
        if (!r.isConfirmed) return;

        // Deshabilitar botón para evitar doble envío
        document.querySelector('button[onclick="guardarRecupero()"]').disabled = true;

        const payload = {
            cliente_id:   parseInt(clienteId),
            fecha,
            forma_cobro:  formaCobro,
            referencia:   referencia || null,
            observaciones: obs || null,
            facturas:     facturas.map(f => ({ factura_id: f.factura_id, monto: f.monto })),
        };

        fetch('<?= base_url('recuperos/guardar') ?>', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(payload),
        })
        .then(res => res.json())
        .then(d => {
            if (d.success) {
                Swal.fire({
                    title:             '¡Recupero guardado!',
                    html:              `<strong>${d.numero}</strong> registrado correctamente.<br>Total: <strong>$${total.toFixed(2)}</strong>`,
                    icon:              'success',
                    confirmButtonText: 'Ver detalle',
                    showCancelButton:  true,
                    cancelButtonText:  'Nuevo recupero',
                }).then(res2 => {
                    if (res2.isConfirmed) {
                        window.location.href = '<?= base_url('recuperos/') ?>' + d.id;
                    } else {
                        window.location.href = '<?= base_url('recuperos/nuevo') ?>';
                    }
                });
            } else {
                document.querySelector('button[onclick="guardarRecupero()"]').disabled = false;
                Swal.fire('Error al guardar', d.message, 'error');
            }
        })
        .catch(() => {
            document.querySelector('button[onclick="guardarRecupero()"]').disabled = false;
            Swal.fire('Error', 'Error de conexión. Intenta nuevamente.', 'error');
        });
    });
}

// ─── Buscador de facturas ─────────────────────────────────────────
function filtrarFacturas(term) {
    const q = term.trim().toLowerCase();
    document.getElementById('btnLimpiarBusqueda').style.display = q ? 'flex' : 'none';
    document.querySelectorAll('#tbodyFacturas tr.factura-row').forEach(tr => {
        const doc = tr.querySelector('.btn-detalle')?.textContent.toLowerCase() ?? '';
        tr.style.display = (!q || doc.includes(q)) ? '' : 'none';
    });
}

function limpiarBusqueda() {
    const input = document.getElementById('buscadorFacturas');
    input.value = '';
    filtrarFacturas('');
    input.focus();
}

// ─── Helpers ──────────────────────────────────────────────────────
function formatDate(str) {
    if (!str) return '—';
    const [y, m, d] = str.split('-');
    return `${d}/${m}/${y}`;
}

// ─── Detalle de factura (mini modal) ─────────────────────────────
function verDetalleFactura(facturaId) {
    const tipos = { '01': 'FAC', '03': 'CCF', '05': 'N.C.', '06': 'N.D.' };

    document.getElementById('detalleModalBody').innerHTML =
        '<div class="text-center py-3"><i class="fa-solid fa-spinner fa-spin fa-lg text-muted"></i></div>';
    $('#modalDetalleFactura').modal('show');

    fetch(`<?= base_url('recuperos/detalle-factura/') ?>${facturaId}`)
        .then(r => r.json())
        .then(d => {
            if (!d.success) {
                document.getElementById('detalleModalBody').innerHTML =
                    `<div class="alert alert-danger mb-0">Error: ${d.message}</div>`;
                return;
            }
            const f    = d.factura;
            const tipo = tipos[f.tipo_dte] ?? f.tipo_dte;

            const filas = d.lineas.length
                ? d.lineas.map(l => `
                    <tr>
                        <td class="small">${l.codigo ?? '—'}</td>
                        <td class="small">${l.descripcion ?? '—'}</td>
                        <td class="text-right small">${parseFloat(l.cantidad ?? 0).toFixed(2)}</td>
                        <td class="small">${l.unidad_medida ?? ''}</td>
                        <td class="text-right small">$${parseFloat(l.precio_unitario ?? 0).toFixed(2)}</td>
                        <td class="text-right small font-weight-bold">$${parseFloat(l.venta_gravada ?? 0).toFixed(2)}</td>
                    </tr>`).join('')
                : '<tr><td colspan="6" class="text-center text-muted small py-2">Sin líneas registradas</td></tr>';

            document.getElementById('detalleModalBody').innerHTML = `
                <div class="row mb-3">
                    <div class="col-6">
                        <small class="text-muted d-block">Documento</small>
                        <strong>${f.numero_control}</strong>
                        <span class="badge badge-secondary ml-1">${tipo}</span>
                    </div>
                    <div class="col-3">
                        <small class="text-muted d-block">Fecha emisión</small>
                        <strong>${f.fecha_emision ? formatDate(f.fecha_emision) : '—'}</strong>
                    </div>
                    <div class="col-3">
                        <small class="text-muted d-block">Vendedor</small>
                        <strong>${f.vendedor}</strong>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-4">
                        <small class="text-muted d-block">Total factura</small>
                        <strong>$${parseFloat(f.total_pagar).toFixed(2)}</strong>
                    </div>
                    <div class="col-4">
                        <small class="text-muted d-block">Saldo pendiente</small>
                        <strong class="${parseFloat(f.saldo) > 0 ? 'text-danger' : 'text-success'}">
                            $${parseFloat(f.saldo).toFixed(2)}
                        </strong>
                    </div>
                    <div class="col-4">
                        <small class="text-muted d-block">Estado pago</small>
                        ${parseFloat(f.saldo) == 0
                            ? '<span class="badge badge-success">Pagada</span>'
                            : '<span class="badge badge-warning">Pendiente</span>'}
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0" id="tablaDetalleModal">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th class="text-right">Cant.</th>
                                <th>U/M</th>
                                <th class="text-right">Precio</th>
                                <th class="text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>${filas}</tbody>
                    </table>
                </div>`;
        })
        .catch(() => {
            document.getElementById('detalleModalBody').innerHTML =
                '<div class="alert alert-danger mb-0">Error de conexión al cargar el detalle.</div>';
        });
}

// Init
toggleReferencia();
</script>

<!-- Modal: detalle de factura -->
<div class="modal fade" id="modalDetalleFactura" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-file-lines mr-2"></i>Detalle de factura
                </h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="detalleModalBody">
                <!-- cargado por JS -->
            </div>
            <div class="modal-footer py-2">
                <button class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
