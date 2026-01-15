<?= $this->extend('Layouts/mainbody_welcome') ?>
<?= $this->section('content') ?>

<section class="py-5">
    <div class="container">
        <?php if ($groups): ?>
            <?php foreach ($groups as $group): ?>
                <h2 class="mb-4"><?= esc($group->title) ?></h2>
                <div class="row g-3 mb-5">
                    <?php if ($group->images): ?>
                        <?php foreach ($group->images as $img): ?>
                            <div class="col-md-4">
                                <div class="card shadow-sm">
                                    <img src="<?= base_url('upload/content/' . $img->image) ?>"
                                        class="card-img-top img-thumbnail"
                                        style="object-fit: contain; max-height: 250px; width: 100%; cursor: pointer;"
                                        alt="<?= esc($img->caption) ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#imageModal"
                                        data-img="<?= base_url('upload/content/' . $img->image) ?>"
                                        data-caption="<?= esc($img->caption) ?>">

                                    <?php if ($img->caption): ?>
                                        <div class="card-body">
                                            <p class="card-text text-center"><?= esc($img->caption) ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No hay imágenes en este grupo.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center">No hay grupos de rutas para mostrar.</p>
        <?php endif; ?>
    </div>
</section>

<!-- Modal para imagen ampliada -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-body p-0 position-relative">

                <button type="button"
                    class="btn-close position-absolute top-0 end-0 m-3"
                    data-bs-dismiss="modal"
                    aria-label="Cerrar"
                    style="z-index: 10;">
                </button>

                <img src="" id="modalImage" class="w-100" style="object-fit: contain;">
                <p class="text-center p-2" id="modalCaption"></p>

            </div>
        </div>
    </div>
</div>

<script>
    // Cuando se haga click en cualquier imagen de card
    const imageModal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const modalCaption = document.getElementById('modalCaption');

    imageModal.addEventListener('show.bs.modal', function(event) {
        const img = event.relatedTarget;
        modalImage.src = img.getAttribute('data-img');
        modalCaption.textContent = img.getAttribute('data-caption');
    });
</script>

<?= $this->endSection() ?>