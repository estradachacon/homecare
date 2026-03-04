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
                Carga Masiva de Facturas (JSON)
            </h4>
            <small class="text-danger fw-bold ms-2">
                Máximo 35 archivos por carga
            </small>
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

            // 🔥 VALIDACIÓN DE LÍMITE
            if (archivosSeleccionados.length + archivosArray.length > MAX_ARCHIVOS) {

                Swal.fire({
                    icon: 'warning',
                    title: 'Límite excedido',
                    html: `
                        Puedes cargar un máximo de <strong>${MAX_ARCHIVOS}</strong> archivos por proceso.<br><br>
                        Actualmente tienes ${archivosSeleccionados.length} archivo(s) cargado(s).
                    `
                });

                return;
            }
            let codigosEnLote = new Set();
            let duplicados = [];
            archivosArray.forEach(file => {
                if (!file.name.toLowerCase().endsWith('.json')) return;
                const reader = new FileReader();
                reader.onload = function(e) {
                    try {
                        const json = JSON.parse(e.target.result);
                        const nitJson = json.emisor?.nit ?? null;
                        const nrcJson = json.emisor?.nrc ?? null;

                        const nitSistema = limpiarDoc(EMISOR_NIT);
                        const nrcSistema = limpiarDoc(EMISOR_NRC);

                        const nitFactura = limpiarDoc(nitJson);
                        const nrcFactura = limpiarDoc(nrcJson);

                        // 🔴 VALIDACIÓN DEL EMISOR
                        if (nitFactura !== nitSistema && nrcFactura !== nrcSistema) {

                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: 'Factura rechazada',
                                html: `
                                    <div style="text-align:left;">
                                        <small><strong>El emisor no coincide</strong></small><br><br>

                                        <small><b>NIT factura:</b> ${nitJson ?? 'N/D'}</small><br>
                                        <small><b>NRC factura:</b> ${nrcJson ?? 'N/D'}</small><br><br>

                                        <small><b>Emisor esperado:</b></small><br>
                                        <small>NIT: ${EMISOR_NIT}</small><br>
                                        <small>NRC: ${EMISOR_NRC}</small>
                                    </div>
                                `,
                                showConfirmButton: false,
                                timer: 5000
                            });

                            return; // NO CARGA LA FACTURA
                        }

                        const codigo = json.identificacion?.codigoGeneracion ?? null;
                        const numeroControlCompleto = json.identificacion?.numeroControl ?? null;
                        if (!codigo || !numeroControlCompleto) return;
                        const correlativoInterno = numeroControlCompleto.slice(-6);
                        if (codigosEnLote.has(codigo)) {
                            duplicados.push(file.name);
                            mostrarDuplicados(duplicados);
                            return;
                        }
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
                            productos: json.cuerpoDocumento ?? [],
                            seller_id: null,
                            tipo_venta_id: null,
                            condicion_operacion: parseInt(json.resumen?.condicionOperacion ?? 1),
                            plazo_credito: (json.resumen?.condicionOperacion == 2) ? 30 : null
                        };
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
                                    return;
                                }
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

        function initTipoVentaSelects() {
            $('.tipo-venta-select').each(function() {

                if ($(this).hasClass("select2-hidden-accessible")) return;

                const index = $(this).data('index');

                $(this).select2({
                    placeholder: 'Tipo venta...',
                    minimumInputLength: 1,
                    ajax: {
                        url: "<?= base_url('/tipo_venta/searchAjax') ?>",
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                q: params.term,
                                select2: 1
                            };
                        },
                        processResults: function(data) {
                            return data;
                        }
                    }
                });

                $(this)
                    .append(new Option('Privado', 1, true, true))
                    .trigger('change');

                archivosSeleccionados[index].tipo_venta_id = 1;

                $(this).on('select2:select', function(e) {
                    archivosSeleccionados[index].tipo_venta_id = e.params.data.id;
                });

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
                        productos: json.cuerpoDocumento ?? [],
                    };
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
            const jsons = archivosSeleccionados.map(f => ({
                codigoGeneracion: f.codigoGeneracion,
                json: f.file
            }));
            const formData = new FormData();

            archivosSeleccionados.forEach((factura, index) => {
                formData.append('archivos[]', factura.file);
                formData.append('seller_ids[]', factura.seller_id);
                formData.append('tipo_venta_ids[]', factura.tipo_venta_id);
                formData.append('condiciones[]', factura.condicion_operacion);
                formData.append('plazos_credito[]', factura.plazo_credito);
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

        function formatFecha(fecha) {
            if (!fecha) return '';
            const partes = fecha.split('-');
            if (partes.length !== 3) return fecha;
            return `${partes[2]}/${partes[1]}/${partes[0]}`;
        }

        function initSellerSelects() {
            $('.seller-select').each(function() {
                if ($(this).hasClass("select2-hidden-accessible")) return;
                const index = $(this).data('index');
                $(this).select2({
                    placeholder: 'Buscar vendedor...',
                    minimumInputLength: 2,
                    ajax: {
                        url: "<?= base_url('sellers/searchAjax') ?>",
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                q: params.term,
                                select2: 1
                            };
                        },
                        processResults: function(data) {
                            return data;
                        }
                    }
                });
                $(this).on('click select2:opening select2:select', function(e) {
                    e.stopPropagation();
                });
                $(this).on('select2:select', function(e) {
                    archivosSeleccionados[index].seller_id = e.params.data.id;
                });
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
                        <td class="remove-cell">
                            ${index + 1}

                            <button class="btn btn-sm btn-outline-danger remove-row"
                                data-index="${index}">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                        <td class="text-center">
                            <span class="badge-xl bg-white">
                                ${factura.correlativo ?? '------'}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex justify-content-between align-items-start">

                                <div class="d-flex flex-column">
                                    <strong class="mb-1">${factura.cliente}</strong>

                                    <small class="text-primary">
                                        ${sigla} - ${descripcion}
                                    </small>

                                    <small class="text-muted">
                                        ${factura.file.name}
                                    </small>
                                </div>
                                <div class="ms-auto select-group">
                            <div class="ms-auto select-group">
                                <div class="d-flex flex-wrap" style="width: 360px;">

                                    <div style="width: 50%; padding:2px;">
                                        <select class="condicion-select form-control form-control-sm"
                                            data-index="${index}">
                                            <option value="1" ${factura.condicion_operacion == 1 ? 'selected' : ''}>Contado</option>
                                            <option value="2" ${factura.condicion_operacion == 2 ? 'selected' : ''}>Crédito</option>
                                        </select>
                                    </div>

                                    <div style="width: 50%; padding:2px;">
                                        <select class="seller-select form-control form-control-sm"
                                            data-index="${index}">
                                        </select>
                                    </div>

                                    <div style="width: 50%; padding:2px;">
                                        <select class="credito-select form-control form-control-sm"
                                            data-index="${index}"
                                            style="${factura.condicion_operacion == 2 ? '' : 'visibility:hidden;'}">

                                            <option value="30" ${factura.plazo_credito == 30 ? 'selected' : ''}>30 días</option>
                                            <option value="45" ${factura.plazo_credito == 45 ? 'selected' : ''}>45 días</option>
                                            <option value="60" ${factura.plazo_credito == 60 ? 'selected' : ''}>60 días</option>
                                            <option value="90" ${factura.plazo_credito == 90 ? 'selected' : ''}>90 días</option>
                                            <option value="120" ${factura.plazo_credito == 120 ? 'selected' : ''}>120 días</option>
                                        </select>
                                    </div>

                                    <div style="width: 50%; padding:2px;">
                                        <select class="tipo-venta-select form-control form-control-sm"
                                            data-index="${index}">
                                        </select>
                                    </div>

                                </div>
                            </div>
                            </div>
                            </div>
                        </td>
                        <td>${formatFecha(factura.fecha)}</td>
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

            // Bloqueo para el row de los detalles en factura
            document.querySelectorAll('.main-row').forEach(row => {
                row.addEventListener('click', function(e) {

                    // 🔥 bloquear clicks en cualquier select
                    if (
                        e.target.closest('select') ||
                        e.target.closest('.select2')
                    ) {
                        return;
                    }

                    const target = document.getElementById(this.dataset.target);

                    target.style.display =
                        target.style.display === "none" ?
                        "table-row" :
                        "none";
                });
            });

            document.querySelectorAll('.remove-row').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();

                    const idx = this.dataset.index;

                    archivosSeleccionados.splice(idx, 1);
                    renderTable();
                });
            });

            document.querySelectorAll('.credito-select').forEach(select => {
                select.addEventListener('change', function() {
                    const idx = this.dataset.index;
                    archivosSeleccionados[idx].plazo_credito = parseInt(this.value);
                });
            });

            document.querySelectorAll('.condicion-select').forEach(select => {
                select.addEventListener('change', function() {

                    const idx = this.dataset.index;
                    const valor = parseInt(this.value);

                    archivosSeleccionados[idx].condicion_operacion = valor;

                    if (valor === 1) {
                        // Contado
                        archivosSeleccionados[idx].plazo_credito = null;
                    } else {
                        // Crédito
                        archivosSeleccionados[idx].plazo_credito = 30;
                    }

                    renderTable(); // 🔥 redibujar fila
                });
            });

            btnProcesar.addEventListener('click', function() {
                const sinVendedor = archivosSeleccionados.some(f => !f.seller_id);
                const sinTipoVenta = archivosSeleccionados.some(f => !f.tipo_venta_id);
                if (sinTipoVenta) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Faltan tipos de venta',
                        text: 'Todas las facturas deben tener tipo de venta.'
                    });
                    return;
                }
                if (sinVendedor) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Faltan vendedores',
                        text: 'Todas las facturas deben tener vendedor asignado.'
                    });
                    return;
                }
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
            btnProcesar.disabled = archivosSeleccionados.length === 0 || archivosSeleccionados.length > MAX_ARCHIVOS;
            initSellerSelects();
            initTipoVentaSelects();
        }
    </script>

    <?= $this->endSection() ?>