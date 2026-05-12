<?php

namespace App\Controllers;

use App\Models\ContPeriodosModel;
use App\Models\ContPlanCuentasModel;
use App\Models\ContAsientosHeadModel;
use App\Models\ContAsientosDetalleModel;
use App\Models\ContTransaccionesHistModel;
use App\Models\ContSaldosCuentasModel;
use App\Models\ContSaldosHistoricosModel;

class ContReportesController extends BaseController
{
    // ─── LISTADOS ────────────────────────────────────────────────

    public function relacionCuentas()
    {
        $chk = requerirPermiso('ver_listados_contables');
        if ($chk !== true) return $chk;

        $model   = new ContPlanCuentasModel();
        $cuentas = $model->orderBy('codigo','ASC')->findAll();

        return view('contabilidad/listados/relacion_cuentas', ['cuentas' => $cuentas]);
    }

    public function costos()
    {
        $chk = requerirPermiso('ver_listados_contables');
        if ($chk !== true) return $chk;

        $periodosModel = new ContPeriodosModel();
        $saldosModel   = new ContSaldosCuentasModel();

        $periodos  = $periodosModel->orderBy('anio','DESC')->orderBy('mes','DESC')->findAll();
        $periodoId = $this->request->getGet('periodo_id');
        $filas     = [];

        if ($periodoId) {
            $db   = \Config\Database::connect();
            $filas = $db->query(
                'SELECT pc.codigo, pc.nombre, sc.total_debe, sc.total_haber, sc.saldo_final
                 FROM cont_saldos_cuentas sc
                 INNER JOIN cont_plan_cuentas pc ON pc.id = sc.cuenta_id
                 WHERE sc.periodo_id = ? AND pc.tipo = "COSTO"
                 ORDER BY pc.codigo ASC',
                [$periodoId]
            )->getResult();
        }

        return view('contabilidad/listados/costos', [
            'periodos'  => $periodos,
            'periodoId' => $periodoId,
            'filas'     => $filas,
        ]);
    }

    public function gastos()
    {
        $chk = requerirPermiso('ver_listados_contables');
        if ($chk !== true) return $chk;

        $periodosModel = new ContPeriodosModel();
        $periodos  = $periodosModel->orderBy('anio','DESC')->orderBy('mes','DESC')->findAll();
        $periodoId = $this->request->getGet('periodo_id');
        $filas     = [];

        if ($periodoId) {
            $db   = \Config\Database::connect();
            $filas = $db->query(
                'SELECT pc.codigo, pc.nombre, sc.total_debe, sc.total_haber, sc.saldo_final
                 FROM cont_saldos_cuentas sc
                 INNER JOIN cont_plan_cuentas pc ON pc.id = sc.cuenta_id
                 WHERE sc.periodo_id = ? AND pc.tipo = "GASTO"
                 ORDER BY pc.codigo ASC',
                [$periodoId]
            )->getResult();
        }

        return view('contabilidad/listados/gastos', [
            'periodos'  => $periodos,
            'periodoId' => $periodoId,
            'filas'     => $filas,
        ]);
    }

    public function comparativos()
    {
        $chk = requerirPermiso('ver_listados_contables');
        if ($chk !== true) return $chk;

        $histModel = new ContSaldosHistoricosModel();
        $anio1     = $this->request->getGet('anio1') ?? (int)date('Y') - 1;
        $anio2     = $this->request->getGet('anio2') ?? (int)date('Y');
        $filas     = [];

        if ($anio1 && $anio2) {
            $filas = $histModel->getComparativo((int)$anio1, (int)$anio2);
        }

        $db   = \Config\Database::connect();
        $anios = $db->query('SELECT DISTINCT anio FROM cont_saldos_historicos ORDER BY anio DESC')->getResultArray();
        $anios = array_column($anios, 'anio');

        return view('contabilidad/listados/comparativos', [
            'filas' => $filas,
            'anio1' => $anio1,
            'anio2' => $anio2,
            'anios' => $anios,
        ]);
    }

    public function catalogos()
    {
        $chk = requerirPermiso('ver_listados_contables');
        if ($chk !== true) return $chk;

        $model   = new ContPlanCuentasModel();
        $tipo    = $this->request->getGet('tipo') ?? '';
        $nivel   = $this->request->getGet('nivel') ?? '';

        $q = $model->orderBy('codigo','ASC');
        if ($tipo) $q->where('tipo', $tipo);
        if ($nivel) $q->where('nivel', $nivel);

        $cuentas = $q->findAll();

        return view('contabilidad/listados/catalogos', [
            'cuentas' => $cuentas,
            'tipo'    => $tipo,
            'nivel'   => $nivel,
        ]);
    }

