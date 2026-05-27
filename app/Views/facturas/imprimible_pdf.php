<?php helper('dte'); ?>
<?php
if (!function_exists('_facturaPdfNumeroLetras')) {
    function _facturaPdfNumeroLetras(int $n): string
    {
        $unidades = [
            '',
            'uno',
            'dos',
            'tres',
            'cuatro',
            'cinco',
            'seis',
            'siete',
            'ocho',
            'nueve',
            'diez',
            'once',
            'doce',
            'trece',
            'catorce',
            'quince',
            'dieciseis',
            'diecisiete',
            'dieciocho',
            'diecinueve',
            'veinte',
            'veintiun',
            'veintidos',
            'veintitres',
            'veinticuatro',
            'veinticinco',
            'veintiseis',
            'veintisiete',
            'veintiocho',
            'veintinueve'
        ];
        $decenas = [
            '',
            '',
            'veinte',
            'treinta',
            'cuarenta',
            'cincuenta',
            'sesenta',
            'setenta',
            'ochenta',
            'noventa'
        ];
        $centenas = [
            '',
            'ciento',
            'doscientos',
            'trescientos',
            'cuatrocientos',
            'quinientos',
            'seiscientos',
            'setecientos',
            'ochocientos',
            'novecientos'
        ];

        if ($n === 0) {
            return 'cero';
        }

        if ($n < 30) {
            return $unidades[$n];
        }

        if ($n < 100) {
            $resto = $n % 10;
            return $resto === 0 ? $decenas[intdiv($n, 10)] : $decenas[intdiv($n, 10)] . ' y ' . $unidades[$resto];
        }

        if ($n === 100) {
            return 'cien';
        }

        if ($n < 1000) {
            $resto = $n % 100;
            return $resto === 0 ? $centenas[intdiv($n, 100)] : $centenas[intdiv($n, 100)] . ' ' . _facturaPdfNumeroLetras($resto);
        }

        if ($n < 2000) {
            $resto = $n % 1000;
            return 'mil' . ($resto === 0 ? '' : ' ' . _facturaPdfNumeroLetras($resto));
        }

        if ($n < 1000000) {
            $miles = intdiv($n, 1000);
            $resto = $n % 1000;
            return _facturaPdfNumeroLetras($miles) . ' mil' . ($resto === 0 ? '' : ' ' . _facturaPdfNumeroLetras($resto));
        }

        if ($n < 2000000) {
            $resto = $n % 1000000;
            return 'un millon' . ($resto === 0 ? '' : ' ' . _facturaPdfNumeroLetras($resto));
        }

        $millones = intdiv($n, 1000000);
        $resto = $n % 1000000;
        return _facturaPdfNumeroLetras($millones) . ' millones' . ($resto === 0 ? '' : ' ' . _facturaPdfNumeroLetras($resto));
    }

    function facturaPdfMontoLetras(float $monto): string
    {
        $entero = (int)floor(abs($monto));
        $centavos = (int)round((abs($monto) - $entero) * 100);

        if ($centavos === 100) {
            $entero++;
            $centavos = 0;
        }

        return ucfirst(_facturaPdfNumeroLetras($entero)) . ' ' . str_pad((string)$centavos, 2, '0', STR_PAD_LEFT) . '/100 dólares';
    }
}

$tipoDoc = dte_descripciones()[dte_siglas()[$factura->tipo_dte] ?? ''] ?? 'Documento Tributario Electronico';
$numeroCorto = !empty($factura->numero_control) ? substr($factura->numero_control, -6) : 'N/D';
$condicion = ((int)($factura->condicion_operacion ?? 1) === 1)
    ? 'Contado'
    : 'Credito ' . (int)($factura->plazo_credito ?? 0) . ' dias';
$subtotalGravada  = 0;
$subtotalExenta   = 0;
$subtotalNoSujeta = 0;
foreach ($detalles as $d) {
    $subtotalGravada  += (float)($d->venta_gravada   ?? 0);
    $subtotalExenta   += (float)($d->venta_exenta    ?? 0);
    $subtotalNoSujeta += (float)($d->venta_no_sujeta ?? 0);
}
$totalIva  = (float)($factura->total_iva  ?? 0);
$retencion = (float)($factura->iva_rete1  ?? 0);
$esCCF     = $factura->tipo_dte === '03';

