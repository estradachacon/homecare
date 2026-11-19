<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Facturación por Producto</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
        }

        .header {
            background: linear-gradient(135deg, #1f4e79 0%, #2d5a96 100%);
            color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .header h1 {
            font-size: 18pt;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 9pt;
            opacity: 0.9;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding: 10px;
            background: #f5f5f5;
            border-left: 4px solid #1f4e79;
        }

        .info-col {
            flex: 1;
        }

        .info-label {
            font-weight: bold;
            font-size: 9pt;
            color: #666;
            margin-bottom: 3px;
        }

        .info-value {
            font-size: 10pt;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        thead {
            background: #1f4e79;
            color: white;
        }

        th {
            padding: 8px;
            text-align: left;
            font-size: 9pt;
            font-weight: bold;
            border: 1px solid #1f4e79;
        }

        td {
            padding: 7px;
            border: 1px solid #ddd;
            font-size: 9pt;
        }

        tbody tr:nth-child(odd) {
            background: #f9f9f9;
        }

        tbody tr:hover {
            background: #f0f5ff;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .amount {
            font-weight: 500;
            text-align: right;
        }

        tfoot {
            background: #e2efda;
            font-weight: bold;
        }

        tfoot td {
            background: #e2efda;
            border-top: 2px solid #548235;
            padding: 10px 7px;
            font-weight: bold;
        }

        .row-anulada {
            background: #ffe8e8 !important;
        }

        .row-con-nc {
            background: #fff8e1 !important;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
        }

        .badge-success {
            background: #28a745;
            color: white;
        }

        .badge-danger {
            background: #dc3545;
            color: white;
        }

        .badge-warning {
            background: #ffc107;
            color: #333;
        }

        .badge-info {
            background: #17a2b8;
            color: white;
        }

        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 2px solid #1f4e79;
            text-align: center;
            font-size: 8pt;
            color: #999;
        }

        .summary-box {
            background: #1f4e79;
            color: white;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            display: flex;
            justify-content: space-around;
            font-size: 11pt;
        }

        .summary-item {
            text-align: center;
        }

        .summary-label {
            font-size: 9pt;
            opacity: 0.8;
            margin-bottom: 5px;
        }

        .summary-value {
            font-size: 14pt;
            font-weight: bold;
        }

        @page {
            size: A4 landscape;
            margin: 15mm 10mm 15mm 10mm;
            @bottom-center {
                content: "Página " counter(page) " de " counter(pages);
                font-size: 8pt;
            }
        }

        .page-break {
            page-break-after: always;
        }

        .legend {
            margin-top: 10px;
            padding: 10px;
            background: #f5f5f5;
            border-left: 4px solid #1f4e79;
            font-size: 8pt;
        }

        .legend-item {
            display: inline-block;
            margin-right: 20px;
            padding: 3px 8px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE FACTURACIÓN POR PRODUCTO</h1>
        <p><?= date('d/m/Y', strtotime($fechaDesde)) ?> al <?= date('d/m/Y', strtotime($fechaHasta)) ?></p>
    </div>

    <div class="info-row">
        <div class="info-col">
            <div class="info-label">Período</div>
            <div class="info-value"><?= date('d \d\e F \d\e Y', strtotime($fechaDesde)) ?> - <?= date('d \d\e F \d\e Y', strtotime($fechaHasta)) ?></div>
        </div>
        <div class="info-col">
            <div class="info-label">Comisión Aplicada</div>
            <div class="info-value"><?= number_format($comision, 1) ?>%</div>
        </div>
        <div class="info-col">
            <div class="info-label">Modo</div>
            <div class="info-value"><?= $resumir ? 'Resumen de Productos' : 'Detallado' ?></div>
        </div>
        <div class="info-col">
            <div class="info-label">Generado</div>
            <div class="info-value"><?= date('d/m/Y H:i:s') ?></div>
        </div>
    </div>

    <?php if (!empty($lineas)): ?>
        <div class="summary-box">
            <div class="summary-item">
                <div class="summary-label">Total en Ventas</div>
                <div class="summary-value">$ <?= number_format($totalPrecio, 2) ?></div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Comisión Total</div>
                <div class="summary-value">$ <?= number_format($totalComision, 2) ?></div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Líneas Incluidas</div>
                <div class="summary-value"><?= count($lineas) ?></div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <?php if ($resumir): ?>
                        <th>Código Producto</th>
                        <th>Descripción</th>
                        <th class="text-right">Cantidad Vendida</th>
                        <th class="text-right">Acreditado NC</th>
                        <th class="text-right">Cantidad Neta</th>
                        <th class="text-right">Precio Unit.</th>
                        <th class="text-right">Monto s/IVA</th>
                        <th class="text-right">Comisión <?= number_format($comision, 1) ?>%</th>
                    <?php else: ?>
                        <th>Fecha</th>
                        <th>Doc.</th>
                        <th>Nº Doc</th>
                        <th>Cliente</th>
                        <th>Vendedor</th>
                        <th>Cód. Producto</th>
                        <th>Descripción</th>
                        <th class="text-right">Cantidad</th>
                        <th class="text-right">NC</th>
                        <th class="text-right">Monto s/IVA</th>
                        <th class="text-right">Comisión</th>
                        <th class="text-center">Estado</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lineas as $l):
                    $monto  = $l->cantidad_neta * (float)$l->precio_factura;
                    $comVal = $monto * ($comision / 100);

                    if (!$resumir) {
                        $rowClass = match ($l->estado_factura) {
                            'anulada' => 'row-anulada',
                            'con_nc'  => 'row-con-nc',
                            default   => '',
                        };
                    } else {
                        $rowClass = '';
                    }
                ?>
                <tr class="<?= $rowClass ?>">
                    <?php if ($resumir): ?>
                        <td><strong><?= esc($l->producto_codigo) ?></strong></td>
                        <td><?= esc($l->producto_descripcion) ?></td>
                        <td class="text-right"><?= number_format((float)$l->cantidad_facturada, 2) ?></td>
                        <td class="text-right"><?= (float)$l->cantidad_acreditada_nc > 0 ? number_format((float)$l->cantidad_acreditada_nc, 2) : '—' ?></td>
                        <td class="text-right"><?= number_format((float)$l->cantidad_neta, 2) ?></td>
                        <td class="text-right">$ <?= number_format((float)$l->precio_factura, 2) ?></td>
                        <td class="amount">$ <?= number_format($monto, 2) ?></td>
                        <td class="amount">$ <?= number_format($comVal, 2) ?></td>
                    <?php else: ?>
                        <td><?= date('d/m/Y', strtotime($l->fecha_emision)) ?></td>
                        <td class="text-center"><span class="badge badge-info"><?= strtoupper(substr($l->numero_control, -6)) ?></span></td>
                        <td><?= esc(substr($l->numero_control, -6)) ?></td>
                        <td><?= esc($l->cliente_nombre ?? '—') ?></td>
                        <td><?= esc($l->vendedor_nombre ?? '—') ?></td>
                        <td><strong><?= esc($l->producto_codigo) ?></strong></td>
                        <td><?= esc($l->producto_descripcion) ?></td>
                        <td class="text-right"><?= number_format((float)$l->cantidad_facturada, 2) ?></td>
                        <td class="text-right"><?= (float)$l->cantidad_acreditada_nc > 0 ? number_format((float)$l->cantidad_acreditada_nc, 2) : '—' ?></td>
                        <td class="amount">$ <?= number_format($monto, 2) ?></td>
                        <td class="amount">$ <?= number_format($comVal, 2) ?></td>
                        <td class="text-center">
                            <?php if ($l->estado_factura === 'anulada'): ?>
                                <span class="badge badge-danger">Anulada</span>
                            <?php elseif ($l->estado_factura === 'con_nc'): ?>
                                <span class="badge badge-warning">Con NC</span>
                            <?php else: ?>
                                <span class="badge badge-success">Vigente</span>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <?php if ($resumir): ?>
                        <td colspan="6" class="text-right">TOTALES:</td>
                        <td class="amount">$ <?= number_format($totalPrecio, 2) ?></td>
                        <td class="amount">$ <?= number_format($totalComision, 2) ?></td>
                    <?php else: ?>
                        <td colspan="9" class="text-right">TOTALES:</td>
                        <td class="amount">$ <?= number_format($totalPrecio, 2) ?></td>
                        <td class="amount">$ <?= number_format($totalComision, 2) ?></td>
                        <td></td>
                    <?php endif; ?>
                </tr>
            </tfoot>
        </table>

        <div class="legend">
            <strong>Leyenda:</strong>
            <span class="legend-item"><span class="badge badge-success">Vigente</span> - Factura activa</span>
            <span class="legend-item"><span class="badge badge-warning">Con NC</span> - Con nota de crédito parcial/total</span>
            <span class="legend-item"><span class="badge badge-danger">Anulada</span> - Factura anulada</span>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; color: #999;">
            <p style="font-size: 12pt;">No hay datos para mostrar con los filtros seleccionados.</p>
        </div>
    <?php endif; ?>

    <div class="footer">
        <p>Reporte generado el <?= date('d/m/Y H:i:s') ?> • Sistema de Facturación</p>
    </div>
</body>
</html>
