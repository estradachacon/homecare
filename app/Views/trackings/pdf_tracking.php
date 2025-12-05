<?php
$titulo = 'Rendición de Tracking ID: ' . $tracking->id;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?= $titulo ?></title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10pt;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f7f7f7;
        }

        .container {
            width: 100%;
            padding: 20px;
        }

        .header {
            background-color: #1a73e8;
            color: #fff;
            padding: 15px 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
            margin-bottom: 20px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            margin: 0;
            font-size: 18pt;
            font-weight: 500;
        }

        .header p {
            margin: 5px 0 0;
            font-size: 11pt;
            opacity: 0.9;
        }

        .card {
            background-color: #fff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .card h2 {
            font-size: 14pt;
            color: #1a73e8;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 5px;
            margin: 0 0 15px 0;
        }

        .info-row {
            display: flex;
            padding: 5px 0;
        }

        .info-label {
            font-weight: bold;
            width: 35%;
            color: #555;
        }

        .info-value {
            width: 65%;
        }

        .table-section h2 {
            font-size: 14pt;
            color: #1a73e8;
            margin-bottom: 10px;
        }

        .table-section th,
        .table-section td {
            word-wrap: break-word;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        th,
        td {
            padding: 10px 12px;
            border: 1px solid #e0e0e0;
        }

        th {
            background-color: #1a73e8;
            color: #fff;
            text-align: left;
            font-weight: 600;
        }

        tr:nth-child(even) {
            background-color: #f4f4f9;
        }

        .status {
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 9pt;
            display: inline-block;
        }

        .status-entregado {
            background-color: #e6ffed;
            color: #00891d;
        }

        .status-no_retirado {
            background-color: #ffebe6;
            color: #d93025;
        }

        .status-recolectado {
            background-color: #fff4e5;
            color: #e37400;
        }

        .status-otro {
            background-color: #f0f0f0;
            color: #5f6368;
        }

        .footer {
            text-align: center;
            font-size: 8pt;
            color: #777;
            margin-top: 20px;
            border-top: 1px solid #e0e0e0;
            padding-top: 8px;
        }

        .table-section table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed; /* NECESARIO para que los widths funcionen */
}

.table-section th:nth-child(1), /* ID */
.table-section td:nth-child(1) {
    width: 10%;
}

.table-section th:nth-child(2), /* Tipo Servicio */
.table-section td:nth-child(2) {
    width: 30%;
}

.table-section th:nth-child(3), /* Vendedor */
.table-section td:nth-child(3) {
    width: 40%;
}

.table-section th:nth-child(4), /* Estatus Final */
.table-section td:nth-child(4) {
    width: 20%;
}
    </style>
</head>

<body>
    <div class="container">

        <!-- Encabezado -->
        <div class="header">
            <h1><?= $titulo ?></h1>
            <p>Generado el: <?= date('d/m/Y H:i:s') ?></p>
        </div>

        <!-- Información General -->
        <div class="card">
            <h2>Información General</h2>
            <div class="info-row">
                <span class="info-label">ID Tracking:</span>
                <span class="info-value"><?= $tracking->id ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Motorista:</span>
                <span class="info-value"><?= $tracking->motorista_name ?? 'N/A' ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Fecha Creación:</span>
                <span class="info-value"><?= date('d/m/Y', strtotime($tracking->created_at)) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Estatus General:</span>
                <span class="info-value"><?= ucwords(str_replace('_', ' ', $tracking->status)) ?></span>
            </div>
        </div>

        <!-- Tabla de Paquetes -->
        <div class="table-section">
            <h2>Detalles de Paquetes</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID Paquete</th>
                        <th>Tipo Servicio</th>
                        <th>Vendedor</th>
                        <th>Estatus Final</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($detalles)): ?>
                        <?php foreach ($detalles as $detalle):
                            $statusClass = 'status-otro';
                            if ($detalle->status === 'entregado' || $detalle->status === 'recolectado') {
                                $statusClass = 'status-entregado';
                            } elseif ($detalle->status === 'no_retirado' || $detalle->status === 'recolecta_fallida') {
                                $statusClass = 'status-no_retirado';
                            }
                            $serviceName = $tiposServicio[$detalle->tipo_servicio] ?? 'Desconocido';
                        ?>
                            <tr>
                                <td><?= $detalle->package_id ?></td>
                                <td><?= $serviceName ?></td>
                                <td><?= $detalle->vendedor_nombre ?? 'N/A' ?></td>
                                <td><span class="status <?= $statusClass ?>"><?= ucwords(str_replace('_', ' ', $detalle->status)) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align:center;">No hay paquetes asociados a este Tracking.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pie de Página -->
        <div class="footer">
            Sistema de Tracking - Generado automáticamente
        </div>

    </div>
</body>

</html>