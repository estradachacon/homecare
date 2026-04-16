<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h4 class="header-title mb-0"><i class="fa-solid fa-file-pen me-2"></i>Asientos Contables</h4>
                <div class="ms-auto">
                    <?php if (tienePermiso('crear_asiento')): ?>
                    <a href="<?= base_url('contabilidad/asientos/nuevo') ?>" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-plus"></i> Nuevo Asiento
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-body">
                <!-- Filtros -->
                <form onsubmit="return false" class="mb-3">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <small class="text-muted">Período</small>
                            <select id="filtroPeriodo" class="form-select form-select-sm">
                                <option value="">Todos los períodos</option>
                                <?php
                                $mesesNombre = [1=>'Ene',2=>'Feb',3=>'Mar',4=>'Abr',5=>'May',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dic'];
                                foreach ($periodos as $p):
                                    $sel = ($filtros['periodo_id'] == $p->id) ? 'selected' : '';
                                ?>
                                <option value="<?= $p->id ?>" <?= $sel ?>><?= $mesesNombre[$p->mes] ?>/<?= $p->anio ?> — <?= $p->estado ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">Tipo</small>
                            <select id="filtroTipo" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                <option value="DIARIO">DIARIO</option>
                                <option value="AJUSTE">AJUSTE</option>
                                <option value="CIERRE">CIERRE</option>
                                <option value="APERTURA">APERTURA</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">Estado</small>
                            <select id="filtroEstado" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                <option value="BORRADOR">BORRADOR</option>
                                <option value="APROBADO">APROBADO</option>
                                <option value="ANULADO">ANULADO</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">Fecha desde</small>
                            <input type="date" id="filtroDesde" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">Fecha hasta</small>
                            <input type="date" id="filtroHasta" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-1">
                            <small class="text-muted">x Página</small>
                            <select id="perPage" class="form-select form-select-sm">
                                <option value="15">15</option>
                                <option value="25" selected>25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                </form>

                <table class="table table-bordered table-hover table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width:80px">N° Asiento</th>
                            <th class="text-center" style="width:100px">Fecha</th>
                            <th>Descripción</th>
                            <th class="text-center" style="width:90px">Tipo</th>
                            <th class="text-center" style="width:90px">Estado</th>
                            <th class="text-end" style="width:110px">Debe</th>
                            <th class="text-end" style="width:110px">Haber</th>
                            <th class="text-center" style="width:60px"></th>
                        </tr>
                    </thead>
                    <tbody id="tbodyAsientos">
                        <?= $this->include('contabilidad/asientos/_tbody') ?>
                    </tbody>
                </table>

                <div id="pagerContainer">
                    <?= $pager->links('default', 'bootstrap_full') ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function cargarAsientos() {
    const params = new URLSearchParams({
        periodo_id:  $('#filtroPeriodo').val(),
        tipo:        $('#filtroTipo').val(),
        estado:      $('#filtroEstado').val(),
        fecha_desde: $('#filtroDesde').val(),
        fecha_hasta: $('#filtroHasta').val(),
        per_page:    $('#perPage').val(),
    });
    fetch('<?= base_url('contabilidad/asientos') ?>?' + params, { headers:{'X-Requested-With':'XMLHttpRequest'} })
        .then(r=>r.json()).then(d=>{
            document.getElementById('tbodyAsientos').innerHTML = d.tbody;
            document.getElementById('pagerContainer').innerHTML = d.pager;
        });
}
$('#filtroPeriodo, #filtroTipo, #filtroEstado, #perPage').on('change', cargarAsientos);
$('#filtroDesde, #filtroHasta').on('change', cargarAsientos);
$(document).on('click','#pagerContainer a', function(e){
    e.preventDefault();
    fetch($(this).attr('href'), {headers:{'X-Requested-With':'XMLHttpRequest'}})
        .then(r=>r.json()).then(d=>{
            document.getElementById('tbodyAsientos').innerHTML = d.tbody;
            document.getElementById('pagerContainer').innerHTML = d.pager;
        });
});
</script>

<?= $this->endSection() ?>
