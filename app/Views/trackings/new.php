<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>

</style>
<div id="toastContainer" class="toast-container"></div>
<?= csrf_field() ?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="header-title mb-0">Asignar paquetes al motorista</h4>
            </div>

            <div class="card-body">

                <!-- Motorista -->
                <div class="row mb-3 justify-content-between">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Motorista</label>
                        <select class="form-control" name="motorista_id" id="motorista_id" required>
                            <option value="">Seleccione un motorista</option>
                            <?php foreach ($motoristas as $m): ?>
                                <option value="<?= $m['id'] ?>"><?= $m['user_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4 d-none">
                        <label class="form-label fw-bold">Ruta</label>
                        <select class="form-control" name="ruta_id" id="ruta_id">
                            <option value="">Seleccione una ruta</option>
                            <?php foreach ($rutas as $r): ?>
                                <option value="<?= $r->id ?>"><?= $r->route_name ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Cargará automáticamente los paquetes pendientes.</small>
                    </div>


                    <!-- Botón para abrir modal -->
                    <div class="col-md-4">
                        <label class="form-label fw-bold" style="visibility: hidden;">Botón</label>
                        <button type="button" class="btn btn-primary w-100" data-toggle="modal"
                            data-target="#modalPaquetes">
                            Agregar / quitar paquetes
                        </button>
                    </div>

                </div>

                <hr>

                <!-- TABLA DE PAQUETES ASIGNADOS -->
                <div class="table-responsive">
                    <table class="table table-bordered tabla-compacta" id="tablaPaquetes">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Vendedor/Cliente</th>
                                <th>Destino</th>
                                <th>Monto a cobrar</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>

                        <tbody id="body-paquetes">
                            <!-- Aquí el JS irá agregando filas -->
                        </tbody>

                        <tfoot class="table-secondary fw-bold">
                            <tr>
                                <td colspan="3" class="text-end">Total a cobrar:</td>
                                <td id="total-cobro" class="text-end">$ 0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Botón Guardar -->
                <div class="mt-3">
                    <button class="btn btn-success" id="btnGuardar">
                        Guardar asignación
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>


<!-- MODAL PARA AGREGAR / QUITAR PAQUETES -->
<div class="modal fade" id="modalPaquetes" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" style="max-width:60%;">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Seleccionar paquetes</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">

                <!-- Filtros dentro del modal (CORRECTO) -->
                <div class="d-flex align-items-center mb-3" style="gap: 10px;">

                    <!-- Filtro por ruta -->
                    <select id="filtroRuta" class="form-control w-50" multiple="multiple">
                        <?php foreach ($rutas as $r): ?>
                            <option value="<?= $r->id ?>"><?= $r->route_name ?></option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Botón Seleccionar todos -->
                    <button id="btnSelectAll" class="btn btn-secondary ms-2">
                        Seleccionar todos
                    </button>

                </div>

                <div class="table-responsive table-bordered" style="max-height:450px; overflow-y:auto;">
                    <table class="table table-bordered" id="tabla-selector">
                        <thead>
                            <tr>
                                <th>Vendedor/Cliente</th>
                                <th>Destino</th>
                                <th>Monto</th>
                                <th class="text-center"></th>
                            </tr>
                        </thead>
                        <tbody id="lista-paquetes"></tbody>
                    </table>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button class="btn btn-primary" id="btnAplicarPaquetes">Aplicar selección</button>
            </div>


        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {

        let paquetesSeleccionados = {}; // { paquete_id: {objeto paquete} }
        let paquetesTodos = {}; // Todos los paquetes para el modal
        let paquetesGlobal = []; // Para filtro

        const tablaBody = document.getElementById("body-paquetes");
        const tablaTotal = document.getElementById("total-cobro");
        const tablaModal = document.getElementById("lista-paquetes");
        const btnAplicar = document.getElementById("btnAplicarPaquetes");
        const btnGuardar = document.getElementById("btnGuardar");

        // 1) Cargar paquetes pendientes según la ruta (invisible)
        document.getElementById("ruta_id").addEventListener("change", function () {
            const rutaId = this.value;
            if (!rutaId) return;

            fetch(`/tracking/pendientes/ruta/${rutaId}`)
                .then(res => res.json())
                .then(data => {
                    paquetesSeleccionados = {};
                    data.forEach(pkg => paquetesSeleccionados[pkg.id] = pkg);
                    renderTablaPrincipal();
                });
        });

        // 2) Inicializar Select2 para filtro
        $('#filtroRuta').select2({
            placeholder: "Seleccione una o más rutas",
            width: '100%',
            allowClear: true,
            dropdownParent: $('#modalPaquetes')
        });

        // 3) Abrir modal y cargar paquetes - CORREGIDO
        $('#modalPaquetes').on('show.bs.modal', function () {
            // Si ya cargamos los paquetes antes, solo aplicar filtro actual
            if (paquetesGlobal.length > 0) {
                aplicarFiltroRuta();
                return;
            }

            Swal.fire({
                title: "Cargando paquetes...",
                text: "Por favor espere",
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch(`/tracking/pendientes/todos`)
                .then(res => res.json())
                .then(data => {
                    paquetesGlobal = data;
                    paquetesTodos = {};

                    // ** CORRECCIÓN: Mapear ruta_nombre a ruta_id usando las opciones del select **
                    const rutasMap = {};
                    <?php foreach ($rutas as $r): ?>
                        rutasMap['<?= $r->route_name ?>'] = '<?= $r->id ?>';
                    <?php endforeach; ?>

                    // Guardar paquetes en el objeto paquetesTodos y agregar ruta_id
                    data.forEach(pkg => {
                        // ** ASIGNAR ruta_id basado en ruta_nombre **
                        pkg.ruta_id = rutasMap[pkg.ruta_nombre] || '';
                        paquetesTodos[pkg.id] = pkg;
                    });

                    // Renderizar tabla completa
                    renderizarTablaModal(data);

                    // Limpiar filtro al abrir
                    $('#filtroRuta').val(null).trigger('change');

                    Swal.close();
                })
                .catch(() => Swal.fire("Error", "No se pudieron cargar los paquetes", "error"));
        });

        // 4) Filtro por rutas (multiselect) - CORREGIDO
        $('#filtroRuta').on('change', function () {
            aplicarFiltroRuta();
        });

        // ** FUNCIÓN DE FILTRADO CORREGIDA **
        function aplicarFiltroRuta() {
            const rutasSeleccionadas = $('#filtroRuta').val(); // Array de IDs de rutas seleccionadas
            const filas = document.querySelectorAll("#lista-paquetes tr");
            let filasVisibles = 0;

            // Obtener las rutas que estaban visibles antes del cambio
            const rutasAnteriores = new Set();
            filas.forEach(fila => {
                if (fila.style.display !== 'none') {
                    rutasAnteriores.add(fila.dataset.rutaId);
                }
            });

            filas.forEach(fila => {
                const rutaIdFila = fila.dataset.rutaId;
                const chk = fila.querySelector(".chk-paq");
                const id = chk.dataset.id;

                if (!rutasSeleccionadas || rutasSeleccionadas.length === 0 ||
                    rutasSeleccionadas.includes(rutaIdFila)) {
                    // FILA VISIBLE - mantener estado actual
                    fila.style.display = '';
                    filasVisibles++;
                } else {
                    // FILA OCULTA - si antes estaba visible (se quitó el filtro), deseleccionar
                    if (rutasAnteriores.has(rutaIdFila)) {
                        chk.checked = false;
                        // Eliminar de paquetesSeleccionados
                        if (paquetesSeleccionados[id]) {
                            delete paquetesSeleccionados[id];
                        }
                    }
                    fila.style.display = 'none';
                }
            });

            actualizarBotonSeleccionarTodos();
        }


        // 5) Aplicar selección del modal - MANTENIENDO TU MÉTODO QUE FUNCIONA
        btnAplicar.addEventListener("click", function () {
            // Actualizar paquetesSeleccionados con TODOS los checkboxes, no solo los visibles
            document.querySelectorAll(".chk-paq").forEach(chk => {
                const id = chk.dataset.id;
                if (chk.checked) {
                    paquetesSeleccionados[id] = paquetesTodos[id];
                } else {
                    delete paquetesSeleccionados[id];
                }
            });

            renderTablaPrincipal();
            $('#modalPaquetes').modal('hide');
        });

        // 6) Renderizar tabla principal
        function renderTablaPrincipal() {
            tablaBody.innerHTML = "";
            let total = 0, line = 1;

            Object.values(paquetesSeleccionados).forEach(pkg => {
                const monto = parseFloat(pkg.monto);
                total += monto;

                const destino = pkg.tipo_servicio == "1" ? pkg.punto_fijo_nombre
                    : pkg.tipo_servicio == "2" ? pkg.destino_personalizado
                        : pkg.lugar_recolecta_paquete;

                tablaBody.innerHTML += `
                <tr>
                    <td>${line++}</td>
                    <td><strong>${pkg.cliente}</strong><br><small class="text-muted">Vendedor: ${pkg.vendedor}</small></td>
                    <td>${destino}</td>
                    <td class="text-end">$ ${monto.toFixed(2)}</td>
                    <td class="text-center">
                        <button class="btn btn-danger btn-sm btn-quitar" data-id="${pkg.id}">
                            <i class="fa fa-times"></i>
                        </button>
                    </td>
                </tr>`;
            });

            tablaTotal.innerHTML = `$ ${total.toFixed(2)}`;

            // Agregar eventos a botones de quitar
            document.querySelectorAll(".btn-quitar").forEach(btn => {
                btn.addEventListener("click", function () {
                    delete paquetesSeleccionados[this.dataset.id];
                    renderTablaPrincipal();
                });
            });
        }

        // 7) Renderizar tabla modal - CORREGIDO
        function renderizarTablaModal(lista) {
            tablaModal.innerHTML = "";

            lista.forEach(pkg => {
                const checked = paquetesSeleccionados[pkg.id] ? "checked" : "";

                // ** IMPORTANTE: Agregar data-ruta-id para el filtrado **
                // Usamos pkg.ruta_id que ahora hemos asignado
                tablaModal.innerHTML += `
                <tr data-paquete-id="${pkg.id}" data-ruta-id="${pkg.ruta_id}">
                    <td>
                        <strong>${pkg.cliente}</strong><br>
                        <small class="text-muted">Vendedor: ${pkg.vendedor}</small><br>
                        <small class="text-info">Ruta: ${pkg.ruta_nombre} (ID: ${pkg.ruta_id})</small>
                    </td>
                    <td>${pkg.tipo_servicio == 1 ? pkg.punto_fijo_nombre
                        : pkg.tipo_servicio == 2 ? pkg.destino_personalizado
                            : pkg.lugar_recolecta_paquete}</td>
                    <td>$${parseFloat(pkg.monto).toFixed(2)}</td>
                    <td class="text-center">
                        <input type="checkbox" class="chk-paq" data-id="${pkg.id}" ${checked}>
                    </td>
                </tr>`;
            });

            actualizarBotonSeleccionarTodos();
        }

        // 8) Actualizar botón seleccionar todos
        function actualizarBotonSeleccionarTodos() {
            const checksVisibles = document.querySelectorAll("#lista-paquetes tr:not([style*='display: none']) .chk-paq");
            const allChecked = checksVisibles.length > 0 && [...checksVisibles].every(c => c.checked);

            document.getElementById("btnSelectAll").textContent = allChecked ?
                "Deseleccionar todos" : "Seleccionar todos";
        }
        // 9) Botón seleccionar/deseleccionar todos - MANTENIENDO TU LÓGICA
        document.getElementById("btnSelectAll").addEventListener("click", function () {
            const checksVisibles = document.querySelectorAll("#lista-paquetes tr:not([style*='display: none']) .chk-paq");

            if (checksVisibles.length === 0) return;

            const allChecked = [...checksVisibles].every(c => c.checked);

            // Solo cambiar el estado visual de los checkboxes visibles
            checksVisibles.forEach(c => {
                c.checked = !allChecked;
            });

            this.textContent = allChecked ? "Seleccionar todos" : "Deseleccionar todos";
        });

        // 10) Guardar asignación
        btnGuardar.addEventListener("click", function () {
            if (Object.keys(paquetesSeleccionados).length === 0) {
                showToast("Debe seleccionar al menos un paquete.", "warning");
                return;
            }

            if (!document.getElementById("motorista_id").value) {
                showToast("Debe seleccionar un motorista.", "warning");
                return;
            }

            const datos = {
                motorista_id: document.getElementById("motorista_id").value,
                ruta_id: document.getElementById("ruta_id").value,
                paquetes: Object.keys(paquetesSeleccionados),
                total: tablaTotal.innerText.replace("$", "").trim(),
                csrf_test_name: document.querySelector("input[name=csrf_test_name]").value
            };

            console.log("Datos listos para enviar:", datos);

            // Aquí tu código para enviar los datos
            // fetch('/tracking/store', { 
            //     method: "POST", 
            //     headers: {
            //         'Content-Type': 'application/json',
            //     },
            //     body: JSON.stringify(datos) 
            // });
        });

        // 11) Toast
        function showToast(message, type = "danger") {
            const id = "t" + Date.now();
            const toastHTML = `
            <div id="${id}" class="toast bg-${type}">
                <div class="toast-header text-white bg-${type}">
                    <span>${type === "warning" ? "Advertencia" : type === "success" ? "Éxito" : "Error"}</span>
                    <button class="toast-close" onclick="document.getElementById('${id}').remove()">&times;</button>
                </div>
                <div class="toast-body">${message}</div>
            </div>`;
            document.getElementById("toastContainer").insertAdjacentHTML("beforeend", toastHTML);
            const toast = document.getElementById(id);
            setTimeout(() => toast.classList.add("show"), 10);
            setTimeout(() => {
                toast.classList.remove("show");
                setTimeout(() => toast.remove(), 400);
            }, 4000);
        }
    });
</script>
<script>
    // Agregar esto en el DOMContentLoaded, después de cargar los paquetes
    document.addEventListener("click", function (e) {
        if (e.target.classList.contains('chk-paq')) {
            const id = e.target.dataset.id;
            if (e.target.checked) {
                paquetesSeleccionados[id] = paquetesTodos[id];
            } else {
                delete paquetesSeleccionados[id];
            }
        }
    });
</script>
<?= $this->endSection() ?>