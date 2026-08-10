<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4 class="mb-0">
                    <i class="fa-solid fa-code-merge me-2"></i> Clientes duplicados
                </h4>
                <a href="<?= base_url('clientes') ?>" class="btn btn-light btn-sm">
                    <i class="fa-solid fa-arrow-left"></i> Volver a clientes
                </a>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">
                    Se agrupan los clientes que comparten el mismo número de documento (DUI/NIT) o el mismo NRC,
                    sin importar si uno tiene guiones/espacios y el otro no (p.ej. "1315-100574-101-4" y
                    "13151005741014" se detectan como el mismo).
                    Elige cuál registro quieres <strong>conservar</strong> y fusiona los demás dentro de él:
                    todas las facturas, notas de pedido, notas de envío, pagos, quedans y recuperos del
                    duplicado se moverán al cliente conservado y el duplicado se eliminará. Si el duplicado
                    tenía su propia cuenta contable, sus asientos, saldos y movimientos también se mueven
                    a la cuenta real y la cuenta duplicada se desactiva.
                </p>

                <?php if (empty($grupos)): ?>
                    <div class="alert alert-success mb-0">
                        <i class="fa-solid fa-circle-check me-1"></i> No se encontraron clientes duplicados por número de documento ni por NRC.
                    </div>
                <?php else: ?>
                    <?php foreach ($grupos as $grupo): ?>
                        <div class="card mb-4 dup-grupo">
                            <div class="card-header bg-light">
                                <span class="badge text-white bg-primary me-2"><?= esc($grupo['tipo']) ?></span>
                                <strong><?= esc($grupo['tipo']) ?>:</strong> <?= esc($grupo['documento']) ?>
                                <span class="badge bg-secondary text-white ms-2"><?= count($grupo['clientes']) ?> registros</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover mb-0 align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:40px">
                                                    <i class="fa-solid fa-star text-warning" title="Conservar"></i>
                                                </th>
                                                <th>#</th>
                                                <th>Nombre</th>
                                                <th>Teléfono</th>
                                                <th>Correo</th>
                                                <th>Cuenta contable</th>
                                                <th>Creado</th>
                                                <th>Registros asociados</th>
                                                <th style="width:110px">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($grupo['clientes'] as $i => $c): ?>
                                                <tr>
                                                    <td class="text-center">
                                                        <input type="radio"
                                                               name="principal_<?= esc($grupo['documento'], 'attr') ?>"
                                                               class="radio-principal"
                                                               value="<?= $c->id ?>"
                                                               <?= $i === 0 ? 'checked' : '' ?>>
                                                    </td>
                                                    <td><span class="badge bg-light text-dark">#<?= $c->id ?></span></td>
                                                    <td><?= esc($c->nombre) ?></td>
                                                    <td><?= esc($c->telefono) ?></td>
                                                    <td><?= esc($c->correo) ?></td>
                                                    <td>
                                                        <?php if ($c->cuenta_contable): ?>
                                                            <span class="badge bg-light text-dark"><?= esc($c->cuenta_contable->codigo) ?></span>
                                                            <?= esc($c->cuenta_contable->nombre) ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">Sin cuenta</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= $c->created_at ? date('d/m/Y H:i', strtotime($c->created_at)) : '—' ?></td>
                                                    <td>
                                                        <?php if ($c->total_referencias > 0): ?>
                                                            <?php foreach ($c->referencias as $tabla => $cnt): ?>
                                                                <?php if ($cnt > 0): ?>
                                                                    <span class="badge bg-info text-dark me-1" title="<?= esc($tabla) ?>">
                                                                        <?= esc($tabla) ?>: <?= $cnt ?>
                                                                    </span>
                                                                <?php endif; ?>
                                                            <?php endforeach; ?>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Sin registros</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="<?= base_url('clientes/' . $c->id) ?>" class="btn btn-sm btn-outline-secondary" target="_blank" title="Ver cliente">
                                                            <i class="fa-solid fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <button type="button" class="btn btn-primary btn-sm btn-fusionar" data-grupo="<?= esc($grupo['documento'], 'attr') ?>">
                                    <i class="fa-solid fa-code-merge"></i> Fusionar seleccionado dentro del marcado con <i class="fa-solid fa-star text-warning"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.dup-grupo').forEach(function (grupoEl) {
        const btn = grupoEl.querySelector('.btn-fusionar');
        if (!btn) return;

        btn.addEventListener('click', function () {
            const radios = grupoEl.querySelectorAll('.radio-principal');
            let principalId = null;
            const todosIds = [];

            radios.forEach(function (r) {
                todosIds.push(r.value);
                if (r.checked) principalId = r.value;
            });

            if (!principalId) {
                Swal.fire({ icon: 'warning', title: 'Selecciona un cliente a conservar (la estrella).' });
                return;
            }

            const duplicados = todosIds.filter(id => id !== principalId);

            if (!duplicados.length) {
                Swal.fire({ icon: 'info', title: 'No hay duplicados para fusionar en este grupo.' });
                return;
            }

            Swal.fire({
                icon: 'warning',
                title: '¿Fusionar clientes?',
                html: `Se moverán todos los registros de <b>${duplicados.length}</b> cliente(s) duplicado(s) al cliente #${principalId} y se eliminarán los duplicados. Esta acción no se puede deshacer.`,
                showCancelButton: true,
                confirmButtonText: 'Sí, fusionar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#0d6efd',
            }).then(function (result) {
                if (!result.isConfirmed) return;

                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Fusionando...';

                fusionarSecuencial(duplicados.slice(), principalId, btn);
            });
        });
    });

    function fusionarSecuencial(pendientes, principalId, btn) {
        if (!pendientes.length) {
            Swal.fire({ icon: 'success', title: 'Fusión completada' }).then(() => window.location.reload());
            return;
        }

        const duplicadoId = pendientes.shift();

        fetch("<?= base_url('clientes/fusionar-ajax') ?>", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: new URLSearchParams({ principal_id: principalId, duplicado_id: duplicadoId }),
        })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    Swal.fire({ icon: 'error', title: 'Error al fusionar', text: data.message || 'Ocurrió un error.' });
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-code-merge"></i> Fusionar seleccionado dentro del marcado';
                    return;
                }
                fusionarSecuencial(pendientes, principalId, btn);
            })
            .catch(() => {
                Swal.fire({ icon: 'error', title: 'Error de conexión al fusionar.' });
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-code-merge"></i> Fusionar seleccionado dentro del marcado';
            });
    }
});
</script>

<?= $this->endSection() ?>
