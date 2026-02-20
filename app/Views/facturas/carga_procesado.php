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
                                <th style="width: 90px;" class="text-center">Correlativo</th>
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
    const DTE_TIPOS = <?= json_encode(dte_tipos()) ?>;
    const DTE_SIGLAS = <?= json_encode(dte_siglas()) ?>;
    const DTE_DESCRIPCIONES = <?= json_encode(dte_descripciones()) ?>;
</script>

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
                    const numeroControlCompleto = json.identificacion?.numeroControl ?? null;

                    if (!codigo || !numeroControlCompleto) return;

                    const correlativoInterno = numeroControlCompleto.slice(-6);

                    // 1️⃣ Duplicado dentro del mismo lote
                    if (codigosEnLote.has(codigo)) {
                        duplicados.push(file.name);
                        mostrarDuplicados(duplicados);
                        return;
                    }

                    // 2️⃣ Duplicado contra los ya agregados en vista
                    const yaExiste = archivosSeleccionados.some(f =>
                        f.codigoGeneracion === codigo
                    );

                    if (yaExiste) {
                        duplicados.push(file.name);
                        mostrarDuplicados(duplicados);
                        return;
                    }

                    const factura = {
                        file: file,
                        codigoGeneracion: codigo,
                        numeroControl: numeroControlCompleto,
                        correlativo: correlativoInterno,
                        tipoDoc: json.identificacion?.tipoDte ?? 'N/D',
                        fecha: json.identificacion?.fecEmi ?? 'N/D',
                        cliente: json.receptor?.nombre ?? 'N/D',
                        total: json.resumen?.montoTotalOperacion ?? 0,
                        productos: json.cuerpoDocumento ?? []
                    };

                    // 🔎 VALIDAR EN BASE DE DATOS
                    fetch("<?= base_url('facturas/validar-numero-control') ?>", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: "numero_control=" + encodeURIComponent(numeroControlCompleto)
                        })
                        .then(res => res.json())
                        .then(data => {

                            if (data.existe) {

                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'error',
                                    title: 'Factura ya existe en el sistema',
                                    html: `
                        <div style="text-align:left;">
                            <small><strong>Número de control:</strong></small><br>
                            <small>${numeroControlCompleto}</small>
                        </div>
                    `,
                                    showConfirmButton: false,
                                    timer: 4000
                                });

                                return; // 🚫 No se agrega
                            }

                            // ✅ Si pasa todo, ahora sí agregamos
                            codigosEnLote.add(codigo);
                            archivosSeleccionados.push(factura);
                            renderTable();
                        });

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

    function procesarFacturas() {

        Swal.fire({
            title: 'Procesando...',
            text: 'Por favor espera mientras se cargan las facturas.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Solo enviamos los JSON ya leídos
        const jsons = archivosSeleccionados.map(f => ({
            codigoGeneracion: f.codigoGeneracion,
            json: f.file
        }));

        const formData = new FormData();

        archivosSeleccionados.forEach((factura, index) => {
            formData.append('archivos[]', factura.file);
        });

        fetch("<?= base_url('facturas/cargar') ?>", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {

                Swal.close();

                if (data.success) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Carga completada',
                        text: data.message
                    });

                    archivosSeleccionados = [];
                    renderTable();

                } else {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });

                }

            })
            .catch(error => {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error inesperado',
                    text: 'Ocurrió un problema en el servidor.'
                });
                console.error(error);
            });
    }

    function renderTable() {
        tableBody.innerHTML = '';

        archivosSeleccionados.forEach((factura, index) => {

            const detailId = "detail_" + index;

            const productosHtml = factura.productos.length ?
                `
                <ul class="list-group list-group-flush">
                    ${factura.productos.map(p => `
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div>
                                <strong>${(p.descripcion || '').replace(/\n/g, '<br>')}</strong>
                                <br>
                                <small class="text-muted">
                                    Cantidad: ${p.cantidad} | 
                                    Precio: $${parseFloat(p.precioUni || 0).toFixed(2)}
                                </small>
                            </div>
                            <span class="badge-xl bg-white rounded-pill">
                                $${(parseFloat(p.cantidad || 0) * parseFloat(p.precioUni || 0)).toFixed(2)}
                            </span>
                        </li>
                    `).join('')}
                </ul>
                ` :
                '<small class="text-muted">Sin productos</small>';

            const nombre = DTE_TIPOS[factura.nombre] ?? 'Desconocido';
            const sigla = DTE_SIGLAS[factura.tipoDoc] ?? '';
            const descripcion = DTE_DESCRIPCIONES[sigla] ?? '';

            const row = `
            <tr class="main-row" data-target="${detailId}" style="cursor:pointer;">
                <td>${index + 1}</td>
                <td class="text-center">
                    <span class="badge-xl bg-white">
                        ${factura.correlativo ?? '------'}
                    </span>
                </td>
                <td>
                    <strong>${factura.cliente}</strong>
                    <br>
                    <small class="text-primary">${sigla} - ${descripcion}</small>
                    <br>
                    <small class="text-muted">${factura.file.name}</small>
                </td>
                <td>${factura.fecha}</td>
                <td class="text-end">$ ${parseFloat(factura.total || 0).toFixed(2)}</td>
            </tr>

            <tr id="${detailId}" class="detail-row" style="display:none;">
                <td colspan="5">
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

        btnProcesar.addEventListener('click', function() {

            if (archivosSeleccionados.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'No hay facturas para procesar',
                    text: 'Debes cargar al menos un archivo JSON.'
                });
                return;
            }

            Swal.fire({
                title: '¿Confirmar carga masiva?',
                html: `
            <div style="text-align:left;">
                Se procesarán <strong>${archivosSeleccionados.length}</strong> factura(s).<br><br>
                Esta acción enviará la información al sistema.
            </div>
        `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, procesar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {

                if (result.isConfirmed) {

                    // Aquí va tu lógica real de envío al backend
                    procesarFacturas();

                }

            });

        });
        btnProcesar.disabled = archivosSeleccionados.length === 0;
    }
</script>

<?= $this->endSection() ?>