<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nota de Envío <?= esc($consignacion->numero) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #000;
            background: #fff;
        }

        /* ── Simulación de hoja en pantalla ─────── */
        @media screen {
            body { background: #909090; padding: 16px; }
            .page-wrapper {
                background: #fff;
                width: 215.9mm;
                min-height: 279.4mm;
                margin: 0 auto;
                padding: 8mm 12mm;
                box-shadow: 0 4px 16px rgba(0,0,0,.45);
            }
        }

        /* ── Cada copia = exactamente mitad de la hoja ── */
        /* letter - 2×8mm margen = 263.4mm ÷ 2 copias - 3mm separador ≈ 130mm cada una */
        .copia {
            height: 130mm;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* ── Separador de corte ──────────────────── */
        .separator {
            height: 3mm;
            display: flex;
            align-items: center;
            gap: 5px;
            color: #777;
            font-size: 8px;
        }
        .separator::before,
        .separator::after { content: ''; flex: 1; border-top: 1.5px dashed #aaa; }

        /* ── Etiqueta de tipo de copia ───────────── */
        .tipo-copia {
            flex-shrink: 0;
            text-align: center;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            color: #555;
            letter-spacing: .9px;
            border: 1px dashed #ccc;
            padding: 1.5px 0;
            margin-bottom: 3px;
        }

        /* ── Header: empresa | NE | estados ─────── */
        .hdr {
            flex-shrink: 0;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8px;
            border-bottom: 1.5px solid #000;
            padding-bottom: 4px;
            margin-bottom: 5px;
        }
        .hdr-empresa { font-size: 12px; font-weight: bold; line-height: 1.3; }
        .hdr-empresa small {
            display: block;
            font-size: 8.5px;
            font-weight: normal;
            color: #555;
            margin-top: 1px;
        }
        .hdr-ne { text-align: center; flex-shrink: 0; }
        .hdr-ne .titulo { font-size: 7.5px; text-transform: uppercase; letter-spacing: .5px; color: #555; }
        .hdr-ne .numero {
            font-size: 15px;
            font-weight: bold;
            border: 1.5px solid #000;
            padding: 2px 10px;
            display: inline-block;
            margin-top: 2px;
        }
        .hdr-meta { text-align: right; font-size: 8.5px; color: #555; flex-shrink: 0; }
        .hdr-meta .badges {
            margin-top: 3px;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 2px;
        }

        /* ── Badges ──────────────────────────────── */
        .badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
            border: 1px solid currentColor;
        }
        .b-abierta   { color: #155724; background: #d4edda; }
        .b-cerrada   { color: #383d41; background: #e2e3e5; }
        .b-anulada   { color: #721c24; background: #f8d7da; }
        .b-aprobada  { color: #155724; background: #d4edda; }
        .b-rechazada { color: #721c24; background: #f8d7da; }
        .b-pendiente { color: #856404; background: #fff3cd; }

        /* ── Info grid ───────────────────────────── */
        .info-grid {
            flex-shrink: 0;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            border: 1px solid #ccc;
            margin-bottom: 5px;
        }
        .info-item { padding: 3px 6px; border-right: 1px solid #ddd; }
        .info-item:nth-child(3n) { border-right: none; }
        .info-item.row2 { border-top: 1px solid #ddd; }
        .info-item label {
            display: block;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 1px;
        }
        .info-item span { font-size: 9.5px; }

        /* ── Tabla de productos ──────────────────── */
        .tabla-wrap { flex-shrink: 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        thead th {
            background: #2c2c2c;
            color: #fff;
            padding: 2px 5px;
            font-size: 8px;
            text-align: left;
        }
        tbody td { border-bottom: 1px solid #e8e8e8; padding: 2px 5px; font-size: 9px; }
        tbody tr:nth-child(even) { background: #f7f7f7; }
        .fila-lotes td { background: #f3f6fa !important; border-bottom: 1px solid #dde4ee; padding: 2px 5px 4px 10px !important; }
        .lote-tag {
            display: inline-block;
            background: #fff;
            border: 1px solid #c5d0e0;
            border-radius: 3px;
            padding: 1px 5px;
            font-size: 7.5px;
            margin-right: 3px;
            margin-bottom: 1px;
            color: #333;
        }
        .lote-tag strong { color: #1a4a8a; }
        .tar { text-align: right; }
        .tac { text-align: center; }

        /* ── Total ───────────────────────────────── */
        .totales {
            flex-shrink: 0;
            text-align: right;
            border-top: 1.5px solid #000;
            padding-top: 3px;
            margin-bottom: 4px;
            font-size: 10.5px;
            font-weight: bold;
        }

        /* ── Observaciones ───────────────────────── */
        .obs-box {
            flex-shrink: 0;
            border: 1px solid #ccc;
            padding: 2px 6px;
            margin-bottom: 4px;
            font-size: 8.5px;
        }
        .obs-box label { font-weight: bold; text-transform: uppercase; font-size: 7px; color: #555; }

        /* ── Firmas ──────────────────────────────── */
        /* margin-top: auto empuja las firmas al fondo de los 130mm */
        .firmas {
            margin-top: auto;
            flex-shrink: 0;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            padding-top: 3px;
        }
        .firma-box {
            border-top: 1.5px solid #000;
            padding-top: 4px;
            text-align: center;
            font-size: 9px;
            display: flex;
            flex-direction: column;
        }
        /* espacio vertical para la firma — más del doble del anterior (~7 mm) */
        .firma-espacio { height: 18mm; }
        .firma-nombre { padding-top: 2px; }

        /* ── Botón imprimir (solo pantalla) ──────── */
        @page { size: letter portrait; margin: 8mm 12mm; }

        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .page-wrapper { padding: 0; box-shadow: none; }
        }
    </style>
</head>
<body>

<!-- Barra de acciones (no se imprime) -->
<div class="no-print" style="background:#555; padding:8px 16px; text-align:right; margin-bottom:12px;">
    <button onclick="window.print()"
        style="padding:7px 20px; background:#fff; color:#333; border:none; cursor:pointer; border-radius:4px; font-weight:bold;">
        🖨 Imprimir
    </button>
    <button onclick="window.close()"
        style="padding:7px 18px; background:#888; color:#fff; border:none; cursor:pointer; border-radius:4px; margin-left:8px;">
        Cerrar
    </button>
</div>

<?php
$nombreEmpresa = setting('company_name') ?? 'HomeCare';

$est      = $consignacion->estado ?? 'abierta';
$estClass = match($est) { 'cerrada' => 'b-cerrada', 'anulada' => 'b-anulada', default => 'b-abierta' };
$estLabel = match($est) { 'cerrada' => 'Cerrada',   'anulada' => 'Anulada',   default => 'Activa'    };

$apEst    = $consignacion->aprobacion_estado ?? 'pendiente';
$apClass  = match($apEst) { 'aprobada' => 'b-aprobada', 'rechazada' => 'b-rechazada', default => 'b-pendiente' };
$apLabel  = match($apEst) { 'aprobada' => 'Aprobada',   'rechazada' => 'Rechazada',   default => 'Pend. Aprobación' };

$tieneDoctor   = !empty($consignacion->doctor_nombre);
$tieneCliente  = !empty($consignacion->cliente_nombre);
$tieneConcepto = !empty($consignacion->concepto);
$tieneRow2     = $tieneDoctor || $tieneCliente || $tieneConcepto;

$copias = ['Original – Empresa', 'Copia – Recepción'];
?>

<div class="page-wrapper">

<?php foreach ($copias as $idx => $tipoCopia): ?>

    <?php if ($idx > 0): ?>
        <div class="separator">&#9986;&nbsp;CORTAR</div>
    <?php endif; ?>

    <div class="copia">

        <!-- Etiqueta de copia -->
        <div class="tipo-copia"><?= $tipoCopia ?></div>

        <!-- Header: empresa | NE número | fecha y estados -->
        <div class="hdr">
            <div class="hdr-empresa">
                <?= esc($nombreEmpresa) ?>
                <small>Nota de Envío / Consignación</small>
            </div>
            <div class="hdr-ne">
                <div class="titulo">Nota de Envío</div>
                <div class="numero"><?= esc($consignacion->numero) ?></div>
            </div>
            <div class="hdr-meta">
                Generada: <?= date('d/m/Y H:i', strtotime($consignacion->fecha_generacion)) ?>
                <div class="badges">
                    <span class="badge <?= $estClass ?>"><?= $estLabel ?></span>
                    <span class="badge <?= $apClass ?>"><?= $apLabel ?></span>
                </div>
            </div>
        </div>

        <!-- Info: fila 1 siempre; fila 2 si hay doctor/cliente/concepto -->
        <div class="info-grid">
            <div class="info-item">
                <label>Vendedor / Representante</label>
                <span><?= esc($consignacion->vendedor_nombre) ?></span>
            </div>
            <div class="info-item">
                <label>Paciente</label>
                <span><?= esc($consignacion->nombre ?: '—') ?></span>
            </div>
            <div class="info-item">
                <label>Fecha</label>
                <span><?= date('d/m/Y', strtotime($consignacion->fecha)) ?><?= $consignacion->hora ? ' · ' . substr($consignacion->hora, 0, 5) : '' ?></span>
            </div>
            <?php if ($tieneRow2): ?>
                <div class="info-item row2">
                    <label>Doctor</label>
                    <span><?= $tieneDoctor ? esc($consignacion->doctor_nombre) : '—' ?></span>
                </div>
                <div class="info-item row2">
                    <label>Cliente a facturar</label>
                    <span><?= $tieneCliente ? esc($consignacion->cliente_nombre) : '—' ?></span>
                </div>
                <div class="info-item row2">
                    <label>Concepto</label>
                    <span><?= $tieneConcepto ? esc($consignacion->concepto) : '—' ?></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tabla de productos -->
        <div class="tabla-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width:65px">Código</th>
                        <th>Descripción</th>
                        <th class="tac" style="width:52px">Cant.</th>
                        <th class="tar" style="width:76px">P. Unit.</th>
                        <th class="tar" style="width:76px">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detalles as $d):
                        $lotes = $lotesPorDetalle[$d->id] ?? [];
                    ?>
                    <tr>
                        <td><?= esc($d->producto_codigo) ?></td>
                        <td><?= esc($d->producto_nombre) ?></td>
                        <td class="tac"><?= number_format($d->cantidad, 2) ?></td>
                        <td class="tar">$<?= number_format($d->precio_unitario, 2) ?></td>
                        <td class="tar">$<?= number_format($d->subtotal, 2) ?></td>
                    </tr>
                    <?php if (!empty($lotes)): ?>
                    <tr class="fila-lotes">
                        <td colspan="5">
                            <span style="font-size:7px; font-weight:bold; text-transform:uppercase; color:#666; margin-right:4px;">Lotes:</span>
                            <?php foreach ($lotes as $lote): ?>
                                <span class="lote-tag">
                                    <strong><?= esc($lote->numero_lote) ?></strong>
                                    <?php if (!empty($lote->fecha_vencimiento)): ?>· Vence: <?= esc($lote->fecha_vencimiento) ?><?php endif; ?>
                                    <?php if (!empty($lote->manufactura)): ?>· Mfr: <?= esc($lote->manufactura) ?><?php endif; ?>
                                    · Cant: <?= number_format($lote->cantidad, 2) ?>
                                </span>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="totales">
                SUBTOTAL:&nbsp;&nbsp;$<?= number_format($consignacion->subtotal, 2) ?>
            </div>

            <?php if ($consignacion->observaciones): ?>
            <div class="obs-box">
                <label>Obs.: </label><?= esc($consignacion->observaciones) ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Firmas — pegadas al fondo de la copia via margin-top: auto -->
        <div class="firmas">
            <div class="firma-box">
                <div>Entregado por</div>
                <div class="firma-nombre"><strong><?= esc($user['user_name']) ?></strong></div>
            </div>
            <div class="firma-box">
                <div>Recibido por</div>
                <div class="firma-nombre"><strong><?= esc($consignacion->vendedor_nombre) ?></strong></div>
            </div>
            <br>
        </div>

    </div><!-- /.copia -->

<?php endforeach; ?>

</div><!-- /.page-wrapper -->

</body>
</html>
