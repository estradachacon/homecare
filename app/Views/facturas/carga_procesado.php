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
                                <th style="width: 50px;">#</th>
                                <th>Documento</th>
                                <th style="width: 140px;">Fecha</th>
                                <th style="width: 140px;" class="text-end">Total</th>
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

    dropZone.addEventListener('click', () => inputFiles.click());

    inputFiles.addEventListener('change', () => {
        handleFiles(inputFiles.files);
    });

    window.addEventListener('dragenter', (e) => {
        e.preventDefault();
        dragCounter++;
        overlay.classList.remove('d-none');
    });

    window.addEventListener('dragover', (e) => e.preventDefault());

    window.addEventListener('dragleave', () => {
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

    function handleFiles(files) {

        let archivosArray = Array.from(files);
        let codigosEnLote = new Set();
        let duplicados = [];

        archivosArray.forEach(file => {
            if (!file.name.toLowerCase().endsWith('.json')) return;

            const reader = new FileReader();

            reader.onload = function(e) {
                try {
                    const json = JSON.parse(e.target.result);

                    const codigo = json.identificacion?.codigoGeneracion ?? null;

                    if (!codigo) return;

                    // 1️⃣ Duplicado dentro del mismo lote
                    if (codigosEnLote.has(codigo)) {
                        duplicados.push(file.name);
                        mostrarDuplicados(duplicados);
                        return;
                    }

                    // 2️⃣ Duplicado contra los ya agregados
                    const yaExiste = archivosSeleccionados.some(f =>
                        f.codigoGeneracion === codigo
                    );

                    if (yaExiste) {
                        duplicados.push(file.name);
                        mostrarDuplicados(duplicados);
                        return;
                    }

                    codigosEnLote.add(codigo);

                    const factura = {
                        file: file,
                        codigoGeneracion: codigo,
                        numeroControl: json.identificacion?.numeroControl ?? null,
                        tipoDoc: json.identificacion?.tipoDte ?? 'N/D',
                        fecha: json.identificacion?.fecEmi ?? 'N/D',
                        cliente: json.receptor?.nombre ?? 'N/D',
                        total: json.resumen?.montoTotalOperacion ?? 0,
                        productos: json.cuerpoDocumento ?? []
                    };

                    archivosSeleccionados.push(factura);
                    renderTable();

                } catch (error) {
                    console.error("Error leyendo JSON:", error);
                }
            };

            reader.readAsText(file);
        });
    }

    function mostrarDuplicados(lista) {

        if (!lista.length) return;

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'warning',
            title: 'Facturas duplicadas en esta vista detectadas:',
            html: `
            <div style="text-align:left;">
                ${lista.map(n => `<small>• ${n}</small>`).join('<br>')}
            </div>
        `,
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true
        });

    }

    function readJsonFile(file) {
        const reader = new FileReader();

        reader.onload = function(e) {
            try {
                const json = JSON.parse(e.target.result);

                const factura = {
                    file: file,
                    codigoGeneracion: json.identificacion?.codigoGeneracion ?? null,
                    numeroControl: json.identificacion?.numeroControl ?? null,
                    tipoDoc: json.identificacion?.tipoDte ?? 'N/D',
                    fecha: json.identificacion?.fecEmi ?? 'N/D',
                    cliente: json.receptor?.nombre ?? 'N/D',
                    total: json.resumen?.montoTotalOperacion ?? 0,
                    productos: json.cuerpoDocumento ?? []
                };
                // Verificar duplicado por codigoGeneracion
                const existe = archivosSeleccionados.some(f =>
                    f.codigoGeneracion === factura.codigoGeneracion
                );

                if (existe) {

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'warning',
                        title: 'Factura duplicada en esta vista',
                        html: `
            <div style="text-align:left;">
                <strong>Archivo:</strong><br>
                <small>${file.name}</small><br><br>
                <strong>Código Generación:</strong><br>
                <small>${factura.codigoGeneracion ?? 'N/D'}</small>
            </div>
        `,
                        showConfirmButton: false,
                        timer: 3500,
                        timerProgressBar: true
                    });

                    return;
                }

                archivosSeleccionados.push(factura);
                renderTable();
            } catch (error) {
                console.error("Error leyendo JSON:", error);
            }
        };

        reader.readAsText(file);
    }

    function renderTable() {
        tableBody.innerHTML = '';

        archivosSeleccionados.forEach((factura, index) => {

            const detailId = "detail_" + index;

            const productosHtml = factura.productos.map(p => `
            <div class="d-flex justify-content-between small border-bottom py-1">
                <div>${p.descripcion}</div>
                <div>
                    ${p.cantidad} x ${parseFloat(p.precioUni || 0).toFixed(2)}
                </div>
            </div>
        `).join('');

            const row = `
            <tr class="main-row" data-target="${detailId}" style="cursor:pointer;">
                <td>${index + 1}</td>
                <td>
                    <strong>${factura.tipoDoc}</strong> - ${factura.cliente}
                    <br>
                    <small class="text-muted">${factura.file.name}</small>
                </td>
                <td>${factura.fecha}</td>
                <td>$ ${parseFloat(factura.total || 0).toFixed(2)}</td>
            </tr>

            <tr id="${detailId}" class="detail-row" style="display:none;">
                <td colspan="4">
                    <div class="p-2 bg-light rounded">
                        ${productosHtml || '<small class="text-muted">Sin productos</small>'}
                    </div>
                </td>
            </tr>
        `;

            tableBody.innerHTML += row;
        });

        // Toggle manual
        document.querySelectorAll('.main-row').forEach(row => {
            row.addEventListener('click', function() {
                const target = document.getElementById(this.dataset.target);

                if (target.style.display === "none") {
                    target.style.display = "table-row";
                } else {
                    target.style.display = "none";
                }
            });
        });

        btnProcesar.disabled = archivosSeleccionados.length === 0;
    }
</script>

<?= $this->endSection() ?>