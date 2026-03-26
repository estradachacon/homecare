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

        .seller-inline+.select2 {
            width: 180px !important;
        }

        .select2-container--default .select2-selection--single {
            height: 26px !important;
            border-radius: 6px;
            font-size: 16px;
        }

        .select2-selection__rendered {
            line-height: 24px !important;
            padding-left: 6px !important;
        }

        .select2-selection__arrow {
            height: 24px !important;
        }

        .remove-cell {
            position: relative;
            /* referencia para el botón */
            padding-bottom: 28px;
            /* espacio para la X abajo */
        }

        .remove-cell .remove-row {
            position: absolute;
            bottom: 4px;
            /* 🔥 pegado al fondo */
            left: 50%;
            transform: translateX(-50%);
            opacity: .4;

            align-items: center;
        }

        .remove-cell:hover .remove-row {
            opacity: 1;
        }

        .index-cell button {
            opacity: .4;
        }

        .index-cell:hover button {
            opacity: 1;
        }

        .form-select-sm {
            font-size: 13px;
            padding: .25rem .5rem;
        }

        .select-group {
            min-width: 380px;
            min-height: 70px;
            /* 🔥 Ajusta aquí */
            display: flex;
            align-items: center;
        }

        .condicion-select,
        .credito-select {
            border-radius: 8px !important;
            max-height: 27px !important;
        }
    </style>
    <div id="globalDropOverlay" class="d-none">
        <div class="overlay-content">
            <i class="fas fa-cloud-upload-alt fa-3x mb-3"></i>
            <h4 class="header-title mb-0">
                <i class="fas fa-file-invoice me-2 text-primary"></i>
                Carga Masiva de Facturas de compras (JSON)
            </h4>
            <small class="text-danger fw-bold ms-2">
                Máximo 35 archivos por carga
            </small>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <h4 class="header-title mb-0">
                        <i class="fas fa-file-invoice me-2"></i>
                        Carga Masiva de Facturas de compras (JSON)
                        <br>
                        <small class="text-danger fw-bold ms-2">
                            Máximo 35 archivos por carga
                        </small>
                    </h4>

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
        const EMISOR_NIT = "<?= $emisor->nit ?>";
        const EMISOR_NRC = "<?= $emisor->nrc ?>";

        // función para normalizar NIT/NRC (quitar guiones y espacios)
        function limpiarDoc(doc) {
            if (!doc) return '';
            return doc.toString().replace(/[^0-9]/g, '');
        }
    </script>
    <script>
        const overlay = document.getElementById('globalDropOverlay');
        const inputFiles = document.getElementById('jsonFiles');
        const dropZone = document.getElementById('dropZone');
        const tableBody = document.querySelector('#filesTable tbody');
        const btnProcesar = document.getElementById('btnProcesar');

        const MAX_ARCHIVOS = 35;

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

    if (archivosSeleccionados.length + archivosArray.length > MAX_ARCHIVOS) {
        Swal.fire('Límite excedido', 'Máximo 35 archivos', 'warning');
        return;
    }

    archivosArray.forEach(file => {

        if (!file.name.toLowerCase().endsWith('.json')) return;

        const reader = new FileReader();

        reader.onload = function(e) {

            try {
                const json = JSON.parse(e.target.result);

                // =============================
                // 🔥 VALIDAR QUE SEA COMPRA (RECEPTOR = MI EMPRESA)
                // =============================

                const receptor = json.receptor ?? {};

                const limpiar = (val) => (val || '').toString().replace(/[^0-9]/g, '');

                const nrcJson = limpiar(receptor.nrc);
                const nitJson = limpiar(receptor.nit);

                const nrcSistema = limpiar(EMISOR_NRC);
                const nitSistema = limpiar(EMISOR_NIT);

                const esMiEmpresa =
                    (nrcJson && nrcJson === nrcSistema) ||
                    (nitJson && nitJson === nitSistema);

                if (!esMiEmpresa) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Documento inválido',
                        html: `
                            <div style="text-align:left;">
                                <b>El documento no está emitido a tu empresa</b><br><br>

                                <small><b>NRC documento:</b> ${receptor.nrc ?? 'N/D'}</small><br>
                                <small><b>NIT documento:</b> ${receptor.nit ?? 'N/D'}</small><br><br>

                                <small><b>Tu empresa:</b></small><br>
                                <small>NRC: ${EMISOR_NRC}</small><br>
                                <small>NIT: ${EMISOR_NIT}</small>
                            </div>
                        `
                    });

                    return;
                }

                // =============================
                // DATOS PRINCIPALES
                // =============================

                const codigo = json.identificacion?.codigoGeneracion ?? null;
                const numeroControl = json.identificacion?.numeroControl ?? null;

                if (!codigo || !numeroControl) return;

                const existe = archivosSeleccionados.some(f => f.codigoGeneracion === codigo);

                if (existe) {
                    Swal.fire('Duplicado', file.name, 'warning');
                    return;
                }

                const factura = {
                    file: file,
                    codigoGeneracion: codigo,
                    numeroControl: numeroControl,
                    correlativo: numeroControl.slice(-6),
                    tipoDoc: json.identificacion?.tipoDte ?? '',
                    fecha: json.identificacion?.fecEmi ?? '',
                    proveedor: json.emisor?.nombre ?? 'N/D',
                    total: json.resumen?.montoTotalOperacion ?? 0,
                    productos: json.cuerpoDocumento ?? []
                };

                archivosSeleccionados.push(factura);
                renderTable();

            } catch (err) {
                console.error(err);
            }
        };

        reader.readAsText(file);
    });
}

        function renderTable() {

    tableBody.innerHTML = '';

    archivosSeleccionados.forEach((f, index) => {

        const detailId = "detail_" + index;

        const productosHtml = f.productos.length ? `
            <ul class="list-group list-group-flush">
                ${f.productos.map(p => `
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <strong>${(p.descripcion || '').replace(/\n/g, '<br>')}</strong>
                            <br>
                            <small class="text-muted">
                                Cantidad: ${p.cantidad} | 
                                Precio: $${parseFloat(p.precioUni || 0).toFixed(2)}
                            </small>
                        </div>
                        <span class="badge bg-light text-dark">
                            $${(parseFloat(p.cantidad || 0) * parseFloat(p.precioUni || 0)).toFixed(2)}
                        </span>
                    </li>
                `).join('')}
            </ul>
        ` : '<small class="text-muted">Sin productos</small>';

        const row = `
            <tr class="main-row" data-target="${detailId}" style="cursor:pointer;">
                <td>
                    ${index + 1}
                    <button class="btn btn-sm btn-outline-danger ms-2 remove" data-index="${index}">
                        ×
                    </button>
                </td>
                <td class="text-center">${f.correlativo}</td>
                <td>
                    <strong>${f.proveedor}</strong><br>
                    <small>${f.file.name}</small>
                </td>
                <td>${formatFecha(f.fecha)}</td>
                <td class="text-end">$ ${parseFloat(f.total).toFixed(2)}</td>
            </tr>

            <tr id="${detailId}" style="display:none;">
                <td colspan="5">
                    <div class="p-2 bg-light rounded">
                        ${productosHtml}
                    </div>
                </td>
            </tr>
        `;

        tableBody.innerHTML += row;
    });

    // 🔥 click para expandir
    document.querySelectorAll('.main-row').forEach(row => {
        row.addEventListener('click', function(e) {

            // evitar conflicto con botón eliminar
            if (e.target.closest('.remove')) return;

            const target = document.getElementById(this.dataset.target);

            target.style.display =
                target.style.display === "none"
                ? "table-row"
                : "none";
        });
    });

    // 🔥 eliminar
    document.querySelectorAll('.remove').forEach(btn => {
        btn.onclick = function(e) {
            e.stopPropagation();
            archivosSeleccionados.splice(this.dataset.index, 1);
            renderTable();
        };
    });

    btnProcesar.disabled = archivosSeleccionados.length === 0;
}

        function procesar() {

            Swal.fire({
                title: 'Procesando...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            const formData = new FormData();

            archivosSeleccionados.forEach(f => {
                formData.append('archivos[]', f.file);
            });

            fetch("<?= base_url('purchases/processload') ?>", {
                    method: "POST",
                    body: formData
                })
                .then(res => res.json())
                .then(data => {

                    Swal.close();

                    if (data.success) {

                        Swal.fire(
                            'Éxito',
                            `${data.total} compra(s) procesadas correctamente`,
                            'success'
                        );

                        archivosSeleccionados = [];
                        renderTable();

                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }

                })
                .catch(err => {
                    Swal.close();
                    Swal.fire('Error', 'Error en servidor', 'error');
                    console.error(err);
                });
        }

        btnProcesar.addEventListener('click', () => {

            if (archivosSeleccionados.length === 0) {
                Swal.fire('Sin datos', 'Carga archivos primero', 'info');
                return;
            }

            Swal.fire({
                title: '¿Procesar?',
                text: `Se procesarán ${archivosSeleccionados.length} archivos`,
                icon: 'question',
                showCancelButton: true
            }).then(r => {
                if (r.isConfirmed) procesar();
            });
        });

        function formatFecha(fecha) {
            if (!fecha) return '';
            const p = fecha.split('-');
            return `${p[2]}/${p[1]}/${p[0]}`;
        }
    </script>

    <?= $this->endSection() ?>