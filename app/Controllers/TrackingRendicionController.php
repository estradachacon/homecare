<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\TrackingHeaderModel;
use App\Models\TrackingDetailsModel;
use Mpdf\Mpdf; // Clase mPDF importada (La ruta es correcta si Composer funciona)

class TrackingRendicionController extends BaseController
{
    protected $headerModel;
    protected $detailModel;

    public function __construct()
    {
        $this->headerModel = new TrackingHeaderModel();
        $this->detailModel = new TrackingDetailsModel();
    }

    public function index($trackingId)
    {
        $header = $this->headerModel->getHeaderWithRelations($trackingId);
        $userModel = new \App\Models\UserModel();

        $motoristas = $userModel
            ->where('role_id', 4)
            ->orderBy('user_name', 'ASC')
            ->findAll();

        if (!$header) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Tracking ID $trackingId no encontrado");
        }

        // $paquetes incluirá ahora el vendedor
        $paquetes = $this->detailModel->getDetailsWithPackages($trackingId);

        // Buscar el nombre del motorista correspondiente
        $motoristaNombre = '';
        foreach ($motoristas as $m) {
            if ($m['id'] == $header->user_id) {
                $motoristaNombre = $m['user_name'];
                break;
            }
        }

        // Asignar el nombre del motorista al objeto tracking para la vista
        $header->motorista_name = $motoristaNombre;

