<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<style>
.linea-asiento td { vertical-align:middle; }
.linea-asiento input { border:1px solid #dee2e6; }
</style>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h4 class="header-title mb-0">
                    <i class="fa-solid fa-plus-circle me-2"></i>Nuevo Asiento Contable
                    <span class="badge bg-secondary ms-2">AST-<?= str_pad($nextNumero, 5, '0', STR_PAD_LEFT) ?></span>
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
                            $mesesN = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
                                       7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
                            foreach ($periodos as $p): ?>
                            <option value="<?= $p->id ?>"><?= $mesesN[$p->mes] ?> <?= $p->anio ?></option>
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
                                <th style="width:220px">Descripción línea</th>
                                <th class="text-end" style="width:130px">Debe ($)</th>
                                <th class="text-end" style="width:130px">Haber ($)</th>
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
                    <button class="btn btn-outline-secondary btn-sm" onclick="agregarLinea()">
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
            <select class="form-select form-select-sm cuenta-select" id="cuenta-${i}" style="min-width:300px">
                <option value="">Buscar cuenta...</option>
            </select>
        </td>
        <td><input type="text" class="form-control form-control-sm linea-desc" placeholder="Descripción" id="desc-${i}"></td>
        <td><input type="number" class="form-control form-control-sm text-end monto-debe" id="debe-${i}" value="0.00" step="0.01" min="0" oninput="recalcular()"></td>
        <td><input type="number" class="form-control form-control-sm text-end monto-haber" id="haber-${i}" value="0.00" step="0.01" min="0" oninput="recalcular()"></td>
        <td class="text-center">
            <button class="btn btn-sm btn-outline-danger py-0 px-1" onclick="eliminarLinea(${i})" title="Quitar">
                <i class="fa-solid fa-times"></i>
            </button>
        </td>
    </tr>`;
    document.getElementById('lineasBody').insertAdjacentHTML('beforeend', row);

    $(`#cuenta-${i}`).select2({
        width: '100%',
        placeholder: 'Buscar cuenta...',
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
}

function eliminarLinea(i) {
    document.getElementById('linea-' + i)?.remove();
    recalcular();
}

function recalcular() {
    let debe = 0, haber = 0;
    document.querySelectorAll('.monto-debe').forEach(el => debe  += parseFloat(el.value)||0);
    document.querySelectorAll('.monto-haber').forEach(el => haber += parseFloat(el.value)||0);
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
    const periodoId  = $('#periodoId').val();
    const fecha      = $('#fechaAsiento').val();
    const descripcion = $('#descAsiento').val().trim();
    const tipo       = $('#tipoAsiento').val();
    const referencia = $('#refAsiento').val().trim();

    if (!periodoId || !fecha || !descripcion) {
        Swal.fire('Faltan datos', 'Completa período, fecha y descripción', 'warning'); return;
    }

    const lineas = [];
    document.querySelectorAll('.linea-asiento').forEach(row => {
        const id = row.id.replace('linea-', '');
        const cuentaId = $(`#cuenta-${id}`).val();
        const desc     = $(`#desc-${id}`).val();
        const debe     = parseFloat($(`#debe-${id}`).val())  || 0;
        const haber    = parseFloat($(`#haber-${id}`).val()) || 0;
        if (cuentaId && (debe > 0 || haber > 0)) {
            lineas.push({ cuenta_id: cuentaId, descripcion: desc, debe, haber });
        }
    });

    if (lineas.length < 2) {
        Swal.fire('Error', 'Se requieren al menos 2 líneas con montos', 'error'); return;
    }

    const totalDebe  = lineas.reduce((s,l)=>s+l.debe,  0);
    const totalHaber = lineas.reduce((s,l)=>s+l.haber, 0);

    if (Math.abs(totalDebe - totalHaber) > 0.01) {
        Swal.fire('Asiento no cuadra', `Debe: $${totalDebe.toFixed(2)} | Haber: $${totalHaber.toFixed(2)}`, 'error'); return;
    }

    fetch('<?= base_url('contabilidad/asientos/store') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ periodo_id: periodoId, fecha, descripcion, tipo, referencia, lineas })
    }).then(r=>r.json()).then(d=>{
        if (d.success) {
            Swal.fire('Guardado', d.message, 'success').then(()=>{
                window.location = '<?= base_url('contabilidad/asientos/') ?>' + d.id;
            });
        } else {
            Swal.fire('Error', d.message, 'error');
        }
    });
}

// Agregar 2 líneas iniciales
agregarLinea();
agregarLinea();
</script>

<?= $this->endSection() ?>
