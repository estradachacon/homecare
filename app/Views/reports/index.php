<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex text-center justify-content-center">
                <h4 class="header-title">Menú Reportes</h4>
            </div>
            <div class="card-body">
                <div class="row" style="padding-left: 100px !important;padding-right: 100px !important">
                    <a href="<?= base_url('reports/packages') ?>" class="col-md-3 card-options">
                        <div class="card border-success mb-3 card-option-container">
                            <div class="card-body text-info icon-card-options"><i class="fa-solid fa-boxes-packing"></i></div>
                            <div class="card-footer bg-transparent border-info card-footer-options">Reporte de paquetería por fecha</div>
                        </div>
                    </a>

                    <a href="#" class="col-md-3 card-options">
                        <div class="card border-success mb-3 card-option-container">
                            <div class="card-body text-info icon-card-options"><i class="fa-solid fa-money-bill-transfer"></i></div>
                            <div class="card-footer bg-transparent border-info card-footer-options">Reporte de Movimientos financieros</div>
                        </div>
                    </a>
                    <a href="#" class="col-md-3 card-options">
                        <div class="card border-success mb-3 card-option-container">
                            <div class="card-body text-info icon-card-options"><i class="fa-solid fa-cash-register"></i></div>
                            <div class="card-footer bg-transparent border-info card-footer-options">Reporte de Movimientos de caja</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle detalles en móviles
        document.querySelectorAll('.toggle-details').forEach(btn => {
            btn.addEventListener('click', function() {
                const details = this.closest('.card').querySelector('.details');
                details.classList.toggle('d-none');
                this.textContent = details.classList.contains('d-none') ? 'Ver' : 'Ocultar';
            });
        });

        // Botones eliminar
        const deleteButtons = document.querySelectorAll('.delete-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
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
                }).then((result) => {
                    if (result.isConfirmed) {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const csrfHeader = document.querySelector('meta[name="csrf-header"]').getAttribute('content');
                        fetch("<?= base_url('settledpoint/delete') ?>", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    [csrfHeader]: csrfToken
                                },
                                body: new URLSearchParams({
                                    id: id
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                Swal.fire({
                                    title: data.status === 'success' ? 'Éxito' : 'Error',
                                    text: data.message,
                                    icon: data.status,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                if (data.status === 'success') {
                                    const row = button.closest('tr');
                                    if (row) row.remove();
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 1500);
                                }
                            })
                            .catch(err => {
                                Swal.fire('Error', 'Ocurrió un problema en la petición.', 'error');
                            });
                    }
                });
            });
        });
    });
</script>
<?= $this->endSection() ?>