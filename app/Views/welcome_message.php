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
    </style>
</head>

<body>

    <!-- HEADER: MENU + HEROE SECTION -->
    <header>

        <div class="menu">
            <div class="menu-header">
                <div class="logo">
                    <a href="<?= base_url() ?>">
                        <img src="<?= base_url('img/logo.jpg') ?>" alt="Logo de Encomiendas"
                            style="height: 44px; width: auto;">
                    </a>
                </div>
                <div class="menu-toggle">
                    <button id="menuToggle" aria-expanded="false" aria-label="Menú principal">&#9776;</button>
                </div>
            </div>
            <ul class="menu-list hidden">
                <li class="menu-item"><a href="#">Nuestras rutas</a></li>
                <li class="menu-item"><a href="#" target="_blank">Ubicación</a></li>
                <li class="menu-item"><a href="#" target="_blank">Quienes somos</a></li>
                <li class="menu-item"><a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Iniciar sesión</a>
                </li>
            </ul>
        </div>
        <div class="heroe bienvenida-encomiendas">
            <h1>¡Bienvenido a tu aliado logístico! 🚀</h1>
            <h2>Servicios de Encomienda Rápidos y Seguros</h2>
            <p>Estamos comprometidos a conectar tus envíos con sus destinos de manera eficiente y confiable.</p>
        </div>

    </header>

    <!-- CONTENT -->

    <section>

        <div>

        </div>

    </section>

    <div class="further">

        <section>
            <?php echo 'Hora actual del servidor: ' . date('Y-m-d H:i:s');?>

            <h1>Nuestras redes sociales</h1>

            <h2>
                <i class="fab fa-facebook-square" style="color: #4267B2;"></i>
                <i class="fab fa-twitter-square" style="color: #1DA1F2;"></i>
                <i class="fab fa-instagram-square" style="color: #E1306C;"></i>
                Síguenos
            </h2>
        </section>

    </div>

    <!-- FOOTER: DEBUG INFO + COPYRIGHTS -->

    <footer>
        <div class="environment">
            <h2>Nuestra Visión</h2>
            <p>
                En <strong>FC Encomiendas</strong> aspiramos a ser la empresa líder en servicios de encomiendas en El
                Salvador,
                brindando entregas rápidas, seguras y confiables. Buscamos conectar a personas y negocios con
                eficiencia,
                compromiso y confianza, superando siempre las expectativas de nuestros clientes.
            </p>
            <p>Ambiente: <?= ENVIRONMENT ?></p>

        </div>

        <div class="copyrights">

            <p>&reg; <?= date('Y') ?> FC Encomiendas.</p>

        </div>

    </footer>

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
        document.addEventListener('click', function (event) {
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

</body>

</html>