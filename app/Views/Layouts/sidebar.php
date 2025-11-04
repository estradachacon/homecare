<div id="layoutSidenav_nav">
            <span class="close-mobile-nav"><i class="ti-close"></i></span>
            <nav class="sb-sidenav accordion sb-sidenav-light" id="sidenavAccordion">

                <div class="sidebar-user">

                    <div
                        style=" position: absolute; top: 0; background-color: #1d2744; width: 100%; left: 0; height: 69px; z-index: -1; border-radius: 6px 6px 0 0px;">
                    </div>
                    <a href="javascript: void(0);">

                        <img class="logo" src="<?= base_url('img/logo.jpg') ?>" alt="logo-company" height="60" class="shadow-sm">
                        <span class="sidebar-user-name">Casa Matriz</span>
                    </a>
                </div>

                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading">NAVEGACION</div>

                        <a class="nav-link" href="#">
                            <div class="sb-nav-link-icon"><i class="fa-solid fa-house"></i></div>
                            Menú General
                        </a>

                        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#cash"
                            aria-expanded="false" aria-controls="collapseLayouts">
                            <div class="sb-nav-link-icon"><i class="fa-solid fa-cash-register"></i></div>
                            Cajas
                            <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="cash" aria-labelledby="headingOne" data-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="/cashiers">Lista de Cajas</a>
                                <a class="nav-link" href="/cashiers/new">Creación de
                                    caja</a>
                                <a class="nav-link" href="#/cash_movement/create?cashmov_type=Closing">Corte
                                    de caja</a>
                            </nav>
                        </div>

                        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#sales"
                            aria-expanded="false" aria-controls="collapseLayouts">
                            <div class="sb-nav-link-icon"><i class="fa-solid fa-box-open"></i></div>
                            Paquetería
                            <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="sales" aria-labelledby="headingOne" data-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="#/invoices/create">Registrar paquete</a>
                                <a class="nav-link" href="#/invoices">Lista de
                                    paquetes</a>

                                <a class="nav-link" href="#/sales_returns">Devolución de
                                    No retirados</a>
                            </nav>
                        </div>

                        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#treasury"
                            aria-expanded="false" aria-controls="collapseLayouts">
                            <div class="sb-nav-link-icon"><i class="fa-solid fa-wallet"></i></div>
                            Remuneraciones
                            <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="treasury" aria-labelledby="headingOne"
                            data-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav" id="navAccordionTreasury">
                                <a class="nav-link" href="#/invoices/create">Remunerar paquetes</a>
                                <a class="nav-link" href="#/invoices/create">Movimientos de caja actual</a>
                            </nav>
                        </div>

                        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#purchase_orders"
                            aria-expanded="false" aria-controls="collapseLayouts">
                            <div class="sb-nav-link-icon"><i class="fa-solid fa-bag-shopping"></i></div>
                            Otros gastos
                            <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="purchase_orders" aria-labelledby="headingOne"
                            data-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="#/purchase_orders">Lista de gastos</a>
                                <a class="nav-link" href="#/purchase_returns">Tipos de gastos</a>
                            </nav>
                        </div>

                        <a class="nav-link" href="#">
                            <div class="sb-nav-link-icon"><i class="fa-solid fa-address-book"></i></div>
                            Vendedores
                        </a>

                        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#accounts"
                            aria-expanded="false" aria-controls="collapseLayouts">
                            <div class="sb-nav-link-icon"><i class="fa-solid fa-file"></i></div>
                            Solicitudes
                            <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="accounts" aria-labelledby="headingOne"
                            data-parent="#navAccordionTreasury">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="#/accounts">Reversión
                                    de pagos</a>
                                <a class="nav-link" href="#/accounts/create">Anular flete</a>
                            </nav>
                        </div>

                        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#reports"
                            aria-expanded="false" aria-controls="collapseLayouts">
                            <div class="sb-nav-link-icon"><i class="fa-solid fa-file-export"></i></div>
                            Informes
                            <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="reports" aria-labelledby="headingOne" data-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="#/reports/account_statement">Informe de Remuneraciones</a>
                                <a class="nav-link" href="#/reports/account_statement">Informe de Cobros de envio</a>
                                <a class="nav-link" href="#/reports/account_statement">Informe de No retirados</a>
                                <a class="nav-link" href="#/reports/account_statement">Informe de gastos</a>
                                <a class="nav-link" href="#/reports/account_statement">Balance por rango de fechas</a>
                            </nav>
                        </div>

                        <div class="sb-sidenav-menu-heading">Ajustes del sistema</div>

                        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#company_settings"
                            aria-expanded="false" aria-controls="collapseLayouts">
                            <div class="sb-nav-link-icon"><i class="fa-solid fa-cog"></i></div>
                            Ajustes del sistema
                            <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="company_settings" aria-labelledby="headingOne"
                            data-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="/settings">Configuración general</a>
                                <a class="nav-link collapsed" href="#" data-toggle="collapse"
                                    data-target="#transactions" aria-expanded="false" aria-controls="collapseLayouts">
                                    <div class="sb-nav-link-icon"><i class="fa-solid fa-store"></i></div>Sucursales
                                    <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                                </a>
                                <div class="collapse" id="transactions" aria-labelledby="headingOne"
                                    data-parent="#navAccordionTreasury">
                                    <nav class="sb-sidenav-menu-nested nav">
                                        <a class="nav-link"
                                            href="/branches">Listado de
                                            sucursales</a>
                                        <a class="nav-link"
                                            href="#/expense">Gastos</a>
                                        <a class="nav-link"
                                            href="#/transfer/create">Transferir</a>
                                        <a class="nav-link"
                                            href="#/income/calendar">Calendario
                                            de ingresos</a>
                                        <a class="nav-link"
                                            href="#/expense/calendar">Calendario
                                            de gastos</a>
                                    </nav>
                                </div>
                                <a class="nav-link" href="#/admin/administration/general_settings">Ver
                                    almacenamiento</a>
                            </nav>
                        </div>

                        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#staffs"
                            aria-expanded="false" aria-controls="collapseLayouts">
                            <div class="sb-nav-link-icon"><i class="fa-solid fa-users"></i></div>
                            Gestión de usuarios
                            <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="staffs" aria-labelledby="headingOne" data-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="users">Lista de usuarios</a>
                            </nav>
                        </div>

                        <a class="nav-link" href="/logs">
                            <div class="sb-nav-link-icon"><i class="fas fa-book"></i></div>Bitácora
                        </a>
                    </div>
                </div>
            </nav>
        </div>