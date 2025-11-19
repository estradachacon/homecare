document.addEventListener("DOMContentLoaded", () => {

    // =========================================================
    // VARIABLES GLOBALES
    // =========================================================
    let paquetesSeleccionados = {};   // id => paquete seleccionado
    let paquetesCache = {};           // id => paquete cargado de API
    let listaRuta = [];               // paquetes filtrados por ruta
    let listaEspeciales = [];         // paquetes tipo 2 y 3

    // =========================================================
    // ELEMENTOS DEL DOM
    // =========================================================
    const tablaTracking = document.getElementById("tracking-body");

    const modalRutas = $("#modalRutas");          // Bootstrap 4
    const modalEspeciales = $("#modalEspeciales");

    const selectRuta = document.getElementById("ruta_select");
    const tablaPaquetesRuta = document.getElementById("tablaPaquetesRuta");

    const selectFiltroTipo = document.getElementById("filtro_tipo");
    const tablaEspecialesBody = document.getElementById("tablaEspeciales");

    const btnAgregarRuta = document.getElementById("agregarPorRuta");
    const btnAgregarEspeciales = document.getElementById("agregarEspeciales");

    const motorista = document.getElementById("motorista");
    const fechaTracking = document.getElementById("fecha_tracking");
    const btnGuardar = document.getElementById("btnGuardar");

    // CSRF si existe
    const csrfInput = document.querySelector("input[name='csrf_test_name']");

    function coincideConFecha(pkg, fecha) {

        if (!fecha) return false;

        switch (pkg.tipo_servicio) {

            // Punto fijo
            case "1":
            case 1:
                return pkg.fecha_entrega_puntofijo === fecha;

            // Personalizado
            case "2":
            case 2:
                return pkg.fecha_entrega_personalizado === fecha;

            // Recolección → siempre disponible
            case "3":
            case 3:
                return true;

            default:
                return false;
        }
    }


    // =========================================================
    // FLATPICKR – Fecha del tracking
    // =========================================================
    flatpickr("#fecha_tracking", {
        dateFormat: "Y-m-d",
        locale: "es",
        disableMobile: true
    });


    // =========================================================
    // CARGA DE PAQUETES POR RUTA
    // =========================================================
    async function loadPaquetesPorRuta(rutaId) {
        tablaPaquetesRuta.innerHTML = `<tr><td colspan="4">Cargando...</td></tr>`;

        try {
            const resp = await fetch(`/tracking/pendientes/ruta/${rutaId}`);
            const data = await resp.json();

            listaRuta = data;
            listaRuta.forEach(p => paquetesCache[p.id] = p);

            renderPaquetesRuta();
        } catch (e) {
            tablaPaquetesRuta.innerHTML = `<tr><td colspan="4">Error al cargar</td></tr>`;
        }
    }

    function renderPaquetesRuta() {

        tablaPaquetesRuta.innerHTML = "";

        const fechaGlobal = fechaTracking.value;

        let lista = listaRuta.filter(p => coincideConFecha(p, fechaGlobal));

        if (!lista.length) {
            tablaPaquetesRuta.innerHTML = `<tr><td colspan="4">No hay paquetes para esta ruta y esta fecha</td></tr>`;
            return;
        }

        lista.forEach(p => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
            <td><input type="checkbox" class="chkRuta" data-id="${p.id}"></td>
            <td>${p.cliente}</td>
            <td>${p.punto_fijo_nombre}</td>
            <td>$${parseFloat(p.monto).toFixed(2)}</td>
        `;
            tablaPaquetesRuta.appendChild(tr);
        });
    }



    // =========================================================
    // CARGA PAQUETES ESPECIALES (tipo 2 y 3)
    // =========================================================
    async function loadEspeciales() {
        tablaEspecialesBody.innerHTML = `<tr><td colspan="4">Cargando...</td></tr>`;

        try {
            const resp = await fetch("/tracking/pendientes/todos");
            const data = await resp.json();

            listaEspeciales = data.filter(p =>
                p.tipo_servicio == 2 || p.tipo_servicio == 3
            );

            listaEspeciales.forEach(p => paquetesCache[p.id] = p);

            renderEspeciales();
        } catch (err) {
            tablaEspecialesBody.innerHTML = `<tr><td colspan="4">Error</td></tr>`;
        }
    }

    function renderEspeciales() {

        tablaEspecialesBody.innerHTML = "";

        const fechaGlobal = fechaTracking.value;
        const filtro = selectFiltroTipo.value;

        let lista = listaEspeciales.filter(p => {
            if (filtro && p.tipo_servicio != filtro) return false;
            return coincideConFecha(p, fechaGlobal);
        });

        if (!lista.length) {
            tablaEspecialesBody.innerHTML = `<tr><td colspan="4">Sin resultados para esta fecha</td></tr>`;
            return;
        }

        lista.forEach(p => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
            <td><input type="checkbox" class="chkEspecial" data-id="${p.id}"></td>
            <td>${p.cliente}</td>
            <td>${p.tipo_servicio == 2 ? p.destino_personalizado : p.lugar_recolecta_paquete}</td>
            <td>$${parseFloat(p.monto).toFixed(2)}</td>
        `;
            tablaEspecialesBody.appendChild(tr);
        });
    }

    // =========================================================
    // SELECT / DESELECT ALL – RUTAS
    // =========================================================
    document.getElementById("selectAllRuta").addEventListener("click", () => {
        const checks = document.querySelectorAll("#tablaPaquetesRuta .chkRuta");
        const allChecked = [...checks].every(c => c.checked);

        // Si todos están checked → desmarcarlos
        // Si no → marcarlos todos
        checks.forEach(c => c.checked = !allChecked);
    });

    // =========================================================
    // SELECT / DESELECT ALL – ESPECIALES
    // =========================================================
    document.getElementById("selectAllEspeciales").addEventListener("click", () => {
        const checks = document.querySelectorAll("#tablaEspeciales .chkEspecial");
        const allChecked = [...checks].every(c => c.checked);

        checks.forEach(c => c.checked = !allChecked);
    });

    selectFiltroTipo.addEventListener("change", renderEspeciales);
    fechaTracking.addEventListener("change", () => {
        // Si ya cargaste listas antes, re-renderízalas
        if (modalRutas.hasClass("show")) renderPaquetesRuta();
        if (modalEspeciales.hasClass("show")) renderEspeciales();
    });

    // =========================================================
    // AGREGAR DESDE MODAL RUTA
    // =========================================================
    btnAgregarRuta.addEventListener("click", () => {

        const fechaGlobal = fechaTracking.value;

        if (!fechaGlobal) {
            alert("Debe seleccionar la fecha del tracking antes de agregar paquetes.");
            return;
        }

        const checks = document.querySelectorAll(".chkRuta:checked");

        if (!checks.length) {
            alert("Seleccione al menos un paquete.");
            return;
        }

        checks.forEach(chk => {
            const id = chk.dataset.id;
            let pkg = paquetesCache[id];

            pkg.assigned_date = fechaGlobal; // usamos la fecha principal
            paquetesSeleccionados[id] = pkg;
        });

        renderTracking();
        modalRutas.modal("hide");
    });


    // =========================================================
    // AGREGAR DESDE MODAL ESPECIALES
    // =========================================================
    btnAgregarEspeciales.addEventListener("click", () => {
        const checks = document.querySelectorAll(".chkEspecial:checked");

        if (!checks.length) {
            alert("Seleccione al menos un paquete.");
            return;
        }

        const fechaGlobal = fechaTracking.value || null;

        checks.forEach(chk => {
            const id = chk.dataset.id;
            const pkg = paquetesCache[id];
            pkg.assigned_date = fechaGlobal;
            paquetesSeleccionados[id] = pkg;
        });

        renderTracking();
        modalEspeciales.modal("hide");
    });


    // =========================================================
    // RENDER TABLA PRINCIPAL
    // =========================================================
    function renderTracking() {
        tablaTracking.innerHTML = "";

        Object.values(paquetesSeleccionados).forEach(pkg => {

            const destino =
                pkg.tipo_servicio == 1
                    ? `Punto fijo → ${pkg.punto_fijo_nombre}`
                    : pkg.tipo_servicio == 2
                        ? `Personalizado → ${pkg.destino_personalizado}`
                        : `Recolección → ${pkg.lugar_recolecta_paquete}`;

            const tr = document.createElement("tr");

            tr.innerHTML = `
                <td>${pkg.tipo_servicio}</td>
                <td>${pkg.cliente}</td>
                <td>${destino}</td>
                <td>$${parseFloat(pkg.monto).toFixed(2)}</td>
                <td><button class="btn btn-danger btn-sm btnQuit" data-id="${pkg.id}">X</button></td>
            `;

            tablaTracking.appendChild(tr);
        });

        // Botones quitar
        tablaTracking.querySelectorAll(".btnQuit").forEach(btn => {
            btn.addEventListener("click", () => {
                delete paquetesSeleccionados[btn.dataset.id];
                renderTracking();
            });
        });
    }


    // =========================================================
    // CARGA AUTOMÁTICA DE LISTAS AL ABRIR MODALES
    // =========================================================
    selectRuta.addEventListener("change", () => {
        if (!selectRuta.value) return;
        loadPaquetesPorRuta(selectRuta.value);
    });

    modalEspeciales.on("shown.bs.modal", () => {
        if (!listaEspeciales.length) loadEspeciales();
    });


    // =========================================================
    // GUARDAR TRACKING FINAL
    // =========================================================
    btnGuardar.addEventListener("click", () => {

        if (!Object.keys(paquetesSeleccionados).length) {
            Swal.fire({
                icon: "warning",
                title: "Sin paquetes",
                text: "Debe seleccionar al menos un paquete."
            });
            return;
        }

        if (!motorista.value) {
            Swal.fire({
                icon: "warning",
                title: "Motorista faltante",
                text: "Seleccione un motorista."
            });
            return;
        }

        Swal.fire({
            title: "¿Guardar Tracking?",
            text: "Se registrará este seguimiento con los paquetes seleccionados.",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#28a745",
            cancelButtonColor: "#dc3545",
            confirmButtonText: "Sí, guardar",
            cancelButtonText: "Cancelar"
        }).then(result => {

            if (result.isConfirmed) {

                // Loader
                Swal.fire({
                    title: "Procesando...",
                    text: "Espere un momento",
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                // ====== Construcción del formulario ======
                const form = document.createElement("form");
                form.method = "POST";
                form.action = "/tracking/store";

                if (csrfInput) {
                    let c = document.createElement("input");
                    c.type = "hidden";
                    c.name = csrfInput.name;
                    c.value = csrfInput.value;
                    form.appendChild(c);
                }

                let m = document.createElement("input");
                m.type = "hidden";
                m.name = "motorista_id";
                m.value = motorista.value;
                form.appendChild(m);

                let f = document.createElement("input");
                f.type = "hidden";
                f.name = "fecha";
                f.value = fechaTracking.value || "";
                form.appendChild(f);

                Object.keys(paquetesSeleccionados).forEach(id => {
                    const i = document.createElement("input");
                    i.type = "hidden";
                    i.name = "paquetes[]";
                    i.value = id;
                    form.appendChild(i);
                });

                document.body.appendChild(form);
                form.submit();
            }

        });

    });

    // Cuando cambio la ruta manualmente
    $("#ruta_select").on("change", function () {
        if (!this.value) return;
        loadPaquetesPorRuta(this.value);
    });

    // Cuando abro el modal de rutas → cargar según la ruta seleccionada
    $("#modalRutas").on("shown.bs.modal", function () {
        const rutaId = $("#ruta_select").val();
        if (rutaId) {
            loadPaquetesPorRuta(rutaId);
        }
    });
});
