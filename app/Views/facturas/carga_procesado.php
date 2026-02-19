<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
    #globalDropOverlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 123, 255, 0.08);
        backdrop-filter: blur(3px);
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    #globalDropOverlay .overlay-content {
        border: 2px dashed #0d6efd;
        padding: 40px 60px;
        border-radius: 12px;
        text-align: center;
        background: white;
        color: #0d6efd;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }
</style>

<!-- Overlay global tipo WhatsApp -->
<div id="globalDropOverlay" class="d-none">
    <div class="overlay-content">
        <i class="fas fa-cloud-upload-alt fa-3x mb-3"></i>
        <h4>Suelta los archivos JSON aquí</h4>
        <p class="mb-0">Carga masiva de facturas</p>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">

                <h4 class="header-title mb-0">
                    <i class="fas fa-file-invoice me-2"></i>
                    Carga Masiva de Facturas (JSON)
                </h4>

                <!-- Dropzone compacto -->
                <div id="dropZone"
                    class="border border-2 border-dashed rounded px-3 py-2 text-center"
                    style="cursor: pointer; background-color: #f8f9fa; min-width: 220px;">

                    <small class="text-muted">
                        <i class="fas fa-cloud-upload-alt me-1"></i>
                        Arrastra o haz clic
                    </small>

                    <input type="file"
                        id="jsonFiles"
                        accept=".json"
                        multiple
                        hidden>
                </div>

            </div>

            <div class="card-body">

                <!-- Tabla -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle" id="filesTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Nombre Archivo</th>
                                <th>Tamaño</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Inicialmente vacío -->
                        </tbody>
                    </table>
                </div>

                <div class="text-end mt-3">
                    <button class="btn btn-success" id="btnProcesar" disabled>
                        <i class="fas fa-check me-1"></i>
                        Procesar Archivos
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const overlay = document.getElementById('globalDropOverlay');
    const inputFiles = document.getElementById('jsonFiles');
    const dropZone = document.getElementById('dropZone');
    const tableBody = document.querySelector('#filesTable tbody');
    const btnProcesar = document.getElementById('btnProcesar');

    let archivosSeleccionados = [];
    let dragCounter = 0;

    // CLICK EN DROPZONE PEQUEÑO
    dropZone.addEventListener('click', () => inputFiles.click());

    inputFiles.addEventListener('change', () => {
        handleFiles(inputFiles.files);
    });

    // OVERLAY GLOBAL (TIPO WHATSAPP)
    window.addEventListener('dragenter', (e) => {
        e.preventDefault();
        dragCounter++;
        overlay.classList.remove('d-none');
    });

    window.addEventListener('dragover', (e) => {
        e.preventDefault();
    });

    window.addEventListener('dragleave', (e) => {
        dragCounter--;
        if (dragCounter === 0) {
            overlay.classList.add('d-none');
        }
    });

    window.addEventListener('drop', (e) => {
        e.preventDefault();
        overlay.classList.add('d-none');
        dragCounter = 0;

        if (e.dataTransfer.files.length > 0) {
            handleFiles(e.dataTransfer.files);
        }
    });

    // MANEJO DE ARCHIVOS
    function handleFiles(files) {
        for (let file of files) {
            if (file.name.toLowerCase().endsWith('.json')) {
                archivosSeleccionados.push(file);
            }
        }
        renderTable();
    }

    function renderTable() {
        tableBody.innerHTML = '';

        archivosSeleccionados.forEach((file, index) => {
            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${file.name}</td>
                    <td>${(file.size / 1024).toFixed(2)} KB</td>
                    <td><span class="badge bg-secondary">Pendiente</span></td>
                </tr>
            `;
            tableBody.innerHTML += row;
        });
        btnProcesar.disabled = archivosSeleccionados.length === 0;
    }
</script>

<?= $this->endSection() ?>