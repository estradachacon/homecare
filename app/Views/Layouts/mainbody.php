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

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap4.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

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
        /* ═══════════════════════════════════════════════════════════
           NAVBAR
        ═══════════════════════════════════════════════════════════ */
        /* Navbar fijo en la parte superior, ancho completo */
        .sb-topnav {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            width: 100% !important;
            height: 58px !important;
            box-shadow: 0 2px 14px rgba(0,0,0,.32) !important;
            z-index: 1040 !important;
        }
        /* Compensar el navbar fijo para que no tape el contenido */
        body {
            padding-top: 58px !important;
        }
        .navbar-brand-erp {
            font-size: .92rem;
            font-weight: 700;
            letter-spacing: .03em;
            color: #fff !important;
            text-decoration: none;
            white-space: nowrap;
        }
        .navbar-divider {
            width: 1px;
            height: 26px;
            background: rgba(255,255,255,.2);
            flex-shrink: 0;
        }
        .nav-module-label {
            font-size: .75rem;
            font-weight: 500;
            color: rgba(255,255,255,.55);
            white-space: nowrap;
        }
        .nav-clock-wrap {
            line-height: 1.25;
            text-align: right;
        }
        .nav-clock-time {
            display: block;
            font-size: .88rem;
            font-weight: 700;
            color: rgba(255,255,255,.92);
            letter-spacing: .03em;
        }
        .nav-clock-date {
            display: block;
            font-size: .67rem;
            color: rgba(255,255,255,.48);
            text-transform: capitalize;
        }
        .nav-user-name {
            font-size: .8rem;
            font-weight: 600;
            color: #fff;
            max-width: 130px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.2;
        }
        .nav-avatar {
            border: 2px solid rgba(255,255,255,.3);
            object-fit: cover;
        }
        .user-dropdown-header {
            background: #f4f6f9;
            border-radius: 10px 10px 0 0;
            padding: 10px 14px;
            border-bottom: 1px solid #e9ecef;
        }
        .user-dropdown-header .ud-name {
            font-size: .85rem;
            font-weight: 700;
            color: #1a2b3c;
        }
        .user-dropdown-header .ud-branch {
            font-size: .73rem;
            color: #6c757d;
        }
        .dropdown-menu.user-menu {
            border: 0;
            border-radius: 10px;
            box-shadow: 0 8px 30px rgba(0,0,0,.14);
            min-width: 210px;
            padding: 0;
            overflow: hidden;
        }
        .user-menu .dropdown-item {
            padding: 9px 14px;
            font-size: .83rem;
        }
        .user-menu .dropdown-item:hover { background: #f0f4f8; }
        .user-menu .dropdown-item.text-danger:hover { background: #fff5f5; }

        /* ═══════════════════════════════════════════════════════════
           LAYOUT — sidebar fijo al borde izquierdo, sin gaps
        ═══════════════════════════════════════════════════════════ */
        #layoutSidenav {
            padding: 0 !important;
            overflow: visible !important;
            min-height: calc(100vh - 58px);
        }
        /* Sidebar: position fixed, pegado esquina superior-izquierda */
        #layoutSidenav_nav,
        #layoutSidenav #layoutSidenav_nav,
        .sb-nav-fixed #layoutSidenav #layoutSidenav_nav {
            position: fixed !important;
            top: 58px !important;
            left: 0 !important;
            width: 250px !important;
            height: calc(100vh - 58px) !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1038 !important;
            transition: transform .18s ease, margin-left .18s ease;
        }
        /* Contenido: desplazado 250px para no quedar detrás del sidebar fijo */
        #layoutSidenav #layoutSidenav_content {
            margin-left: 250px !important;
            margin-top: 0 !important;
            padding-top: 0;
            min-height: calc(100vh - 58px);
        }
        /* Toggle: ocultar sidebar deslizándolo fuera de pantalla */
        .sb-sidenav-toggled #layoutSidenav_nav,
        .sb-sidenav-toggled #layoutSidenav #layoutSidenav_nav {
            transform: translateX(-250px) !important;
        }
        .sb-sidenav-toggled #layoutSidenav #layoutSidenav_content {
            margin-left: 0 !important;
        }
        /* Móvil: el sidebar cubre toda la altura sin offset del navbar */
        @media (max-width: 991.98px) {
            #layoutSidenav_nav,
            #layoutSidenav #layoutSidenav_nav,
            .sb-nav-fixed #layoutSidenav #layoutSidenav_nav {
                top: 0 !important;
                height: 100vh !important;
                z-index: 9999 !important;
                transform: translateX(-250px) !important;
            }
            body.sb-mobile-open #layoutSidenav_nav,
            body.sb-mobile-open #layoutSidenav #layoutSidenav_nav,
            body.sb-mobile-open.sb-nav-fixed #layoutSidenav #layoutSidenav_nav {
                transform: translateX(0) !important;
            }
            body.sb-mobile-open::before {
                content: '';
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, .45);
                z-index: 9998;
            }
            #layoutSidenav #layoutSidenav_content {
                margin-left: 0 !important;
            }
        }

        /* ═══════════════════════════════════════════════════════════
           SIDEBAR — ERP Dark Theme
        ═══════════════════════════════════════════════════════════ */
        .sb-sidenav-custom {
            background: #131f2e !important;
            color: #7da8c2;
        }
        #layoutSidenav_nav {
            background: #131f2e;
        }
        /* Brand strip */
        .sidebar-brand {
            height: 58px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 1rem;
            border-bottom: 1px solid rgba(255,255,255,.07);
            flex-shrink: 0;
        }
        .sidebar-brand-link {
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        .sidebar-logo {
            max-height: 38px;
            max-width: 158px;
            object-fit: contain;
        }
        .sidebar-brand-text {
            color: #fff;
            font-weight: 700;
            font-size: .9rem;
            letter-spacing: .04em;
        }
        /* User section */
        .sidebar-user-section {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            background: rgba(0,0,0,.22);
            border-bottom: 1px solid rgba(255,255,255,.06);
        }
        .sidebar-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255,255,255,.18);
            flex-shrink: 0;
        }
        .sidebar-user-meta { min-width: 0; }
        .sidebar-user-name {
            color: #cce0f0;
            font-size: .8rem;
            font-weight: 600;
            line-height: 1.3;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 170px;
        }
        .sidebar-branch-name {
            color: #3f6075;
            font-size: .69rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 170px;
        }
        /* Scrollable menu */
        .sb-sidenav-custom .sb-sidenav-menu {
            overflow-y: auto;
            overflow-x: hidden;
        }
        .sb-sidenav-custom .sb-sidenav-menu::-webkit-scrollbar { width: 3px; }
        .sb-sidenav-custom .sb-sidenav-menu::-webkit-scrollbar-track { background: transparent; }
        .sb-sidenav-custom .sb-sidenav-menu::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,.08);
            border-radius: 2px;
        }
        /* Encabezados de sección */
        .sb-sidenav-custom .sb-sidenav-menu .sb-sidenav-menu-heading {
            color: #475569 !important;
            font-size: .59rem !important;
            font-weight: 800 !important;
            letter-spacing: .14em;
            padding: .9rem 1.1rem .3rem !important;
        }
        /* Level-1 — gris-blanco neutro */
        .sb-sidenav-custom .sb-sidenav-menu .nav-link {
            color: #94a3b8 !important;
            font-size: .815rem;
            font-weight: 400;
            padding: .5rem 1.1rem !important;
            border-left: 3px solid transparent;
            transition: color .14s, background .14s, border-color .14s;
        }
        .sb-sidenav-custom .sb-sidenav-menu .nav-link:hover {
            color: #e2e8f0 !important;
            background: rgba(255,255,255,.06);
            border-left-color: rgba(255,255,255,.18);
        }
        .sb-sidenav-custom .sb-sidenav-menu .nav-link.active {
            color: #f8fafc !important;
            background: rgba(255,255,255,.1);
            border-left-color: #64748b;
            font-weight: 600;
        }
        /* Icon container */
        .sb-sidenav-custom .sb-nav-link-icon {
            width: 22px;
            text-align: center;
            margin-right: 9px !important;
            font-size: .88rem;
            flex-shrink: 0;
            transition: transform .18s;
        }
        .sb-sidenav-custom .nav-link:hover .sb-nav-link-icon { transform: scale(1.12); }
        /* Flecha de colapso */
        .sb-sidenav-custom .sb-sidenav-collapse-arrow {
            color: #475569 !important;
            font-size: .68rem;
        }
        /* Level-2 (subitems) */
        .sb-sidenav-custom .sb-sidenav-menu-nested {
            margin-left: 0 !important;
            border-left: 1px solid rgba(255,255,255,.07);
            margin-left: 1.85rem !important;
        }
        .sb-sidenav-custom .sb-sidenav-menu-nested .nav-link {
            padding-left: 1rem !important;
            font-size: .785rem !important;
            color: #64748b !important;
            border-left: 2px solid transparent;
        }
        .sb-sidenav-custom .sb-sidenav-menu-nested .nav-link::before {
            content: '';
            display: inline-block;
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: #475569;
            margin-right: 8px;
            flex-shrink: 0;
            vertical-align: middle;
            transition: background .14s;
        }
        .sb-sidenav-custom .sb-sidenav-menu-nested .nav-link:hover {
            color: #cbd5e1 !important;
            background: rgba(255,255,255,.045);
            border-left-color: rgba(255,255,255,.15);
        }
        .sb-sidenav-custom .sb-sidenav-menu-nested .nav-link:hover::before { background: #94a3b8; }
        .sb-sidenav-custom .sb-sidenav-menu-nested .nav-link.active {
            color: #e2e8f0 !important;
            background: rgba(255,255,255,.08);
            border-left-color: #94a3b8;
            font-weight: 600;
        }
        .sb-sidenav-custom .sb-sidenav-menu-nested .nav-link.active::before { background: #94a3b8; }
        /* Level-3 */
        .sb-sidenav-custom .sb-sidenav-menu-nested .sb-sidenav-menu-nested .nav-link {
            padding-left: 1.2rem !important;
            font-size: .75rem !important;
            color: #4a5568 !important;
        }
        /* Module icon colors */
        .si-inicio    { color: #6ea8fe !important; }
        .si-ventas    { color: #5b9cf6 !important; }
        .si-cxc       { color: #1cc88a !important; }
        .si-inventario{ color: #f6c23e !important; }
        .si-finanzas  { color: #36b9cc !important; }
        .si-contab    { color: #b39ddb !important; }
        .si-comis     { color: #fd9843 !important; }
        .si-pedidos   { color: #20c997 !important; }
        .si-consig    { color: #e879a0 !important; }
        .si-vendedores{ color: #74c0fc !important; }
        .si-tipoventa { color: #a9e34b !important; }
        .si-admin     { color: #8899aa !important; }
        .si-reports   { color: #63c9a8 !important; }
        .si-log       { color: #748da4 !important; }

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

        /* ===========================
   NOTIFICACIONES
=========================== */

        .notif-card {
            display: flex;
            gap: 12px;
            padding: 12px 14px;
            border-bottom: 1px solid #f1f1f1;
            transition: all .2s ease;

            width: calc(100% - 10px);
            /* deja margen */
            margin: 0 auto;
        }

        .notif-card:hover {
            background: #f8f9fa;
            transform: translateX(4px);
        }

        .notif-icon {
            font-size: 18px;
            color: #ffc107;
            margin-top: 3px;
        }

        .notif-content {
            flex: 1;
            min-width: 0;
        }

        .notif-title {
            font-weight: 600;
            font-size: 14px;
            color: #1d2744;
        }

        .notif-msg {
            font-size: 12px;
            color: #6c757d;
            white-space: normal;
            word-break: break-word;
        }

        .dropdown-menu {
            border-radius: 10px;
        }

        .dropdown-header {
            font-size: 14px;
            color: #1d2744;
        }

        .notif-dropdown {
            width: 420px;
            max-height: 400px;
            overflow-y: auto;
            overflow-x: hidden;
            /* evita barra lateral */
            padding: 0;
        }

        #sidebarToggle i {
            font-size: 1.5rem;
            transition: 0.25s ease;
        }

        #sidebarToggle:hover i {
            transform: scale(1.1);
            opacity: 0.75;
        }
    </style>
</head>

<body class="sb-nav-fixed">
    <!--Header Nav-->
    <nav class="navbar navbar-expand navbar-dark sb-topnav"
         style="background-color:<?= setting('primary_color') ?? '#1d2744' ?>;">
        <div class="container-fluid">

            <!-- ── IZQUIERDA: toggle + brand + módulo ────────── -->
            <div class="d-flex align-items-center">
                <button class="btn btn-link text-white p-2 mr-2" id="sidebarToggle"
                        style="border-radius:6px; line-height:1;">
                    <i class="fa-solid fa-bars fa-lg"></i>
                </button>
                <a class="navbar-brand-erp mr-2 d-none d-md-inline"
                   href="<?= base_url('dashboard') ?>">
                    <?= esc(setting('company_name') ?? 'Sistema') ?>
                </a>
                <div class="navbar-divider d-none d-lg-block mx-2"></div>
                <span class="nav-module-label d-none d-lg-inline" id="navModuleLabel"></span>
            </div>

            <!-- ── DERECHA: reloj + campana + usuario ────────── -->
            <div class="d-flex align-items-center">

                <!-- Reloj -->
                <div class="nav-clock-wrap d-none d-lg-block mr-3">
                    <span class="nav-clock-time" id="navClock"></span>
                    <span class="nav-clock-date" id="navDate"></span>
                </div>
                <div class="navbar-divider d-none d-lg-block mr-3"></div>

                <!-- Campana -->
                <li class="nav-item dropdown mr-3" style="list-style:none;">
                    <a class="nav-link text-white position-relative" href="#"
                       id="notifDropdown" data-toggle="dropdown"
                       aria-haspopup="true" aria-expanded="false">
                        <i id="notifBell" class="fa-solid fa-bell fa-lg"></i>
                        <span id="notifCount" class="badge badge-danger position-absolute"
                              style="top:-5px;right:-10px;font-size:11px;display:none;"></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow notif-dropdown"
                         aria-labelledby="notifDropdown">
                        <div class="dropdown-header">
                            <i class="fa-solid fa-bell text-primary mr-1"></i>
                            <strong>Notificaciones</strong>
                        </div>
                        <div id="notifList">
                            <div class="dropdown-item text-muted text-center">Sin notificaciones</div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="<?= base_url('notifications') ?>"
                           class="dropdown-item text-center text-primary small">
                            Ver todas
                        </a>
                    </div>
                </li>

                <!-- Usuario -->
                <?php
                $foto = session('foto');
                $fotoPath = ($foto && file_exists(FCPATH . 'upload/perfiles/' . $foto))
                    ? base_url('upload/perfiles/' . $foto)
                    : base_url('upload/profile/user.jpg');
                ?>
                <div class="dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center p-0 text-white"
                       href="#" id="userDropdown" data-toggle="dropdown"
                       aria-haspopup="true" aria-expanded="false">
                        <img src="<?= esc($fotoPath) ?>" alt="avatar"
                             height="36" width="36"
                             class="rounded-circle nav-avatar mr-2">
                        <div class="d-none d-md-block">
                            <div class="nav-user-name"><?= esc($session->get('user_name') ?? 'N/A') ?></div>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right user-menu"
                         aria-labelledby="userDropdown">
                        <div class="user-dropdown-header">
                            <div class="ud-name"><?= esc($session->get('user_name') ?? 'N/A') ?></div>
                            <div class="ud-branch">
                                <i class="fa-solid fa-location-dot mr-1"></i>
                                <?= esc($session->get('branch_name') ?? '') ?>
                            </div>
                        </div>
                        <a class="dropdown-item mt-1" href="<?= base_url('perfil') ?>">
                            <i class="fa-regular fa-user mr-2 text-muted"></i>Mi Perfil
                        </a>
                        <div class="dropdown-divider my-0"></div>
                        <a class="dropdown-item text-danger" href="/logout">
                            <i class="fa-solid fa-power-off mr-2"></i>Cerrar Sesión
                        </a>
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
                <div class="content-wrapper mt-3">
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
        /* ── Reloj en vivo ──────────────────────── */
        (function sidebarMobileInit() {
            var btn = document.getElementById('sidebarToggle');
            var mobileQuery = window.matchMedia('(max-width: 991.98px)');

            function closeMobileSidebar() {
                document.body.classList.remove('sb-mobile-open');
                if (btn) btn.setAttribute('aria-expanded', 'false');
            }

            if (btn) {
                btn.setAttribute('aria-controls', 'layoutSidenav_nav');
                btn.setAttribute('aria-expanded', 'false');
                btn.addEventListener('click', function () {
                    if (!mobileQuery.matches) return;
                    var isOpen = document.body.classList.toggle('sb-mobile-open');
                    btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                });
            }

            document.addEventListener('click', function (event) {
                if (!mobileQuery.matches || !document.body.classList.contains('sb-mobile-open')) return;
                if (event.target.closest('#layoutSidenav_nav') || event.target.closest('#sidebarToggle')) return;
                closeMobileSidebar();
            });

            document.querySelectorAll('.close-mobile-nav, #layoutSidenav_nav a.nav-link[href]:not([href="#"])')
                .forEach(function (el) {
                    el.addEventListener('click', closeMobileSidebar);
                });

            mobileQuery.addEventListener('change', function (event) {
                if (!event.matches) closeMobileSidebar();
            });
        })();

        (function clockInit() {
            function tick() {
                var now  = new Date();
                var time = now.toLocaleTimeString('es-SV', { hour: '2-digit', minute: '2-digit' });
                var date = now.toLocaleDateString('es-SV', { weekday: 'short', day: '2-digit', month: 'short' });
                var c = document.getElementById('navClock');
                var d = document.getElementById('navDate');
                if (c) c.textContent = time;
                if (d) d.textContent = date;
            }
            tick();
            setInterval(tick, 10000);
        })();

        /* ── Indicador de módulo activo ─────────── */
        (function moduleLabel() {
            var map = {
                'dashboard':'Dashboard','facturas':'Ventas','factura':'Ventas',
                'clientes':'Ventas','payments':'Cuentas por Cobrar','quedans':'CxC',
                'inventory':'Inventario','purchases':'Inventario','proveedores':'Inventario',
                'compraspagos':'Inventario','cashiers':'Finanzas','cashier':'Finanzas',
                'transactions':'Finanzas','accounts':'Finanzas',
                'contabilidad':'Contabilidad','comisiones':'Comisiones',
                'pedidos':'Pedidos','consignaciones':'Consignaciones',
                'sellers':'Vendedores','tipo_venta':'Tipos de Venta',
                'users':'Administración','roles':'Administración',
                'branches':'Administración','settings':'Configuración',
                'content':'Multimedia','reports':'Reportería',
                'logs':'Bitácora','notifications':'Notificaciones','perfil':'Mi Perfil'
            };
            var seg  = (window.location.pathname.split('/').filter(Boolean)[0] || 'dashboard');
            var lbl  = map[seg] || '';
            var el   = document.getElementById('navModuleLabel');
            if (el && lbl) el.textContent = lbl;
        })();

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
                            <a class="dropdown-item notif-item notif-card" 
                            data-id="${n.id}" 
                            href="${n.link ?? '#'}">

                                <div class="notif-icon">
                                    <i class="fa-solid fa-circle-exclamation"></i>
                                </div>

                                <div class="notif-content">
                                    <div class="notif-title">${n.titulo}</div>
                                    <div class="notif-msg">${n.mensaje ?? ''}</div>
                                </div>

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
