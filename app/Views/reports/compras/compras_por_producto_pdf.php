<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
body { font-family: DejaVu Sans; font-size: 8.5px; color: #333; }
h3 { margin-bottom: 4px; font-size: 11px; }
.header-info { margin-bottom: 8px; font-size: 8px; }
table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
th, td { border: 0.5px solid #bbb; padding: 3px 4px; }
th { background: #1f4e79; color: #fff; font-weight: bold; }
.text-right  { text-align: right; }
.text-center { text-align: center; }

/* Cabecera de producto */
.prod-header td {
    background: #2e75b6;
    color: #fff;
    font-weight: bold;
    font-size: 8.5px;
    border-color: #1f4e79;
    padding: 4px 5px;
}

/* Filas de detalle de documentos */
.doc-row td { background: #f9f9f9; }
.doc-row:nth-child(even) td { background: #eef4fb; }

/* Subtotal por producto */
.subtotal td {
    background: #deeaf1;
    font-weight: bold;
    border-top: 1.5px solid #2e75b6;
}

/* Gran total */
.gran-total td {
    background: #e2efda;
    font-weight: bold;
    border-top: 2px solid #548235;
    font-size: 9px;
}

@page { margin-top: 55px; margin-bottom: 50px; margin-left: 30px; margin-right: 30px; }
</style>
</head>
<body>

<h3>COMPRAS POR PRODUCTO — CON DETALLE DE DOCUMENTOS</h3>

<div class="header-info">
    <strong>Período:</strong> <?= date('d/m/Y', strtotime($desde)) ?> al <?= date('d/m/Y', strtotime($hasta)) ?>
    &nbsp;&nbsp;
    <?php if (!empty($proveedor)): ?>
        <strong>Proveedor:</strong> <?= esc($proveedor->nombre) ?> &nbsp;&nbsp;
    <?php endif; ?>
    <strong>Generado:</strong> <?= esc($generado_en) ?>
</div>

<?php
$siglas = [
    '01' => 'FAC', '03' => 'CCF', '05' => 'NC',
    '06' => 'ND',  '07' => 'CR',  '08' => 'LC',
    '09' => 'DL',  '11' => 'CF',  '14' => 'FSE',
];

$gtCant = 0; $gtBase = 0; $gtIva = 0; $gtTotal = 0;
?>

<table>
    <thead>
        <tr>
            <th width="15%" class="text-center">Documento</th>
            <th width="10%" class="text-center">Fecha</th>
            <th width="20%">Proveedor</th>
            <th width="8%"  class="text-right">Cant.</th>
            <th width="10%" class="text-right">P. Unit.</th>
            <th width="11%" class="text-right">Base S/IVA</th>
            <th width="13%"  class="text-right">IVA 13%</th>
            <th width="13%"  class="text-right">Total</th>
        </tr>
    </thead>

    <?php foreach ($reporte as $prod): ?>
        <?php
        $subBase  = $prod['base'];
        $subIva   = round($subBase * 0.13, 2);
        $subTotal = $subBase + $subIva;
        $subCant  = $prod['cantidad'];
        $gtCant  += $subCant;
        $gtBase  += $subBase;
        $gtIva   += $subIva;
        $gtTotal += $subTotal;
        ?>

        <!-- Cabecera de producto -->
        <tbody>
            <tr class="prod-header">
                <td colspan="8">
                    <?= esc($prod['codigo']) ?> — <?= esc($prod['descripcion']) ?>
                </td>
            </tr>
        </tbody>

        <!-- Filas de documentos -->
        <tbody>
            <?php foreach ($prod['documentos'] as $doc): ?>
                <?php
                $sigla       = $siglas[$doc['tipo_dte']] ?? $doc['tipo_dte'];
                $correlativo = str_pad(substr($doc['numero_control'], -6), 6, '0', STR_PAD_LEFT);
                $docLabel    = $sigla . ' ' . $correlativo;
                $dBase       = $doc['base'];
                $dIva        = round($dBase * 0.13, 2);
                $dTotal      = $dBase + $dIva;
                ?>
                <tr class="doc-row">
                    <td class="text-center"><?= esc($docLabel) ?></td>
                    <td class="text-center"><?= date('d/m/Y', strtotime($doc['fecha_emision'])) ?></td>
                    <td><?= esc($doc['proveedor']) ?></td>
                    <td class="text-right"><?= number_format($doc['cantidad'], 2) ?></td>
                    <td class="text-right">$ <?= number_format($doc['precio_unitario'], 4) ?></td>
                    <td class="text-right">$ <?= number_format($dBase,  2) ?></td>
                    <td class="text-right">$ <?= number_format($dIva,   2) ?></td>
                    <td class="text-right">$ <?= number_format($dTotal, 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>

        <!-- Subtotal del producto -->
        <tbody>
            <tr class="subtotal">
                <td width="15%" style="border:none; background:#deeaf1;"></td>
                <td width="10%" style="border:none; background:#deeaf1;"></td>
                <td width="20%" style="border-left:none; border-right:none; background:#deeaf1;">
                    Subtotal &mdash; <?= esc($prod['descripcion']) ?>
                </td>
                <td width="8%"  class="text-right">
                    <?= number_format($subCant, 2) ?>
                </td>
                <td width="10%" style="border:none; background:#deeaf1;"></td>
                <td width="11%" class="text-right">$ <?= number_format($subBase,  2) ?></td>
                <td width="13%"  class="text-right">$ <?= number_format($subIva,   2) ?></td>
                <td width="13%"  class="text-right">$ <?= number_format($subTotal, 2) ?></td>
            </tr>
        </tbody>

    <?php endforeach; ?>

    <!-- Gran total -->
    <tbody>
        <tr class="gran-total">
            <td width="15%" style="border:none; background:#e2efda;"></td>
            <td width="10%" style="border:none; background:#e2efda;"></td>
            <td width="20%" style="border-left:none; border-right:none; background:#e2efda; border-top:2px solid #548235;">GRAN TOTAL</td>
            <td width="8%"  class="text-right" style="border-top:2px solid #548235;"><?= number_format($gtCant, 2) ?></td>
            <td width="10%" style="border:none; background:#e2efda;"></td>
            <td width="11%" class="text-right" style="border-top:2px solid #548235;">$ <?= number_format($gtBase,  2) ?></td>
            <td width="13%"  class="text-right" style="border-top:2px solid #548235;">$ <?= number_format($gtIva,   2) ?></td>
            <td width="13%"  class="text-right" style="border-top:2px solid #548235;">$ <?= number_format($gtTotal, 2) ?></td>
        </tr>
    </tbody>

</table>

</body>
</html>
