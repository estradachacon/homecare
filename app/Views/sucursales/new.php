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

                <form action="<?= site_url('branches') ?>" method="post" id="branchForm">

                    <div class="mb-3">
                        <label for="branch_name" class="form-label">Nombre de la Sucursal</label>
                        <input type="text" name="branch_name" id="branch_name" class="form-control"
                            value="<?= old('branch_name') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="branch_direction" class="form-label">Dirección</label>
                        <input type="text" name="branch_direction" id="branch_direction" class="form-control"
                            value="<?= old('branch_direction') ?>" required placeholder="Escribe la dirección">
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

<!-- Leaflet JS y CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    // 1. Referencia al formulario corregida
    const form = document.getElementById('branchForm');

    // 2. Coordenadas iniciales (Prioridad: valor del input > San Salvador)
    let initialLat = parseFloat(document.getElementById('latitude').value) || 13.6929;
    let initialLng = parseFloat(document.getElementById('longitude').value) || -89.2182;

    // 3. Inicializar mapa y marcador
    let map = L.map('map').setView([initialLat, initialLng], 15);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    let marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);

    // Función para actualizar los inputs ocultos
    function updateCoords(lat, lng) {
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
    }

    // Evento al arrastrar el marcador
    marker.on('dragend', function(e) {
        const pos = e.target.getLatLng();
        updateCoords(pos.lat, pos.lng);
    });

    // Evento al hacer click en el mapa (opcional, ayuda al usuario)
    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        updateCoords(e.latlng.lat, e.latlng.lng);
    });

    // 4. Geocoding (Buscar por dirección)
    const inputDir = document.getElementById('branch_direction');
    inputDir.addEventListener('change', function() {
        const address = this.value;
        if (!address) return;

        fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(address)}&format=json&limit=1`)
            .then(resp => resp.json())
            .then(data => {
                if (data.length > 0) {
                    const { lat, lon } = data[0];
                    const newPos = [parseFloat(lat), parseFloat(lon)];
                    map.setView(newPos, 17);
                    marker.setLatLng(newPos);
                    updateCoords(lat, lon);
                }
            });
    });

    // 5. Asegurar datos antes de enviar
    form.addEventListener('submit', function(e) {
        const pos = marker.getLatLng();
        updateCoords(pos.lat, pos.lng);
    });
</script>

<?= $this->endSection() ?>