        return view('trackings/rendicion_index', [
            'tracking' => $header,
            'detalles' => $paquetes, // Cambiado 'paquetes' a 'detalles' para coincidir con la vista original del usuario
            'motoristaNombre' => $motoristaNombre, // enviar nombre
        ]);
    }

    public function save()
    {
        helper(['form', 'bitacora', 'transaction']);
        $db = \Config\Database::connect();

        // 🔧 Helper interno para sumar saldo
        $sumarSaldo = function ($accountId, $monto) use ($db) {
            if ($monto <= 0) return;

            $db->table('accounts')
                ->where('id', $accountId)
                ->set('balance', 'balance + ' . $monto, false)
                ->update();
        };

        // ============================================================
        // 1) DATOS DEL POST
        // ============================================================
        $trackingId       = $this->request->getPost('tracking_id');
        $regresados       = $this->request->getPost('regresados') ?? [];
        $recolectadosSolo = $this->request->getPost('recolectados_solo') ?? [];
        $cuentasAsignadas = $this->request->getPost('cuenta_asignada') ?? [];

        // ============================================================
        // 2) DATA BASE
        // ============================================================
        $paquetes     = $this->detailModel->getDetailsWithPackages($trackingId);
        $packageModel = new \App\Models\PackageModel();

        $header = $this->headerModel->find($trackingId);
        $motoristaNombre = '';
        if ($header) {
            $userModel = new \App\Models\UserModel();
            $motorista = $userModel->find($header->user_id);
            $motoristaNombre = $motorista ? $motorista['user_name'] : '';
        }

        $session = session();
        $userId  = $session->get('user_id');
        $today   = date('Y-m-d');

        $paquetesModificados = [];

        // ============================================================
        // 3) LOOP PRINCIPAL
        // ============================================================
        foreach ($paquetes as $p) {

            // --------------------------------------------------------
            // A) CONTAR DESTINOS (solo servicio 3)
            // --------------------------------------------------------
            $destinoCount = 1;
            if ($p->tipo_servicio == 3) {
                if (!empty($p->destino_personalizado)) $destinoCount++;
                if (!empty($p->puntofijo_nombre)) $destinoCount++;
            }

            // --------------------------------------------------------
            // B) DETERMINAR ESTATUS
            // --------------------------------------------------------
            if (in_array($p->id, $regresados)) {

                if ($p->tipo_servicio == 3) {
                    $newStatus = ($destinoCount == 1)
                        ? 'recolecta_fallida'
                        : 'no_retirado';
                } else {
                    $newStatus = 'no_retirado';
                }
            } else {

                if ($p->tipo_servicio == 3) {

                    if ($destinoCount == 1) {
                        $newStatus = 'recolectado';
                    } else {
                        $newStatus = in_array($p->id, $recolectadosSolo)
                            ? 'recolectado'
                            : 'entregado';
                    }
                } else {
                    $newStatus = 'entregado';
                }
            }

            // --------------------------------------------------------
            // C) UPDATE DEL PAQUETE
            // --------------------------------------------------------
            $updateData = ['estatus' => $newStatus];

            if (in_array($newStatus, ['entregado', 'recolectado'])) {
                $updateData['fecha_pack_entregado'] = $today;
            }

            // 👉 Bandera financiera SOLO se setea, no se decide dinero aquí
            if ($p->tipo_servicio == 3 && in_array($newStatus, ['recolectado', 'entregado'])) {
                $updateData['flete_rendido'] = 1;
            }

            $packageModel->update($p->package_id, $updateData);
            $paquetesModificados[] = "ID {$p->package_id} → {$newStatus}";

            // --------------------------------------------------------
            // D) NO COBRAR SI REGRESADO
            // --------------------------------------------------------
            if (in_array($p->id, $regresados)) {
                continue;
            }

            // --------------------------------------------------------
            // E) CUENTA DE INGRESO
            // --------------------------------------------------------
            $cuentaDeIngreso = isset($cuentasAsignadas[$p->id])
                ? (int)$cuentasAsignadas[$p->id]
                : 1;

            $packageModel->update($p->package_id, [
                'pago_cuenta' => $cuentaDeIngreso
            ]);

            // --------------------------------------------------------
            // F) MONTOS
            // --------------------------------------------------------
            $montoPaquete = floatval($p->monto);
            $montoVendedor = ($p->toggle_pago_parcial == 0)
                ? floatval($p->flete_total)
                : floatval($p->flete_pagado);

            $togglePago      = (int)$p->toggle_pago_parcial;
            $fleteYaRendido  = !empty($p->flete_rendido);

            // ========================================================
            // G) LÓGICA FINANCIERA REAL
            // ========================================================
            if ($p->tipo_servicio == 3) {

                // 🟡 SOLO RECOLECTA (primer evento)
                if ($newStatus === 'recolectado' && !$fleteYaRendido) {

                    $sumarSaldo($cuentaDeIngreso, $montoVendedor);

                    registrarEntrada(
                        $cuentaDeIngreso,
                        $montoVendedor,
                        ($togglePago === 0
                            ? "Flete completo (solo recolección)"
                            : "Flete parcial (solo recolección)"),
                        "Paquete {$p->package_id} | Tracking {$trackingId}",
                        $trackingId
                    );
                }

                // 🟢 ENTREGA FINAL
                if ($newStatus === 'entregado') {

                    // ✔ Siempre paquete
                    $sumarSaldo($cuentaDeIngreso, $montoPaquete);

                    registrarEntrada(
                        $cuentaDeIngreso,
                        $montoPaquete,
                        "Pago de paquete recibido (entrega final)",
                        "Paquete {$p->package_id} | Tracking {$trackingId}",
                        $trackingId
                    );

                    // ✔ Flete SOLO si no fue rendido antes
                    if (!$fleteYaRendido) {

                        $sumarSaldo($cuentaDeIngreso, $montoVendedor);

                        registrarEntrada(
                            $cuentaDeIngreso,
                            $montoVendedor,
                            ($togglePago === 0 ? "Flete completo" : "Flete parcial"),
                            "Paquete {$p->package_id} | Tracking {$trackingId}",
                            $trackingId
                        );
                    }
                }
            } else {

                // 🔵 SERVICIO NORMAL
                $sumarSaldo($cuentaDeIngreso, $montoPaquete);

                registrarEntrada(
                    $cuentaDeIngreso,
                    $montoPaquete,
                    "Recolecta de remuneración (entrega directa)",
                    "Paquete {$p->package_id} | Tracking {$trackingId}",
                    $trackingId
                );
            }
        }

        // ============================================================
        // 4) FINALIZAR TRACKING
        // ============================================================
        $this->headerModel->update($trackingId, ['status' => 'finalizado',
        'rendicion_procesada' => 1]);

        registrar_bitacora(
            'Rendición de Tracking Finalizada',
            'Tracking',
            "Se procesó la rendición del Tracking ID $trackingId (Motorista: $motoristaNombre). Estados: "
                . implode(', ', $paquetesModificados),
            $userId
        );

        return redirect()
            ->to(base_url('tracking/' . $trackingId))
            ->with('success', 'Rendición guardada correctamente');
    }



    public function pdf($trackingId)
    {
        // 1. Cargar datos
        $header = $this->headerModel->getHeaderWithRelations($trackingId);
        $paquetes = $this->detailModel->getDetailsWithPackages($trackingId);

        if (!$header) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Tracking ID $trackingId no encontrado");
        }

        $tiposServicio = [
            1 => 'Punto fijo',
            2 => 'Personalizado',
            3 => 'Recolecta de paquete',
            4 => 'Casillero'
        ];

        // 2. Renderizar la vista a una cadena HTML
        // NOTA: Se está usando 'detalles' en la vista original del usuario, se mantiene por compatibilidad.
        // Asegúrate de que 'trackings/pdf_tracking' exista y no 'trackings/rendicion_index' como en tu index()
        $html = view('trackings/pdf_tracking', [
            'tracking' => $header,
            'detalles' => $paquetes,
            'tiposServicio' => $tiposServicio
        ]);

        // ** Importante para evitar conflictos de salida (como el que tenías con Dompdf) **
        // Limpiamos el buffer de salida por si acaso alguna librería o espacio invisible ha impreso algo.
        if (ob_get_length()) {
            ob_clean();
        }

        // 3. Inicializar mPDF con la configuración más segura
        // La ruta temporal DEBE existir y DEBE ser escribible por el servidor web.
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            // Usamos WRITEPATH, que es la forma correcta en CodeIgniter
            'tempDir' => WRITEPATH . 'temp'
        ]);

        // 4. Escribir el HTML
        $mpdf->WriteHTML($html);

        // 5. Generar y enviar el PDF
        $filename = "tracking_{$trackingId}.pdf";

        // El método Output con 'S' devuelve el PDF como una cadena (string)
        $pdfOutput = $mpdf->Output($filename, 'S');

        // Retornamos el archivo usando el objeto Response de CodeIgniter
        return $this->response
            ->setStatusCode(200)
            ->setContentType('application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"') // 'inline' para abrir en navegador
            ->setBody($pdfOutput);
    }
}
