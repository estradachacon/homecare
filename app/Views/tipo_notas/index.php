<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="header-title mb-0">Catálogo: Tipos de Nota</h4>
                <button class="btn btn-primary btn-sm" id="btnNuevo">Nuevo</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="thead-light">
                            <tr><th>Nombre</th><th style="width:100px" class="text-center">Acciones</th></tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($items)): foreach ($items as $it): ?>
                                <tr>
                                    <td><?= esc($it->nombre) ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-warning btnEditar" data-id="<?= $it->id ?>" data-nombre="<?= esc($it->nombre) ?>"><i class="fa-solid fa-edit"></i></button>
                                        <button class="btn btn-sm btn-danger btnEliminar" data-id="<?= $it->id ?>"><i class="fa-solid fa-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr><td colspan="2" class="text-center text-muted">No hay registros.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?= $pager->links('default','bootstrap_full') ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTipoNota" tabindex="-1">
    <div class="modal-dialog">
        <form id="formTipoNota">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Tipo de Nota</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="tipoNotaId" value="">
                    <div class="form-group">
                        <label>Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="tipoNotaNombre" class="form-control" required>
                    </div>
                    <div class="alert alert-danger d-none" id="tipoNotaError"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $('#btnNuevo').on('click', function(){
        $('#tipoNotaId').val('');
        $('#tipoNotaNombre').val('');
        $('#tipoNotaError').addClass('d-none').text('');
        $('#modalTipoNota').modal('show');
    });

    $(document).on('click', '.btnEditar', function(){
        $('#tipoNotaId').val($(this).data('id'));
        $('#tipoNotaNombre').val($(this).data('nombre'));
        $('#tipoNotaError').addClass('d-none').text('');
        $('#modalTipoNota').modal('show');
    });

    $('#formTipoNota').on('submit', function(e){
        e.preventDefault();
        const btn = $(this).find('button[type="submit"]');
        const err = $('#tipoNotaError');
        err.addClass('d-none').text('');
        btn.prop('disabled', true).text('Guardando...');

        $.ajax({
            url: '<?= base_url('tipo-notas/guardar') ?>',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success(res){
                if(!res.success){ err.removeClass('d-none').text(res.message || 'Error'); return; }
                location.reload();
            },
            error(){ err.removeClass('d-none').text('Error de conexión.'); },
            complete(){ btn.prop('disabled', false).text('Guardar'); }
        });
    });

    $(document).on('click', '.btnEliminar', function(){
        const id = $(this).data('id');
        if(!confirm('¿Eliminar?')) return;
        $.post('<?= base_url('tipo-notas/eliminar') ?>/' + id, {'<?= csrf_token() ?>': document.querySelector('meta[name="csrf-token"]')?.content || ''}, function(res){ if(res.success) location.reload(); else alert(res.message || 'Error'); }, 'json');
    });
</script>

<?= $this->endSection() ?>