    // ─── REPORTES ────────────────────────────────────────────────

    public function diario()
    {
        $chk = requerirPermiso('ver_reportes_contables');
        if ($chk !== true) return $chk;

        $periodosModel = new ContPeriodosModel();
        $headModel     = new ContAsientosHeadModel();
        $detalleModel  = new ContAsientosDetalleModel();

        $periodos     = $periodosModel->orderBy('anio','DESC')->orderBy('mes','DESC')->findAll();
        $periodoId    = $this->request->getGet('periodo_id');
        $fechaDesde   = $this->request->getGet('fecha_desde');
        $fechaHasta   = $this->request->getGet('fecha_hasta');
        $asientosData = [];

        if ($periodoId || ($fechaDesde && $fechaHasta)) {
            $filtros = ['periodo_id' => $periodoId, 'fecha_desde' => $fechaDesde, 'fecha_hasta' => $fechaHasta, 'estado' => 'APROBADO'];
            $asientosHead = $headModel->getListadoFiltrado($filtros, 99999);
            foreach ($asientosHead as $a) {
                $a->lineas = $detalleModel->getByAsiento($a->id);
                $asientosData[] = $a;
            }
        }

        return view('contabilidad/reportes/diario', [
            'periodos'   => $periodos,
            'periodoId'  => $periodoId,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
            'asientos'   => $asientosData,
        ]);
    }

    public function mayor()
    {
        $chk = requerirPermiso('ver_reportes_contables');
        if ($chk !== true) return $chk;

        $cuentasModel  = new ContPlanCuentasModel();
        $histModel     = new ContTransaccionesHistModel();
        $periodosModel = new ContPeriodosModel();

        $periodos   = $periodosModel->orderBy('anio','DESC')->orderBy('mes','DESC')->findAll();
        $cuentaId   = $this->request->getGet('cuenta_id');
        $fechaDesde = $this->request->getGet('fecha_desde') ?? date('Y-01-01');
        $fechaHasta = $this->request->getGet('fecha_hasta') ?? date('Y-m-d');
        $movimientos = [];
        $cuenta      = null;

        if ($cuentaId) {
            $cuenta      = $cuentasModel->find($cuentaId);
            $movimientos = $histModel->getLibroMayor($cuentaId, $fechaDesde, $fechaHasta);
        }

        return view('contabilidad/reportes/mayor', [
            'periodos'   => $periodos,
            'cuentaId'   => $cuentaId,
            'cuenta'     => $cuenta,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
            'movimientos'=> $movimientos,
        ]);
    }

