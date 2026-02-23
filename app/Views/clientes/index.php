<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex">
                <h4 class="mb-0">
                    <i class="fa-solid fa-users me-2"></i> Clientes
                </h4>

                <?php if (tienePermiso('crear_cliente')): ?>
                    <a href="<?= base_url('clientes/new') ?>" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-plus"></i> Nuevo
                    </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <!-- Modo Escritorio -->
                <!-- Buscador -->
                <form method="get" class="row mb-3">

                    <div class="col-md-10">
                        <input
                            type="text"
                            name="q"
                            class="form-control"
                            placeholder="Buscar por nombre, documento o NRC"
                            value="<?= esc($q ?? '') ?>">
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-secondary w-100">
                            <i class="fa-solid fa-search"></i> Buscar
                        </button>
                    </div>

                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">

                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Cliente</th>
                                <th>Documento</th>
                                <th>NRC</th>
                                <th>Teléfono</th>
                                <th style="width:100px">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php if (!empty($clientes)): ?>

                            <?php foreach ($clientes as $c): ?>
                                <tr>
                                    <td><?= $c->id ?></td>

                                    <td>
                                        <strong><?= esc($c->nombre) ?></strong>
                                    </td>

                                    <td><?= esc($c->numero_documento) ?></td>

                                    <td><?= esc($c->nrc) ?></td>

                                    <td><?= esc($c->telefono) ?></td>

                                    <td class="text-center">

                                        <a href="<?= base_url('clientes/'.$c->id) ?>"
                                           class="btn btn-sm btn-info">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>

                                        <?php if (tienePermiso('editar_clientes')): ?>
                                            <a href="<?= base_url('clientes/edit/'.$c->id) ?>"
                                               class="btn btn-sm btn-warning">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                        <?php endif; ?>

                                    </td>
                                </tr>
                            <?php endforeach ?>

                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    No se encontraron clientes
                                </td>
                            </tr>
                        <?php endif ?>
                        </tbody>
                    </table>
                </div>
                <!-- Paginación -->
                <div class="d-flex justify-content-center mt-3">
                    <?= $pager->links('default', 'bootstrap_full') ?>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="transferModal" tabindex="-1" role="dialog" aria-labelledby="transferModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transferModalLabel">Hacer Transferencia</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="transferForm" action="<?= base_url('accounts-transfer') ?>" method="post">
                    <div class="mt-3 divAccount" id="gastoCuenta<?= esc($q['id'] ?? '') ?>">
                        <label class="form-label">Cuenta inicial</label>
                        <select name="account_source"
                            id="account_source"
                            class="form-control select2-account"
                            data-initial-id=""
                            data-initial-text="">
                        </select>
                    </div>

                    <!-- Cuenta destino -->
                    <div class="form-group mt-3">
                        <label for="cuentaDestino">Cuenta Destino</label>
                        <select name="account_destination"
                            id="account_destination"
                            class="form-control select2-account"
                            data-initial-id=""
                            data-initial-text="">
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="montoTransferir">Monto</label>
                        <input type="number" class="form-control" id="montoTransferir" name="monto" min="0.01" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="descripcionTransferencia">Descripción</label>
                        <input type="text" class="form-control" id="descripcionTransferencia" name="descripcion" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Realizar Transferencia</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    const accountSearchUrl = "<?= base_url('accounts-list') ?>";
    $('#transferForm').on('submit', function(e) {
        e.preventDefault(); // Evita recargar la página

        $.ajax({
            url: "/accounts-transfer",
            method: "POST",
            data: $(this).serialize(),
            dataType: "json",
            success: function(response) {

                if (response.status === "success") {
                    Swal.fire({
                        icon: "success",
                        title: "Éxito",
                        text: response.message,
                        confirmButtonText: "Aceptar"
                    }).then(() => {
                        window.location.href = "/accounts";
                    });

                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudo procesar la transferencia."
                });
            }
        });
    });

    $(document).ready(function() {
        $.fn.modal.Constructor.prototype._enforceFocus = function() {};
        // Interceptar SOLO los forms de agregar destino
        $('.select2-account').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Buscar cuenta...',
            allowClear: true,
            minimumInputLength: 1,
            dropdownParent: $('#transferModal'), // importante dentro del modal
            ajax: {
                url: accountSearchUrl,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term
                    };
                },

                processResults: function(data) {
                    return {
                        results: data.map(item => ({
                            id: item.id,
                            text: item.name + "  ||  Saldo: $" + item.balance
                        }))
                    };
                }
            }
        });

    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const tableContainer = document.getElementById('table-container');
        const loadingSpinner = document.getElementById('loading-spinner');
        const clearSearchBtn = document.getElementById('clearSearchBtn');
        const perPageSelect = document.getElementById('perPageSelect');
        const baseUrl = '<?= base_url('accounts/searchAjax') ?>';

        let searchTimeout;

        function loadResults(query = '', page = 1) {
            const perPage = perPageSelect.value;
            const url = `${baseUrl}?q=${encodeURIComponent(query)}&page=${page}&perPage=${perPage}`;

            loadingSpinner.style.display = 'inline-block';

            fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.text())
                .then(html => {
                    tableContainer.innerHTML = html;
                    loadingSpinner.style.display = 'none';
                    updateClearButton(query);
                    rebindEvents();
                })
                .catch(() => {
                    loadingSpinner.style.display = 'none';
                    tableContainer.innerHTML =
                        '<div class="alert alert-danger">Error al cargar los datos.</div>';
                });
        }
        perPageSelect.addEventListener('change', () => {
            loadResults(searchInput.value.trim(), 1);
        });

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();

            searchTimeout = setTimeout(() => {
                loadResults(query, 1);
            }, 300);
        });

        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            loadResults('', 1);
            updateClearButton('');
        });

        function updateClearButton(query) {
            clearSearchBtn.style.display = query.length ? 'inline-block' : 'none';
        }

        function rebindEvents() {

            document.querySelectorAll('#pagination-links a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = new URL(this.href);
                    const page = url.searchParams.get('page') || 1;
                    loadResults(searchInput.value.trim(), page);
                });
            });

            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.onclick = handleDelete;
            });
            document.querySelectorAll('.toggle-details').forEach(btn => {
                btn.onclick = function() {
                    const details = this.closest('.card').querySelector('.details');
                    details.classList.toggle('d-none');
                    this.textContent = details.classList.contains('d-none') ?
                        'Ver' :
                        'Ocultar';
                };
            });
        }

        function handleDelete() {
            const id = this.dataset.id;

            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(result => {
                if (!result.isConfirmed) return;

                fetch("<?= base_url('accounts/delete') ?>", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new URLSearchParams({
                            id
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        Swal.fire({
                            icon: data.status,
                            title: data.status === 'success' ? 'Éxito' : 'Error',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });

                        if (data.status === 'success') {
                            loadResults(searchInput.value.trim());
                        }
                    });
            });
        }

        rebindEvents();
        updateClearButton(searchInput.value.trim());
    });
</script>

<?= $this->endSection() ?>