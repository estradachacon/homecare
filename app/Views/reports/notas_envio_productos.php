<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>
<style>
/* ── Screen ───────────────────────────────────────── */
.ne-table th, .ne-table td { vertical-align: middle; }
.ne-table .badge { font-size: .68rem; }
.row-anulada td   { background: #fff0f0 !important; }
.row-facturado td { background: #f0fff4 !important; }
.row-cambiado td  { background: #fffbf0 !important; }
.row-devuelto td  { background: #f0f4ff !important; }

/* ── Print ────────────────────────────────────────── */
.print-header { display: none; }

@page {
    size: letter landscape;
    margin: 8mm 6mm 12mm 6mm;
    @bottom-right {
        content: "Pág. " counter(page) " / " counter(pages);
        font-family: Arial, Helvetica, sans-serif;
        font-size: 6.5pt; color: #555;
    }
    @bottom-left {
        content: "NE por Producto";
        font-family: Arial, Helvetica, sans-serif;
        font-size: 6.5pt; color: #888;
    }
}

@media print {
    .no-print, .card-header, nav, aside,
    .sidebar-wrapper, #sidebar-wrapper { display: none !important; }
    .print-header { display: block !important; }
    .card, .card-body { border: none !important; box-shadow: none !important; padding: 0 !important; margin: 0 !important; }
    .col-md-12, .row { padding: 0 !important; margin: 0 !important; }
    body { background: #fff !important; color: #000 !important; }
    * { font-family: Arial, Helvetica, sans-serif !important; }
    table { width: 100% !important; border-collapse: collapse !important; }
    thead { display: table-header-group; }
    tr { page-break-inside: avoid; }
    thead th {
        background: #fff !important; color: #000 !important;
        font-size: 6.5pt !important; font-weight: bold !important;
        padding: 0.8pt 3pt !important;
        border: 0.4pt solid #ccc !important;
        border-bottom: 1.5pt solid #1f4e79 !important;
        line-height: 1.15 !important;
    }
    tbody td {
        font-size: 6.5pt !important; padding: 1pt 3pt !important;
        border: 0.4pt solid #ddd !important; line-height: 1.2 !important;
    }
    .row-anulada td   { background: #ffe8e8 !important; print-color-adjust: exact !important; }
    .row-facturado td { background: #e8f5e9 !important; print-color-adjust: exact !important; }
    .row-cambiado td  { background: #fff8e1 !important; print-color-adjust: exact !important; }
    .row-devuelto td  { background: #e8eaf6 !important; print-color-adjust: exact !important; }
    tfoot td {
        background: #e2efda !important; print-color-adjust: exact !important;
        font-weight: bold !important; font-size: 7pt !important;
        border-top: 1.5pt solid #548235 !important;
        padding: 1.5pt 3pt !important;
    }
    .badge { display: none !important; }
    .acct-hdr {
        background: #dce6f0 !important; print-color-adjust: exact !important;
        padding: 2pt 4pt !important; font-size: 7pt !important;
        font-weight: bold !important; border: 0.4pt solid #1f4e79 !important;
    }
}
</style>

<div class="row">
  <div class="col-md-12">
    <div class="card">

      <div class="card-header py-2 d-flex justify-content-between no-print">
        <h5 class="mb-0">
          <i class="fa-solid fa-boxes-stacking text-primary mr-2"></i>
          Reporte NE por Producto
        </h5>
        <div class="d-flex gap-2">
          <?php if (!empty($lineas)): ?>
          <button class="btn btn-sm btn-outline-secondary mr-1" onclick="window.print()">
            <i class="fa-solid fa-print"></i> Imprimir
          </button>
          <a href="<?= current_url() . '?' . http_build_query(array_merge($_GET, ['exportar' => 'excel'])) ?>"
             class="btn btn-sm btn-outline-success">
            <i class="fa-solid fa-file-excel"></i> Excel
          </a>
          <?php endif; ?>
        </div>
      </div>

      <div class="card-body">

        <!-- ── FILTROS ────────────────────────────────────────────── -->
        <form method="get" class="no-print mb-4">
          <div class="row g-2 align-items-end">

            <div class="col-md-2">
              <label class="small font-weight-bold mb-1">Desde</label>
              <input type="date" name="fecha_desde" class="form-control form-control-sm"
                     value="<?= esc($fechaDesde) ?>">
            </div>

            <div class="col-md-2">
              <label class="small font-weight-bold mb-1">Hasta</label>
              <input type="date" name="fecha_hasta" class="form-control form-control-sm"
                     value="<?= esc($fechaHasta) ?>">
            </div>

            <div class="col-md-3">
              <label class="small font-weight-bold mb-1">Vendedor</label>
              <select name="vendedor_id" class="form-control form-control-sm">
                <option value="">— Todos —</option>
                <?php foreach ($vendedores as $v): ?>
                <option value="<?= $v->id ?>" <?= $v->id == $vendedorId ? 'selected' : '' ?>>
                  <?= esc($v->seller) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-2">
              <label class="small font-weight-bold">Comisión %</label>
              <div class="input-group input-group-sm">
                <input type="number" name="comision" class="form-control form-control-sm mt-2"
                       min="0" max="100" step="0.1"
                       value="<?= esc($comision) ?>"
                       style="max-width:80px; max-height: 35px;">
                <span class="input-group-text">%</span>
              </div>
            </div>

            <div class="col-md-2">
              <button type="submit" class="btn btn-primary btn-sm btn-block">
                <i class="fa-solid fa-magnifying-glass"></i> Buscar
              </button>
            </div>

          </div>
        </form>

        <!-- ── PRINT HEADER ──────────────────────────────────────── -->
        <div class="print-header" style="margin-bottom:8pt;">
          <div style="display:flex;justify-content:space-between;align-items:center;
                      border-bottom:1.5pt solid #1f4e79;padding-bottom:4pt;margin-bottom:5pt;">
            <div>
              <?php $logo = setting('logo'); if ($logo): ?>
              <img src="<?= base_url('upload/settings/' . $logo) ?>"
                   style="height:12mm;max-width:40mm;object-fit:contain;"
                   onerror="this.style.display='none'">
              <?php endif; ?>
            </div>
            <div style="text-align:right;">
              <div style="font-size:10pt;font-weight:bold;color:#1f4e79;">
                <?= esc(setting('company_name') ?? 'Empresa') ?>
              </div>
            </div>
          </div>
          <div style="margin-bottom:5pt;">
            <div style="font-size:9pt;font-weight:bold;color:#1f4e79;text-transform:uppercase;">
              Reporte Notas de Envío por Producto
            </div>
            <div style="font-size:7pt;color:#333;margin-top:2pt;">
              Del <?= date('d/m/Y', strtotime($fechaDesde)) ?> al <?= date('d/m/Y', strtotime($fechaHasta)) ?>
              &nbsp;·&nbsp; Comisión: <?= number_format($comision, 1) ?>%
            </div>
            <div style="font-size:6pt;color:#999;margin-top:1pt;">
              Generado: <?= date('d/m/Y H:i') ?>
            </div>
          </div>
        </div>

        <?php if (empty($lineas)): ?>
        <div class="alert alert-info">
          <i class="fa-solid fa-circle-info mr-2"></i>
          No se encontraron líneas de NE para los filtros seleccionados.
        </div>

        <?php else: ?>

        <!-- ── INFO BANNER ────────────────────────────────────────── -->
        <div class="alert alert-light border-left border-primary py-2 mb-3 no-print"
             style="font-size:0.82rem;border-left-width:4px !important;">
          <i class="fa-solid fa-circle-info text-primary mr-1"></i>
          <strong><?= count($lineas) ?></strong> línea(s) de producto en el rango.
          Comisión aplicada: <strong><?= number_format($comision, 1) ?>%</strong>
          &nbsp;|&nbsp;
          <span class="badge badge-success">Verde</span> = facturado
          &nbsp;
          <span class="badge badge-danger">Rojo</span> = NE anulada
          &nbsp;
          <span class="badge badge-warning">Amarillo</span> = cambio de NE
          &nbsp;
          <span class="badge badge-primary">Azul</span> = devuelto
        </div>

        <div class="table-responsive">
        <table class="table table-bordered table-sm ne-table" style="font-size:0.79rem;">
          <thead class="table-dark">
            <tr>
              <th>Fecha NE</th>
              <th>Nº NE</th>
              <th>Fecha Pedido</th>
              <th>Nº Pedido</th>
              <th>Fecha Factura</th>
              <th class="text-center">Días NE→Fac</th>
              <th>Doc Emitido</th>
              <th>Nº Doc</th>
              <th>Cliente Facturado</th>
              <th>Cód. Producto</th>
              <th>Descripción</th>
              <th class="text-right">Cantidad</th>
              <th class="text-right">Precio s/IVA</th>
              <th class="text-right">Comisión <?= number_format($comision, 1) ?>%</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
          <?php
          $prevNe = null;
          foreach ($lineas as $l):
              $esAnulada = ($l->ne_anulada == 1 || $l->ne_estado === 'anulada');
              $cantFact  = (float)($l->cantidad_facturada ?? 0);
              $precioFac = (float)($l->precio_factura ?? 0);
              // Precio y comisión solo cuando hay match real en factura_detalles
              $precio    = (!$esAnulada && $cantFact > 0 && $precioFac > 0)
                           ? $precioFac * $cantFact : 0.0;
              $comVal    = $precio * ($comision / 100);

              // Estado y clase de fila
              if ($esAnulada) {
                  $estado   = 'NE ANULADA';
                  $rowClass = 'row-anulada';
              } elseif ($cantFact > 0 && $l->factura_id) {
                  $estado   = !empty($l->pedido_id) ? 'Facturado vía NP' : 'Facturado';
                  $rowClass = 'row-facturado';
              } elseif (!empty($l->numero_nueva_ne)) {
                  $estado   = 'Cambio → NE ' . $l->numero_nueva_ne;
                  $rowClass = 'row-cambiado';
              } elseif ((float)($l->cantidad_devuelta ?? 0) > 0) {
                  $estado   = 'Devuelto';
                  $rowClass = 'row-devuelto';
              } elseif ((float)($l->cantidad_stock_vendedor ?? 0) > 0) {
                  $estado   = 'En stock vendedor';
                  $rowClass = '';
              } elseif ($l->ne_estado === 'cerrada') {
                  $estado   = 'Cerrada s/factura';
                  $rowClass = '';
              } else {
                  $estado   = 'Pendiente';
                  $rowClass = '';
              }

              $diasNeFac = '';
              if (!$esAnulada && $l->fecha_factura) {
                  $d = (int)round((strtotime($l->fecha_factura) - strtotime($l->fecha_ne)) / 86400);
                  $diasNeFac = $d . ' d';
              }

              $docSigla  = $l->tipo_dte ? ($siglas[$l->tipo_dte] ?? $l->tipo_dte) : '';
              $docNum    = $l->numero_control ? substr($l->numero_control, -6) : '';
          ?>
          <tr class="<?= $rowClass ?>">
            <td><?= $l->fecha_ne ? date('d/m/Y', strtotime($l->fecha_ne)) : '—' ?></td>
            <td class="font-weight-bold"><?= esc($l->numero_ne) ?></td>
            <td><?= $l->fecha_pedido ? date('d/m/Y', strtotime($l->fecha_pedido)) : '—' ?></td>
            <td><?= esc($l->numero_pedido ?? '—') ?></td>
            <td><?= $l->fecha_factura ? date('d/m/Y', strtotime($l->fecha_factura)) : '—' ?></td>
            <td class="text-center"><?= $diasNeFac ?: '—' ?></td>
            <td class="text-center">
              <?php if ($docSigla): ?>
              <span class="badge bg-info text-white"><?= esc($docSigla) ?></span>
              <?php else: ?> — <?php endif; ?>
            </td>
            <td><?= esc($docNum) ?: '—' ?></td>
            <td><?= esc($l->cliente_facturado ?? '—') ?></td>
            <td><code><?= esc($l->producto_codigo) ?></code></td>
            <td><?= esc($l->producto_descripcion) ?></td>
            <td class="text-right"><?= $cantFact > 0 ? number_format($cantFact, 2) : '—' ?></td>
            <td class="text-right">
              <?= $precio > 0 ? '$ ' . number_format($precio, 2) : '—' ?>
            </td>
            <td class="text-right">
              <?= $comVal > 0 ? '$ ' . number_format($comVal, 2) : '—' ?>
            </td>
            <td>
              <?php if ($esAnulada): ?>
                <span class="badge badge-danger"><?= esc($estado) ?></span>
              <?php elseif ($rowClass === 'row-facturado'): ?>
                <span class="badge badge-success"><?= esc($estado) ?></span>
              <?php elseif ($rowClass === 'row-cambiado'): ?>
                <span class="badge badge-warning text-dark"><?= esc($estado) ?></span>
              <?php elseif ($rowClass === 'row-devuelto'): ?>
                <span class="badge badge-primary"><?= esc($estado) ?></span>
              <?php else: ?>
                <span class="text-muted small"><?= esc($estado) ?></span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr class="font-weight-bold">
              <td colspan="12" class="text-right">TOTALES</td>
              <td class="text-right">$ <?= number_format($totalPrecio, 2) ?></td>
              <td class="text-right">$ <?= number_format($totalComision, 2) ?></td>
              <td></td>
            </tr>
          </tfoot>
        </table>
        </div>

        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
