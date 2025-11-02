<?php $session = session(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>FC Encomiendas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.min.css" rel="stylesheet" type="text/css" />
    <!-- Dropify -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/css/dropify.min.css" />
    <!-- Sweet Alert -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.11.1/dist/sweetalert2.min.css">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jquery-toast-plugin/1.3.2/jquery.toast.min.css"
        rel="stylesheet" />
    <!-- App Css -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@icon/themify-icons@1.0.6/themify-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@simonwep/pickr@1.9.1/dist/themes/classic.min.css">
    <link rel="stylesheet" href="backend/assets/css/styles.css">
    <link rel="stylesheet" href="backend/assets/css/helper.css">
    <link rel="stylesheet" href="backend/assets/css/timeline.css?v=1.0">
    <!-- Modernizr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js"></script>

    <script type="text/javascript">
        //var _url = "https://suplidoresdiversos.tec101cloud.net";
        var _date_format = "d/m/Y";
        var _backend_direction = "ltr";
        var _currency = "$";

        var $lang_alert_title = "¿Estas seguro?";
        var $lang_alert_message = "¡Una vez eliminada, no podrá recuperar esta información!";
        var $lang_confirm_button_text = "¡Sí, eliminalo!";
        var $lang_cancel_button_text = "Cancelar";
        var $lang_no_data_found = "Datos no encontrados";
        var $lang_showing = "Mostrar";
        var $lang_to = "a";
        var $lang_of = "de";
        var $lang_entries = "Entradas";
        var $lang_showing_0_to_0_of_0_entries = "Mostrar 0 a 0 de 0 Entradas";
        var $lang_show = "Mostrar";
        var $lang_loading = "Cargando...";
        var $lang_processing = "Procesando...";
        var $lang_search = "Buscar";
        var $lang_no_matching_records_found = "No se encontraron registros coincidentes";
        var $lang_first = "Primero";
        var $lang_last = "Último";
        var $lang_next = "Siguiente";
        var $lang_previous = "Previo";
        var $lang_copy = "Copiar";
        var $lang_excel = "Excel";
        var $lang_pdf = "PDF";
        var $lang_print = "Imprimir";
        var $lang_income = "Ingreso";
        var $lang_expense = "Gastos";
        var $lang_income_vs_expense = "Ingresos vs Gastos";
        var $lang_source = "Fuente";
        var $lang_created = "Creado";
        var $lang_tax_method = "Método de impuestos";
        var $lang_inclusive = "INCLUSIVO";
        var $lang_exclusive = "EXCLUSIVO";
        var $lang_unit_price = "Precio unitario";
        var $lang_quantity = "Cantidad";
        var $lang_discount = "Descuento";
        var $lang_tax = "impuesto";
        var $lang_save = "Guardar";
        var $lang_no_tax = "Sin impuestos";
        var $lang_update_product = "Actualizar producto";
        var $lang_none = "NINGUNO";
        var $lang_copied_invoice_link = "Enlace de factura copiada";
        var $lang_copied_quotation_link = "Enlace de cotización copiado";
        var $lang_no_user_assigned = "Ningún usuario asignado";
        var $lang_select_milestone = "Seleccionar hito";
        var $lang_no_data_available = "Datos no disponibles";
        var $lang_select_tax = "Seleccione IMPUESTO";
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
        integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <style>
        /* Preloader Styles */
        #preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #ffffff;
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Spinner de carga */
        .loader {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Opcional: Texto de carga */
        .loading-text {
            margin-top: 20px;
            font-family: Arial, sans-serif;
            color: #333;
            font-size: 16px;
        }
    </style>

</head>


<body class="sb-nav-fixed">
    <!-- Main Modal -->
    <div id="main_modal" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="alert alert-danger d-none m-3"></div>
                <div class="alert alert-primary d-none m-3"></div>
                <div class="modal-body overflow-hidden"></div>

            </div>
        </div>
    </div>

    <!-- Secondary Modal -->
    <div id="secondary_modal" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title mt-0 text-dark"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="alert alert-danger d-none m-3"></div>
                <div class="alert alert-primary d-none m-3"></div>
                <div class="modal-body overflow-hidden"></div>
            </div>
        </div>
    </div>

    <!-- Preloader area start -->
    <div id="preloader">
        <div class="loader"></div>
        <div class="loading-text">Cargando...</div>
    </div>
    <!-- Preloader area end -->

    <!--Header Nav-->

    <nav class="sb-topnav navbar navbar-expand navbar-dark" style="background-color: #1d2744;">



        <div class="container-fluid">
            <a class="navbar-brand text-md-center" href="#/dashboard">FC
                Encomiendas</a>
            <button class="btn btn-link btn-sm mr-auto" id="sidebarToggle" href="#">
                <div class="lines">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </button>

            <ul class="navbar-nav ml-auto ml-md-0">
                <li class="nav-item dropdown animate-dropdown">
                    <a class="nav-link text-white"><?= esc($session->get('branch_name') ?? 'N/A') ?></a>
        </div>
        </li>
        </ul>

        <ul class="navbar-nav ml-auto ml-md-0">
            <li class="nav-item dropdown animate-dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center text-white"
                    data-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false">

                    <!-- 1. Ícono de Usuario (con margen derecho para separarlo del nombre) -->
                    <i class="fa-solid fa-user mr-2"></i>

                    <!-- 2. Nombre del Usuario (envuelto en span para control) -->
                    <span class="badge badge-primary mr-3 p-2 font-weight-large">
                        <?= esc($session->get('user_name') ?? 'N/A') ?>
                    </span>

                    <!-- 3. Imagen de Perfil -->
                    <img src="upload/profile/user.jpg" alt="user-image" height="42"
                        class="rounded-circle shadow-sm">
                </a>

                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                    <a class="dropdown-item" href="#/profile"><i class="fa-regular fa-user"></i>
                        Mi perfil</a>
                    <a class="dropdown-item" href="#/profile/edit"><i class="fa-solid fa-gear"></i>
                        Configuración de perfil</a>
                    <a class="dropdown-item" href="#/profile/change_password"><i class="fa-solid fa-key"></i>
                        Cambiar la contraseña</a>

                    <a class="dropdown-item" href="#/admin/administration/general_settings"><i
                            class="fa-solid fa-layer-group"></i> Ajustes del sistema</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="/logout"><i class="fa-solid fa-power-off"></i>
                        Cerrar sesión</a>
                </div>
            </li>
        </ul>
        </div>
    </nav>
    <!--End Header Nav-->

    <div id="layoutSidenav" class="container-fluid d-flex align-items-stretch">
        <div id="layoutSidenav_nav">
            <span class="close-mobile-nav"><i class="ti-close"></i></span>
            <nav class="sb-sidenav accordion sb-sidenav-light" id="sidenavAccordion">

                <div class="sidebar-user">

                    <div
                        style=" position: absolute; top: 0; background-color: #1d2744; width: 100%; left: 0; height: 69px; z-index: -1; border-radius: 6px 6px 0 0px;">
                    </div>
                    <a href="javascript: void(0);">

                        <img class="logo" src="img/logo.jpg" alt="logo-company" height="60" class="shadow-sm">
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
                                <a class="nav-link" href="#/cash">Lista de Cajas</a>
                                <a class="nav-link" href="#/cash">Creación de
                                    caja</a>
                                <a class="nav-link" href="#/cash_movement">Movimientos de
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
                                <a class="nav-link" href="#/admin/administration/general_settings">Configuración general</a>
                                <a class="nav-link collapsed" href="#" data-toggle="collapse"
                                    data-target="#transactions" aria-expanded="false" aria-controls="collapseLayouts">
                                    <div class="sb-nav-link-icon"><i class="fa-solid fa-store"></i></div>Sucursales
                                    <div class="sb-sidenav-collapse-arrow"><i class="fa-solid fa-angle-down"></i></div>
                                </a>
                                <div class="collapse" id="transactions" aria-labelledby="headingOne"
                                    data-parent="#navAccordionTreasury">
                                    <nav class="sb-sidenav-menu-nested nav">
                                        <a class="nav-link"
                                            href="#/income">Listado de
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
                                <a class="nav-link" href="#/roles">Roles del
                                    usuario</a>
                            </nav>
                        </div>

                        <a class="nav-link" href="#/logs">
                            <div class="sb-nav-link-icon"><i class="fas fa-book"></i></div>Bitácora
                        </a>
                    </div>
                </div>
            </nav>
        </div>
        <!--ENd layoutSidenav_nav-->

        <div id="layoutSidenav_content">
            <main>
                <div class="alert alert-success alert-dismissible" id="main_alert" role="alert">
                    <button type="button" id="close_alert" class="close">
                        <span aria-hidden="true"><i class="ti-close"></i></span>
                    </button>
                    <span class="msg"></span>
                </div>


                <div class="content-wrapper">
                    <?= $this->renderSection('content') ?>
                </div>


            </main>

        </div>
        <!--End layoutSidenav_content-->
    </div>
    <!--End layoutSidenav-->

    <!-- Core Js  -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 4  -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Print.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/print-js/1.6.0/print.min.js" integrity="sha512-TKv+3cU8+2TrA6+6QbqR1hDXAhW/YPihLeIhK4P7Z4o+F1HgK0B3bxivXPV6d0+7bt4aZCkNpqFsC0mfFtYDXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- Pace.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pace/1.2.4/pace.min.js" integrity="sha512-ihOkl4Ox8aTz6q7AD5xKGl6RZQ5Q9FvhToiLrP8FXbMtk7FbJFSVv21A9Te7EXv+T3Fg9vbvKUZejb2mEXH+WQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- Clipboard.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.11/clipboard.min.js" integrity="sha512-VzH/jE6mSwNjNlAof3wFECXZJ81E0T2IzfI5wz3rDyM8X/oU2sja3xuv4eQ8A2UoD4EEmrEPhnUB4G0gNrbKfA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <!-- Moment.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js" integrity="sha512-Q4nH9nkwd4KXjX4E3n4H1Z6XoPwaF+79iRyJ6RZz0p+Gx9A0OrMxGuNEm6ZQej+mrp1f3KxIolC0mG4HkZgVvQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/es.min.js"></script>



    <!-- Datatable js -->
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
    <!-- Dropify -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/js/dropify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.11.1/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-toast-plugin/1.3.2/jquery.toast.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@simonwep/pickr@1.9.1/dist/pickr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/i18n/es.js"></script>

    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js"></script>

    <!-- App js -->
    <script src="backend/assets/js/scripts.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/TableDnD/0.9.1/jquery.tablednd.js"
        integrity="sha256-d3rtug+Hg1GZPB7Y/yTcRixO/wlI78+2m08tosoRn7A=" crossorigin="anonymous"></script>

    <script type="text/javascript">
        (function($) {

            "use strict";

            const color = "#1d2744";
            const text_color = "#ffffff";
            document.documentElement.style.setProperty('--tab-active-bg', color);
            document.documentElement.style.setProperty('--tab-active-color', text_color);

            //Show Success Message

            //Show Single Error Message



        })(jQuery);
    </script>

    <!-- Custom JS -->
    <script src="<?php echo base_url('public/backend/assets/js/datatables/products-table.js'); ?>"></script>



</body>

</html>