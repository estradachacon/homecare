<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    .remesa-table th {
        font-size: .73rem;
        text-transform: uppercase;
        letter-spacing: .03em;
        background: #212529;
        color: #fff;
        border-color: #343a40 !important;
    }
    .remesa-table td { font-size: .84rem; vertical-align: middle !important; }
    .remesa-table tbody tr.selected-row { background: #e8f4fd; }
    #totalRemesa { font-size: 1.2rem; font-weight: 700; color: #0d6efd; }
    .check-asiento:checked + label { color: #0d6efd; }
    .remesa-table .asiento-desc,
    .remesa-table .asiento-ref {
        max-width: 240px;
        word-break: break-word;
    }
    @media (max-width: 767.98px) {
        .remesa-new-header {
            align-items: flex-start !important;
            gap: .75rem;
        }
        .remesa-new-header .btn,
        .remesa-search-actions .btn {
            width: 100%;
            margin-left: 0 !important;
            margin-top: .35rem;
        }
        .remesa-actions-footer {
            display: grid !important;
            grid-template-columns: 1fr;
            gap: .5rem;
        }
        .remesa-actions-footer .btn {
            width: 100%;
            margin-right: 0 !important;
        }
        .remesa-table-wrap {
            overflow: visible;
        }
        .remesa-table {
            border-collapse: separate;
            border-spacing: 0 .75rem;
        }
        .remesa-table thead {
            display: none;
        }
        .remesa-table,
        .remesa-table tbody,
        .remesa-table tr,
        .remesa-table td {
            display: block;
            width: 100%;
        }
        .remesa-table tbody tr.asiento-row {
            border: 1px solid #e5e9f0;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 2px 10px rgba(31, 41, 55, .06);
            overflow: hidden;
        }
        .remesa-table tbody tr.asiento-row.selected-row {
            background: #eef7ff;
            border-color: #9dccff;
        }
        .remesa-table td {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            border-top: 1px solid #eef1f5 !important;
            padding: .55rem .75rem;
            text-align: right !important;
        }
        .remesa-table td:first-child {
            border-top: 0 !important;
            background: #f8fafc;
        }
        .remesa-table td::before {
            content: attr(data-label);
            color: #6c757d;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .02em;
            text-transform: uppercase;
            text-align: left;
            flex: 0 0 42%;
        }
        .remesa-table td > * {
            max-width: 58%;
        }
        .remesa-table .asiento-desc,
        .remesa-table .asiento-ref {
            max-width: 58%;
        }
        .remesa-table tbody tr:not(.asiento-row) td {
            display: block;
            text-align: center !important;
        }
        .remesa-table tbody tr:not(.asiento-row) td::before {
            display: none;
        }
        .remesa-table tfoot,
        .remesa-table tfoot tr,
        .remesa-table tfoot td {
            display: block;
            width: 100%;
        }
        .remesa-table tfoot tr {
            border: 1px solid #cbd9ff;
            border-radius: 8px;
            background: #f5f8ff;
            overflow: hidden;
        }
        .remesa-table tfoot td {
            border: 0 !important;
            text-align: right !important;
        }
    }
</style>

<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-between py-2 remesa-new-header">
        <h5 class="header-title mb-0">
            <i class="fa-solid fa-layer-group text-primary mr-2"></i>Nueva Remesa Contable
        </h5>
        <a href="<?= base_url('contabilidad/remesas') ?>" class="btn btn-light btn-sm border">
            <i class="fa-solid fa-arrow-left mr-1"></i>Volver
        </a>
    </div>

    <div class="card-body">

        <!-- ── Datos de la remesa ── -->
        <p class="payment-section-title font-weight-bold text-muted mb-3" style="font-size:.72rem;letter-spacing:.08em;text-transform:uppercase;border-bottom:1px solid #eef1f5;padding-bottom:.45rem;">
            Datos de la remesa
        </p>

        <div class="form-row mb-4">
            <div class="form-group col-md-3">
                <label class="small font-weight-bold">Fecha <span class="text-danger">*</span></label>
                <input type="date" id="fechaRemesa" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group col-md-5">
                <label class="small font-weight-bold">Descripción <span class="text-danger">*</span></label>
                <input type="text" id="descripcionRemesa" class="form-control form-control-sm"
                       placeholder="Ej: Remesa de cobros semana del 12 al 16 de mayo">
            </div>
            <div class="form-group col-md-4">
                <label class="small font-weight-bold">Observaciones</label>
                <input type="text" id="observacionesRemesa" class="form-control form-control-sm"
                       placeholder="Notas adicionales (opcional)">
            </div>
        </div>

        <!-- ── Filtro de asientos ── -->
        <p class="font-weight-bold text-muted mb-3" style="font-size:.72rem;letter-spacing:.08em;text-transform:uppercase;border-bottom:1px solid #eef1f5;padding-bottom:.45rem;">
            Buscar asientos disponibles
        </p>

        <div class="form-row align-items-end mb-3">
            <div class="form-group col-md-4 mb-2 remesa-search-actions">
                <label class="small font-weight-bold">Tipo de partida</label>
                <select id="filtroTipoPartida" class="form-control form-control-sm">
                    <option value="">— Todos los tipos —</option>
                    <?php foreach ($tiposPartida as $tp): ?>
                        <option value="<?= $tp->id ?>"
                            <?= (isset($config->tipo_partida_remesas_id) && $config->tipo_partida_remesas_id == $tp->id) ? 'selected' : '' ?>>
                            <?= esc($tp->nombre) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($config->tipo_partida_remesas_id)): ?>
                    <small class="text-muted"><i class="fa-solid fa-circle-info mr-1"></i>Pre-seleccionado desde configuración</small>
                <?php endif; ?>
            </div>
            <div class="form-group col-md-2 mb-2">
                <label class="small font-weight-bold">Fecha desde</label>
                <input type="date" id="filtroDesde" class="form-control form-control-sm">
            </div>
            <div class="form-group col-md-2 mb-2">
                <label class="small font-weight-bold">Fecha hasta</label>
                <input type="date" id="filtroHasta" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group col-md-4 mb-2">
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="cargarAsientos()">
                    <i class="fa-solid fa-magnifying-glass mr-1"></i>Buscar asientos
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm ml-1 d-none" id="btnSelTodo">
                    <i class="fa-solid fa-check-double mr-1"></i>Sel. todos
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm ml-1 d-none" id="btnDeselTodo">
                    <i class="fa-solid fa-xmark mr-1"></i>Desel. todos
                </button>
            </div>
        </div>

        <!-- ── Tabla de asientos ── -->
        <div class="table-responsive remesa-table-wrap">
            <table class="table table-sm table-bordered remesa-table">
                <thead>
                    <tr>
                        <th style="width:36px" class="text-center">
                            <input type="checkbox" id="chkTodos" title="Seleccionar/deseleccionar todos">
                        </th>
                        <th class="text-dark">N° Asiento</th>
                        <th class="text-dark">Fecha</th>
                        <th class="text-dark">Período</th>
                        <th class="text-dark">Tipo Partida</th>
                        <th class="text-dark">Descripción</th>
                        <th class="text-dark">Referencia</th>
                        <th class="text-dark text-right">Monto</th>
                    </tr>
                </thead>
                <tbody id="tbodyAsientos">
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <br>
                            <i class="fa-solid fa-magnifying-glass fa-lg mb-2 d-block"></i>
                            Haz clic en "Buscar asientos" para cargar los registros disponibles.
                        </td>
                    </tr>
                </tbody>
                <tfoot id="tfootRemesa" class="d-none">
                    <tr class="table-light font-weight-bold">
                        <td colspan="7" class="text-right">
                            <span id="countSeleccionados" class="text-muted mr-2 small"></span>
                            TOTAL SELECCIONADO:
                        </td>
                        <td class="text-right">
                            <span id="totalRemesa">$0.00</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- ── Acciones ── -->
        <div class="d-flex justify-content-end mt-3 pt-3 border-top remesa-actions-footer">
            <a href="<?= base_url('contabilidad/remesas') ?>" class="btn btn-secondary btn-sm mr-2">
                Cancelar
            </a>
            <button type="button" class="btn btn-primary btn-sm" id="btnGuardar" onclick="guardarRemesa()" disabled>
                <i class="fa-solid fa-floppy-disk mr-1"></i>Crear remesa
            </button>
        </div>

    </div>
</div>

<script>
let asientosData = [];

function cargarAsientos() {
    const tipoPartidaId = $('#filtroTipoPartida').val();
    const desde         = $('#filtroDesde').val();
    const hasta         = $('#filtroHasta').val();

    const params = new URLSearchParams();
    if (tipoPartidaId) params.set('tipo_partida_id', tipoPartidaId);
    if (desde)         params.set('fecha_desde', desde);
    if (hasta)         params.set('fecha_hasta', hasta);

    $('#tbodyAsientos').html(`
        <tr><td colspan="8" class="text-center py-3">
            <div class="spinner-border spinner-border-sm text-primary mr-2"></div>
            <small class="text-muted">Cargando asientos...</small>
        </td></tr>`);
    $('#tfootRemesa').addClass('d-none');
    $('#btnSelTodo, #btnDeselTodo').addClass('d-none');
    $('#btnGuardar').prop('disabled', true);

    fetch('<?= base_url('contabilidad/remesas/asientos-disponibles') ?>?' + params.toString())
        .then(r => r.json())
        .then(data => {
            asientosData = data;
            renderTabla(data);
        })
        .catch(() => {
            $('#tbodyAsientos').html(`
                <tr><td colspan="8" class="text-center text-danger py-3">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i>Error al cargar los asientos.
                </td></tr>`);
        });
}

function renderTabla(data) {
    if (!data.length) {
        $('#tbodyAsientos').html(`
            <tr><td colspan="8" class="text-center text-muted py-4">
                <i class="fa-solid fa-circle-check fa-lg mb-2 d-block text-success"></i>
                No hay asientos disponibles con esos filtros (ya remesados o sin aprobar).
            </td></tr>`);
        $('#btnSelTodo, #btnDeselTodo').addClass('d-none');
        return;
    }

    let html = '';
    data.forEach(a => {
        const fecha    = a.fecha ? a.fecha.substring(0,10).split('-').reverse().join('/') : '—';
        const monto    = parseFloat(a.total_debe || 0).toFixed(2);
        const refHtml  = a.referencia ? `<span class="small text-muted">${esc(a.referencia)}</span>` : '—';
        const tpHtml   = a.tipo_partida_nombre
            ? `<span class="badge badge-light border">${esc(a.tipo_partida_nombre)}</span>`
            : '<span class="text-muted">—</span>';
        html += `
        <tr class="asiento-row" data-id="${a.id}" data-monto="${a.total_debe}">
            <td data-label="Seleccionar" class="text-center">
                <input type="checkbox" class="chk-asiento" value="${a.id}" data-monto="${a.total_debe}">
            </td>
            <td data-label="Asiento">
                <a href="<?= base_url('contabilidad/asientos/') ?>${a.id}" target="_blank"
                   class="font-weight-bold text-dark">
                    #${a.numero_asiento}
                </a>
            </td>
            <td data-label="Fecha" class="small">${fecha}</td>
            <td data-label="Periodo" class="small text-muted">${a.periodo || '—'}</td>
            <td data-label="Tipo partida">${tpHtml}</td>
            <td data-label="Descripcion" class="small asiento-desc">${esc(a.descripcion)}</td>
            <td data-label="Referencia" class="asiento-ref">${refHtml}</td>
            <td data-label="Monto" class="text-right font-weight-bold">$${monto}</td>
        </tr>`;
    });

    $('#tbodyAsientos').html(html);
    $('#tfootRemesa').removeClass('d-none');
    $('#btnSelTodo, #btnDeselTodo').removeClass('d-none');
    actualizarTotal();
}

function esc(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function actualizarTotal() {
    let total = 0;
    let count = 0;
    $('.chk-asiento:checked').each(function() {
        total += parseFloat($(this).data('monto') || 0);
        count++;
    });
    $('#totalRemesa').text('$' + total.toFixed(2));
    $('#countSeleccionados').text(count ? count + ' seleccionado(s)' : '');
    $('.asiento-row').each(function() {
        const checked = $(this).find('.chk-asiento').is(':checked');
        $(this).toggleClass('selected-row', checked);
    });
    $('#btnGuardar').prop('disabled', count === 0);
    $('#chkTodos').prop('indeterminate', count > 0 && count < asientosData.length);
    $('#chkTodos').prop('checked', count > 0 && count === asientosData.length);
}

$(document).on('change', '.chk-asiento', actualizarTotal);

$('#chkTodos').on('change', function() {
    $('.chk-asiento').prop('checked', this.checked);
    actualizarTotal();
});

$('#btnSelTodo').on('click', function() {
    $('.chk-asiento').prop('checked', true);
    actualizarTotal();
});

$('#btnDeselTodo').on('click', function() {
    $('.chk-asiento').prop('checked', false);
    actualizarTotal();
});

function guardarRemesa() {
    const fecha       = $('#fechaRemesa').val();
    const descripcion = $('#descripcionRemesa').val().trim();
    const asientos    = $('.chk-asiento:checked').map(function() { return parseInt(this.value); }).get();

    if (!fecha) {
        Swal.fire('Requerido', 'Ingresa la fecha de la remesa.', 'warning');
        return;
    }
    if (!descripcion) {
        Swal.fire('Requerido', 'Ingresa una descripción para la remesa.', 'warning');
        $('#descripcionRemesa').focus();
        return;
    }
    if (!asientos.length) {
        Swal.fire('Sin asientos', 'Selecciona al menos un asiento para incluir en la remesa.', 'warning');
        return;
    }

    let total = 0;
    $('.chk-asiento:checked').each(function() { total += parseFloat($(this).data('monto') || 0); });

    Swal.fire({
        icon: 'question',
        title: 'Crear remesa',
        html: `<div class="text-left">
            <p><strong>Descripción:</strong> ${esc(descripcion)}</p>
            <p><strong>Asientos incluidos:</strong> ${asientos.length}</p>
            <p><strong>Total:</strong> $${total.toFixed(2)}</p>
        </div>`,
        showCancelButton: true,
        confirmButtonText: 'Crear remesa',
        cancelButtonText: 'Cancelar',
        buttonsStyling: false,
        customClass: { confirmButton: 'btn btn-primary mr-2', cancelButton: 'btn btn-secondary' },
        showLoaderOnConfirm: true,
        allowOutsideClick: () => !Swal.isLoading(),
        preConfirm: () => {
            return fetch('<?= base_url('contabilidad/remesas/store') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    fecha:           fecha,
                    descripcion:     descripcion,
                    observaciones:   $('#observacionesRemesa').val(),
                    tipo_partida_id: $('#filtroTipoPartida').val() || null,
                    asientos:        asientos,
                })
            })
            .then(r => r.json())
            .catch(() => Swal.showValidationMessage('Error de comunicación con el servidor.'));
        }
    }).then(result => {
        if (result.isConfirmed && result.value) {
            const d = result.value;
            if (d.success) {
                Swal.fire('Creada', d.message, 'success')
                    .then(() => { window.location = '<?= base_url('contabilidad/remesas/') ?>' + d.id; });
            } else {
                Swal.fire('Error', d.message, 'error');
            }
        }
    });
}

// Auto-cargar si hay tipo_partida configurado
$(function() {
    <?php if (!empty($config->tipo_partida_remesas_id)): ?>
    cargarAsientos();
    <?php endif; ?>
});
</script>

<?= $this->endSection() ?>
