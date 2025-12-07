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

        $trackingId       = $this->request->getPost('tracking_id');
        $regresados       = $this->request->getPost('regresados') ?? [];
        $recolectadosSolo = $this->request->getPost('recolectados_solo') ?? [];

        // Paquetes del tracking
        $paquetes = $this->detailModel->getDetailsWithPackages($trackingId);
        $packageModel = new \App\Models\PackageModel();

        // Info del motorista
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

        foreach ($paquetes as $p) {

            // ============================================================
            // 1) CONTAR DESTINOS (solo tipo 3)
            // ============================================================
            $destinoCount = 1;
            if ($p->tipo_servicio == 3) {
                if (!empty($p->destino_personalizado)) $destinoCount++;
                if (!empty($p->puntofijo_nombre)) $destinoCount++;
            }

            // ============================================================
            // 2) DETERMINAR EL NUEVO ESTATUS
            // ============================================================
            if (in_array($p->id, $regresados)) {

                if ($p->tipo_servicio == 3) {
                    $newStatus = ($destinoCount == 1)
                        ? 'recolecta_fallida'
                        : 'no_retirado';
                } else {
                    $newStatus = 'no_retirado';
                }
            } else {

                // EXITOSO
                if ($p->tipo_servicio == 3) {

                    if ($destinoCount == 1) {
                        $newStatus = 'recolectado';
                    } elseif ($destinoCount >= 2) {

                        if (in_array($p->id, $recolectadosSolo)) {
                            $newStatus = 'recolectado';
                        } else {
                            $newStatus = 'entregado';
                        }
                    }
                } else {
                    $newStatus = 'entregado';
                }
            }

            // ============================================================
            // 3) PREPARAR UPDATE EN DB
            // ============================================================
            $updateData = ['estatus' => $newStatus];

            if ($newStatus === 'entregado' || $newStatus === 'recolectado') {
                $updateData['fecha_pack_entregado'] = $today;
            }

            $packageModel->update($p->package_id, $updateData);

            // Guardar cambios para bitácora
            $paquetesModificados[] = "ID {$p->package_id} → {$newStatus}";

            // ============================================================
            // 4) TRANSACCIONES PARA RENDICIÓN
            // ============================================================

            // ❌ NO SE REGISTRA NADA PARA PAQUETES REGRESADOS
            if (in_array($p->id, $regresados)) {
                continue;
            }

            // Cuentas: aquí ajusta la cuenta destino según tu sistema
            $cuentaDeIngreso = 1; // ejemplo: cuenta de la empresa

            // ----- Determinar montos -----
            $montoPaquete = floatval($p->monto);

            // Flete dependiendo del toggle parcial
            $montoVendedor = ($p->toggle_pago_parcial == 0)
                ? floatval($p->flete_total)
                : floatval($p->flete_pagado);
            $togglePago = intval($p->toggle_pago_parcial);
            // ----- Registrar según caso -----
            // ----- Registrar según caso -----
            if ($p->tipo_servicio == 3) {

                // 🟡 SOLO RECOLECTA (no hubo entrega final)
                if (in_array($p->id, $recolectadosSolo)) {

                    // 1️⃣ Flete (solo se cobra el flete porque no hubo entrega final)
                    registrarEntrada(
                        $cuentaDeIngreso,
                        $montoVendedor,
                        ($togglePago === 0 ? "Flete completo (solo recolección)" : "Flete parcial (solo recolección)"),
                        "Paquete {$p->package_id} | Tracking {$trackingId}",
                        $trackingId
                    );

                    // En este modo NO sumas remuneración porque aún no hay entrega

                } else {

                    // 🟢 RECOLECTA + ENTREGA FINAL
                    // Se debe separar remuneración y flete en dos transacciones

                    // 1️⃣ Remuneración del paquete
                    registrarEntrada(
                        $cuentaDeIngreso,
                        $montoPaquete,
                        "Remuneración del paquete (recolección + entrega)",
                        "Paquete {$p->package_id} | Tracking {$trackingId}",
                        $trackingId
                    );

                    // 2️⃣ Flete (total o parcial)
                    registrarEntrada(
                        $cuentaDeIngreso,
                        $montoVendedor,
                        ($togglePago === 0 ? "Flete completo" : "Flete parcial"),
                        "Paquete {$p->package_id} | Tracking {$trackingId}",
                        $trackingId
                    );
                }
            } else {
                registrarEntrada(
                    $cuentaDeIngreso,
                    $montoPaquete,
                    "Recolecta de remuneración (entrega directa)",
                    "Paquete {$p->package_id} | Tracking {$trackingId}",
                    $trackingId
                );
            }
        }

        // 5) MARCAR EL TRACKING COMO FINALIZADO
        $this->headerModel->update($trackingId, ['status' => 'finalizado']);

        // 6) BITÁCORA
        registrar_bitacora(
            'Rendición de Tracking Finalizada',
            'Tracking',
            "Se procesó la rendición del Tracking ID $trackingId (Motorista: $motoristaNombre). Estados: "
                . implode(', ', $paquetesModificados),
            $userId
        );

        return redirect()->to('tracking/' . $trackingId)
            ->with('success', 'Rendición procesada con éxito.');
    }


    public function pdf($trackingId)
    {
        // ... (Tu código existente para pdf)

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