    public function auxiliar()
    {
        $chk = requerirPermiso('ver_reportes_contables');
        if ($chk !== true) return $chk;

        $periodosModel = new ContPeriodosModel();
        $planModel     = new ContPlanCuentasModel();
        $db            = \Config\Database::connect();

        $periodos = $periodosModel->orderBy('anio', 'DESC')->orderBy('mes', 'DESC')->findAll();
        $cuentas  = $planModel->where('acepta_movimientos', 1)->orderBy('codigo', 'ASC')->findAll();

        $periodoId   = $this->request->getGet('periodo_id');
        $fechaDesde  = $this->request->getGet('fecha_desde');
        $fechaHasta  = $this->request->getGet('fecha_hasta');
        $cuentaId    = $this->request->getGet('cuenta_id');
        $subtotalDia = $this->request->getGet('subtotal_dia') === '1';
        $subtotalMes = $this->request->getGet('subtotal_mes') === '1';
        $formato     = $this->request->getGet('formato') ?: 'contable';

        $movimientos   = [];
        $saldosPrevios = [];
        $filtrado      = $periodoId || $fechaDesde || $fechaHasta || $cuentaId;

        if ($filtrado) {
            // Individual movements query
            $sql    = "SELECT d.id, d.asiento_id, d.cuenta_id,
                              d.descripcion AS linea_desc, d.debe, d.haber, d.orden,
                              h.fecha, h.numero_asiento,
                              h.descripcion AS asiento_desc, h.referencia, h.periodo_id,
                              cp.codigo, cp.nombre AS cuenta_nombre, cp.tipo, cp.naturaleza
                       FROM   cont_asientos_detalle d
                       JOIN   cont_asientos_head   h  ON h.id  = d.asiento_id
                       JOIN   cont_plan_cuentas    cp ON cp.id = d.cuenta_id
                       WHERE  h.estado = 'APROBADO'";
            $params = [];

            if ($periodoId) { $sql .= ' AND h.periodo_id = ?';  $params[] = $periodoId; }
            if ($fechaDesde) { $sql .= ' AND h.fecha >= ?';     $params[] = $fechaDesde; }
            if ($fechaHasta) { $sql .= ' AND h.fecha <= ?';     $params[] = $fechaHasta; }
            if ($cuentaId)   { $sql .= ' AND d.cuenta_id = ?';  $params[] = $cuentaId; }

            $sql .= ' ORDER BY cp.codigo, h.fecha, h.numero_asiento, d.orden';
            $movimientos = $db->query($sql, $params)->getResult();

            // Saldo previo per account (movements before the filtered range)
            if ($periodoId || $fechaDesde) {
                $sqlPrev    = "SELECT d.cuenta_id,
                                      COALESCE(SUM(d.debe - d.haber), 0) AS saldo_previo
                               FROM   cont_asientos_detalle d
                               JOIN   cont_asientos_head   h  ON h.id  = d.asiento_id
                               JOIN   cont_periodos        pp ON pp.id = h.periodo_id
                               WHERE  h.estado = 'APROBADO'";
                $paramsPrev = [];

                if ($periodoId) {
                    $po          = $periodosModel->find($periodoId);
                    $sqlPrev    .= ' AND (pp.anio < ? OR (pp.anio = ? AND pp.mes < ?))';
                    $paramsPrev[] = $po->anio;
                    $paramsPrev[] = $po->anio;
                    $paramsPrev[] = $po->mes;
                } elseif ($fechaDesde) {
                    $sqlPrev    .= ' AND h.fecha < ?';
                    $paramsPrev[] = $fechaDesde;
                }

                if ($cuentaId) { $sqlPrev .= ' AND d.cuenta_id = ?'; $paramsPrev[] = $cuentaId; }

                $sqlPrev .= ' GROUP BY d.cuenta_id';
                foreach ($db->query($sqlPrev, $paramsPrev)->getResult() as $row) {
                    $saldosPrevios[(int)$row->cuenta_id] = (float)$row->saldo_previo;
                }
            }
        }

        return view('contabilidad/reportes/auxiliar', [
            'periodos'      => $periodos,
            'cuentas'       => $cuentas,
            'periodoId'     => $periodoId,
            'fechaDesde'    => $fechaDesde,
            'fechaHasta'    => $fechaHasta,
            'cuentaId'      => $cuentaId,
            'subtotalDia'   => $subtotalDia,
            'subtotalMes'   => $subtotalMes,
            'formato'       => $formato,
            'movimientos'   => $movimientos,
            'saldosPrevios' => $saldosPrevios,
            'filtrado'      => $filtrado,
        ]);
    }

