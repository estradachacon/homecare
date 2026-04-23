<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="header-title mb-0"><i class="fa-solid fa-list-ul me-2"></i>Relación de Cuentas</h4>
                <div class="ms-auto d-flex gap-2">
                    <button class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print"></i> Imprimir</button>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <label>Buscar</label>
                        <input type="text" id="buscar" class="form-control form-control-sm" placeholder="Buscar código o nombre...">
                    </div>
                    <div class="col-md-2">
                        <label>Tipo</label>
                        <select id="filtroTipo" class="form-select form-select-sm">
                            <option value="">Todos los tipos</option>
                            <option>ACTIVO</option><option>PASIVO</option><option>CAPITAL</option>
                            <option>INGRESO</option><option>COSTO</option><option>GASTO</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Con movimientos</label>
                        <select id="filtroMovim" class="form-select form-select-sm">
                            <option value="">Con y sin movimientos</option>
                            <option value="1">Solo con movimientos</option>
                            <option value="0">Solo grupos</option>
                        </select>
                    </div>
                </div>

                <table class="table table-bordered table-sm table-hover" id="tablaRelacion">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:140px">Código</th>
                            <th>Nombre</th>
                            <th class="text-center" style="width:100px">Tipo</th>
                            <th class="text-center" style="width:110px">Naturaleza</th>
                            <th class="text-center" style="width:70px">Nivel</th>
                            <th class="text-center" style="width:90px">Movimientos</th>
                            <th class="text-center" style="width:80px">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $tipoBadge = ['ACTIVO'=>'primary text-white','PASIVO'=>'danger text-white','CAPITAL'=>'warning','INGRESO'=>'success text-white','COSTO'=>'secondary text-white','GASTO'=>'dark text-white'];
                        foreach ($cuentas as $c): ?>
                        <tr data-codigo="<?= strtolower($c->codigo) ?>" data-nombre="<?= strtolower($c->nombre) ?>"
                            data-tipo="<?= $c->tipo ?>" data-movim="<?= $c->acepta_movimientos ?>">
                            <td><code style="padding-left:<?= ($c->nivel-1)*14 ?>px" class="<?= $c->nivel<=2?'fw-bold':'' ?>"><?= esc($c->codigo) ?></code></td>
                            <td class="<?= $c->nivel<=2?'fw-bold':($c->nivel==3?'fw-semibold':'') ?>"><?= esc($c->nombre) ?></td>
                            <td class="text-center"><span class="badge bg-<?= $tipoBadge[$c->tipo]??'secondary' ?>" style="font-size:0.68rem"><?= $c->tipo ?></span></td>
                            <td class="text-center"><small class="<?= $c->naturaleza==='DEUDORA'?'text-info':'text-secondary' ?>"><?= $c->naturaleza ?></small></td>
                            <td class="text-center text-muted small"><?= $c->nivel ?></td>
                            <td class="text-center"><?= $c->acepta_movimientos ? '<span class="badge bg-success"><i class="fa-solid fa-check"></i></span>' : '<span class="badge bg-light text-muted border">Grupo</span>' ?></td>
                            <td class="text-center"><span class="badge <?= $c->activo?'bg-success text-white':'bg-secondary' ?>"><?= $c->activo?'Activa':'Inactiva' ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="text-muted small mt-2">Total: <?= count($cuentas) ?> cuentas</div>
            </div>
        </div>
    </div>
</div>

<script>
function filtrar() {
    const q = document.getElementById('buscar').value.toLowerCase();
    const tipo = document.getElementById('filtroTipo').value;
    const movim = document.getElementById('filtroMovim').value;
    document.querySelectorAll('#tablaRelacion tbody tr').forEach(r => {
        let ok = true;
        if (q && !r.dataset.codigo.includes(q) && !r.dataset.nombre.includes(q)) ok = false;
        if (tipo && r.dataset.tipo !== tipo) ok = false;
        if (movim !== '' && r.dataset.movim !== movim) ok = false;
        r.style.display = ok ? '' : 'none';
    });
}
document.getElementById('buscar').addEventListener('input', filtrar);
document.getElementById('filtroTipo').addEventListener('change', filtrar);
document.getElementById('filtroMovim').addEventListener('change', filtrar);
</script>

<?= $this->endSection() ?>
