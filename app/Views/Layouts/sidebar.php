<?php
$session = session();
$foto = $session->get('foto');
$sidebarFotoPath = ($foto && file_exists(FCPATH . 'upload/perfiles/' . $foto))
    ? base_url('upload/perfiles/' . $foto)
    : base_url('upload/profile/user.jpg');
$primaryColor = setting('primary_color') ?? '#1d2744';
?>

<div id="layoutSidenav_nav">
    <span class="close-mobile-nav"><i class="fa-solid fa-close"></i></span>
    <nav class="sb-sidenav accordion sb-sidenav-dark sb-sidenav-custom" id="sidenavAccordion">

        <!-- ── BRAND ────────────────────────────────────────── -->
        <div class="sidebar-brand" style="background-color:<?= $primaryColor ?>;">
            <a href="<?= base_url('dashboard') ?>" class="sidebar-brand-link">
                <?php if (setting('logo')): ?>
                    <img src="<?= base_url('upload/settings/' . setting('logo')) ?>"
                         alt="logo" class="sidebar-logo">
                <?php else: ?>
                    <span class="sidebar-brand-text">
                        <?= esc(setting('company_name') ?? 'ERP') ?>
                    </span>
                <?php endif; ?>
            </a>
        </div>

        <!-- ── USER SECTION ──────────────────────────────────── -->
        <div class="sidebar-user-section">
            <img src="<?= esc($sidebarFotoPath) ?>" alt="avatar" class="sidebar-avatar">
            <div class="sidebar-user-meta">
                <div class="sidebar-user-name"><?= esc($session->get('user_name') ?? 'Usuario') ?></div>
                <div class="sidebar-branch-name">
                    <i class="fa-solid fa-location-dot" style="font-size:.6rem;"></i>
                    <?= esc($session->get('branch_name') ?? 'Sistema') ?>
                </div>
            </div>
        </div>

        <!-- ── MENU ──────────────────────────────────────────── -->
        <div class="sb-sidenav-menu">
            <div class="nav">

                <div class="sb-sidenav-menu-heading">Principal</div>

                <!-- DASHBOARD -->
                <a class="nav-link" href="/dashboard">
                    <div class="sb-nav-link-icon si-inicio"><i class="fa-solid fa-gauge-high"></i></div>
                    Dashboard
                </a>

                <!-- VENTAS -->
                <?php if (tienePermiso('cargar_facturas') || tienePermiso('ver_facturas') || tienePermiso('ver_clientes') || tienePermiso('ver_tipo_venta')): ?>
                    <a class="nav-link collapsed" href="#"
                       data-toggle="collapse" data-target="#ventas"
                       aria-expanded="false" aria-controls="ventas">
                        <div class="sb-nav-link-icon si-ventas"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                        Ventas
                        <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="ventas" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav">
                            <?php if (tienePermiso('emitir_dte')): ?>
                                <a class="nav-link" href="/factura/crear">Emisión de DTE</a>
                            <?php endif; ?>
                            <?php if (tienePermiso('cargar_facturas')): ?>
                                <a class="nav-link" href="/facturas/carga">Cargar JSON</a>
                            <?php endif; ?>
                            <?php if (tienePermiso('ver_facturas')): ?>
                                <a class="nav-link" href="/facturas">Ver Facturas</a>
                            <?php endif; ?>
                            <?php if (tienePermiso('ver_clientes')): ?>
                                <a class="nav-link" href="/clientes">Clientes</a>
                            <?php endif; ?>
                            <?php if (tienePermiso('ver_tipo_venta')): ?>
                                <a class="nav-link" href="/tipo_venta">Tipos de Venta</a>
                            <?php endif; ?>
                        </nav>
                    </div>
                <?php endif; ?>

                <!-- CUENTAS POR COBRAR -->
                <?php if (tienePermiso('ingresar_pagos') || tienePermiso('ver_pagos') || tienePermiso('ver_quedans')): ?>
                    <a class="nav-link collapsed" href="#"
                       data-toggle="collapse" data-target="#cuentasCobrar"
                       aria-expanded="false" aria-controls="cuentasCobrar">
                        <div class="sb-nav-link-icon si-cxc"><i class="fa-solid fa-hand-holding-dollar"></i></div>
                        Cuentas por Cobrar
                        <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="cuentasCobrar" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav">
                            <?php if (tienePermiso('ingresar_pagos')): ?>
                                <a class="nav-link" href="/payments/new">Ingresar Pagos</a>
                            <?php endif; ?>
                            <?php if (tienePermiso('ver_pagos')): ?>
                                <a class="nav-link" href="/payments">Ver Pagos</a>
                            <?php endif; ?>
                            <?php if (tienePermiso('ver_quedans')): ?>
                                <a class="nav-link" href="/quedans">Control de Quedans</a>
                            <?php endif; ?>
                        </nav>
                    </div>
                <?php endif; ?>

                <!-- INVENTARIO -->
                <?php if (tienePermiso('ver_inventario')): ?>
                    <a class="nav-link collapsed" href="#"
                       data-toggle="collapse" data-target="#inventario"
                       aria-expanded="false" aria-controls="inventario">
                        <div class="sb-nav-link-icon si-inventario"><i class="fa-solid fa-boxes-stacked"></i></div>
                        Inventario
                        <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="inventario" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav">
                            <?php if (tienePermiso('ver_inventario')): ?>
                                <a class="nav-link" href="/inventory">Inventario</a>
                            <?php endif; ?>
                            <?php if (tienePermiso('ver_compras')): ?>
                                <a class="nav-link" href="/purchases">Ver Compras</a>
                            <?php endif; ?>
                            <?php if (tienePermiso('ver_pagos_a_compras')): ?>
                                <a class="nav-link" href="/compraspagos">Pagos a Compras</a>
                            <?php endif; ?>
                            <?php if (tienePermiso('ver_proveedores')): ?>
                                <a class="nav-link" href="/proveedores">Proveedores</a>
                            <?php endif; ?>
                        </nav>
                    </div>
                <?php endif; ?>

                <!-- FINANZAS -->
                <?php if (tienePermiso('ver_transacciones') || tienePermiso('ver_cajas') || tienePermiso('crear_caja') || tienePermiso('ver_cuentas')): ?>
                    <a class="nav-link collapsed" href="#"
                       data-toggle="collapse" data-target="#cash"
                       aria-expanded="false" aria-controls="cash">
                        <div class="sb-nav-link-icon si-finanzas"><i class="fa-solid fa-wallet"></i></div>
                        Finanzas
                        <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="cash" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav">
                            <?php if (tienePermiso('ver_cajas') || tienePermiso('ver_historicos_de_caja') || tienePermiso('crear_caja')): ?>
                                <a class="nav-link collapsed" href="#"
                                   data-toggle="collapse" data-target="#subCajas"
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
                                            <a class="nav-link" href="/cashiers/new">Nueva Caja</a>
                                        <?php endif; ?>
                                        <?php if (tienePermiso('ver_historicos_de_caja')): ?>
                                            <a class="nav-link" href="/cashier/transactions">Movimientos</a>
                                        <?php endif; ?>
                                    </nav>
                                </div>
                            <?php endif; ?>
                            <?php if (tienePermiso('ver_transacciones')): ?>
                                <a class="nav-link" href="/transactions">Movimientos Históricos</a>
                            <?php endif; ?>
                            <?php if (tienePermiso('ver_cuentas')): ?>
                                <a class="nav-link" href="/accounts">Cuentas</a>
                            <?php endif; ?>
                        </nav>
                    </div>
                <?php endif; ?>

                <!-- CONTABILIDAD -->
                <?php if (
                    tienePermiso('ver_contabilidad') || tienePermiso('ver_plan_cuentas') ||
                    tienePermiso('ver_asientos') || tienePermiso('ver_listados_contables') ||
                    tienePermiso('ver_reportes_contables') || tienePermiso('ejecutar_cierre_mes') ||
                    tienePermiso('ver_mantenimientos_contables') || tienePermiso('configurar_contabilidad')
                ): ?>
                    <a class="nav-link collapsed" href="#"
                       data-toggle="collapse" data-target="#contabilidad"
                       aria-expanded="false" aria-controls="contabilidad">
                        <div class="sb-nav-link-icon si-contab"><i class="fa-solid fa-book-open-reader"></i></div>
                        Contabilidad
                        <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="contabilidad" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav">
                            <?php if (tienePermiso('ver_contabilidad')): ?>
                                <a class="nav-link" href="/contabilidad">Panel Resumen</a>
                            <?php endif; ?>
                            <?php if (tienePermiso('ver_plan_cuentas')): ?>
                                <a class="nav-link" href="/contabilidad/plan-cuentas">Catálogo de Cuentas</a>
                            <?php endif; ?>
                            <?php if (tienePermiso('ver_periodos_contables')): ?>
                                <a class="nav-link" href="/contabilidad/periodos">Períodos</a>
                            <?php endif; ?>
                            <?php if (tienePermiso('ver_asientos')): ?>
                                <a class="nav-link" href="/contabilidad/asientos">Asientos Contables</a>
                            <?php endif; ?>
                            <?php if (tienePermiso('ver_remesas_contables')): ?>
                                <a class="nav-link" href="/contabilidad/remesas">Remesas Contables</a>
                            <?php endif; ?>
                            <?php if (tienePermiso('ver_listados_contables')): ?>
                                <a class="nav-link collapsed" href="#"
                                   data-toggle="collapse" data-target="#contListados"
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
                                <a class="nav-link collapsed" href="#"
                                   data-toggle="collapse" data-target="#contReportes"
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
                                <a class="nav-link collapsed" href="#"
                                   data-toggle="collapse" data-target="#contMant"
                                   aria-expanded="false" aria-controls="contMant">
                                    Mantenimientos
                                    <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                                </a>
                                <div class="collapse" id="contMant">
                                    <nav class="sb-sidenav-menu-nested nav">
                                        <a class="nav-link" href="/contabilidad/mantenimientos/tipos-partida">Tipos de Partida</a>
                                        <a class="nav-link" href="/contabilidad/mantenimientos/acumulados">Saldos Actuales</a>
                                        <a class="nav-link" href="/contabilidad/mantenimientos/acumulados-historicos">Saldos Históricos</a>
                                        <a class="nav-link" href="/contabilidad/mantenimientos/transacciones-hist">Transacciones Históricas</a>
                                    </nav>
                                </div>
                            <?php endif; ?>
                            <?php if (tienePermiso('ejecutar_cierre_mes') || tienePermiso('ejecutar_cierre_anual')): ?>
                                <a class="nav-link collapsed" href="#"
                                   data-toggle="collapse" data-target="#contProcesos"
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
                                <a class="nav-link" href="/contabilidad/configuracion">Configuración</a>
                            <?php endif; ?>
                        </nav>
                    </div>
                <?php endif; ?>

                <!-- COMISIONES -->
                <?php if (tienePermiso('ver_comisiones') || tienePermiso('configurar_comisiones') || tienePermiso('ver_reportes_comisiones')): ?>
                    <a class="nav-link collapsed" href="#"
                       data-toggle="collapse" data-target="#comisiones"
                       aria-expanded="false" aria-controls="comisiones">
                        <div class="sb-nav-link-icon si-comis"><i class="fa-solid fa-percent"></i></div>
                        Comisiones
                        <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="comisiones" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav">
                            <?php if (tienePermiso('ver_comisiones')): ?>
                                <a class="nav-link" href="/comisiones">Ver Comisiones</a>
                            <?php endif; ?>
                            <?php if (tienePermiso('configurar_comisiones')): ?>
                                <a class="nav-link" href="/comisiones/configuracion">Configuración</a>
                            <?php endif; ?>
                            <?php if (tienePermiso('ver_reportes_comisiones')): ?>
                                <a class="nav-link" href="/comisiones/reportes">Reportes</a>
                            <?php endif; ?>
                        </nav>
                    </div>
                <?php endif; ?>

                <!-- MÓDULO DE VENDEDORES -->
                <?php if (
                    tienePermiso('ver_pedidos') || tienePermiso('crear_pedidos') ||
                    tienePermiso('ver_consignaciones') || tienePermiso('crear_consignaciones') ||
                    tienePermiso('ver_precios_consignaciones') ||
                    tienePermiso('ver_recuperos') || tienePermiso('crear_recupero')
                ): ?>
                    <a class="nav-link collapsed" href="#"
                       data-toggle="collapse" data-target="#modVendedores"
                       aria-expanded="false" aria-controls="modVendedores">
                        <div class="sb-nav-link-icon si-vendedores"><i class="fa-solid fa-user-tie"></i></div>
                        Módulo de Vendedores
                        <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="modVendedores" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav">

                            <!-- Notas de Pedido -->
                            <?php if (tienePermiso('ver_pedidos') || tienePermiso('crear_pedidos')): ?>
                                <a class="nav-link collapsed" href="#"
                                   data-toggle="collapse" data-target="#subPedidos"
                                   aria-expanded="false" aria-controls="subPedidos">
                                    <i class="fa-solid fa-cart-shopping mr-1" style="font-size:.75rem;"></i> Notas de Pedido
                                    <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                                </a>
                                <div class="collapse" id="subPedidos" data-parent="#modVendedores">
                                    <nav class="sb-sidenav-menu-nested nav">
                                        <?php if (tienePermiso('ver_pedidos')): ?>
                                            <a class="nav-link" href="/pedidos">Ver Pedidos</a>
                                        <?php endif; ?>
                                        <?php if (tienePermiso('crear_pedidos')): ?>
                                            <a class="nav-link" href="/pedidos/crear">Nueva Nota</a>
                                        <?php endif; ?>
                                    </nav>
                                </div>
                            <?php endif; ?>

                            <!-- Notas de Envío -->
                            <?php if (tienePermiso('ver_consignaciones') || tienePermiso('crear_consignaciones') || tienePermiso('ver_precios_consignaciones') || tienePermiso('crear_consignacion_emergencia')): ?>
                                <a class="nav-link collapsed" href="#"
                                   data-toggle="collapse" data-target="#subConsig"
                                   aria-expanded="false" aria-controls="subConsig">
                                    <i class="fa-solid fa-truck-ramp-box mr-1" style="font-size:.75rem;"></i> Notas de Envío
                                    <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                                </a>
                                <div class="collapse" id="subConsig" data-parent="#modVendedores">
                                    <nav class="sb-sidenav-menu-nested nav">
                                        <?php if (tienePermiso('ver_consignaciones')): ?>
                                            <a class="nav-link" href="/consignaciones">Ver Notas de Envío</a>
                                        <?php endif; ?>
                                        <?php if (tienePermiso('crear_consignaciones')): ?>
                                            <a class="nav-link" href="/consignaciones/crear">Nueva Nota de Envío</a>
                                        <?php endif; ?>
                                        <?php if (tienePermiso('crear_consignacion_emergencia')): ?>
                                            <a class="nav-link" href="/consignaciones/crear-emergencia">
                                                <i class="fa-solid fa-bolt text-warning mr-1" style="font-size:.65rem;"></i>NE Stock Emergencia
                                            </a>
                                        <?php endif; ?>
                                        <?php if (tienePermiso('ver_precios_consignaciones')): ?>
                                            <a class="nav-link" href="/consignaciones/precios">Precios por Vendedor</a>
                                        <?php endif; ?>
                                        <?php if (tienePermiso('ver_consignaciones')): ?>
                                            <a class="nav-link" href="/pacientes">Pacientes</a>
                                            <a class="nav-link" href="/doctores">Doctores</a>
                                            <a class="nav-link" href="/tipo-notas">Tipo de Nota</a>
                                            <a class="nav-link" href="/consignaciones/reportes">Reportes</a>
                                        <?php endif; ?>
                                    </nav>
                                </div>
                            <?php endif; ?>

                            <!-- Recuperos -->
                            <?php if (tienePermiso('ver_recuperos') || tienePermiso('crear_recupero')): ?>
                                <a class="nav-link collapsed" href="#"
                                   data-toggle="collapse" data-target="#subRecuperos"
                                   aria-expanded="false" aria-controls="subRecuperos">
                                    <i class="fa-solid fa-hand-holding-dollar mr-1" style="font-size:.75rem;"></i> Recuperos
                                    <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                                </a>
                                <div class="collapse" id="subRecuperos" data-parent="#modVendedores">
                                    <nav class="sb-sidenav-menu-nested nav">
                                        <?php if (tienePermiso('ver_recuperos')): ?>
                                            <a class="nav-link" href="/recuperos">Ver Recuperos</a>
                                        <?php endif; ?>
                                        <?php if (tienePermiso('crear_recupero')): ?>
                                            <a class="nav-link" href="/recuperos/nuevo">Nuevo Recupero</a>
                                        <?php endif; ?>
                                    </nav>
                                </div>
                            <?php endif; ?>

                        </nav>
                    </div>
                <?php endif; ?>


                <!-- ADMINISTRACIÓN -->
                <?php if (
                    tienePermiso('ver_configuracion') || tienePermiso('ver_sucursales') ||
                    tienePermiso('ver_usuarios') || tienePermiso('ver_roles') ||
                    tienePermiso('ver_reportes') || tienePermiso('ver_bitacora')
                ): ?>
                    <div class="sb-sidenav-menu-heading">Administración</div>

                    <?php if (tienePermiso('ver_configuracion') || tienePermiso('ver_sucursales') || tienePermiso('ver_usuarios') || tienePermiso('ver_roles')): ?>
                        <a class="nav-link collapsed" href="#"
                           data-toggle="collapse" data-target="#company_settings"
                           aria-expanded="false" aria-controls="company_settings">
                            <div class="sb-nav-link-icon si-admin"><i class="fa-solid fa-gear"></i></div>
                            Ajustes del Sistema
                            <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="company_settings" data-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <?php if (tienePermiso('ver_usuarios') || tienePermiso('ver_roles') || tienePermiso('ver_vendedores')): ?>
                                    <a class="nav-link collapsed" href="#"
                                       data-toggle="collapse" data-target="#staffs"
                                       aria-expanded="false" aria-controls="staffs">
                                        Gestión de Usuarios
                                        <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                                    </a>
                                    <div class="collapse" id="staffs">
                                        <nav class="sb-sidenav-menu-nested nav">
                                            <?php if (tienePermiso('ver_usuarios')): ?>
                                                <a class="nav-link" href="/users">Lista de Usuarios</a>
                                            <?php endif; ?>
                                            <?php if (tienePermiso('ver_roles')): ?>
                                                <a class="nav-link" href="/roles">Roles y Permisos</a>
                                            <?php endif; ?>
                                            <?php if (tienePermiso('ver_vendedores')): ?>
                                                <a class="nav-link" href="/sellers">Vendedores</a>
                                            <?php endif; ?>
                                        </nav>
                                    </div>
                                <?php endif; ?>
                                <?php if (tienePermiso('ver_sucursales')): ?>
                                    <a class="nav-link" href="/branches">Sucursales</a>
                                <?php endif; ?>
                                <?php if (tienePermiso('ajustes_multimedia')): ?>
                                    <a class="nav-link" href="/content">Multimedia</a>
                                <?php endif; ?>
                                <?php if (tienePermiso('ver_configuracion')): ?>
                                    <a class="nav-link" href="/settings">Información del Sistema</a>
                                <?php endif; ?>
                            </nav>
                        </div>
                    <?php endif; ?>

                    <?php if (tienePermiso('ver_reportes')): ?>
                        <a class="nav-link" href="/reports">
                            <div class="sb-nav-link-icon si-reports"><i class="fa-solid fa-chart-line"></i></div>
                            Reportería
                        </a>
                    <?php endif; ?>

                    <?php if (tienePermiso('ver_bitacora')): ?>
                        <a class="nav-link" href="/logs">
                            <div class="sb-nav-link-icon si-log"><i class="fa-solid fa-book"></i></div>
                            Bitácora
                        </a>
                    <?php endif; ?>

                <?php endif; ?>

            </div>
        </div>
    </nav>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let currentPath = window.location.pathname;
    if (currentPath === '/') {
        currentPath = '/dashboard';
    } else {
        currentPath = currentPath.startsWith('/') ? currentPath.substring(1) : currentPath;
        currentPath = currentPath.split('?')[0].split('#')[0];
    }

    document.querySelectorAll('.nav-link').forEach(link => {
        let href = link.getAttribute('href');
        if (!href) return;
        let normalized = href.startsWith('/') ? href.substring(1) : href;
        normalized = normalized.startsWith('#') ? normalized.substring(1) : normalized;

        if (currentPath === normalized) {
            link.classList.add('active');
            let parentCollapse = link.closest('.collapse');
            if (parentCollapse) {
                parentCollapse.classList.add('show');
                const parentLink = document.querySelector(`a[data-target="#${parentCollapse.id}"]`);
                if (parentLink) {
                    parentLink.classList.remove('collapsed');
                    parentLink.setAttribute('aria-expanded', 'true');
                }
            }
        }
    });
});
</script>
