<?= $this->extend('Layouts/mainbody') ?>
<?= $this->section('content') ?>

<?php
$mn = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
       7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];

$tipoBadge = [
    'ACTIVO'  => 'primary',
    'PASIVO'  => 'danger',
    'CAPITAL' => 'warning',
    'INGRESO' => 'success',
    'COSTO'   => 'secondary',
    'GASTO'   => 'dark',
];

// ── Pre-process: group movements by account with running balance & subtotals ──
$porCuenta   = [];
$grandTotalD = 0.0;
$grandTotalH = 0.0;

foreach ($movimientos as $m) {
    $porCuenta[(int)$m->cuenta_id][] = $m;
}

$cuentasRender = [];
foreach ($porCuenta as $cId => $lines) {
    $meta      = $lines[0];
    $previo    = (float)($saldosPrevios[$cId] ?? 0.0);
    $saldo     = $previo;
    $totalD    = 0.0; $totalH  = 0.0;
    $dayD      = 0.0; $dayH    = 0.0;
    $monthD    = 0.0; $monthH  = 0.0;
    $rows      = [];
    $currDay   = null;
    $currMonth = null;

    foreach ($lines as $l) {
        $day   = $l->fecha;
        $month = substr($l->fecha, 0, 7);

        if ($subtotalDia && $currDay && $currDay !== $day) {
            $rows[] = ['type' => 'day_sub',   'label' => $currDay,   'debe' => $dayD,   'haber' => $dayH];
            $dayD = 0.0; $dayH = 0.0;
        }
        if ($subtotalMes && $currMonth && $currMonth !== $month) {
            $rows[] = ['type' => 'month_sub', 'label' => $currMonth, 'debe' => $monthD, 'haber' => $monthH];
            $monthD = 0.0; $monthH = 0.0;
        }

        $currDay   = $day;
        $currMonth = $month;
        $saldo    += (float)$l->debe - (float)$l->haber;
        $totalD   += (float)$l->debe; $totalH   += (float)$l->haber;
        $dayD     += (float)$l->debe; $dayH     += (float)$l->haber;
        $monthD   += (float)$l->debe; $monthH   += (float)$l->haber;

        $rows[] = ['type' => 'row', 'data' => $l, 'saldo' => $saldo];
    }

    if ($subtotalDia   && $currDay)   $rows[] = ['type' => 'day_sub',   'label' => $currDay,   'debe' => $dayD,   'haber' => $dayH];
    if ($subtotalMes   && $currMonth) $rows[] = ['type' => 'month_sub', 'label' => $currMonth, 'debe' => $monthD, 'haber' => $monthH];
    $rows[] = ['type' => 'total', 'debe' => $totalD, 'haber' => $totalH, 'saldo' => $saldo];

    $grandTotalD += $totalD;
    $grandTotalH += $totalH;

    $cuentasRender[$cId] = [
        'meta'       => $meta,
        'previo'     => $previo,
        'rows'       => $rows,
        'totalD'     => $totalD,
        'totalH'     => $totalH,
        'saldoFinal' => $saldo,
    ];
}
?>

