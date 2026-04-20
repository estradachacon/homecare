<div id="layoutSidenav_nav">
    <span class="close-mobile-nav"><i class="fa-solid fa-close"></i></span>
    <nav class="sb-sidenav accordion sb-sidenav-light" id="sidenavAccordion">

        <div class="sidebar-user position-relative">

            <!-- Fondo superior con color del sistema -->
            <div
                style="
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 69px;
            background-color: <?= setting('primary_color') ?? '#1d2744' ?>;
            z-index: -1;
            border-radius: 6px 6px 0 0;
        ">
            </div>

            <a href="<?= base_url('dashboard') ?>" class="text-center d-block pt-3">

                <!-- LOGO EMPRESA -->
                <?php if (setting('logo')): ?>
                    <img class="logo shadow-sm"
                        src="<?= base_url('upload/settings/' . setting('logo')) ?>"
                        alt="logo-company"
                        height="60">
                <?php else: ?>
                    <h5 class="text-white font-weight-bold">
                        <?= esc(setting('company_name') ?? 'Empresa') ?>
                    </h5>
                <?php endif; ?>

                <!-- SUCURSAL -->
                <div class="nav-link text-dark mt-2 p-0">
                    <?= esc($session->get('branch_name') ?? 'N/A') ?>
                </div>

            </a>
        </div>

        <div class="sb-sidenav-menu">
            <div class="nav">
                <div class="sb-sidenav-menu-heading">NAVEGACION</div>

                <a class="nav-link" href="/dashboard">
                    <div class="sb-nav-link-icon"><i class="fa-solid fa-house"></i></div>
                    Inicio
                </a>

                <?php if (
                    tienePermiso('cargar_facturas') ||
                    tienePermiso('ver_facturas') ||
                    tienePermiso('ver_clientes')
                ): ?>

                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#ventas" aria-expanded="false"
                        aria-controls="ventas">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                        Ventas
                        <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                    </a>

                    <div class="collapse" id="ventas" aria-labelledby="headingOne" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav">
                            <?php if (tienePermiso('emitir_dte')): ?>
                                <a class="nav-link" href="/factura/crear">Emisión de DTE</a>
                            <?php endif; ?>

                            <?php if (tienePermiso('cargar_facturas')): ?>
                                <a class="nav-link" href="/facturas/carga">Cargar Facturas con JSON</a>
                            <?php endif; ?>

                            <?php if (tienePermiso('ver_facturas')): ?>
                                <a class="nav-link" href="/facturas">Ver Facturas</a>
                            <?php endif; ?>
                            <?php if (tienePermiso('ver_clientes')): ?>
                                <a class="nav-link" href="/clientes">Ver Clientes</a>
                            <?php endif; ?>
                        </nav>
                    </div>

                <?php endif; ?>

                <?php if (
                    tienePermiso('ingresar_pagos') ||
                    tienePermiso('ver_pagos')
                ): ?>
                    <a class="nav-link collapsed" href="#"
                        data-toggle="collapse"
                        data-target="#cuentasCobrar"
                        aria-expanded="false"
                        aria-controls="cuentasCobrar">

                        <div class="sb-nav-link-icon">
                            <i class="fa-solid fa-hand-holding-dollar"></i>
                        </div>

                        Cuentas por cobrar

                        <div class="sb-sidenav-collapse-arrow">
                            <i class="fa-solid fa-angle-down"></i>
                        </div>
                    </a>

                    <div class="collapse" id="cuentasCobrar" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav">
                            <?php if (tienePermiso('ingresar_pagos')): ?>
                                <a class="nav-link" href="/payments/new">
                                    Ingresar pagos
                                </a>
                            <?php endif; ?>
                            <?php if (tienePermiso('ver_pagos')): ?>
                                <a class="nav-link" href="/payments">
                                    Ver pagos
                                </a>
                            <?php endif; ?>
                            <?php if (tienePermiso('ver_quedans')): ?>
                                <a class="nav-link" href="/quedans">
                                    Control de Quedans
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                <?php endif; ?>

                <?php if (
                    tienePermiso('ver_transacciones') ||
                    tienePermiso('ver_cajas') ||
                    tienePermiso('crear_caja') ||
                    tienePermiso('ver_cuentas')
                ): ?>

                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#cash" aria-expanded="false"
                        aria-controls="cash">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-wallet"></i></div>
                        Finanzas
                        <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                    </a>

                    <div class="collapse" id="cash" aria-labelledby="headingOne" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav">

                            <!-- SUBMENÚ CAJAS -->
                            <?php if (
                                tienePermiso('ver_cajas') ||
                                tienePermiso('ver_historicos_de_caja') ||
                                tienePermiso('crear_caja')
                            ): ?>

                                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#subCajas"
                                    aria-expanded="false" aria-controls="subCajas">
                                    Cajas
                                    <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                                </a>

                                <div class="collapse" id="subCajas" data-parent="#cash">
                                    <nav class="sb-sidenav-menu-nested nav">

                                        <?php if (tienePermiso('ver_cajas')): ?>
                                            <a class="nav-link" href="/cashiers">Lista de Cajas</a>
                                        <?php endif; ?>

                                        <?php if (tienePermiso('crear_caja')): ?>
                                            <a class="nav-link" href="/cashiers/new">Creación de caja</a>
                                        <?php endif; ?>

                                        <?php if (tienePermiso('ver_historicos_de_caja')): ?>
                                            <a class="nav-link" href="/cashier/transactions">Movimientos de caja</a>
                                        <?php endif; ?>
                                    </nav>
                                </div>

                            <?php endif; ?>

                            <?php if (tienePermiso('ver_transacciones')): ?>
                                <a class="nav-link" href="/transactions">Movimientos históricos</a>
                            <?php endif; ?>

                            <?php if (tienePermiso('ver_cuentas')): ?>
                                <a class="nav-link" href="/accounts">Cuentas</a>
                            <?php endif; ?>

                        </nav>
                    </div>

                <?php endif; ?>
                <?php if (
                    tienePermiso('ver_contabilidad') ||
                    tienePermiso('ver_plan_cuentas') ||
                    tienePermiso('ver_asientos') ||
                    tienePermiso('ver_listados_contables') ||
                    tienePermiso('ver_reportes_contables') ||
                    tienePermiso('ejecutar_cierre_mes') ||
                    tienePermiso('ver_mantenimientos_contables') ||
                    tienePermiso('configurar_contabilidad')
                ): ?>

                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#contabilidad"
                        aria-expanded="false" aria-controls="contabilidad">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-book-open-reader"></i></div>
                        Contabilidad
                        <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                    </a>

                    <div class="collapse" id="contabilidad" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav">

                            <?php if (tienePermiso('ver_contabilidad')): ?>
                                <a class="nav-link" href="/contabilidad">Panel Resumen
                                </a>
                            <?php endif; ?>

                            <?php if (tienePermiso('ver_plan_cuentas')): ?>
                                <a class="nav-link" href="/contabilidad/plan-cuentas">Catalogo de Cuentas
                                </a>
                            <?php endif; ?>

                            <?php if (tienePermiso('ver_periodos_contables')): ?>
                                <a class="nav-link" href="/contabilidad/periodos">Períodos
                                </a>
                            <?php endif; ?>

                            <?php if (tienePermiso('ver_asientos')): ?>
                                <a class="nav-link" href="/contabilidad/asientos">Asientos Contables
                                </a>
                            <?php endif; ?>

                            <?php if (tienePermiso('ver_listados_contables')): ?>
                                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#contListados"
                                    aria-expanded="false" aria-controls="contListados">
                                    Listados
                                    <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                                </a>
                                <div class="collapse" id="contListados">
                                    <nav class="sb-sidenav-menu-nested nav">
                                        <a class="nav-link" href="/contabilidad/listados/relacion-cuentas">Relación de Cuentas</a>
                                        <a class="nav-link" href="/contabilidad/listados/costos">Costos</a>
                                        <a class="nav-link" href="/contabilidad/listados/gastos">Gastos</a>
                                        <a class="nav-link" href="/contabilidad/listados/comparativos">Comparativos</a>
                                        <a class="nav-link" href="/contabilidad/listados/catalogos">Catálogos</a>
                                    </nav>
                                </div>
                            <?php endif; ?>

                            <?php if (tienePermiso('ver_reportes_contables')): ?>
                                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#contReportes"
                                    aria-expanded="false" aria-controls="contReportes">
                                    Reportes
                                    <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                                </a>
                                <div class="collapse" id="contReportes">
                                    <nav class="sb-sidenav-menu-nested nav">
                                        <a class="nav-link" href="/contabilidad/reportes/diario">Libro Diario</a>
                                        <a class="nav-link" href="/contabilidad/reportes/mayor">Libro Mayor</a>
                                        <a class="nav-link" href="/contabilidad/reportes/auxiliar">Auxiliar de Cuentas</a>
                                    </nav>
                                </div>
                            <?php endif; ?>

                            <?php if (tienePermiso('ver_mantenimientos_contables')): ?>
                                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#contMant"
                                    aria-expanded="false" aria-controls="contMant">
                                    Mantenimientos
                                    <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                                </a>
                                <div class="collapse" id="contMant">
                                    <nav class="sb-sidenav-menu-nested nav">
                                        <a class="nav-link" href="/contabilidad/mantenimientos/acumulados">Saldos Actuales</a>
                                        <a class="nav-link" href="/contabilidad/mantenimientos/acumulados-historicos">Saldos Históricos</a>
                                        <a class="nav-link" href="/contabilidad/mantenimientos/transacciones-hist">Transacciones Históricas</a>
                                    </nav>
                                </div>
                            <?php endif; ?>

                            <?php if (tienePermiso('ejecutar_cierre_mes') || tienePermiso('ejecutar_cierre_anual')): ?>
                                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#contProcesos"
                                    aria-expanded="false" aria-controls="contProcesos">
                                    Procesos
                                    <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                                </a>
                                <div class="collapse" id="contProcesos">
                                    <nav class="sb-sidenav-menu-nested nav">
                                        <?php if (tienePermiso('ejecutar_cierre_mes')): ?>
                                            <a class="nav-link" href="/contabilidad/procesos/cierre-mes">Cierre de Mes</a>
                                        <?php endif; ?>
                                        <?php if (tienePermiso('ejecutar_cierre_anual')): ?>
                                            <a class="nav-link" href="/contabilidad/procesos/cierre-anual">Cierre Anual</a>
                                        <?php endif; ?>
                                    </nav>
                                </div>
                            <?php endif; ?>

                            <?php if (tienePermiso('configurar_contabilidad')): ?>
                                <a class="nav-link" href="/contabilidad/configuracion">
                                    <i class="fa-solid fa-cog mr-1"></i>Configuración
                                </a>
                            <?php endif; ?>

                        </nav>
                    </div>
                <?php endif; ?>
                <?php if (
                    tienePermiso('ver_inventario')
                ): ?>
                    <a class="nav-link collapsed" href="#"
                        data-toggle="collapse"
                        data-target="#inventario"
                        aria-expanded="false"
                        aria-controls="inventario">

                        <div class="sb-nav-link-icon">
                            <i class="fa-solid fa-boxes-packing"></i>
                        </div>

                        Inventario

                        <div class="sb-sidenav-collapse-arrow">
                            <i class="fa-solid fa-angle-down"></i>
                        </div>
                    </a>

                    <div class="collapse" id="inventario" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav">
                            <?php if (tienePermiso('ver_inventario')): ?>
                                <a class="nav-link" href="/inventory">
                                    Inventario
                                </a>
                            <?php endif; ?>
                            <?php if (tienePermiso('ver_compras')): ?>
                                <a class="nav-link" href="/purchases">
                                    Ver compras
                                </a>
                            <?php endif; ?>
                            <?php if (tienePermiso('ver_pagos_a_compras')): ?>
                                <a class="nav-link" href="/compraspagos">
                                    Ver Pagos a compras
                                </a>
                            <?php endif; ?>
                            <?php if (tienePermiso('ver_proveedores')): ?>
                                <a class="nav-link" href="/proveedores">
                                    Proveedores
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                <?php endif; ?>

                <?php if (
                    tienePermiso('ver_comisiones') ||
                    tienePermiso('configurar_comisiones') ||
                    tienePermiso('ver_reportes_comisiones')
                ): ?>
                    <a class="nav-link collapsed" href="#"
                        data-toggle="collapse"
                        data-target="#comisiones"
                        aria-expanded="false"
                        aria-controls="comisiones">

                        <div class="sb-nav-link-icon">
                            <i class="fa-solid fa-percent"></i>
                        </div>

                        Comisiones

                        <div class="sb-sidenav-collapse-arrow">
                            <i class="fa-solid fa-angle-down"></i>
                        </div>
                    </a>

                    <div class="collapse" id="comisiones" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav">

                            <?php if (tienePermiso('ver_comisiones')): ?>
                                <a class="nav-link" href="/comisiones">
                                    Ver comisiones
                                </a>
                            <?php endif; ?>

                            <?php if (tienePermiso('configurar_comisiones')): ?>
                                <a class="nav-link" href="/comisiones/configuracion">
                                    Configuración
                                </a>
                            <?php endif; ?>

                            <?php if (tienePermiso('ver_reportes_comisiones')): ?>
                                <a class="nav-link" href="/comisiones/reportes">
                                    Reportes
                                </a>
                            <?php endif; ?>

                        </nav>
                    </div>
                <?php endif; ?>

                <?php if (
                    tienePermiso('ver_consignaciones') ||
                    tienePermiso('crear_consignaciones') ||
                    tienePermiso('ver_precios_consignaciones')
                ): ?>
                    <a class="nav-link collapsed" href="#"
                        data-toggle="collapse"
                        data-target="#consignaciones"
                        aria-expanded="false"
                        aria-controls="consignaciones">
                        <div class="sb-nav-link-icon">
                            <i class="fa-solid fa-truck-ramp-box"></i>
                        </div>
                        Consignaciones
                        <div class="sb-sidenav-collapse-arrow">
                            <i class="fa-solid fa-angle-down"></i>
                        </div>
                    </a>

                    <div class="collapse" id="consignaciones" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav">
                            <?php if (tienePermiso('ver_consignaciones')): ?>
                                <a class="nav-link" href="/consignaciones">
                                    Notas de Envío
                                </a>
                            <?php endif; ?>
                            <?php if (tienePermiso('crear_consignaciones')): ?>
                                <a class="nav-link" href="/consignaciones/crear">
                                    Nueva Nota
                                </a>
                            <?php endif; ?>
                            <?php if (tienePermiso('ver_precios_consignaciones')): ?>
                                <a class="nav-link" href="/consignaciones/precios">
                                    Precios por Vendedor
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                <?php endif; ?>

                <?php if (
                    tienePermiso('ver_vendedores')
                ): ?>
                    <a class="nav-link" href="/sellers">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-address-book"></i></div>
                        Vendedores
                    </a>
                <?php endif; ?>

                <?php if (
                    tienePermiso('ver_tipo_venta')
                ): ?>
                    <a class="nav-link" href="/tipo_venta">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-clipboard-list"></i></div>
                        Tipos de venta
                    </a>
                <?php endif; ?>

                <?php if (
                    tienePermiso('ver_configuracion') ||
                    tienePermiso('ver_sucursales') ||
                    tienePermiso('ver_usuarios') ||
                    tienePermiso('ver_roles')
                ): ?>
                    <div class="sb-sidenav-menu-heading">Ajustes del sistema</div>

                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#company_settings"
                        aria-expanded="false" aria-controls="company_settings">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-cog"></i></div>
                        Ajustes del sistema
                        <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="company_settings"
                        aria-labelledby="headingOne"
                        data-parent="#sidenavAccordion">

                        <nav class="sb-sidenav-menu-nested nav">

                            <?php if (tienePermiso('ver_usuarios') || tienePermiso('ver_roles')): ?>

                                <a class="nav-link collapsed" href="#"
                                    data-toggle="collapse"
                                    data-target="#staffs"
                                    aria-expanded="false"
                                    aria-controls="staffs">
                                    Gestión de usuarios
                                    <div class="sb-sidenav-collapse-arrow">
                                        <i class="fa-solid fa-angle-down"></i>
                                    </div>
                                </a>

                                <div class="collapse" id="staffs">
                                    <nav class="sb-sidenav-menu-nested nav">

                                        <?php if (tienePermiso('ver_usuarios')): ?>
                                            <a class="nav-link" href="/users">Lista de usuarios</a>
                                        <?php endif; ?>

                                        <?php if (tienePermiso('ver_roles')): ?>
                                            <a class="nav-link" href="/roles">Roles</a>
                                        <?php endif; ?>

                                    </nav>
                                </div>

                            <?php endif; ?>

                            <?php if (tienePermiso('ver_sucursales')): ?>
                                <a class="nav-link" href="/branches">Listado de sucursales</a>
                            <?php endif; ?>
                            <?php if (tienePermiso('ajustes_multimedia')): ?>
                                <a class="nav-link" href="/content">Multimedia</a>
                            <?php endif; ?>
                            <?php if (tienePermiso('ver_configuracion')): ?>
                                <a class="nav-link" href="/settings">Información de Sistema</a>
                            <?php endif; ?>
                        </nav>
                    </div>

                <?php endif; ?>
                <?php if (tienePermiso('ver_reportes')): ?>
                    <a class="nav-link" href="/reports">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-chart-line"></i></div>
                        Reportería
                    </a>
                <?php endif; ?>
                <?php if (tienePermiso('ver_bitacora')): ?>
                    <a class="nav-link" href="/logs">
                        <div class="sb-nav-link-icon"><i class="fas fa-book"></i></div>Bitácora
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</div>