    public function auxiliarExcel()
    {
        $chk = requerirPermiso('ver_reportes_contables');
        if ($chk !== true) return $chk;

        $periodosModel = new ContPeriodosModel();
        $planModel     = new ContPlanCuentasModel();
        $db            = \Config\Database::connect();

        $periodos = $periodosModel->orderBy('anio', 'DESC')->orderBy('mes', 'DESC')->findAll();
        $cuentas  = $planModel->where('acepta_movimientos', 1)->orderBy('codigo', 'ASC')->findAll();

        $periodoId   = $this->request->getGet('periodo_id');
        $fechaDesde  = $this->request->getGet('fecha_desde');
        $fechaHasta  = $this->request->getGet('fecha_hasta');
        $cuentaId    = $this->request->getGet('cuenta_id');
        $subtotalDia = $this->request->getGet('subtotal_dia') === '1';
        $subtotalMes = $this->request->getGet('subtotal_mes') === '1';
        $formato     = $this->request->getGet('formato') ?: 'contable';

        // ── Same SQL as auxiliar() ──────────────────────────────
        $movimientos   = [];
        $saldosPrevios = [];

        $sql    = "SELECT d.id, d.asiento_id, d.cuenta_id,
                          d.descripcion AS linea_desc, d.debe, d.haber, d.orden,
                          h.fecha, h.numero_asiento,
                          h.descripcion AS asiento_desc, h.referencia, h.periodo_id,
                          cp.codigo, cp.nombre AS cuenta_nombre, cp.tipo, cp.naturaleza
                   FROM   cont_asientos_detalle d
                   JOIN   cont_asientos_head   h  ON h.id  = d.asiento_id
                   JOIN   cont_plan_cuentas    cp ON cp.id = d.cuenta_id
                   WHERE  h.estado = 'APROBADO'";
        $params = [];

        if ($periodoId)  { $sql .= ' AND h.periodo_id = ?'; $params[] = $periodoId; }
        if ($fechaDesde) { $sql .= ' AND h.fecha >= ?';     $params[] = $fechaDesde; }
        if ($fechaHasta) { $sql .= ' AND h.fecha <= ?';     $params[] = $fechaHasta; }
        if ($cuentaId)   { $sql .= ' AND d.cuenta_id = ?';  $params[] = $cuentaId; }

        $sql .= ' ORDER BY cp.codigo, h.fecha, h.numero_asiento, d.orden';
        $movimientos = $db->query($sql, $params)->getResult();

        if ($periodoId || $fechaDesde) {
            $sqlPrev    = "SELECT d.cuenta_id,
                                  COALESCE(SUM(d.debe - d.haber), 0) AS saldo_previo
                           FROM   cont_asientos_detalle d
                           JOIN   cont_asientos_head   h  ON h.id  = d.asiento_id
                           JOIN   cont_periodos        pp ON pp.id = h.periodo_id
                           WHERE  h.estado = 'APROBADO'";
            $paramsPrev = [];

            if ($periodoId) {
                $po          = $periodosModel->find($periodoId);
                $sqlPrev    .= ' AND (pp.anio < ? OR (pp.anio = ? AND pp.mes < ?))';
                $paramsPrev[] = $po->anio; $paramsPrev[] = $po->anio; $paramsPrev[] = $po->mes;
            } elseif ($fechaDesde) {
                $sqlPrev    .= ' AND h.fecha < ?'; $paramsPrev[] = $fechaDesde;
            }
            if ($cuentaId) { $sqlPrev .= ' AND d.cuenta_id = ?'; $paramsPrev[] = $cuentaId; }
            $sqlPrev .= ' GROUP BY d.cuenta_id';
            foreach ($db->query($sqlPrev, $paramsPrev)->getResult() as $row) {
                $saldosPrevios[(int)$row->cuenta_id] = (float)$row->saldo_previo;
            }
        }

        // ── Same pre-processing as view ─────────────────────────
        $mn = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
               7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];

        $porCuenta   = [];
        $grandTotalD = 0.0;
        $grandTotalH = 0.0;
        foreach ($movimientos as $m) {
            $porCuenta[(int)$m->cuenta_id][] = $m;
        }

        $cuentasRender = [];
        foreach ($porCuenta as $cId => $lines) {
            $meta = $lines[0]; $previo = (float)($saldosPrevios[$cId] ?? 0.0); $saldo = $previo;
            $totalD = 0.0; $totalH = 0.0; $dayD = 0.0; $dayH = 0.0; $monthD = 0.0; $monthH = 0.0;
            $rows = []; $currDay = null; $currMonth = null;
            foreach ($lines as $l) {
                $day = $l->fecha; $month = substr($l->fecha, 0, 7);
                if ($subtotalDia && $currDay && $currDay !== $day) {
                    $rows[] = ['type'=>'day_sub','label'=>$currDay,'debe'=>$dayD,'haber'=>$dayH];
                    $dayD = 0.0; $dayH = 0.0;
                }
                if ($subtotalMes && $currMonth && $currMonth !== $month) {
                    $rows[] = ['type'=>'month_sub','label'=>$currMonth,'debe'=>$monthD,'haber'=>$monthH];
                    $monthD = 0.0; $monthH = 0.0;
                }
                $currDay = $day; $currMonth = $month;
                $saldo += (float)$l->debe - (float)$l->haber;
                $totalD += (float)$l->debe; $totalH += (float)$l->haber;
                $dayD   += (float)$l->debe; $dayH   += (float)$l->haber;
                $monthD += (float)$l->debe; $monthH += (float)$l->haber;
                $rows[] = ['type'=>'row','data'=>$l,'saldo'=>$saldo];
            }
            if ($subtotalDia && $currDay)   $rows[] = ['type'=>'day_sub',  'label'=>$currDay,   'debe'=>$dayD,   'haber'=>$dayH];
            if ($subtotalMes && $currMonth) $rows[] = ['type'=>'month_sub','label'=>$currMonth, 'debe'=>$monthD, 'haber'=>$monthH];
            $rows[] = ['type'=>'total','debe'=>$totalD,'haber'=>$totalH,'saldo'=>$saldo];
            $grandTotalD += $totalD; $grandTotalH += $totalH;
            $cuentasRender[$cId] = ['meta'=>$meta,'previo'=>$previo,'rows'=>$rows,'totalD'=>$totalD,'totalH'=>$totalH,'saldoFinal'=>$saldo];
        }

