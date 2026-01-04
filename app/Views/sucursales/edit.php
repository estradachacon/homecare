<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex">
                <h4 class="header-title">Editar sucursal</h4>
            </div>
            <div class="card-body">

                <form action="<?= site_url('branches/update/' . $branch->id) ?>" method="post" id="branchForm">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label">Nombre de la Sucursal</label>
                        <input type="text" name="branch_name" class="form-control"
                            value="<?= old('branch_name', $branch->branch_name) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="branch_direction">Dirección</label>
                        <input type="text" name="branch_direction" id="branch_direction" class="form-control"
                            value="<?= old('branch_direction', $branch->branch_direction) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ubicación actual (puedes arrastrar el marcador)</label>
                        <div id="map" style="height: 400px; width: 100%; border:1px solid #ccc; border-radius: 8px;"></div>
                    </div>

                    <input type="hidden" name="latitude" id="latitude" value="<?= old('latitude', $branch->latitude) ?>">
                    <input type="hidden" name="longitude" id="longitude" value="<?= old('longitude', $branch->longitude) ?>">

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Actualizar Cambios</button>
                        <a href="<?= site_url('branches') ?>" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
    // 1. Obtener coordenadas desde la base de datos (o usar default si fallan)
    let savedLat = parseFloat(document.getElementById('latitude').value) || 13.6929;
    let savedLng = parseFloat(document.getElementById('longitude').value) || -89.2182;

    // 2. Inicializar el mapa centrado en la ubicación guardada
    let map = L.map('map').setView([savedLat, savedLng], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // 3. Crear el marcador en la ubicación guardada
    let marker = L.marker([savedLat, savedLng], { draggable: true }).addTo(map);

    // Función para actualizar inputs
    function updateCoords(lat, lng) {
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
    }

    // Evento al mover el marcador
    marker.on('dragend', function(e) {
        const pos = e.target.getLatLng();
        updateCoords(pos.lat, pos.lng);
    });

    // Mover marcador al hacer clic en el mapa
    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        updateCoords(e.latlng.lat, e.latlng.lng);
    });

    // Búsqueda por dirección (Geocoding)
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

    // Sincronización final antes del submit
    document.getElementById('branchForm').addEventListener('submit', function() {
        const pos = marker.getLatLng();
        updateCoords(pos.lat, pos.lng);
    });
</script>

<?= $this->endSection() ?>