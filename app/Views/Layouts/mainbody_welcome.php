<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión</title>
    <meta name="description" content="The small framework with powerful features">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="shortcut icon" type="image/png" href="/favicon.ico">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #007bff, #004085);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        header,
        .content-wrapper {
            width: 100%;
        }

        .login-card {
            width: 100%;
            max-width: 500px;
            /* probá 560 o 600 */
            margin: auto;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, .15);
        }

        .card-header {
            background-color: #0056b3;
            color: #fff;
            border-bottom: 2px solid #004494;
        }

        .form-control:focus {
            border-color: #0056b3;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
        }

        .btn-primary {
            background-color: #0056b3;
            border-color: #0056b3;
            transition: all .3s ease;
        }

        .btn-primary:hover {
            background-color: #004494;
            border-color: #004494;
            transform: translateY(-1px);
        }

        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
        }

        .input-group .form-control {
            border-left: none;
        }

        #togglePassword {
            border-left: none;
        }

        .form-check-input:checked {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .alert {
            border-radius: 8px;
        }

        /* Fade + slide */
        .fade-slide {
            animation: fadeSlide .35s ease;
        }

        @keyframes fadeSlide {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Botón bloqueado */
        .btn-loading {
            pointer-events: none;
            opacity: .75;
        }

        /* Spinner pequeño */
        .btn-spinner {
            width: 1rem;
            height: 1rem;
            border: 2px solid #fff;
            border-top-color: transparent;
            border-radius: 50%;
            display: inline-block;
            animation: spin .6s linear infinite;
            vertical-align: middle;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <header>
        <!-- CONTENT -->
        <div class="content-wrapper">
            <?= $this->renderSection('content') ?>
        </div>
        
        <!-- SCRIPTS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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