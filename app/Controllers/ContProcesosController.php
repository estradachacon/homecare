<?php

namespace App\Controllers;

use App\Models\ContPeriodosModel;
use App\Models\ContAsientosHeadModel;
use App\Models\ContAsientosDetalleModel;
use App\Models\ContSaldosCuentasModel;
use App\Models\ContSaldosHistoricosModel;
use App\Models\ContTransaccionesHistModel;
use App\Models\ContPlanCuentasModel;
use App\Models\ContConfiguracionModel;

class ContProcesosController extends BaseController
{
    // ─── CIERRE DE MES ───────────────────────────────────────────

    public function cierreMes()
    {
        $chk = requerirPermiso('ejecutar_cierre_mes');
        if ($chk !== true) return $chk;

        $periodosModel = new ContPeriodosModel();
        $periodos = $periodosModel->where('estado','ABIERTO')
                                  ->orderBy('anio','DESC')
                                  ->orderBy('mes','DESC')
                                  ->findAll();

        return view('contabilidad/procesos/cierre_mes', ['periodos' => $periodos]);
    }

    public function ejecutarCierreMes()
    {
        $chk = requerirPermiso('ejecutar_cierre_mes');
        if ($chk !== true) return $chk;

        $periodoId = $this->request->getPost('periodo_id');
        if (!$periodoId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Selecciona un período']);
        }

        $periodosModel = new ContPeriodosModel();
        $headModel     = new ContAsientosHeadModel();
        $detalleModel  = new ContAsientosDetalleModel();
        $saldosModel   = new ContSaldosCuentasModel();
        $histModel     = new ContSaldosHistoricosModel();

        $periodo = $periodosModel->find($periodoId);
        if (!$periodo || $periodo->estado === 'CERRADO') {
            return $this->response->setJSON(['success' => false, 'message' => 'Período no válido o ya cerrado']);
        }

        // Verificar no hay borradores
        $borradores = $headModel->where('periodo_id', $periodoId)->where('estado', 'BORRADOR')->countAllResults();
        if ($borradores > 0) {
            return $this->response->setJSON(['success' => false, 'message' => "Existen $borradores asiento(s) en borrador pendientes de aprobar"]);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Guardar saldos en histórico mensual
        $saldos = $saldosModel->getByPeriodo((int)$periodoId);
        foreach ($saldos as $s) {
            $existing = $histModel->where('cuenta_id', $s->cuenta_id)
                                  ->where('anio', $periodo->anio)
                                  ->where('mes', $periodo->mes)
                                  ->first();
            $histData = [
                'cuenta_id'     => $s->cuenta_id,
                'anio'          => $periodo->anio,
                'mes'           => $periodo->mes,
                'saldo_inicial' => $s->saldo_inicial,
                'total_debe'    => $s->total_debe,
                'total_haber'   => $s->total_haber,
                'saldo_final'   => $s->saldo_final,
            ];
            if ($existing) {
                $histModel->update($existing->id, $histData);
            } else {
                $histModel->insert($histData);
            }
        }

        // 2. Cerrar el período
        $periodosModel->update($periodoId, [
            'estado'            => 'CERRADO',
            'fecha_cierre'      => date('Y-m-d'),
            'usuario_cierre_id' => session()->get('id'),
        ]);

        // 3. Crear el siguiente período automáticamente
        $nextMes  = $periodo->mes == 12 ? 1 : $periodo->mes + 1;
        $nextAnio = $periodo->mes == 12 ? $periodo->anio + 1 : $periodo->anio;

        if (!$periodosModel->existePeriodo($nextAnio, $nextMes)) {
            $periodosModel->insert([
                'anio'           => $nextAnio,
                'mes'            => $nextMes,
                'estado'         => 'ABIERTO',
                'fecha_apertura' => date('Y-m-d'),
            ]);
        }

        // 4. Trasladar saldo final como saldo inicial del siguiente período (solo cuentas balance)
        $nextPeriodo = $periodosModel->getPeriodoByAnioMes($nextAnio, $nextMes);
        if ($nextPeriodo) {
            foreach ($saldos as $s) {
                // Solo activos, pasivos y capital arrastran saldo (balance = permanentes)
                // Ingresos, costos y gastos se cierran a 0
                if (in_array($s->tipo ?? '', ['ACTIVO','PASIVO','CAPITAL'])) {
                    $nextSaldo = $saldosModel->getByCuentaPeriodo((int)$s->cuenta_id, $nextPeriodo->id);
                    if (!$nextSaldo) {
                        $saldosModel->insert([
                            'cuenta_id'     => $s->cuenta_id,
                            'periodo_id'    => $nextPeriodo->id,
                            'saldo_inicial' => $s->saldo_final,
                            'total_debe'    => 0,
                            'total_haber'   => 0,
                            'saldo_final'   => $s->saldo_final,
                        ]);
                    }
                }
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error en la base de datos']);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => "Cierre de mes completado. Período {$periodo->mes}/{$periodo->anio} cerrado."
        ]);
    }

    // ─── CIERRE ANUAL ─────────────────────────────────────────────

    public function cierreAnual()
    {
        $chk = requerirPermiso('ejecutar_cierre_anual');
        if ($chk !== true) return $chk;

        $db   = \Config\Database::connect();
        $anios = $db->query(
            'SELECT DISTINCT anio FROM cont_periodos WHERE estado="CERRADO" ORDER BY anio DESC'
        )->getResultArray();
        $anios = array_column($anios, 'anio');

        return view('contabilidad/procesos/cierre_anual', ['anios' => $anios]);
    }

    public function ejecutarCierreAnual()
    {
        $chk = requerirPermiso('ejecutar_cierre_anual');
        if ($chk !== true) return $chk;

        $anio = (int)$this->request->getPost('anio');
        if (!$anio) {
            return $this->response->setJSON(['success' => false, 'message' => 'Selecciona un año']);
        }

        $db            = \Config\Database::connect();
        $periodosModel = new ContPeriodosModel();
        $histModel     = new ContSaldosHistoricosModel();
        $configModel   = new ContConfiguracionModel();
        $headModel     = new ContAsientosHeadModel();
        $detalleModel  = new ContAsientosDetalleModel();
        $saldosModel   = new ContSaldosCuentasModel();

        // Verificar que todos los períodos del año estén cerrados
        $abiertos = $periodosModel->where('anio', $anio)->where('estado','ABIERTO')->countAllResults();
        if ($abiertos > 0) {
            return $this->response->setJSON(['success' => false, 'message' => "Hay $abiertos período(s) abiertos en $anio. Cierra todos antes de hacer el cierre anual."]);
        }

        $config = $configModel->getConfig();
        $cuentaResultado = $config->cuenta_resultado_id ?? null;

        $db->transStart();

        // 1. Calcular utilidad/pérdida del año
        $resumenAnual = $histModel->getResumenAnual($anio);
        $totalIngresos = 0;
        $totalCostos   = 0;
        $totalGastos   = 0;

        foreach ($resumenAnual as $r) {
            if ($r->tipo === 'INGRESO') $totalIngresos += (float)$r->saldo_cierre;
            if ($r->tipo === 'COSTO')   $totalCostos   += (float)$r->saldo_cierre;
            if ($r->tipo === 'GASTO')   $totalGastos   += (float)$r->saldo_cierre;
        }

        $utilidad = $totalIngresos - $totalCostos - $totalGastos;

        // 2. Crear asiento de cierre (si hay cuenta de resultado configurada)
        if ($cuentaResultado) {
            $periodoAnterior = $periodosModel->getPeriodoByAnioMes($anio, 12);
            if ($periodoAnterior) {
                $nextNum = $headModel->getSiguienteNumero();
                $asientoId = $headModel->insert([
                    'numero_asiento' => $nextNum,
                    'fecha'          => "$anio-12-31",
                    'descripcion'    => "Cierre anual $anio",
                    'tipo'           => 'CIERRE',
                    'estado'         => 'APROBADO',
                    'periodo_id'     => $periodoAnterior->id,
                    'total_debe'     => abs($utilidad),
                    'total_haber'    => abs($utilidad),
                    'usuario_id'     => session()->get('id'),
                    'usuario_aprueba_id' => session()->get('id'),
                    'fecha_aprobacion'   => date('Y-m-d H:i:s'),
                ]);

                if ($utilidad >= 0) {
                    // Utilidad: cargo ingresos / abono resultado
                    foreach ($resumenAnual as $r) {
                        if ($r->tipo === 'INGRESO' && (float)$r->saldo_cierre > 0) {
                            $detalleModel->insert(['asiento_id' => $asientoId, 'cuenta_id' => $r->cuenta_id, 'debe' => (float)$r->saldo_cierre, 'haber' => 0, 'orden' => 1]);
                        }
                        if (in_array($r->tipo, ['COSTO','GASTO']) && (float)$r->saldo_cierre > 0) {
                            $detalleModel->insert(['asiento_id' => $asientoId, 'cuenta_id' => $r->cuenta_id, 'debe' => 0, 'haber' => (float)$r->saldo_cierre, 'orden' => 2]);
                        }
                    }
                    $detalleModel->insert(['asiento_id' => $asientoId, 'cuenta_id' => $cuentaResultado, 'debe' => 0, 'haber' => $utilidad, 'orden' => 99]);
                } else {
                    $detalleModel->insert(['asiento_id' => $asientoId, 'cuenta_id' => $cuentaResultado, 'debe' => abs($utilidad), 'haber' => 0, 'orden' => 1]);
                    foreach ($resumenAnual as $r) {
                        if ($r->tipo === 'INGRESO') $detalleModel->insert(['asiento_id' => $asientoId, 'cuenta_id' => $r->cuenta_id, 'debe' => (float)$r->saldo_cierre, 'haber' => 0, 'orden' => 2]);
                        if (in_array($r->tipo,['COSTO','GASTO'])) $detalleModel->insert(['asiento_id' => $asientoId, 'cuenta_id' => $r->cuenta_id, 'debe' => 0, 'haber' => (float)$r->saldo_cierre, 'orden' => 3]);
                    }
                }
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error en la base de datos']);
        }

        return $this->response->setJSON([
            'success'  => true,
            'message'  => "Cierre anual $anio completado correctamente",
            'utilidad' => $utilidad,
        ]);
    }
}
