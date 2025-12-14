<div id="layoutSidenav_nav">
    <span class="close-mobile-nav"><i class="fa-solid fa-close"></i></span>
    <nav class="sb-sidenav accordion sb-sidenav-light" id="sidenavAccordion">

        <div class="sidebar-user">

            <div
                style=" position: absolute; top: 0; background-color: #1d2744; width: 100%; left: 0; height: 69px; z-index: -1; border-radius: 6px 6px 0 0px;">
            </div>
            <a href="javascript: void(0);">

                <img class="logo" src="<?= base_url('img/logo.jpg') ?>" alt="logo-company" height="60"
                    class="shadow-sm">
                <a class="nav-link text-dark"><?= esc($session->get('branch_name') ?? 'N/A') ?></a>
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
                    tienePermiso('crear_paquetes') ||
                    tienePermiso('ver_paquetes') ||
                    tienePermiso('ver_tracking')
                ): ?>

                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#sales" aria-expanded="false"
                        aria-controls="sales">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-box-open"></i></div>
                        Paquetería
                        <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                    </a>

                    <div class="collapse" id="sales" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav">

                            <?php if (tienePermiso('crear_paquetes')): ?>
                                <a class="nav-link" href="/packages/new">Registrar paquete</a>
                            <?php endif; ?>

                            <?php if (tienePermiso('ver_paquetes')): ?>
                                <a class="nav-link" href="/packages">Lista de paquetes</a>
                            <?php endif; ?>

                            <?php if (tienePermiso('ver_tracking')): ?>
                                <a class="nav-link" href="/tracking">Seguimiento de paquetes</a>
                            <?php endif; ?>

                        </nav>
                    </div>
                <?php endif; ?>

                <?php if (
                    tienePermiso('remunerar_paquetes') ||
                    tienePermiso('devolver_paquetes') ||
                    tienePermiso('ver_tracking')
                ): ?>
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#treasury"
                        aria-expanded="false" aria-controls="treasury">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-wallet"></i></div>
                        Remuneraciones
                        <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="treasury" aria-labelledby="headingOne" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav" id="navAccordionTreasury">
                            <?php if (tienePermiso('remunerar_paquetes')): ?>
                                <a class="nav-link" href="/remu/create">Remunerar paquetes</a>
                            <?php endif; ?>

                            <?php if (tienePermiso('devolver_paquetes')): ?>
                                <a class="nav-link" href="packages/return">Devolución de paquetes</a>
                            <?php endif; ?>

                            <?php if (tienePermiso('ver_caja_actual')): ?>
                                <a class="nav-link" href="cashiers/now">Movimientos de caja actual</a>
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
                    tienePermiso('ver_puntosfijos') ||
                    tienePermiso('ver_rutas')
                ): ?>
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#settledpoint"
                        aria-expanded="false" aria-controls="settledpoint">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-map-marker-alt"></i></div>
                        Puntos fijos y Rutas
                        <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="settledpoint" aria-labelledby="headingOne" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav">
                            <?php if (tienePermiso('ver_puntosfijos')): ?>
                                <a class="nav-link" href="/settledpoint">Puntos fijos</a>
                            <?php endif; ?>
                            <?php if (tienePermiso('ver_rutas')): ?>
                                <a class="nav-link" href="/routes">Rutas</a>
                            <?php endif; ?>
                        </nav>
                    </div>
                <?php endif; ?>

                <?php if (
                    tienePermiso('invalidar_pago') ||
                    tienePermiso('invalidar_flete')
                ): ?>
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#accounts"
                        aria-expanded="false" aria-controls="accounts">
                        <div class="sb-nav-link-icon"><i class="fa-solid fa-file"></i></div>
                        Solicitudes
                        <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="accounts" aria-labelledby="headingOne" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav">
                            <?php if (tienePermiso('invalidar_pago')): ?>
                                <a class="nav-link" href="invalidatepayments">Reversión de pagos</a>
                            <?php endif; ?>
                            <?php if (tienePermiso('invalidar_flete')): ?>
                                <a class="nav-link" href="invalidatefreight">Anular flete</a>
                            <?php endif; ?>
                        </nav>
                    </div>
                <?php endif; ?>

                <?php if (
                    tienePermiso('ver_configuracion') ||
                    tienePermiso('ver_sucursales') ||
                    tienePermiso('ver_almacenamiento') ||
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

                            <?php if (tienePermiso('ver_configuracion')): ?>
                                <a class="nav-link" href="/settings">Información de Sistema</a>
                            <?php endif; ?>

                            <?php if (tienePermiso('ver_sucursales')): ?>
                                <a class="nav-link" href="/branches">Listado de sucursales</a>
                            <?php endif; ?>

                            <?php if (tienePermiso('ver_almacenamiento')): ?>
                                <a class="nav-link" href="#/admin/administration/general_settings">
                                    Ver almacenamiento
                                </a>
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
                <a class="nav-link" href="/logs">
                    <div class="sb-nav-link-icon"><i class="fas fa-book"></i></div>Bitácora
                </a>
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