        // ── Build filter label ──────────────────────────────────
        $partes = [];
        if ($periodoId) {
            foreach ($periodos as $p) {
                if ($p->id == $periodoId) {
                    $partes[] = 'Período: ' . $mn[$p->mes] . ' ' . $p->anio . ($p->estado === 'CERRADO' ? ' (Cerrado)' : '');
                    break;
                }
            }
        }
        if ($fechaDesde) $partes[] = 'Desde: '  . date('d/m/Y', strtotime($fechaDesde));
        if ($fechaHasta) $partes[] = 'Hasta: '   . date('d/m/Y', strtotime($fechaHasta));
        if ($cuentaId) {
            foreach ($cuentas as $c) {
                if ($c->id == $cuentaId) { $partes[] = 'Cuenta: ' . $c->codigo . ' — ' . $c->nombre; break; }
            }
        }
        $filtroLabel = implode('   ·   ', $partes) ?: 'Todos los movimientos';

        // ── PhpSpreadsheet ──────────────────────────────────────
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Auxiliar');

        $numFmt = '#,##0.00';
        $navy   = '1F4E79';
        $navyLt = 'DCE6F0';
        $green  = 'E2EFDA';
        $yellow = 'FFF3CD';
        $cyan   = 'D1ECF1';
        $gray   = 'F5F5F5';
        $white  = 'FFFFFF';

        $headerFill = function(\PhpOffice\PhpSpreadsheet\Style\Style $s, string $bgHex, string $fgHex = '000000') {
            $s->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB($bgHex);
            $s->getFont()->getColor()->setRGB($fgHex);
        };

        $isContable = ($formato === 'contable');

        // ── Column widths ───────────────────────────────────────
        if ($isContable) {
            // A=Fecha B=Asiento C=Descripción D=Debe E=Haber F=Saldo
            $sheet->getColumnDimension('A')->setWidth(12);
            $sheet->getColumnDimension('B')->setWidth(12);
            $sheet->getColumnDimension('C')->setWidth(48);
            $sheet->getColumnDimension('D')->setWidth(16);
            $sheet->getColumnDimension('E')->setWidth(16);
            $sheet->getColumnDimension('F')->setWidth(16);
            $lastCol = 'F'; $colCount = 6;
        } else {
            // A=Código B=Cuenta C=Fecha D=Asiento E=Descripción F=Debe G=Haber H=Saldo
            $sheet->getColumnDimension('A')->setWidth(12);
            $sheet->getColumnDimension('B')->setWidth(28);
            $sheet->getColumnDimension('C')->setWidth(12);
            $sheet->getColumnDimension('D')->setWidth(12);
            $sheet->getColumnDimension('E')->setWidth(40);
            $sheet->getColumnDimension('F')->setWidth(16);
            $sheet->getColumnDimension('G')->setWidth(16);
            $sheet->getColumnDimension('H')->setWidth(16);
            $lastCol = 'H'; $colCount = 8;
        }

