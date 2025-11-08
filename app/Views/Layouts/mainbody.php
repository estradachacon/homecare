<?php $session = session(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>FC Encomiendas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="csrf-header" content="<?= csrf_header() ?>">


    <!-- App favicon -->
    <link rel="shortcut icon" href="<?= base_url('favicon.ico') ?>">

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
    <link href="<?= base_url('backend/assets/css/styles.css') ?>" rel="stylesheet">
    <link href="<?= base_url('backend/assets/css/helper.css') ?>" rel="stylesheet">
    <link href="<?= base_url('backend/assets/css/timeline.css?v=1.0') ?>" rel="stylesheet">
    <!-- Modernizr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js"></script>

    <script type="text/javascript">
        var _date_format = "d/m/Y";
        var _report_table = "";
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
            <a class="navbar-brand text-md-center" href="/dashboard">FC
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
                <a class="nav-link dropdown-toggle d-flex align-items-center text-white" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">

                    <!-- 1. Ícono de Usuario (con margen derecho para separarlo del nombre) -->
                    <i class="fa-solid fa-user mr-2"></i>

                    <!-- 2. Nombre del Usuario (envuelto en span para control) -->
                    <span class="badge badge-primary mr-3 p-2 font-weight-large">
                        <?= esc($session->get('user_name') ?? 'N/A') ?>
                    </span>

                    <!-- 3. Imagen de Perfil -->
                    <img src="<?= base_url('upload/profile/user.jpg') ?>" alt="user-image" height="42"
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

    <!--Start layoutSidenav_nav-->
    <div id="layoutSidenav" class="container-fluid d-flex align-items-stretch">
        <?php include('sidebar.php'); ?>
        <!--End layoutSidenav_nav-->

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
    <?= $this->include('Layouts/toast') ?>
    <!-- Core Js  -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 4  -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>


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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js"></script>

    <!-- App js -->
    <script src="<?= base_url('backend/assets/js/scripts.js') ?>"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/TableDnD/0.9.1/jquery.tablednd.js"
        integrity="sha256-d3rtug+Hg1GZPB7Y/yTcRixO/wlI78+2m08tosoRn7A=" crossorigin="anonymous"></script>

    <script type="text/javascript">
        (function ($) {

            "use strict";

            const color = "#1d2744";
            const text_color = "#ffffff";
            document.documentElement.style.setProperty('--tab-active-bg', color);
            document.documentElement.style.setProperty('--tab-active-color', text_color);

        })(jQuery);
    </script>
</body>

</html>