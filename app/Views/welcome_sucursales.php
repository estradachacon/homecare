<?= $this->extend('Layouts/mainbody_welcome') ?>
<?= $this->section('content') ?>

<section class="py-5">
    <div class="container">
        <h2 class="mb-4 text-center">Nuestras Sucursales</h2>

        <?php if ($branches): ?>
            <div class="row">
                <?php foreach ($branches as $branch): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><?= esc($branch->branch_name) ?></h5>
                                <p class="card-text"><?= esc($branch->branch_direction) ?></p>
                                <div id="map-<?= $branch->id ?>" style="height: 200px; border:1px solid #ccc;"></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center">No hay sucursales disponibles.</p>
        <?php endif; ?>
    </div>
</section>

<!-- Leaflet JS y CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
    <?php foreach ($branches as $branch): ?>
        (function() {
            const lat = <?= $branch->latitude ?: '13.6929' ?>;
            const lng = <?= $branch->longitude ?: '-89.2182' ?>;
            const mapId = 'map-<?= $branch->id ?>';

            const map = L.map(mapId).setView([lat, lng], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors'
            }).addTo(map);

            L.marker([lat, lng]).addTo(map)
                .bindPopup('<strong><?= esc($branch->branch_name) ?></strong><br><?= esc($branch->branch_direction) ?>')
                .openPopup();
        })();
    <?php endforeach; ?>
</script>

<?= $this->endSection() ?>
