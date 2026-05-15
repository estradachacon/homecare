<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pagina no encontrada | Homecare</title>
    <style>
        :root {
            color-scheme: light;
            --text: #1f2937;
            --muted: #6b7280;
            --border: #e5e7eb;
            --surface: #ffffff;
            --soft: #f8fafc;
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --success: #16a34a;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: var(--soft);
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
        }

        .page {
            width: min(100%, 720px);
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            box-shadow: 0 18px 50px rgba(15, 23, 42, .08);
            overflow: hidden;
        }

        .bar {
            height: 6px;
            background: var(--success);
        }

        .content {
            padding: 36px;
            text-align: center;
        }

        .code {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 74px;
            height: 42px;
            margin-bottom: 18px;
            border-radius: 999px;
            background: #eef6ff;
            color: var(--primary-dark);
            font-weight: 800;
            letter-spacing: .04em;
        }

        h1 {
            margin: 0;
            font-size: clamp(1.5rem, 5vw, 2.4rem);
            line-height: 1.15;
        }

        p {
            margin: 14px auto 0;
            max-width: 520px;
            color: var(--muted);
            line-height: 1.55;
        }

        .actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 26px;
        }

        a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            padding: 8px 14px;
            border-radius: 6px;
            border: 1px solid var(--border);
            color: var(--text);
            text-decoration: none;
            font-weight: 700;
            font-size: .92rem;
        }

        a.primary {
            border-color: var(--primary);
            background: var(--primary);
            color: #fff;
        }

        a.primary:hover {
            background: var(--primary-dark);
        }

        a:hover {
            background: #f3f4f6;
        }

        .hint {
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px solid var(--border);
            font-size: .84rem;
            color: #7b8794;
        }

        @media (max-width: 575.98px) {
            body {
                padding: 14px;
            }

            .content {
                padding: 28px 18px;
            }

            .actions a {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <main class="page" role="main">
        <div class="bar"></div>
        <section class="content">
            <div class="code">404</div>
            <h1>No encontramos esta pagina</h1>
            <p>
                Es posible que el enlace haya cambiado, que el registro ya no exista,
                o que no tengas permiso para ver este recurso.
            </p>

            <div class="actions">
                <a href="<?= site_url('dashboard') ?>" class="primary">Ir al inicio</a>
                <a href="javascript:history.back()">Volver atras</a>
            </div>

            <div class="hint">
                Si crees que deberias tener acceso, revisa tus permisos o consulta con administracion.
            </div>
        </section>
    </main>
</body>
</html>