        // ── Header rows (1-4) ───────────────────────────────────
        $companyName = setting('company_name') ?? 'Empresa';
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', $companyName);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13)->getColor()->setRGB($navy);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', 'LIBRO AUXILIAR DE CUENTAS');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->getColor()->setRGB($navy);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->setCellValue('A3', $filtroLabel);
        $sheet->getStyle('A3')->getFont()->setSize(9)->getColor()->setRGB('444444');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells("A4:{$lastCol}4");
        $sheet->setCellValue('A4', 'Generado: ' . date('d/m/Y H:i') . ' — Solo movimientos aprobados');
        $sheet->getStyle('A4')->getFont()->setSize(8)->getColor()->setRGB('888888');
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->getRowDimension(1)->setRowHeight(18);
        $sheet->getRowDimension(2)->setRowHeight(16);
        $sheet->getRowDimension(3)->setRowHeight(14);
        $sheet->getRowDimension(4)->setRowHeight(13);

        $row = 5; // next data row

        // ── Reporte format: single column header ─────────────────
        if (!$isContable) {
            $row++;
            $headers = ['Código','Cuenta','Fecha','Asiento','Descripción','Debe','Haber','Saldo'];
            foreach ($headers as $i => $h) {
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
                $sheet->setCellValue("{$col}{$row}", $h);
            }
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                ->getFont()->setBold(true)->getColor()->setRGB($white);
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB($navy);
            $sheet->getStyle("D{$row}:H{$row}")->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            $sheet->getRowDimension($row)->setRowHeight(14);
            $sheet->freezePane("A" . ($row + 1));
            $row++;
        }

        // ── Write data ──────────────────────────────────────────
        foreach ($cuentasRender as $cId => $ac) {
            $meta = $ac['meta'];

            if ($isContable) {
                // Account header spanning all cols
                $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                $sheet->setCellValue("A{$row}", $meta->tipo . '   ' . $meta->codigo . ' — ' . $meta->cuenta_nombre);
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                    ->getFont()->setBold(true)->getColor()->setRGB($navy);
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($navyLt);
                $sheet->getRowDimension($row)->setRowHeight(14);
                $row++;

                // Column headers
                foreach (['Fecha','Asiento','Descripción','Debe','Haber','Saldo'] as $i => $h) {
                    $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
                    $sheet->setCellValue("{$col}{$row}", $h);
                }
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                    ->getFont()->setBold(true)->getColor()->setRGB($white);
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($navy);
                $sheet->getStyle("D{$row}:F{$row}")->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $sheet->getRowDimension($row)->setRowHeight(13);
                $row++;

                // Saldo anterior
                $sheet->setCellValue("A{$row}", 'Saldo anterior');
                $sheet->mergeCells("A{$row}:C{$row}");
                $sheet->getStyle("A{$row}")->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                if ($ac['previo'] != 0) {
                    $sheet->setCellValue("F{$row}", $ac['previo']);
                    $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode($numFmt);
                }
                $sheet->getStyle("A{$row}:F{$row}")
                    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($gray);
                $sheet->getStyle("A{$row}:F{$row}")->getFont()->setItalic(true)->setSize(8);
                $sheet->getRowDimension($row)->setRowHeight(12);
                $row++;
            } else {
                // Reporte: saldo anterior row (8 cols)
                $sheet->setCellValue("A{$row}", $meta->codigo);
                $sheet->setCellValue("B{$row}", $meta->tipo . ' — ' . $meta->cuenta_nombre . ' — Saldo anterior');
                $sheet->mergeCells("B{$row}:E{$row}");
                if ($ac['previo'] != 0) {
                    $sheet->setCellValue("H{$row}", $ac['previo']);
                    $sheet->getStyle("H{$row}")->getNumberFormat()->setFormatCode($numFmt);
                }
                $sheet->getStyle("A{$row}:H{$row}")
                    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($gray);
                $sheet->getStyle("A{$row}:H{$row}")->getFont()->setItalic(true)->setSize(8);
                $sheet->getStyle("H{$row}")->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $sheet->getRowDimension($row)->setRowHeight(12);
                $row++;
            }

            foreach ($ac['rows'] as $r) {
                if ($r['type'] === 'row') {
                    $l = $r['data'];
                    $desc = ($l->linea_desc ?: $l->asiento_desc) . ($l->referencia ? ' · ' . $l->referencia : '');
                    $astNum = 'AST-' . str_pad($l->numero_asiento, 5, '0', STR_PAD_LEFT);

                    if ($isContable) {
                        $sheet->setCellValue("A{$row}", date('d/m/Y', strtotime($l->fecha)));
                        $sheet->setCellValue("B{$row}", $astNum);
                        $sheet->setCellValue("C{$row}", $desc);
                        if ((float)$l->debe  > 0) { $sheet->setCellValue("D{$row}", (float)$l->debe);  $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode($numFmt); }
                        if ((float)$l->haber > 0) { $sheet->setCellValue("E{$row}", (float)$l->haber); $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode($numFmt); }
                        $sheet->setCellValue("F{$row}", $r['saldo']);
                        $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode($numFmt);
                        $sheet->getStyle("D{$row}:F{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    } else {
                        $sheet->setCellValue("A{$row}", $meta->codigo);
                        $sheet->setCellValue("B{$row}", $meta->cuenta_nombre);
                        $sheet->setCellValue("C{$row}", date('d/m/Y', strtotime($l->fecha)));
                        $sheet->setCellValue("D{$row}", $astNum);
                        $sheet->setCellValue("E{$row}", $desc);
                        if ((float)$l->debe  > 0) { $sheet->setCellValue("F{$row}", (float)$l->debe);  $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode($numFmt); }
                        if ((float)$l->haber > 0) { $sheet->setCellValue("G{$row}", (float)$l->haber); $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode($numFmt); }
                        $sheet->setCellValue("H{$row}", $r['saldo']);
                        $sheet->getStyle("H{$row}")->getNumberFormat()->setFormatCode($numFmt);
                        $sheet->getStyle("F{$row}:H{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    }
                    $sheet->getRowDimension($row)->setRowHeight(13);

                } elseif ($r['type'] === 'day_sub') {
                    $dLabel = 'Subtotal ' . date('d/m/Y', strtotime($r['label']));
                    if ($isContable) {
                        $sheet->mergeCells("A{$row}:C{$row}");
                        $sheet->setCellValue("A{$row}", $dLabel);
                        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                        $sheet->setCellValue("D{$row}", $r['debe']);  $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode($numFmt);
                        $sheet->setCellValue("E{$row}", $r['haber']); $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode($numFmt);
                        $sheet->getStyle("D{$row}:E{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    } else {
                        $sheet->mergeCells("A{$row}:E{$row}");
                        $sheet->setCellValue("A{$row}", $meta->cuenta_nombre . ' — ' . $dLabel);
                        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                        $sheet->setCellValue("F{$row}", $r['debe']);  $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode($numFmt);
                        $sheet->setCellValue("G{$row}", $r['haber']); $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode($numFmt);
                        $sheet->getStyle("F{$row}:G{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    }
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                        ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB($yellow);
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFont()->setItalic(true)->setBold(true)->setSize(8);
                    $sheet->getRowDimension($row)->setRowHeight(12);

                } elseif ($r['type'] === 'month_sub') {
                    [$anioM, $mesM] = explode('-', $r['label']);
                    $mLabel = 'Subtotal ' . $mn[(int)$mesM] . ' ' . $anioM;
                    if ($isContable) {
                        $sheet->mergeCells("A{$row}:C{$row}");
                        $sheet->setCellValue("A{$row}", $mLabel);
                        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                        $sheet->setCellValue("D{$row}", $r['debe']);  $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode($numFmt);
                        $sheet->setCellValue("E{$row}", $r['haber']); $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode($numFmt);
                        $sheet->getStyle("D{$row}:E{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    } else {
                        $sheet->mergeCells("A{$row}:E{$row}");
                        $sheet->setCellValue("A{$row}", $meta->cuenta_nombre . ' — ' . $mLabel);
                        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                        $sheet->setCellValue("F{$row}", $r['debe']);  $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode($numFmt);
                        $sheet->setCellValue("G{$row}", $r['haber']); $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode($numFmt);
                        $sheet->getStyle("F{$row}:G{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    }
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                        ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB($cyan);
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFont()->setItalic(true)->setBold(true);
                    $sheet->getRowDimension($row)->setRowHeight(13);

                } elseif ($r['type'] === 'total') {
                    $tLabel = 'Total ' . $meta->codigo . ($isContable ? '' : ' ' . $meta->cuenta_nombre);
                    if ($isContable) {
                        $sheet->mergeCells("A{$row}:C{$row}");
                        $sheet->setCellValue("A{$row}", $tLabel);
                        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                        $sheet->setCellValue("D{$row}", $r['debe']);  $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode($numFmt);
                        $sheet->setCellValue("E{$row}", $r['haber']); $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode($numFmt);
                        $sheet->setCellValue("F{$row}", $r['saldo']); $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode($numFmt);
                        $sheet->getStyle("D{$row}:F{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    } else {
                        $sheet->mergeCells("A{$row}:E{$row}");
                        $sheet->setCellValue("A{$row}", $tLabel);
                        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                        $sheet->setCellValue("F{$row}", $r['debe']);  $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode($numFmt);
                        $sheet->setCellValue("G{$row}", $r['haber']); $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode($numFmt);
                        $sheet->setCellValue("H{$row}", $r['saldo']); $sheet->getStyle("H{$row}")->getNumberFormat()->setFormatCode($numFmt);
                        $sheet->getStyle("F{$row}:H{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    }
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                        ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB($green);
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFont()->setBold(true);
                    $sheet->getRowDimension($row)->setRowHeight(14);
                }
                $row++;
            }

            if ($isContable) $row++; // blank separator between accounts
        }

        // ── Grand total ─────────────────────────────────────────
        $row++;
        $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
        $diff = abs($grandTotalD - $grandTotalH);
        $gtLabel = 'GRAN TOTAL — Debe: ' . number_format($grandTotalD, 2)
                 . '   Haber: ' . number_format($grandTotalH, 2)
                 . '   Diferencia: ' . number_format($diff, 2);
        $sheet->setCellValue("A{$row}", $gtLabel);
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")
            ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB($navyLt);
        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFont()->setBold(true)->setSize(11)->getColor()->setRGB($navy);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension($row)->setRowHeight(16);

        // ── Stream download ─────────────────────────────────────
        $filename = 'auxiliar_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // ─── MANTENIMIENTOS ─────────────────────────────────────────

    public function acumuladosActuales()
    {
        $chk = requerirPermiso('ver_mantenimientos_contables');
        if ($chk !== true) return $chk;

        $periodosModel = new ContPeriodosModel();
        $saldosModel   = new ContSaldosCuentasModel();

        $periodos  = $periodosModel->orderBy('anio','DESC')->orderBy('mes','DESC')->findAll();
        $periodoId = $this->request->getGet('periodo_id');
        $filas     = [];

        if ($periodoId) {
            $filas = $saldosModel->getByPeriodo($periodoId);
        }

        return view('contabilidad/mantenimientos/acumulados_actuales', [
            'periodos'  => $periodos,
            'periodoId' => $periodoId,
            'filas'     => $filas,
        ]);
    }

    public function acumuladosHistoricos()
    {
        $chk = requerirPermiso('ver_mantenimientos_contables');
        if ($chk !== true) return $chk;

        $histModel = new ContSaldosHistoricosModel();
        $db        = \Config\Database::connect();
        $anios     = $db->query('SELECT DISTINCT anio FROM cont_saldos_historicos ORDER BY anio DESC')->getResultArray();
        $anios     = array_column($anios, 'anio');
        $anioSel   = $this->request->getGet('anio') ?? (int)date('Y');
        $filas     = $anioSel ? $histModel->getByAnio((int)$anioSel) : [];

        return view('contabilidad/mantenimientos/acumulados_historicos', [
            'anios'   => $anios,
            'anioSel' => $anioSel,
            'filas'   => $filas,
        ]);
    }

    public function transaccionesHistoricas()
    {
        $chk = requerirPermiso('ver_mantenimientos_contables');
        if ($chk !== true) return $chk;

        $histModel    = new ContTransaccionesHistModel();
        $cuentasModel = new ContPlanCuentasModel();
        $db           = \Config\Database::connect();

        $anios    = $db->query('SELECT DISTINCT anio FROM cont_transacciones_hist ORDER BY anio DESC')->getResultArray();
        $anios    = array_column($anios, 'anio');
        $cuentaId = $this->request->getGet('cuenta_id');
        $anioSel  = (int)($this->request->getGet('anio') ?: date('Y'));
        $mesSel   = $this->request->getGet('mes') ?: null;
        $filas    = [];
        $cuenta   = null;

        // Asegurar que el año actual esté en la lista aunque la tabla esté vacía
        if (!in_array((string)$anioSel, $anios)) {
            array_unshift($anios, (string)$anioSel);
        }

        if ($cuentaId) {
            $cuenta = $cuentasModel->find($cuentaId);
            $filas  = $histModel->getByCuenta((int)$cuentaId, $anioSel, $mesSel ? (int)$mesSel : null);
        }

        return view('contabilidad/mantenimientos/transacciones_historicas', [
            'anios'    => $anios,
            'anioSel'  => $anioSel,
            'mesSel'   => $mesSel,
            'cuentaId' => $cuentaId,
            'cuenta'   => $cuenta,
            'filas'    => $filas,
        ]);
    }
}
