<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex">
                <h4 class="header-title">Listado de Cuentas</h4>
                <?php if (tienePermiso('registrar_transferencia')): ?>
                    <button class="btn btn-warning btn-sm ml-auto" data-toggle="modal" data-target="#transferModal">
                        <i class="fa-solid fa-exchange-alt"></i> Realizar Transferencia
                    </button>
                <?php endif; ?>
                <?php if (tienePermiso('crear_cuenta')): ?>
                    <a class="btn btn-primary btn-sm ml-2" href="<?= base_url('accounts/new') ?>">
                        <i class="fa-solid fa-plus"></i> Nueva cuenta
                    </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <!-- Modo Escritorio -->
                <div class="row mb-3 align-items-end">
                    <div class="col-md-10">
                        <label for="searchInput">Buscar cuenta</label>
                        <div class="input-group">
                            <input type="text" id="searchInput" class="form-control" placeholder="Nombre de cuenta o ID"
                                value="<?= esc($q ?? '') ?>">
                            <div class="input-group-append">
                                <span class="input-group-text" id="loading-spinner" style="display: none;">
                                    <i class="fa fa-spinner fa-spin"></i>
                                </span>
                                <button class="btn btn-secondary" id="clearSearchBtn" style="display: none;">
                                    <i class="fa fa-times"></i> Limpiar
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="d-flex justify-content-end align-items-center">
                            <label for="perPageSelect" class="mr-2 mb-0">Resultados:</label>
                            <select id="perPageSelect" class="form-control form-control-sm" style="width: 80px;">
                                <option value="5" <?= ($perPage == 5) ? 'selected' : '' ?>>5</option>
                                <option value="10" <?= ($perPage == 10) ? 'selected' : '' ?>>10</option>
                                <option value="20" <?= ($perPage == 20) ? 'selected' : '' ?>>20</option>
                                <option value="50" <?= ($perPage == 50) ? 'selected' : '' ?>>50</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div id="table-container">
                    <!-- Modo Escritorio -->
                    <div class="d-none d-md-block" id="desktop-container">
                        <?= $this->include('accounts/_account_table') ?>
                    </div>

                    <!-- Modo Movil -->
                    <div class="d-block d-md-none" id="mobile-container">
                        <?= $this->include('accounts/_account_cards') ?>
                    </div>

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