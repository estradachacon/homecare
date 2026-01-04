<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex">
                <h4 class="header-title">Crear sucursal</h4>
            </div>
            <div class="card-body">
                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach (session('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?= site_url('branches') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="branch_name" class="form-label">Nombre de la Sucursal</label>
                        <input type="text" name="branch_name" id="branch_name" class="form-control"
                            value="<?= old('branch_name') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="branch_direction" class="form-label">Dirección</label>
                        <input type="text" name="branch_direction" id="branch_direction" class="form-control"
                            value="<?= old('branch_direction') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ubicación en el mapa</label>
                        <div id="map" style="height: 400px; width: 100%; border:1px solid #ccc;"></div>
                    </div>

                    <!-- Campos ocultos para latitud y longitud -->
                    <input type="hidden" name="latitude" id="latitude" value="<?= isset($branch) ? $branch->latitude : '' ?>">
                    <input type="hidden" name="longitude" id="longitude" value="<?= isset($branch) ? $branch->longitude : '' ?>">

                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <a href="<?= site_url('branches') ?>" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Google Maps -->
<script src="https://maps.googleapis.com/maps/api/js?key=TU_API_KEY&libraries=places"></script>

<script>
    let map, marker;

    function initMap() {
        const lat = parseFloat(document.getElementById('latitude').value) || 13.6929; // default El Salvador
        const lng = parseFloat(document.getElementById('longitude').value) || -89.2182;
        const position = {
            lat,
            lng
        };

        map = new google.maps.Map(document.getElementById('map'), {
            center: position,
            zoom: 15
        });

        marker = new google.maps.Marker({
            position: position,
            map: map,
            draggable: true
        });

        // Actualizar lat/lng al mover marcador
        marker.addListener('dragend', function() {
            const pos = marker.getPosition();
            document.getElementById('latitude').value = pos.lat();
            document.getElementById('longitude').value = pos.lng();
        });

        // Autocompletado para la dirección
        const input = document.getElementById('branch_direction');
        const autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.bindTo('bounds', map);

        autocomplete.addListener('place_changed', function() {
            const place = autocomplete.getPlace();
            if (!place.geometry) return;

            map.setCenter(place.geometry.location);
            map.setZoom(17);
            marker.setPosition(place.geometry.location);
            document.getElementById('latitude').value = place.geometry.location.lat();
            document.getElementById('longitude').value = place.geometry.location.lng();
        });
    }

    window.onload = initMap;
</script>
<?= $this->endSection() ?>