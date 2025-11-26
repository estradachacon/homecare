<?php if (!empty($pkg['foto'])): ?>
    <div class="modal fade" id="fotoModal<?= $pkg['id'] ?>" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document"
            style="max-width: 90%;">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Foto del paquete #<?= esc($pkg['id']) ?></h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body text-center">
                    <img src="<?= base_url('upload/paquetes/' . $pkg['foto']) ?>"
                        alt="Foto del paquete" class="img-fluid rounded"
                        style="max-height: 80vh; object-fit: contain;">
                </div>

            </div>
        </div>
    </div>
<?php endif; ?>