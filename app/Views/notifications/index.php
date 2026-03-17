<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-12">

        <div class="card">

            <div class="card-header d-flex">
                <h4 class="header-title">
                    <i class="fa-solid fa-bell mr-2"></i> Notificaciones
                </h4>
            </div>

            <div class="card-body">

                <div class="row mb-3">

                    <div class="col-md-6">
                        <label>Rango de fechas</label>
                        <input type="text" id="dateRange" class="form-control" placeholder="Seleccionar rango">
                    </div>

                    <div class="col-md-2">
                        <label>Resultados</label>
                        <select id="perPageSelect" class="form-control">

                            <option value="5">5</option>
                            <option value="10" selected>10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>

                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">

                        <button class="btn btn-primary w-100" id="filterBtn">
                            <i class="fa fa-search"></i> Filtrar
                        </button>

                    </div>

                </div>

                <div id="table-container">
                    <?= $this->include('notifications/_table') ?>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const container = document.getElementById('table-container');

        function loadResults(page = 1) {

            const range = document.getElementById('dateRange').value;

            let from = "";
            let to = "";

            if (range) {

                const parts = range.split(" ");

                from = parts[0] ?? "";
                to = parts[2] ?? "";

            }
            
            const perPage = document.getElementById('perPageSelect').value;

            const url = `<?= base_url('notifications-search') ?>?from=${from}&to=${to}&perPage=${perPage}&page=${page}`;

            fetch(url)
                .then(r => r.text())
                .then(html => {
                    container.innerHTML = html;
                    bindPagination();
                });

        }

        function bindPagination() {

            document.querySelectorAll('#pagination-links a').forEach(link => {

                link.addEventListener('click', function(e) {

                    e.preventDefault();

                    const url = new URL(this.href);
                    const page = url.searchParams.get('page');

                    loadResults(page);

                });

            });

        }

        document.getElementById('filterBtn').addEventListener('click', () => loadResults());

        document.getElementById('perPageSelect').addEventListener('change', () => loadResults());

        bindPagination();

        flatpickr("#dateRange", {

            mode: "range",
            dateFormat: "Y-m-d",
            locale: "es",
            defaultDate: [new Date().fp_incr(-7), new Date()]

        });
    });
</script>

<?= $this->endSection() ?>