$sumasBruto     = 0;
$descuentoTotal = 0;
foreach ($detalles as $d) {
    // Para Factura (01) venta_gravada se guarda como base sin IVA; sumamos iva_item para obtener el total real con IVA.
    // Para CCF (03) venta_gravada ya es la base sin IVA y es lo que queremos mostrar en Sumas.
    $ivaLinea = $esCCF ? 0.0 : (float)($d->iva_item ?? 0);
    $sumasBruto     += (float)($d->venta_gravada   ?? 0)
                     + $ivaLinea
                     + (float)($d->venta_exenta    ?? 0)
                     + (float)($d->venta_no_sujeta ?? 0)
                     + (float)($d->monto_descuento ?? 0);
    $descuentoTotal += (float)($d->monto_descuento ?? 0);
}
$sumasBruto     = round($sumasBruto,     2);
$descuentoTotal = round($descuentoTotal, 2);

if ($esCCF) {
    $subTotal = round($sumasBruto - $descuentoTotal + $totalIva, 2);
} else {
    $subTotal = round($sumasBruto - $descuentoTotal - $retencion, 2);
}

$anulada = (int)($factura->anulada ?? 0) === 1;
$montoLetras = facturaPdfMontoLetras((float)$factura->total_pagar);
$nombreComercial = trim((string)($emisor->nombre_comercial ?? ''));
$nombreLegal = trim((string)($emisor->nombre ?? ''));
$tituloEmpresa = $nombreComercial !== '' ? $nombreComercial : ($nombreLegal !== '' ? $nombreLegal : 'Homecare');
$mostrarNombreLegal = $nombreLegal !== '' && strtolower($nombreLegal) !== strtolower($tituloEmpresa);
$notasFactura = trim((string)($factura->notas ?? $factura->nota ?? $factura->observaciones ?? $factura->comentarios ?? ''));
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 24px 28px 34px;
        }

        body {
            color: #1f2933;
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            line-height: 1.35;
        }

        .top {
            border-bottom: 3px solid #174a7c;
            padding-bottom: 12px;
        }

        .brand {
            color: #174a7c;
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 3px;
            text-transform: uppercase;
        }

        .brand-table {
            width: 100%;
        }

        .brand-table td {
            border: 0;
            padding: 0;
            vertical-align: top;
        }

        .brand-logo-cell {
            width: 72px;
        }

        .brand-logo-cell img {
            max-height: 75px;
            max-width: 90px;
        }

        .brand-copy-cell {
            padding-left: 8px;
        }

        .company-line {
            margin-bottom: 2px;
        }

        .company-label {
            color: #425466;
            font-weight: 700;
        }

        .muted {
            color: #627282;
        }

        .small {
            font-size: 9px;
        }

        .doc-title {
            color: #174a7c;
            font-size: 14px;
            font-weight: 600;
            text-align: right;
            text-transform: uppercase;
        }

        .doc-number {
            border: 1px solid #cbd5df;
            border-radius: 4px;
            font-size: 10px;
            margin-top: 8px;
            padding: 7px 8px;
            text-align: right;
        }

        .doc-number-row {
            border-bottom: 1px solid #e0e6ed;
            padding: 0 0 5px;
            word-break: break-all;
        }

        .doc-number-row+.doc-number-row {
            padding-top: 5px;
        }

        .doc-number-row:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .layout td {
            border: 0;
            padding: 0;
            vertical-align: top;
        }

        .box {
            border: 1px solid #d5dde5;
            border-radius: 4px;
            padding: 10px;
        }

        .box-title {
            color: #174a7c;
            font-size: 10px;
            font-weight: 700;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .document-box-table {
            width: 100%;
        }

        .document-box-table td {
            border: 0;
            padding: 0;
            vertical-align: top;
        }

        .document-box-info {
            padding-right: 10px;
        }

        .document-box-qr {
            text-align: right;
            width: 82px;
        }

        .document-box-qr img {
            height: 1.3in;
            width: 1.3in;
        }

        .document-box-qr-label {
            color: #627282;
            font-size: 7px;
            line-height: 1.1;
            margin-top: 2px;
            text-align: center;
        }

        .grid {
            margin-top: 14px;
        }

        .grid td {
            padding: 0 8px 0 0;
        }

        .items {
            margin-top: 16px;
        }

        .items thead {
            display: table-header-group;
        }

        .items tbody {
            display: table-row-group;
        }

        .items tr {
            page-break-inside: avoid;
        }

        .items th {
            background: #174a7c;
            border: 1px solid #174a7c;
            color: #fff;
            font-size: 10px;
            padding: 7px 6px;
            text-align: left;
        }

        .items td {
            border: 1px solid #d5dde5;
            padding: 6px;
            vertical-align: top;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .totals {
            margin-top: 0;
            width: 100%;
        }

        .totals td {
            border-bottom: 1px solid #e0e6ed;
            padding: 5px 0;
        }

        .totals .grand td {
            border-bottom: 0;
            border-top: 1.5px solid #174a7c;
            color: #1f2933;
            font-size: 12px;
            font-weight: 700;
            padding-top: 7px;
        }

        .amount-words {
            background: #f7fafc;
            border: 1px solid #d5dde5;
            border-radius: 4px;
            font-size: 11px;
            padding: 8px 10px;
        }

        .notes-box {
            margin-top: 10px;
            page-break-inside: avoid;
        }

        .notes-label {
            color: #425466;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .04em;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .document-closing {
            page-break-inside: avoid;
        }

        .qr {
            border: 1px solid #d5dde5;
            border-radius: 4px;
            margin-top: 16px;
            padding: 10px;
        }

        .qr img {
            width: 92px;
            height: 92px;
        }

        .seal {
            background: #eef5fb;
            border: 1px solid #c8d9e8;
            border-radius: 4px;
            color: #174a7c;
            font-weight: 700;
            padding: 6px 8px;
            text-align: center;
        }

        .anulada {
            color: #b91c1c;
            font-size: 80px;
            font-weight: 600;
            left: 150px;
            opacity: .25;
            position: absolute;
            text-align: center;
            top: 360px;
            transform: rotate(-22deg);
            width: 430px;
        }
    </style>
</head>

<body>
    <?php if ($anulada): ?>
        <div class="anulada">DOCUMENTO ANULADO</div>
    <?php endif; ?>

    <table class="layout top">
        <tr>
            <td style="width:100%; padding-right:12px;">
                <table class="brand-table">
                    <?php if (!empty($logoDataUri)): ?>
                        <td class="brand-logo-cell">
                            <img src="<?= esc($logoDataUri) ?>" alt="Logo">
                        </td>
                    <?php endif; ?>
                    <tr>

                        <td class="brand-copy-cell">
                            <h1 class="brand"><?= esc($tituloEmpresa) ?></h1>
                            <div class="company-line muted">
                                <span class="company-label">NIT:</span> <?= esc($emisor->nit ?? 'N/D') ?>
                                &nbsp; <span class="company-label">NRC:</span> <?= esc($emisor->nrc ?? 'N/D') ?>
                            </div>
                            <div class="company-line muted">
                                <span class="company-label">Giro:</span> <?= esc($emisor->desc_actividad ?? 'N/D') ?>
                            </div>
                            <div class="company-line muted">
                                <span class="company-label">Direccion:</span> <?= esc($emisor->complemento ?? 'N/D') ?>
                            </div>
                            <div class="company-line muted">
                                <span class="company-label">Tel:</span> <?= esc($emisor->telefono ?? 'N/D') ?>
                                &nbsp; <span class="company-label">Correo:</span> <?= esc($emisor->correo ?? 'N/D') ?>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width:38%;">
                <div class="doc-title"><?= esc($tipoDoc) ?></div>
                <div class="doc-number">
                    <div class="doc-number-row"><strong>No. Control</strong><br><?= esc($factura->numero_control ?? 'N/D') ?></div>
                    <div class="doc-number-row"><strong>Cod. Generacion</strong><br><?= esc($factura->codigo_generacion ?? 'N/D') ?></div>
                    <div class="doc-number-row"><strong>Sello recepcion</strong><br><?= esc($factura->sello_recibido ?? 'N/D') ?></div>
                </div>
                <br>
            </td>
        </tr>
    </table>

    <table class="layout grid">
        <tr>
            <td style="width:58%;">
                <div class="box">
                    <div class="box-title">Receptor</div>
                    <strong><?= esc($factura->cliente ?? 'Cliente no registrado') ?></strong><br>
                    Documento: <?= esc($factura->cliente_documento ?? 'N/D') ?><br>
                    NRC: <?= esc($factura->cliente_nrc ?? 'N/D') ?><br>
                    Correo: <?= esc($factura->cliente_correo ?? 'N/D') ?><br>
                    Direccion: <?= esc($factura->cliente_direccion ?? 'N/D') ?>
                </div>
            </td>
            <td style="width:42%; padding-right:0;">
                <div class="box">
                    <table class="document-box-table">
                        <tr>
                            <td class="document-box-info">
                                <div class="box-title">Documento</div>
                                Fecha y hora:
                                <?= !empty($factura->fecha_emision) ? date('d/m/Y', strtotime($factura->fecha_emision)) : 'N/D' ?>
                                <?= !empty($factura->hora_emision) ? esc($factura->hora_emision) : '' ?><br>
                                Condicion: <?= esc($condicion) ?><br>
                                <strong>Correlativo: <?= esc($numeroCorto) ?></strong>
                            </td>
                            <?php if (!empty($qrHaciendaDataUri)): ?>
                                <td class="document-box-qr">
                                    <img src="<?= esc($qrHaciendaDataUri) ?>" alt="QR Hacienda">
                                    <div class="document-box-qr-label">Consulta MH</div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width:5%;" class="center">#</th>
                <th style="width:40%;">Descripcion</th>
                <th style="width:8%;" class="right">Cantidad</th>
                <th style="width:10%;" class="right">Precio</th>
                <th style="width:12%;" class="right">Ventas No Sujetas</th>
                <th style="width:12%;" class="right">Ventas Exentas</th>
                <th style="width:13%;" class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detalles as $d): ?>
                <?php $lineTotal = (float)$d->cantidad * (float)$d->precio_unitario; ?>
                <tr>
                    <td class="center"><?= esc($d->num_item) ?></td>
                    <td><?= nl2br(esc($d->descripcion)) ?></td>
                    <td class="right"><?= number_format((float)$d->cantidad, 0) ?></td>
                    <td class="right">$<?= number_format((float)$d->precio_unitario, 2) ?></td>
                    <td class="right">$<?= number_format((float)($d->venta_no_sujeta ?? 0), 2) ?></td>
                    <td class="right">$<?= number_format((float)($d->venta_exenta ?? 0), 2) ?></td>
                    <td class="right">$<?= number_format($lineTotal, 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="document-closing">
        <table class="layout" style="margin-top:14px; width:100%;">
            <tr>
                <!-- Izquierda: valor en letras + observaciones -->
                <td style="vertical-align:top; padding-right:16px;">
                    <div class="amount-words">
                        <strong>Son:</strong> <?= esc($montoLetras) ?>
                    </div>
                    <?php if ($notasFactura !== ''): ?>
                        <div class="notes-box">
                            <div class="notes-label">Observaciones</div>
                            <?= nl2br(esc($notasFactura)) ?>
                        </div>
                    <?php endif; ?>
                </td>
                <!-- Derecha: totales -->
                <td style="width:34%; vertical-align:top; padding-right:0;">
                    <table class="totals">
                        <tr>
                            <td>Sumas</td>
                            <td class="right">$<?= number_format($sumasBruto, 2) ?></td>
                        </tr>
                        <?php if ($descuentoTotal > 0): ?>
                            <tr>
                                <td>Descuento</td>
                                <td class="right">-$<?= number_format($descuentoTotal, 2) ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($esCCF): ?>
                            <tr>
                                <td>IVA 13%</td>
                                <td class="right">$<?= number_format($totalIva, 2) ?></td>
                            </tr>
                        <?php else: ?>
                            <?php if ($retencion > 0): ?>
                                <tr>
                                    <td>IVA Retenido</td>
                                    <td class="right">-$<?= number_format($retencion, 2) ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endif; ?>
                        <tr>
                            <td><strong>Sub-Total</strong></td>
                            <td class="right"><strong>$<?= number_format($subTotal, 2) ?></strong></td>
                        </tr>
                        <?php if ($esCCF && $retencion > 0): ?>
                            <tr>
                                <td>Retención IVA 1%</td>
                                <td class="right">-$<?= number_format($retencion, 2) ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($subtotalNoSujeta > 0): ?>
                            <tr>
                                <td>Ventas No Sujetas</td>
                                <td class="right">$<?= number_format($subtotalNoSujeta, 2) ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($subtotalExenta > 0): ?>
                            <tr>
                                <td>Ventas Exentas</td>
                                <td class="right">$<?= number_format($subtotalExenta, 2) ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr class="grand">
                            <td>Total</td>
                            <td class="right">$<?= number_format((float)$factura->total_pagar, 2) ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