<style>
.print-header { display: none; }
@media print {
    .no-print  { display: none !important; }
    .print-header { display: block !important; }
    .card { border: none !important; box-shadow: none !important; }
    .card-header { background: none !important; border-bottom: 2px solid #333 !important; padding: 4px 0 !important; }
    .account-section { page-break-inside: avoid; margin-bottom: 18pt !important; }
    body, table, td, th { font-size: 9pt !important; }
    .badge { border: 1px solid #555 !important; background: #eee !important; color: #000 !important; }
}
</style>

<div class="row">
  <div class="col-md-12">
    <div class="card">

      <!-- Card header -->
      <div class="card-header py-2 d-flex justify-content-between">
        <h5 class="mb-0">
          <i class="fa-solid fa-book-open text-primary mr-2"></i>Libro Auxiliar de Cuentas
        </h5>
        <?php if ($filtrado && !empty($movimientos)): ?>
        <button class="btn btn-sm btn-outline-secondary no-print" onclick="window.print()">
          <i class="fa-solid fa-print"></i> Imprimir
        </button>
        <?php endif; ?>
      </div>

      <div class="card-body">

        <!-- ── FILTER FORM ─────────────────────────────────────────── -->
        <form method="get" class="no-print mb-4">
          <div class="row g-2 align-items-end">

            <div class="col-md-3">
              <label class="small font-weight-bold mb-1">Período</label>
              <select name="periodo_id" class="form-control form-control-sm">
                <option value="">— Cualquier período —</option>
                <?php foreach ($periodos as $p): ?>
                <option value="<?= $p->id ?>" <?= $p->id == $periodoId ? 'selected' : '' ?>>
                  <?= $mn[$p->mes] ?> <?= $p->anio ?>
                  <?php if ($p->estado === 'CERRADO'): ?> — CERRADO<?php endif; ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>

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
              <label class="small font-weight-bold mb-1">Cuenta</label>
              <select name="cuenta_id" class="form-control form-control-sm">
                <option value="">— Todas las cuentas —</option>
                <?php foreach ($cuentas as $c): ?>
                <option value="<?= $c->id ?>" <?= $c->id == $cuentaId ? 'selected' : '' ?>>
                  <?= esc($c->codigo) ?> — <?= esc($c->nombre) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-2">
              <button type="submit" class="btn btn-primary btn-sm btn-block">
                <i class="fa-solid fa-magnifying-glass"></i> Buscar
              </button>
            </div>

          </div>

          <!-- Options row -->
          <div class="row g-2 mt-2">
            <div class="col-auto">
              <span class="small font-weight-bold mr-2">Subtotales:</span>
              <div class="custom-control custom-checkbox custom-control-inline">
                <input type="checkbox" class="custom-control-input" name="subtotal_dia" value="1"
                       id="chkDia" <?= $subtotalDia ? 'checked' : '' ?>>
                <label class="custom-control-label small" for="chkDia">Por día</label>
              </div>
              <div class="custom-control custom-checkbox custom-control-inline">
                <input type="checkbox" class="custom-control-input" name="subtotal_mes" value="1"
                       id="chkMes" <?= $subtotalMes ? 'checked' : '' ?>>
                <label class="custom-control-label small" for="chkMes">Por mes</label>
              </div>
            </div>
            <div class="col-auto ml-4">
              <span class="small font-weight-bold mr-2">Formato:</span>
              <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" class="custom-control-input" name="formato" value="contable"
                       id="fmtContable" <?= $formato === 'contable' ? 'checked' : '' ?>>
                <label class="custom-control-label small" for="fmtContable">Contable</label>
              </div>
              <div class="custom-control custom-radio custom-control-inline">
                <input type="radio" class="custom-control-input" name="formato" value="reporte"
                       id="fmtReporte" <?= $formato === 'reporte' ? 'checked' : '' ?>>
                <label class="custom-control-label small" for="fmtReporte">Reporte</label>
              </div>
            </div>
          </div>
        </form>

        <?php if (!$filtrado): ?>
        <!-- ── No filter yet ── -->
        <div class="text-center text-muted py-5">
          <i class="fa-solid fa-book-open fa-3x mb-3 opacity-50"></i>
          <p class="mb-1">Selecciona al menos un filtro para generar el auxiliar.</p>
          <small>Puedes filtrar por período, rango de fechas y/o cuenta. El reporte muestra todos los movimientos históricos independientemente del estado del período.</small>
        </div>

        <?php elseif (empty($movimientos)): ?>
        <div class="alert alert-info">
          <i class="fa-solid fa-circle-info mr-2"></i>
          No se encontraron movimientos aprobados con los filtros seleccionados.
        </div>

        <?php else: ?>

        <!-- ── Info banner ── -->
        <div class="alert alert-light border-left border-primary border-4 py-2 mb-3 no-print"
             style="font-size:0.82rem; border-left-width:4px !important;">
          <i class="fa-solid fa-circle-info text-primary mr-1"></i>
          Muestra <strong><?= count($movimientos) ?></strong> movimiento(s) en
          <strong><?= count($cuentasRender) ?></strong> cuenta(s).
          Incluye todos los registros <strong>aprobados</strong> sin importar si el período está abierto o cerrado.
        </div>

        <!-- ── Print header (hidden on screen) ── -->
        <div class="print-header text-center mb-3">
          <h4 class="mb-0">Libro Auxiliar de Cuentas</h4>
          <small>
            <?php
            $partes = [];
            if ($periodoId) {
                foreach ($periodos as $p) {
                    if ($p->id == $periodoId) { $partes[] = 'Período: ' . $mn[$p->mes] . ' ' . $p->anio; break; }
                }
            }
            if ($fechaDesde) $partes[] = 'Desde: ' . date('d/m/Y', strtotime($fechaDesde));
            if ($fechaHasta) $partes[] = 'Hasta: ' . date('d/m/Y', strtotime($fechaHasta));
            if ($cuentaId) {
                foreach ($cuentas as $c) {
                    if ($c->id == $cuentaId) { $partes[] = 'Cuenta: ' . $c->codigo . ' ' . $c->nombre; break; }
                }
            }
            echo esc(implode(' · ', $partes) ?: 'Todos los movimientos');
            ?>
            — Generado: <?= date('d/m/Y H:i') ?>
          </small>
        </div>

        <?php if ($formato === 'contable'): ?>
        <!-- ═══════════════════════════════════════════════════════════
             FORMATO CONTABLE — sección por cuenta con saldo corrido
             ═══════════════════════════════════════════════════════════ -->

        <?php foreach ($cuentasRender as $cId => $ac): ?>
          <?php $meta = $ac['meta']; ?>
          <div class="account-section mb-4">

            <div class="d-flex mb-1" style="gap:.4rem">
              <span class="badge badge-<?= $tipoBadge[$meta->tipo] ?? 'secondary' ?> px-2"
                    style="font-size:0.68rem"><?= $meta->tipo ?></span>
              <strong style="font-size:0.88rem"><?= esc($meta->codigo) ?></strong>
              <span style="font-size:0.88rem"><?= esc($meta->cuenta_nombre) ?></span>
            </div>

            <table class="table table-bordered table-sm mb-0" style="font-size:0.81rem">
              <thead class="table-dark">
                <tr>
                  <th style="width:88px">Fecha</th>
                  <th style="width:84px">Asiento</th>
                  <th>Descripción</th>
                  <th class="text-right" style="width:108px">Debe</th>
                  <th class="text-right" style="width:108px">Haber</th>
                  <th class="text-right" style="width:108px">Saldo</th>
                </tr>
              </thead>
              <tbody>

                <!-- Saldo anterior -->
                <tr class="table-secondary" style="font-size:0.76rem; font-style:italic">
                  <td colspan="3" class="text-right text-muted">Saldo anterior</td>
                  <td></td><td></td>
                  <td class="text-right font-weight-bold">
                    <?= $ac['previo'] != 0 ? '$ ' . number_format($ac['previo'], 2) : '—' ?>
                  </td>
                </tr>

                <?php foreach ($ac['rows'] as $row): ?>

                  <?php if ($row['type'] === 'row'): ?>
                    <?php $l = $row['data']; ?>
                    <tr>
                      <td><?= date('d/m/Y', strtotime($l->fecha)) ?></td>
                      <td class="text-muted" style="font-size:0.76rem">
                        AST-<?= str_pad($l->numero_asiento, 5, '0', STR_PAD_LEFT) ?>
                      </td>
                      <td title="<?= esc($l->asiento_desc) ?>">
                        <?= esc($l->linea_desc ?: $l->asiento_desc) ?>
                        <?php if ($l->referencia): ?>
                          <small class="text-muted"> · <?= esc($l->referencia) ?></small>
                        <?php endif; ?>
                      </td>
                      <td class="text-right <?= (float)$l->debe > 0 ? '' : 'text-muted' ?>">
                        <?= (float)$l->debe > 0 ? number_format($l->debe, 2) : '—' ?>
                      </td>
                      <td class="text-right <?= (float)$l->haber > 0 ? '' : 'text-muted' ?>">
                        <?= (float)$l->haber > 0 ? number_format($l->haber, 2) : '—' ?>
                      </td>
                      <td class="text-right font-weight-bold <?= $row['saldo'] < 0 ? 'text-danger' : '' ?>">
                        <?= number_format($row['saldo'], 2) ?>
                      </td>
                    </tr>

                  <?php elseif ($row['type'] === 'day_sub'): ?>
                    <tr class="table-warning" style="font-size:0.76rem; font-style:italic">
                      <td colspan="2" class="text-muted">
                        Subtotal <?= date('d/m/Y', strtotime($row['label'])) ?>
                      </td>
                      <td></td>
                      <td class="text-right font-weight-bold"><?= number_format($row['debe'],  2) ?></td>
                      <td class="text-right font-weight-bold"><?= number_format($row['haber'], 2) ?></td>
                      <td></td>
                    </tr>

                  <?php elseif ($row['type'] === 'month_sub'): ?>
                    <?php [$anioM, $mesM] = explode('-', $row['label']); ?>
                    <tr class="table-info" style="font-size:0.76rem; font-style:italic">
                      <td colspan="2" class="text-muted">
                        Subtotal <?= $mn[(int)$mesM] ?> <?= $anioM ?>
                      </td>
                      <td></td>
                      <td class="text-right font-weight-bold"><?= number_format($row['debe'],  2) ?></td>
                      <td class="text-right font-weight-bold"><?= number_format($row['haber'], 2) ?></td>
                      <td></td>
                    </tr>

                  <?php elseif ($row['type'] === 'total'): ?>
                    <tr class="table-light font-weight-bold" style="font-size:0.81rem">
                      <td colspan="3" class="text-right">Total <?= esc($meta->codigo) ?></td>
                      <td class="text-right text-primary">$ <?= number_format($row['debe'],  2) ?></td>
                      <td class="text-right text-success">$ <?= number_format($row['haber'], 2) ?></td>
                      <td class="text-right <?= $row['saldo'] < 0 ? 'text-danger' : '' ?>">
                        $ <?= number_format($row['saldo'], 2) ?>
                      </td>
                    </tr>

                  <?php endif; ?>
                <?php endforeach; ?>

              </tbody>
            </table>
          </div>
        <?php endforeach; ?>

        <?php else: ?>
        <!-- ═══════════════════════════════════════════════════════════
             FORMATO REPORTE — tabla plana con todas las cuentas
             ═══════════════════════════════════════════════════════════ -->

        <table class="table table-bordered table-sm" style="font-size:0.81rem">
          <thead class="table-dark">
            <tr>
              <th style="width:88px">Código</th>
              <th style="width:160px">Cuenta</th>
              <th style="width:88px">Fecha</th>
              <th style="width:84px">Asiento</th>
              <th>Descripción</th>
              <th class="text-right" style="width:108px">Debe</th>
              <th class="text-right" style="width:108px">Haber</th>
              <th class="text-right" style="width:108px">Saldo</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($cuentasRender as $cId => $ac): ?>
            <?php $meta = $ac['meta']; ?>

            <!-- Account separator + saldo anterior -->
            <tr class="table-secondary" style="font-size:0.76rem; font-style:italic">
              <td><code><?= esc($meta->codigo) ?></code></td>
              <td colspan="4">
                <span class="badge badge-<?= $tipoBadge[$meta->tipo] ?? 'secondary' ?>"
                      style="font-size:0.65rem"><?= $meta->tipo ?></span>
                <strong class="ml-1"><?= esc($meta->cuenta_nombre) ?></strong>
                <span class="text-muted ml-1">— Saldo anterior</span>
              </td>
              <td></td><td></td>
              <td class="text-right font-weight-bold">
                <?= $ac['previo'] != 0 ? '$ ' . number_format($ac['previo'], 2) : '—' ?>
              </td>
            </tr>

            <?php foreach ($ac['rows'] as $row): ?>

              <?php if ($row['type'] === 'row'): ?>
                <?php $l = $row['data']; ?>
                <tr>
                  <td class="text-muted" style="font-size:0.76rem"><code><?= esc($meta->codigo) ?></code></td>
                  <td class="text-muted" style="font-size:0.76rem"><?= esc($meta->cuenta_nombre) ?></td>
                  <td><?= date('d/m/Y', strtotime($l->fecha)) ?></td>
                  <td class="text-muted" style="font-size:0.76rem">
                    AST-<?= str_pad($l->numero_asiento, 5, '0', STR_PAD_LEFT) ?>
                  </td>
                  <td title="<?= esc($l->asiento_desc) ?>">
                    <?= esc($l->linea_desc ?: $l->asiento_desc) ?>
                    <?php if ($l->referencia): ?>
                      <small class="text-muted"> · <?= esc($l->referencia) ?></small>
                    <?php endif; ?>
                  </td>
                  <td class="text-right <?= (float)$l->debe  > 0 ? '' : 'text-muted' ?>">
                    <?= (float)$l->debe  > 0 ? number_format($l->debe,  2) : '—' ?>
                  </td>
                  <td class="text-right <?= (float)$l->haber > 0 ? '' : 'text-muted' ?>">
                    <?= (float)$l->haber > 0 ? number_format($l->haber, 2) : '—' ?>
                  </td>
                  <td class="text-right font-weight-bold <?= $row['saldo'] < 0 ? 'text-danger' : '' ?>">
                    <?= number_format($row['saldo'], 2) ?>
                  </td>
                </tr>

              <?php elseif ($row['type'] === 'day_sub'): ?>
                <tr class="table-warning" style="font-size:0.76rem; font-style:italic">
                  <td colspan="5" class="text-right text-muted">
                    Subtotal <?= esc($meta->cuenta_nombre) ?> — <?= date('d/m/Y', strtotime($row['label'])) ?>
                  </td>
                  <td class="text-right font-weight-bold"><?= number_format($row['debe'],  2) ?></td>
                  <td class="text-right font-weight-bold"><?= number_format($row['haber'], 2) ?></td>
                  <td></td>
                </tr>

              <?php elseif ($row['type'] === 'month_sub'): ?>
                <?php [$anioM, $mesM] = explode('-', $row['label']); ?>
                <tr class="table-info" style="font-size:0.76rem; font-style:italic">
                  <td colspan="5" class="text-right text-muted">
                    Subtotal <?= esc($meta->cuenta_nombre) ?> — <?= $mn[(int)$mesM] ?> <?= $anioM ?>
                  </td>
                  <td class="text-right font-weight-bold"><?= number_format($row['debe'],  2) ?></td>
                  <td class="text-right font-weight-bold"><?= number_format($row['haber'], 2) ?></td>
                  <td></td>
                </tr>

              <?php elseif ($row['type'] === 'total'): ?>
                <tr class="table-light font-weight-bold" style="font-size:0.81rem">
                  <td colspan="5" class="text-right">
                    Total <?= esc($meta->codigo) ?> <?= esc($meta->cuenta_nombre) ?>
                  </td>
                  <td class="text-right text-primary">$ <?= number_format($row['debe'],  2) ?></td>
                  <td class="text-right text-success">$ <?= number_format($row['haber'], 2) ?></td>
                  <td class="text-right <?= $row['saldo'] < 0 ? 'text-danger' : '' ?>">
                    $ <?= number_format($row['saldo'], 2) ?>
                  </td>
                </tr>

              <?php endif; ?>
            <?php endforeach; ?>

          <?php endforeach; ?>
          </tbody>
        </table>

        <?php endif; ?>

        <!-- ── Grand total ── -->
        <div class="alert alert-light border font-weight-bold d-flex justify-content-between mt-3">
          <span><i class="fa-solid fa-sigma mr-2"></i>GRAN TOTAL</span>
          <span>
            Debe: $<?= number_format($grandTotalD, 2) ?> &nbsp;&nbsp;|&nbsp;&nbsp;
            Haber: $<?= number_format($grandTotalH, 2) ?> &nbsp;&nbsp;|&nbsp;&nbsp;
            Diferencia: <span class="<?= abs($grandTotalD - $grandTotalH) > 0.01 ? 'text-danger' : 'text-success' ?>">
              $<?= number_format(abs($grandTotalD - $grandTotalH), 2) ?>
            </span>
          </span>
        </div>

        <?php endif; ?>
      </div><!-- /card-body -->
    </div><!-- /card -->
  </div>
</div>

<?= $this->endSection() ?>
