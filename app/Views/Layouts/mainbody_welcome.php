<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Bienvenido a FC Encomiendas</title>
    <meta name="description" content="The small framework with powerful features">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="shortcut icon" type="image/png" href="/favicon.ico">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- STYLES -->
    <style {csp-style-nonce}>
        * {
            transition: background-color 300ms ease, color 300ms ease;
        }

        *:focus {
            background-color: rgba(65, 129, 214, 0.2);
            outline: none;
        }

        html,
        body {
            color: rgba(33, 37, 41, 1);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji";
            font-size: 16px;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }

        header {
            background-color: rgba(247, 248, 249, 1);
            padding: .4rem 0 0;
        }

        .menu {
            padding: .4rem 2rem;
        }

        header ul {
            border-bottom: 1px solid rgba(72, 118, 177, 0.68);
            list-style-type: none;
            margin: 0;
            overflow: hidden;
            padding: 0;
            text-align: right;
        }

        header li {
            display: inline-block;
        }

        header li a {
            border-radius: 5px;
            color: rgba(0, 0, 0, .5);
            display: block;
            height: 44px;
            text-decoration: none;
        }

        header li.menu-item a {
            border-radius: 5px;
            margin: 5px 0;
            height: 38px;
            line-height: 36px;
            width: 160px;
            /* padding: .4rem .65rem; */
            text-align: center;
        }

        header li.menu-item a:hover,
        header li.menu-item a:focus {
            background-color: rgba(20, 114, 221, 0.2);
            color: rgba(24, 22, 22, 1);
            border-radius: 12px;
        }

        header .logo {
            float: left;
            height: 44px;
            padding: .4rem .5rem;
        }

        header .menu-toggle {
            display: none;
            float: right;
            font-size: 2rem;
            font-weight: bold;
        }

        header .menu-toggle button {
            background-color: rgba(42, 136, 212, 0.9);
            border: none;
            border-radius: 8px;
            color: rgba(255, 255, 255, 1);
            cursor: pointer;
            font: inherit;
            font-size: 1.5rem;
            height: 40px;
            padding: 0;
            margin: 11px 0;
            overflow: visible;
            width: 40px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        header .menu-toggle button:hover,
        header .menu-toggle button:focus {
            background-color: rgba(45, 101, 138, 1);
            color: rgba(255, 255, 255, 1);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        header .heroe {
            margin: 0 auto;
            max-width: 1100px;
            padding: 1rem 1.75rem 1.75rem 1.75rem;
        }

        header .heroe h1 {
            font-size: 2.5rem;
            font-weight: 500;
        }

        header .heroe h2 {
            font-size: 1.5rem;
            font-weight: 300;
        }

        section {
            margin: 0 auto;
            max-width: 1100px;
            padding: 2.5rem 1.75rem 3.5rem 1.75rem;
        }

        section h1 {
            margin-bottom: 2.5rem;
        }

        section h2 {
            font-size: 120%;
            line-height: 2.5rem;
            padding-top: 1.5rem;
        }

        section pre {
            background-color: rgba(247, 248, 249, 1);
            border: 1px solid rgba(242, 242, 242, 1);
            display: block;
            font-size: .9rem;
            margin: 2rem 0;
            padding: 1rem 1.5rem;
            white-space: pre-wrap;
            word-break: break-all;
        }

        section code {
            display: block;
        }

        section a {
            color: rgba(221, 72, 20, 1);
        }

        section svg {
            margin-bottom: -5px;
            margin-right: 5px;
            width: 25px;
        }

        .further {
            background-color: rgba(247, 248, 249, 1);
            border-bottom: 1px solid rgba(242, 242, 242, 1);
            border-top: 1px solid rgba(242, 242, 242, 1);
        }

        .further h2:first-of-type {
            padding-top: 0;
        }

        .svg-stroke {
            fill: none;
            stroke: #000;
            stroke-width: 32px;
        }

        footer {
            background-color: rgba(18, 104, 185, 0.2);
            text-align: center;
        }

        footer .environment {
            color: rgba(83, 81, 81, 1);
            padding: 2rem 1.75rem;
        }

        footer .copyrights {
            background-color: rgba(62, 62, 62, 1);
            color: rgba(200, 200, 200, 1);
            padding: .25rem 1.75rem;
        }

        @media (max-width: 629px) {
            header .menu {
                padding: 0;
                height: 60px;
                position: relative;
                background-color: rgba(247, 248, 249, 1);
            }

            .logo-badge {
                padding: 4px 10px;
                border-radius: 12px;
            }

            .logo-badge img.logo {
                height: 40px;
            }

            header .menu-header {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                padding: 0.5rem 1rem;
                background-color: rgba(247, 248, 249, 1);
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                z-index: 1000;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            header .logo {
                height: 44px;
                padding: 0;
            }

            header .menu-toggle {
                display: block;
                margin: 0;
                padding: 0;
            }

            header .menu-toggle button {
                margin: 0;
                padding: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            header .menu-list {
                position: fixed;
                top: 60px;
                left: 0;
                right: 0;
                background-color: rgba(247, 248, 249, 0.98);
                padding: 20px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                transition: all 0.3s ease-in-out;
                z-index: 1100;
            }

            header .menu-list.hidden {
                opacity: 0;
                visibility: hidden;
                transform: translateY(-100%);
            }

            header .menu-list li.menu-item {
                display: block;
                margin: 10px 0;
                width: 100%;
            }

            header li.menu-item a {
                text-align: center;
                width: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                height: 44px;
                color: rgba(0, 0, 0, 0.8);
                font-weight: 500;
                background-color: rgba(42, 136, 212, 0.1);
                border-radius: 8px;
                transition: all 0.2s ease;
            }

            header li.menu-item a:hover,
            header li.menu-item a:focus {
                background-color: rgba(45, 101, 138, 0.9);
                color: white;
                transform: translateY(-2px);
            }

            /* Ocultar elementos del menú toggle y logo que se duplican */
            header .menu-list .logo,
            header .menu-list .menu-toggle {
                display: none;
            }
        }

        .social-cards {
            text-align: center;
            padding: 30px 15px;
        }

        .social-cards h1 {
            margin-bottom: 25px;
            font-weight: 600;
        }

        .social-grid {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .social-card {
            width: 160px;
            height: 120px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.12);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
        }

        .social-card i {
            font-size: 36px;
            margin-bottom: 10px;
        }

        .social-card span {
            font-weight: 500;
            font-size: 15px;
        }

        /* 🎯 Hover */
        .social-card:hover {
            transform: translateY(-8px) scale(1.05);
            box-shadow: 0 14px 30px rgba(0, 0, 0, 0.25);
            color: #fff;
        }

        /* 🎨 Colores por red */
        .social-card.facebook:hover {
            background: #4267B2;
        }

        .social-card.instagram:hover {
            background: linear-gradient(45deg, #f58529, #dd2a7b, #8134af);
        }

        .social-card.tiktok:hover {
            background: linear-gradient(45deg, #000, #25F4EE);
        }

        /* ✨ Mejora visual SIN cambiar layout */
        /* 🎯 Texto visible y centrado SIEMPRE */
        header li.menu-item a {
            display: flex;
            align-items: center;
            justify-content: center;

            font-weight: 500;
            letter-spacing: 0.3px;
            color: rgba(29, 39, 68, 0.85);
            /* visible sin hover */

            background-color: rgba(42, 136, 212, 0.08);
            border: 1px solid rgba(42, 136, 212, 0.18);

            transition: all 0.25s ease;
        }

        /* ✨ Hover elegante */
        header li.menu-item a:hover,
        header li.menu-item a:focus {
            color: #ffffff;
            background: linear-gradient(135deg,
                    rgba(42, 136, 212, 0.85),
                    rgba(29, 39, 68, 0.9));

            transform: translateY(-3px);
            box-shadow: 0 8px 22px rgba(29, 39, 68, 0.35);
        }

        /* Línea animada inferior */
        header li.menu-item a::after {
            content: "";
            position: absolute;
            bottom: 6px;
            left: 50%;
            width: 0;
            height: 3px;
            background: linear-gradient(90deg, #ffffff, #cfe2ff);
            border-radius: 4px;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        header li.menu-item a:hover::after {
            width: 55%;
        }

        .fancy-menu .menu-item a {
            display: block;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.05);
            text-decoration: none;
            transition: all 0.25s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        /* ✨ Hover bonito */
        .fancy-menu .menu-item a:hover {
            background: linear-gradient(135deg, #4267B2, #1d2744);
            transform: translateX(6px) scale(1.03);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.35);
            color: #fff;
        }

        /* 🟡 Item especial (login) */
        .fancy-menu .menu-item:last-child a {
            background: rgba(255, 193, 7, 0.15);
            border: 1px solid rgba(255, 193, 7, 0.35);
        }

        .fancy-menu .menu-item:last-child a:hover {
            background: #ffc107;
            color: #1d2744;
        }

        /* 🏷️ Logo tipo badge */
        .logo-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            background: #ffffff;
            padding: 6px 12px;
            border-radius: 14px;

            border: 1px solid rgba(42, 136, 212, 0.25);
            box-shadow:
                0 6px 16px rgba(0, 0, 0, 0.12),
                0 0 0 0 rgba(42, 136, 212, 0.0);

            transition: all 0.35s ease;
        }

        /* Imagen del logo */
        .logo-badge img.logo {
            height: 44px;
            width: auto;
            display: block;
            transition: transform 0.35s ease;
        }

        .logo-badge:hover {
            transform: translateY(-2px);
            box-shadow:
                0 10px 28px rgba(0, 0, 0, 0.18),
                0 0 0 6px rgba(42, 136, 212, 0.15);
        }

        .logo-badge:hover img.logo {
            transform: scale(1.05);
        }
    </style>
</head>

<body>
    <header>
        <div class="menu">
            <div class="menu-header">
                <div class="logo logo-badge">

                    <a href="<?= base_url() ?>">
                        <!-- LOGO EMPRESA -->
                        <?php if (setting('logo')): ?>
                            <img class="logo shadow-sm"
                                src="<?= base_url('upload/settings/' . setting('logo')) ?>"
                                alt="logo-company"
                                height="44">
                    </a>
                <?php else: ?>
                    <a href="<?= base_url() ?>" class="text-decoration-none">
                        <h2 class="m-0 p-0">FC Encomiendas</h2>
                    </a>
                <?php endif; ?>
                </div>
                <div class="menu-toggle">
                    <button id="menuToggle" aria-expanded="false" aria-label="Menú principal">&#9776;</button>
                </div>
            </div>
            <ul class="menu-list hidden fancy-menu" id="mainMenu">
                <li class="menu-item"><a href="<?= base_url('/rutas') ?>">Nuestras rutas</a></li>
                <li class="menu-item"><a href="<?= base_url('/sucursales') ?>">Ubicación</a></li>
                <li class="menu-item"><a href="<?= base_url('/quienes-somos') ?>">Quienes somos</a></li>
                <li class="menu-item">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">
                        Iniciar sesión
                    </a>
                </li>
            </ul>

        </div>
        <!-- CONTENT -->
        <div class="content-wrapper">
            <?= $this->renderSection('content') ?>
        </div>
        <!-- Include del Modal de Login -->
        <?= $this->include('modals/login') ?>
        <?= $this->include('modals/reset1') ?>
        <!-- SCRIPTS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script {csp-script-nonce}>
            // Script para el menú
            document.getElementById("menuToggle").addEventListener('click', toggleMenu);

            function toggleMenu() {
                var menuList = document.querySelector('.menu-list');
                menuList.classList.toggle("hidden");
                // Accesibilidad: Actualizar el estado del botón
                var menuButton = document.getElementById("menuToggle");
                var isExpanded = menuList.classList.contains("hidden") ? "false" : "true";
                menuButton.setAttribute("aria-expanded", isExpanded);
            }
            // Cerrar el menú al hacer clic fuera
            document.addEventListener('click', function(event) {
                var menu = document.querySelector('.menu');
                var menuButton = document.getElementById("menuToggle");
                if (!menu.contains(event.target)) {
                    var menuList = document.querySelector('.menu-list');
                    menuList.classList.add("hidden");
                    menuButton.setAttribute("aria-expanded", "false");
                }
            });
        </script>
        <script>
            // Mostrar alertas de flashdata
            <?php if (session()->getFlashdata('alert')): ?>
                <?php $alert = session()->getFlashdata('alert'); ?>
                Swal.fire({
                    icon: '<?= $alert['type'] ?>',
                    title: '<?= $alert['title'] ?>',
                    text: '<?= $alert['message'] ?>',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            <?php endif; ?>
        </script>
        <script>
    const menuToggle = document.getElementById('menuToggle');
    const menu = document.getElementById('mainMenu');

    // Función para cerrar el menú
    function closeMenu() {
        menu.classList.add('hidden');
        menuToggle.setAttribute('aria-expanded', 'false');
    }

    // Cerrar menú al hacer clic en cualquier item
    menu.querySelectorAll('.menu-item a').forEach(link => {
        link.addEventListener('click', () => {
            closeMenu();
        });
    });
</script>

</body>

</html>