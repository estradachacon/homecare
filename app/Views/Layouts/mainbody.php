<?php $session = session(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Core Js  -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <meta charset="utf-8" />
    <title>HomeCare</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="csrf-header" content="<?= csrf_header() ?>">
    <!-- App favicon -->
    <?php
    $favicon = setting('favicon');
    if ($favicon && file_exists(FCPATH . 'upload/settings/' . $favicon)) {
        $faviconUrl = base_url('upload/settings/' . $favicon);
    } else {
        $faviconUrl = base_url('favicon.ico');
    }
    ?>

    <link rel="shortcut icon" href="<?= esc($faviconUrl) ?>">
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
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jquery-toast-plugin/1.3.2/jquery.toast.min.css"
        rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />

    <!-- App Css -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@icon/themify-icons@1.0.6/themify-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@simonwep/pickr@1.9.1/dist/themes/classic.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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

        .loading-text {
            margin-top: 20px;
            font-family: Arial, sans-serif;
            color: #333;
            font-size: 16px;
        }

        .badge-text-lg {
            font-size: 1rem;
        }

        /* Animación campanita */

        .bell-alert {
            color: #ffc107 !important;
            animation: bellShake 1.0s ease-in-out 3;
        }

        @keyframes bellShake {

            0% {
                transform: rotate(0);
            }

            15% {
                transform: rotate(-15deg);
            }

            30% {
                transform: rotate(10deg);
            }

            45% {
                transform: rotate(-10deg);
            }

            60% {
                transform: rotate(6deg);
            }

            75% {
                transform: rotate(-4deg);
            }

            100% {
                transform: rotate(0);
            }

        }

        /* efecto de brillo */

        .bell-glow {
            text-shadow:
                0 0 5px rgba(255, 193, 7, 0.7),
                0 0 10px rgba(255, 193, 7, 0.6),
                0 0 20px rgba(255, 193, 7, 0.5);
        }
    </style>
</head>

<body class="sb-nav-fixed">
    <!--Header Nav-->
    <nav class="navbar navbar-expand navbar-dark"
        style="background-color: <?= setting('primary_color') ?? '#1d2744' ?>;">
        <div class="container-fluid d-flex justify-content-between">
            <div class="d-flex align-items-center">
                <a class="navbar-brand mb-0 h5 mr-3" href="/dashboard">
                    <?= esc(setting('company_name') ?? 'Sistema') ?>
                </a>
                <button class="btn btn-link btn-sm text-white" id="sidebarToggle">
                    <div class="lines">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </button>
            </div>
            <div class="d-flex align-items-center">
                <!-- CAMPANITA DE NOTIFICACIONES -->
                <li class="nav-item dropdown mr-3" style="list-style:none;">

                    <a class="nav-link text-white position-relative"
                        href="#"
                        id="notifDropdown"
                        data-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false">

                        <i id="notifBell" class="fa-solid fa-bell fa-lg"></i>

                        <span id="notifCount"
                            class="badge badge-danger position-absolute"
                            style="top:-5px; right:-10px; font-size:11px; display:none;">
                        </span>

                    </a>

                    <div class="dropdown-menu dropdown-menu-right shadow"
                        style="width:320px; max-height:400px; overflow-y:auto;"
                        aria-labelledby="notifDropdown">

                        <div class="dropdown-header font-weight-bold">
                            Notificaciones
                        </div>

                        <div id="notifList">

                            <div class="dropdown-item text-muted text-center">
                                Sin notificaciones
                            </div>

                        </div>

                        <div class="dropdown-divider"></div>

                        <a href="<?= base_url('notifications') ?>"
                            class="dropdown-item text-center text-primary">

                            Ver todas

                        </a>

                    </div>

                </li>
                <span class="badge badge-primary mr-3 p-3 badge-text-lg">
                    <?= esc($session->get('user_name') ?? 'N/A') ?>
                </span>
                <div class="dropdown">
                    <?php
                    $foto = session('foto');
                    if ($foto && file_exists(FCPATH . 'upload/perfiles/' . $foto)) {
                        $fotoPath = base_url('upload/perfiles/' . $foto);
                    } else {
                        $fotoPath = base_url('upload/profile/user.jpg'); // imagen por defecto
                    }
                    ?>
                    <a class="nav-link dropdown-toggle d-flex align-items-center text-white"
                        href="#"
                        id="userDropdown"
                        data-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false">
                        <img src="<?= esc($fotoPath) ?>"
                            alt="user-image"
                            height="40"
                            class="rounded-circle shadow-sm">
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="<?= base_url('perfil') ?>">
                            <i class="fa-regular fa-user"></i> Mi perfil
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="/logout"><i class="fa-solid fa-power-off"></i> Cerrar sesión</a>
                    </div>
                </div>
            </div>
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
                <?php if (session()->getFlashdata('permiso_error')): ?>
                    <div aria-live="polite" aria-atomic="true" style="position: relative; z-index: 2000;">
                        <div class="toast" style="position: absolute; top: 20px; right: 20px;" data-delay="4500">
                            <div class="toast-header bg-danger text-white">
                                <strong class="mr-auto">Permiso requerido</strong>
                                <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast">&times;</button>
                            </div>
                            <div class="toast-body">
                                <?= session()->getFlashdata('permiso_error') ?>
                            </div>
                        </div>
                    </div>
                    <script>
                        $(document).ready(function() {
                            $('.toast').toast('show');
                        });
                    </script>
                <?php endif; ?>
            </main>
        </div>
        <!--End layoutSidenav_content-->
    </div>
    <!--End layoutSidenav-->
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
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <!-- App js -->
    <script src="https://cdn.jsdelivr.net/npm/pace-js@1.2.4/pace.min.js"></script>
    <script src="<?= base_url('backend/assets/js/scripts.js') ?>"></script>
    <script type="text/javascript">
        (function($) {
            "use strict";

            const color = "#1d2744";
            const text_color = "#ffffff";
            document.documentElement.style.setProperty('--tab-active-bg', color);
            document.documentElement.style.setProperty('--tab-active-color', text_color);
        })(jQuery);
    </script>
    <script>
        let notifActivas = 0;

        function cargarNotificaciones() {

            fetch("<?= base_url('notifications/ultimas') ?>")

                .then(r => r.json())

                .then(data => {

                    let html = '';

                    if (data.length === 0) {

                        html = `<div class="dropdown-item text-muted text-center">
                            Sin notificaciones
                        </div>`;

                        $('#notifCount').hide();

                        $('#notifBell').removeClass('bell-alert bell-glow');

                        notifActivas = 0;

                    } else {

                        $('#notifCount').text(data.length).show();

                        // Animar solo si cambió el número
                        if (data.length !== notifActivas) {

                            $('#notifBell')
                                .removeClass('bell-alert')
                                .addClass('bell-alert bell-glow');

                        }

                        notifActivas = data.length;

                        data.forEach(n => {

                            html += `
                                <a class="dropdown-item notif-item" 
                                data-id="${n.id}" 
                                href="${n.link ?? '#'}">

                                    <div class="font-weight-bold">
                                        ${n.titulo}
                                    </div>

                                    <small class="text-muted">
                                        ${n.mensaje ?? ''}
                                    </small>

                                </a>
                            `;

                        });

                    }

                    $('#notifList').html(html);

                })

                .catch(err => {

                    console.log("Error notificaciones:", err);

                });

        }

        $(document).on('click', '.notif-item', function() {

            let id = $(this).data('id');

            $(this).remove(); // desaparece instantáneamente

            fetch("<?= base_url('notifications/leer/') ?>" + id)
                .then(() => cargarNotificaciones());

        });

        // cargar al iniciar
        cargarNotificaciones();

        // refrescar cada 30 segundos
        setInterval(cargarNotificaciones, 15000);
    </script>
    <?= $this->include('Layouts/toast') ?>
</body>

</html>