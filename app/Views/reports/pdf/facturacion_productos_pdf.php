<?php
$labelEstado = match ($estadoLinea ?? '') {
    'vigente' => 'Solo vigentes',
    'anulada' => 'Solo anuladas',
    'con_nc'  => 'Solo con nota de crédito',
    default   => 'Todos',
};
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 8px;
            color: #333;
            margin: 5px;
        }

        h3 {
            margin-bottom: 3px;
            font-size: 10px;
        }

        .header-info {
            margin-bottom: 6px;
            font-size: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }

        th,
        td {
            border: 0.4px solid #999;
            padding: 2px 3px;
            font-size: <?= $resumir ? '8px' : '7px' ?>;
        }

        th {
            background: #1f4e79;
            color: #fff;
            font-weight: bold;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .totales {
            background: #e2efda;
            font-weight: bold;
        }

        .totales td {
            background: #e2efda;
            border-top: 1.5px solid #548235;
        }

        .anulada {
            color: #888;
            background: #f5f5f5;
        }

        .con-nc {
            background: #fff8e1;
        }

        .leyenda {
            margin-top: 6px;
            font-size: 7px;
            color: #555;
        }
    </style>
</head>

<body>

    <h3>
        REPORTE DE FACTURACIÓN POR PRODUCTO - <?= $resumir ? 'RESUMEN' : 'DETALLE' ?>
        <?= !empty($vendedorNombre) ? ' — ' . esc($vendedorNombre) : '' ?>
    </h3>

    <div class="header-info">
        <strong>Desde:</strong> <?= date('d/m/Y', strtotime($fechaDesde)) ?>
        &nbsp;&nbsp;&nbsp;
        <strong>Hasta:</strong> <?= date('d/m/Y', strtotime($fechaHasta)) ?>
        &nbsp;&nbsp;&nbsp;
        <strong>Estado:</strong> <?= esc($labelEstado) ?>
        &nbsp;&nbsp;&nbsp;
        <strong>Comisión:</strong> <?= number_format($comision, 1) ?>%
        &nbsp;&nbsp;&nbsp;
        <strong>Generado:</strong> <?= esc($generado_en) ?>
    </div>

    <?php if (empty($lineas)): ?>

        <p>No se encontraron líneas facturadas para los filtros seleccionados.</p>

    <?php else: ?>

        <table>
            <thead>
                <tr>
                    <?php if ($resumir): ?>
                        <th width="12%">Cód. Producto</th>
                        <th width="38%">Descripción</th>
                        <th width="10%" class="text-right">Cant. Vendida</th>
                        <th width="8%" class="text-right">Acred. NC</th>
                        <th width="8%" class="text-right">Cant. Neta</th>
                        <th width="8%" class="text-right">Precio Prom.</th>
                        <th width="8%" class="text-right">Monto s/IVA</th>
                        <th width="8%" class="text-right">Comisión <?= number_format($comision, 1) ?>%</th>
                    <?php else: ?>
                        <th width="5%">Fecha</th>
                        <th width="3%">Doc</th>
                        <th width="4%">Nº Doc</th>
                        <th width="14%">Cliente</th>
                        <th width="9%">Vendedor</th>
                        <th width="7%">Cód. Prod.</th>
                        <th width="17%">Descripción</th>
                        <th width="5%" class="text-right">Cant.</th>
                        <th width="4%" class="text-right">NC</th>
                        <th width="6%" class="text-right">Monto s/IVA</th>
                        <th width="6%" class="text-right">Comisión</th>
                        <th width="6%">Estado</th>
                        <th width="4%">Nº NE</th>
                        <th width="4%">Nº Ped.</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>

                <?php foreach ($lineas as $l):
                    $monto  = $l->cantidad_neta * (float) $l->precio_factura;
                    $comVal = $monto * ($comision / 100);
                ?>

                    <?php if ($resumir): ?>

                        <tr>
                            <td><?= esc($l->producto_codigo) ?></td>
                            <td><?= esc($l->producto_descripcion) ?></td>
                            <td class="text-right"><?= number_format((float) $l->cantidad_facturada, 2) ?></td>
                            <td class="text-right"><?= (float) $l->cantidad_acreditada_nc > 0 ? number_format((float) $l->cantidad_acreditada_nc, 2) : '' ?></td>
                            <td class="text-right"><?= number_format((float) $l->cantidad_neta, 2) ?></td>
                            <td class="text-right">$ <?= number_format((float) $l->precio_factura, 2) ?></td>
                            <td class="text-right">$ <?= number_format($monto, 2) ?></td>
                            <td class="text-right">$ <?= number_format($comVal, 2) ?></td>
                        </tr>

                    <?php else:
                        $rowClass = match ($l->estado_factura) {
                            'anulada' => 'anulada',
                            'con_nc'  => 'con-nc',
                            default   => '',
                        };
                        $estadoTexto = match ($l->estado_factura) {
                            'anulada' => 'ANULADA',
                            'con_nc'  => ($l->nc_total ?? false) ? 'Con NC (total)' : 'Con NC (parcial)',
                            default   => 'Vigente',
                        };
                    ?>

                        <tr class="<?= $rowClass ?>">
                            <td><?= date('d/m/Y', strtotime($l->fecha_emision)) ?></td>
                            <td><?= esc($siglas[$l->tipo_dte] ?? $l->tipo_dte) ?></td>
                            <td><?= esc(str_pad(substr($l->numero_control, -6), 6, '0', STR_PAD_LEFT)) ?></td>
                            <td><?= esc($l->cliente_nombre ?? '') ?></td>
                            <td><?= esc($l->vendedor_nombre ?? '') ?></td>
                            <td><?= esc($l->producto_codigo) ?></td>
                            <td><?= esc($l->producto_descripcion) ?></td>
                            <td class="text-right"><?= number_format((float) $l->cantidad_facturada, 2) ?></td>
                            <td class="text-right"><?= (float) $l->cantidad_acreditada_nc > 0 ? number_format((float) $l->cantidad_acreditada_nc, 2) : '' ?></td>
                            <td class="text-right">$ <?= number_format($monto, 2) ?></td>
                            <td class="text-right">$ <?= number_format($comVal, 2) ?></td>
                            <td><?= esc($estadoTexto) ?></td>
                            <td><?= esc($l->numero_ne ?? '') ?></td>
                            <td><?= esc($l->numero_pedido ?? '') ?></td>
                        </tr>

                    <?php endif; ?>

                <?php endforeach; ?>

            </tbody>
            <tfoot>
                <tr class="totales">
                    <?php if ($resumir): ?>
                        <td colspan="6">TOTAL (<?= count($lineas) ?> productos)</td>
                    <?php else: ?>
                        <td colspan="9">TOTAL (<?= count($lineas) ?> líneas)</td>
                    <?php endif; ?>
                    <td class="text-right">$ <?= number_format($totalPrecio, 2) ?></td>
                    <td class="text-right">$ <?= number_format($totalComision, 2) ?></td>
                    <?php if (!$resumir): ?>
                        <td colspan="3"></td>
                    <?php endif; ?>
                </tr>
            </tfoot>
        </table>

        <?php if (!$resumir): ?>
            <div class="leyenda">
                Montos calculados sobre cantidad neta (restando lo acreditado por nota de crédito). Las líneas anuladas no suman.
            </div>
        <?php endif; ?>

    <?php endif; ?>

</body>

</html>
