<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
body { font-family: DejaVu Sans; font-size: 10px; color: #333; }
h3   { margin-bottom: 5px; }
.header-info { margin-bottom: 12px; font-size: 9px; color: #555; }

/* Tabla principal */
table.main { width: 100%; border-collapse: collapse; }
table.main th,
table.main td { border: 0.5px solid #999; padding: 5px; vertical-align: top; }
table.main th { background: #1f4e79; color: #fff; font-weight: bold; text-align: center; }

/* Tabla interior de documentos */
table.docs { width: 100%; border-collapse: collapse; font-size: 8.5px; }
table.docs td { padding: 2px 4px; border: none; border-bottom: 0.3px solid #ddd; }
table.docs tr:last-child td { border-bottom: none; }
table.docs .doc-sigla { font-weight: bold; color: #1f4e79; white-space: nowrap; }
table.docs .doc-corr  { color: #444; white-space: nowrap; }
table.docs .doc-fecha { text-align: center; white-space: nowrap; }
table.docs .doc-monto { text-align: right; white-space: nowrap; }

/* Utilidades */
.text-right  { text-align: right; }
.text-center { text-align: center; }
.total-row   { background: #e2efda; font-weight: bold; }
.no-docs     { color: #aaa; font-style: italic; font-size: 8px; padding: 5px; display: block; }

@page { margin-top: 55px; margin-bottom: 60px; margin-left: 30px; margin-right: 30px; }
</style>
</head>
<body>
<h3>Reporte de Pagos Recibidos</h3>
<div class="header-info">
    Período: <?= esc($filtros['fecha_inicio']) ?> al <?= esc($filtros['fecha_fin']) ?>
    &nbsp;&nbsp;&bull;&nbsp;&nbsp;
    Generado: <?= esc($generado_en) ?>
</div>

<table class="main">
    <thead>
        <tr>
            <th style="width:8%">N° Recupero</th>
            <th style="width:16%">Cliente</th>
            <th style="width:8%">Fecha Pago</th>
            <th style="width:10%">Forma de Pago</th>
            <th style="width:26%">Documentos Aplicados</th>
            <th style="width:6%">Total</th>
            <th style="width:26%">Observaciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pagos as $p): ?>
        <tr>
            <td><?= esc($p['numero_recupero']) ?></td>
            <td><?= esc($p['cliente_nombre']) ?></td>
            <td class="text-center"><?= date('d/m/Y', strtotime($p['fecha_pago'])) ?></td>
            <td><?= esc($p['forma_pago']) ?></td>
            <td style="padding: 0;">
                <?php if (empty($p['docs'])): ?>
                    <span class="no-docs">Sin documentos</span>
                <?php else: ?>
                <table class="docs">
                    <tbody>
                        <?php foreach ($p['docs'] as $d): ?>
                        <tr>
                            <td class="doc-sigla"><?= esc($d['sigla']) ?></td>
                            <td class="doc-corr"><?= esc($d['correlativo']) ?></td>
                            <td class="doc-fecha"><?= date('d/m/Y', strtotime($d['fecha_emision'])) ?></td>
                            <td class="doc-monto">$<?= number_format($d['monto_aplicado'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </td>
            <td class="text-right">$<?= number_format($p['total'], 2) ?></td>
            <td><?= esc($p['observaciones']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($pagos)): ?>
        <tr>
            <td colspan="7" class="text-center" style="color:#888; padding:12px;">
                No hay pagos para el período seleccionado.
            </td>
        </tr>
        <?php endif; ?>
    </tbody>
    <tfoot>
        <tr class="total-row">
            <td colspan="5" class="text-right">TOTAL GENERAL:</td>
            <td class="text-right">$<?= number_format($total, 2) ?></td>
            <td></td>
        </tr>
    </tfoot>
</table>
</body>
</html>
