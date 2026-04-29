<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nota de Envío <?= esc($consignacion->numero) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #000; padding: 20px; }

        .empresa-header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
        .empresa-header h2 { font-size: 16px; margin-bottom: 2px; }
        .empresa-header p  { font-size: 11px; color: #444; }

        .doc-title { text-align: center; margin-bottom: 12px; }
        .doc-title h3 { font-size: 15px; font-weight: bold; text-transform: uppercase; }
        .doc-title .numero { font-size: 18px; font-weight: bold; color: #000; border: 2px solid #000; display: inline-block; padding: 3px 15px; margin-top: 4px; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; margin-bottom: 14px; border: 1px solid #ccc; padding: 10px; }
        .info-item label { font-weight: bold; font-size: 10px; text-transform: uppercase; color: #555; display: block; }
        .info-item span  { font-size: 12px; }

        .info-generacion { text-align: right; font-size: 10px; color: #888; margin-bottom: 10px; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        thead th { background: #333; color: #fff; padding: 5px 8px; text-align: left; font-size: 11px; }
        tbody td { border-bottom: 1px solid #ddd; padding: 5px 8px; font-size: 11px; }
        tbody tr:nth-child(even) { background: #f9f9f9; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .totals { border-top: 2px solid #000; padding-top: 8px; text-align: right; }
        .totals .total-line { font-size: 14px; font-weight: bold; }

        .observaciones { border: 1px solid #ccc; padding: 8px; margin-top: 10px; font-size: 11px; }
        .observaciones label { font-weight: bold; font-size: 10px; text-transform: uppercase; }

        .firmas { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 40px; }
        .firma-box { border-top: 1px solid #000; padding-top: 4px; text-align: center; font-size: 11px; }

        .estado-badge { display: inline-block; padding: 3px 12px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: .5px; border: 1px solid currentColor; }
        .estado-abierta  { color: #155724; background: #d4edda; border-color: #c3e6cb; }
        .estado-cerrada  { color: #383d41; background: #e2e3e5; border-color: #d6d8db; }
        .estado-anulada  { color: #721c24; background: #f8d7da; border-color: #f5c6cb; }
        .aprobacion-badge { display: inline-block; margin-left: 8px; padding: 3px 10px; border-radius: 4px; font-size: 10px; font-weight: bold; text-transform: uppercase; border: 1px solid currentColor; }
        .aprobacion-aprobada  { color: #155724; background: #d4edda; border-color: #c3e6cb; }
        .aprobacion-rechazada { color: #721c24; background: #f8d7da; border-color: #f5c6cb; }
        .aprobacion-pendiente { color: #856404; background: #fff3cd; border-color: #ffeeba; }

        @media print {
            body { padding: 10px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <!-- Botón imprimir (no se imprime) -->
    <div class="no-print" style="margin-bottom:15px; text-align:right;">
        <button onclick="window.print()" style="padding:8px 20px; background:#333; color:#fff; border:none; cursor:pointer; border-radius:4px;">
            🖨 Imprimir
        </button>
        <button onclick="window.close()" style="padding:8px 20px; background:#999; color:#fff; border:none; cursor:pointer; border-radius:4px; margin-left:8px;">
            Cerrar
        </button>
    </div>

    <!-- Encabezado empresa -->
    <?php
    $nombreEmpresa = setting('company_name') ?? 'HomeCare';
    ?>
    <div class="empresa-header">
        <h2><?= esc($nombreEmpresa) ?></h2>
        <p>Nota de Envío / Consignación</p>
    </div>

    <!-- Título del documento -->
    <div class="doc-title">
        <h3>Nota de Envío</h3>
        <div class="numero"><?= esc($consignacion->numero) ?></div>
    </div>

    <!-- Estado -->
    <div style="text-align:center; margin-bottom:8px;">
        <?php
            $est = $consignacion->estado ?? 'abierta';
            $estLabel = match($est) {
                'cerrada' => ['estado-cerrada', 'Cerrada'],
                'anulada' => ['estado-anulada', 'Anulada'],
                default   => ['estado-abierta', 'Activa'],
            };
            $apEst = $consignacion->aprobacion_estado ?? 'pendiente';
            $apLabel = match($apEst) {
                'aprobada'  => ['aprobacion-aprobada', 'Aprobada'],
                'rechazada' => ['aprobacion-rechazada', 'Rechazada'],
                default     => ['aprobacion-pendiente', 'Pend. Aprobación'],
            };
        ?>
        <span class="estado-badge <?= $estLabel[0] ?>"><?= $estLabel[1] ?></span>
        <span class="aprobacion-badge <?= $apLabel[0] ?>"><?= $apLabel[1] ?></span>
    </div>

    <!-- Fecha de generación interna -->
    <div class="info-generacion">
        Fecha de generación del sistema: <?= date('d/m/Y H:i:s', strtotime($consignacion->fecha_generacion)) ?>
    </div>

    <!-- Información principal -->
    <div class="info-grid">
        <div class="info-item">
            <label>Vendedor / Representante</label>
            <span><?= esc($consignacion->vendedor_nombre) ?></span>
        </div>
        <div class="info-item">
            <label>Nombre / Referencia</label>
            <span><?= esc($consignacion->nombre ?: '—') ?></span>
        </div>
        <div class="info-item">
            <label>Fecha</label>
            <span><?= date('d/m/Y', strtotime($consignacion->fecha)) ?></span>
        </div>
        <div class="info-item">
            <label>Hora</label>
            <span><?= $consignacion->hora ? substr($consignacion->hora, 0, 5) : '—' ?></span>
        </div>
        <?php if ($consignacion->concepto): ?>
        <div class="info-item" style="grid-column: span 2;">
            <label>Concepto</label>
            <span><?= esc($consignacion->concepto) ?></span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Tabla de productos -->
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Descripción</th>
                <th class="text-center">Cantidad</th>
                <th class="text-right">Precio Unitario</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detalles as $d): ?>
            <tr>
                <td><?= esc($d->producto_codigo) ?></td>
                <td><?= esc($d->producto_nombre) ?></td>
                <td class="text-center"><?= number_format($d->cantidad, 2) ?></td>
                <td class="text-right">$<?= number_format($d->precio_unitario, 2) ?></td>
                <td class="text-right">$<?= number_format($d->subtotal, 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Totales -->
    <div class="totals">
        <div class="total-line">
            SUBTOTAL TOTAL: &nbsp;&nbsp; $<?= number_format($consignacion->subtotal, 2) ?>
        </div>
    </div>

    <!-- Observaciones -->
    <?php if ($consignacion->observaciones): ?>
    <div class="observaciones">
        <label>Observaciones:</label>
        <p style="margin-top:4px;"><?= esc($consignacion->observaciones) ?></p>
    </div>
    <?php endif; ?>

    <!-- Firmas -->
    <div class="firmas">
        <div class="firma-box">
            Entregado por<br>
            <br><br>
            <strong><?= esc(setting('company_name') ?? 'Empresa') ?></strong>
        </div>
        <div class="firma-box">
            Recibido por<br>
            <br><br>
            <strong><?= esc($consignacion->vendedor_nombre) ?></strong>
        </div>
    </div>

</body>
</html>