<!-- Lógica de activación de Sidebar (requiere jQuery y Bootstrap JS) -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Obtener la ruta actual, normalizada para eliminar la barra inicial si existe, 
        // y limpiar parámetros de consulta si los hay.
        let currentPath = window.location.pathname;

        // Si estás en la raíz (/), el path será solo /.
        if (currentPath === '/') {
            currentPath = '/dashboard'; // Asume que la raíz lleva al dashboard
        } else {
            // Eliminar la barra inicial para coincidencias más flexibles (e.g. /packages/new -> packages/new)
            currentPath = currentPath.startsWith('/') ? currentPath.substring(1) : currentPath;
            // Eliminar parámetros de consulta y hashes (e.g. /tracking?filter=1 -> tracking)
            currentPath = currentPath.split('?')[0].split('#')[0];
        }

        // 1. Iterar sobre todos los enlaces de navegación
        document.querySelectorAll('.nav-link').forEach(link => {
            let linkHref = link.getAttribute('href');

            if (linkHref) {
                // Eliminar la barra inicial de la URL del enlace (e.g. /packages -> packages)
                let normalizedLink = linkHref.startsWith('/') ? linkHref.substring(1) : linkHref;
                // Eliminar el hash inicial de las URLs que usan solo anclas (e.g. #/reports -> /reports)
                normalizedLink = normalizedLink.startsWith('#') ? normalizedLink.substring(1) : normalizedLink;

                // Si la URL del enlace coincide exactamente con el path actual:
                if (currentPath === normalizedLink) {
                    // 2. Resaltar el enlace
                    link.classList.add('active');

                    // 3. Expandir el menú padre si es un sub-enlace
                    // Buscar el contenedor de colapso padre (div.collapse)
                    let parentCollapse = link.closest('.collapse');

                    if (parentCollapse) {
                        // Añadir la clase 'show' para abrir el submenú
                        parentCollapse.classList.add('show');

                        // Encontrar el enlace padre que controla este colapso (a.nav-link.collapsed)
                        // Usamos el ID del colapso para encontrar el data-target coincidente
                        const targetId = '#' + parentCollapse.id;
                        const parentLink = document.querySelector(`a[data-target="${targetId}"]`);

                        if (parentLink) {
                            // Marcar el enlace padre como no colapsado y activo visualmente
                            parentLink.classList.remove('collapsed');
                            parentLink.setAttribute('aria-expanded', 'true');
                            // Opcional: podrías agregar la clase 'active' también al enlace padre si deseas resaltarlo, 
                            // pero solo 'active' en el subenlace es más común.
                        }
                    }
                }
            }
        });
    });
</script>