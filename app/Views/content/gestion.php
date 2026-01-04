<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h4>Gestionar imágenes de: <?= esc($group->title) ?></h4>
                <a href="#" class="btn btn-success" onclick="subirImagen()"><i class="fas fa-plus"></i> Subir nueva imagen</a>
            </div>
            <hr>
            <div class="card-body">

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
                <?php endif; ?>

                <?php if ($images && count($images) > 0): ?>
                    <div class="row g-3">
                        <?php foreach ($images as $img): ?>
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                                <div class="card h-100 shadow-sm">
                                    <img src="<?= base_url('upload/content/' . $img->image) ?>"
                                        class="card-img-top"
                                        alt="<?= esc($img->caption) ?>"
                                        style="height:200px; object-fit:contain; background-color:#f5f5f5;">
                                    <div class="card-body d-flex flex-column">
                                        <h6 class="card-title text-truncate"><?= esc($img->caption) ?></h6>
                                        <div class="mt-auto d-flex justify-content-between">
                                            <button class="btn btn-sm btn-warning edit-btn" data-id="<?= $img->id ?>">
                                                <i class="fa-solid fa-pen"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $img->id ?>">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p>No hay imágenes en este grupo.</p>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editButtons = document.querySelectorAll('.edit-btn');

        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const currentTitle = this.closest('.card') ?
                    this.closest('.card').querySelector('.card-title')?.innerText || '' :
                    '';

                Swal.fire({
                    title: 'Editar título de la imagen',
                    input: 'text',
                    inputLabel: 'Nuevo título',
                    inputValue: currentTitle,
                    inputPlaceholder: 'Escribe el nuevo título aquí',
                    showCancelButton: true,
                    confirmButtonText: 'Guardar',
                    cancelButtonText: 'Cancelar',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'El título no puede estar vacío';
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('id', id);
                        formData.append('caption', result.value);

                        fetch("<?= base_url('content/image/update') ?>", {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(res => res.json())
                            .then(data => {
                                Swal.fire({
                                    title: data.status === 'success' ? '¡Éxito!' : 'Error',
                                    text: data.message,
                                    icon: data.status,
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    if (data.status === 'success') window.location.reload();
                                });
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
<script>
    // Reutilizamos tu función subirImagen()
    function subirImagen() {
        Swal.fire({
            title: 'Subir nueva imagen',
            html: `
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            <input type="file" id="imagen" class="swal2-file-input" accept="image/*" style="width: 100%;">
            <input type="text" id="titulo" class="swal2-input" placeholder="Título" style="width: 80%;">
        </div>
    `,
            showCancelButton: true,
            confirmButtonText: 'Subir',
            preConfirm: () => {
                const file = Swal.getPopup().querySelector('#imagen').files[0];
                const title = Swal.getPopup().querySelector('#titulo').value;

                if (!file) Swal.showValidationMessage('Debes seleccionar una imagen');
                return {
                    file,
                    title
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('image', result.value.file);
                formData.append('title', result.value.title);
                formData.append('group_id', <?= $group->id ?>);

                fetch("<?= base_url('content/upload-image') ?>", {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        Swal.fire({
                            title: data.status === 'success' ? '¡Éxito!' : 'Error',
                            text: data.message,
                            icon: data.status,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            if (data.status === 'success') window.location.reload();
                        });
                    })
                    .catch(err => {
                        Swal.fire('Error', 'Ocurrió un problema en la petición.', 'error');
                    });
            }
        });
    }

    // Borrar imágenes
    document.addEventListener('DOMContentLoaded', function() {
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
                        fetch("<?= base_url('content/image/delete') ?>", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: new URLSearchParams({
                                    id: id
                                })
                            })
                            .then(res => res.json())
                            .then(data => {
                                Swal.fire({
                                    title: data.status === 'success' ? 'Éxito' : 'Error',
                                    text: data.message,
                                    icon: data.status,
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    if (data.status === 'success') window.location.reload();
                                });
                            })
                            .catch(err => Swal.fire('Error', 'Ocurrió un problema.', 'error'));
                    }
                });
            });
        });
    });
</script>

<?= $this->endSection() ?>