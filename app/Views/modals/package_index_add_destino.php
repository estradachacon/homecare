<div class="modal fade" id="setDestinoModal<?= $pkg['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Agregar destino al paquete #<?= $pkg['id'] ?></h5>
                <button class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>

            <form method="post" action="<?= base_url('packages-setDestino') ?>">
                <?= csrf_field() ?>

                <div class="modal-body">

                    <input type="hidden" name="id" value="<?= $pkg['id'] ?>">

                    <label class="form-label">Tipo de destino</label>
                    <select name="tipo_destino" class="form-control selDestino" data-id="<?= $pkg['id'] ?>">
                        <option value="">Seleccione...</option>
                        <option value="punto">Punto fijo</option>
                        <option value="personalizado">Destino personalizado</option>
                        <option value="casillero">Casillero</option>
                    </select>

                    <div class="mt-3 d-none divDestino" id="divPunto<?= $pkg['id'] ?>">
                        <label>Punto fijo</label>
                        <select name="id_puntofijo" class="form-control select2punto puntoSelect" data-id="<?= $pkg['id'] ?>">
                            <option value="">Seleccione...</option>
                            <?php foreach ($puntos_fijos as $punto): ?>
                                <option value="<?= $punto->id ?>"><?= $punto->point_name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mt-3 d-none divDestino" id="divPersonalizado<?= $pkg['id'] ?>">
                         <label>Dirección personalizada</label>
                         <input type="text" name="destino_personalizado" class="form-control inputPersonalizado" placeholder="Escriba el destino...">
                    </div>

                    <div class="mt-3 d-none divDestino" id="divCasillero<?= $pkg['id'] ?>">
                         <label>Destino</label>
                         <input type="text" class="form-control" value="Casillero" readonly>
                    </div>

                    <div class="mt-3" id="fechaEntregaBox<?= $pkg['id'] ?>" style="display:none;">
                         <label>Fecha de entrega</label>
                         <input type="text" name="" class="form-control fechaEntrega" id="fechaEntrega<?= $pkg['id'] ?>" autocomplete="off">
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary">Guardar destino</button>
                </div>

            </form>

        </div>
    </div>
</div>