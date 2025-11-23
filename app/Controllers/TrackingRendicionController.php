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
        $trackingId = $this->request->getPost('tracking_id');
        $regresados = $this->request->getPost('regresados') ?? [];

        // Cargar los paquetes asociados al tracking
        $paquetes = $this->detailModel->getDetailsWithPackages($trackingId);

        $packageModel = new \App\Models\PackageModel();

        // Obtener el nombre del motorista
        $header = $this->headerModel->find($trackingId);
        $motoristaNombre = '';
        if ($header) {
            $userModel = new \App\Models\UserModel();
            $motorista = $userModel->find($header->user_id);
            $motoristaNombre = $motorista ? $motorista['user_name'] : '';
        }

        $session = session();
        $userId = $session->get('user_id');

        $today = date('Y-m-d'); // Fecha actual para los paquetes entregados

        foreach ($paquetes as $p) {
            // Contar destinos (solo aplica para tipo_servicio = 3)
            $destinoCount = 1; // mínimo 1
            if ($p->tipo_servicio == 3) {
                if (!empty($p->destino_personalizado))
                    $destinoCount++;
                if (!empty($p->puntofijo_nombre))
                    $destinoCount++;
            }

            // Determinar nuevo estatus
            if (in_array($p->id, $regresados)) {
                // No exitoso
                if ($p->tipo_servicio == 3) {
                    $newStatus = ($destinoCount == 1) ? 'recolecta_fallida' : 'no_retirado';
                } else {
                    $newStatus = 'no_retirado';
                }
            } else {
                // Exitoso
                if ($p->tipo_servicio == 3) {
                    $newStatus = ($destinoCount == 1) ? 'recolectado' : 'entregado';
                } else {
                    $newStatus = 'entregado';
                }
            }

            // Preparar datos de actualización
            $updateData = ['estatus' => $newStatus];

            // Si es entregado, escribir fecha en fecha_pack_entregado
            if ($newStatus === 'entregado' || $newStatus === 'recolectado') {
                $updateData['fecha_pack_entregado'] = $today;
            }

            // Nota: Aquí se asume que la función registrar_bitacora existe globalmente
            // registrar_bitacora(
            //     'Rendición de Paquete',
            //     'Paquetería',
            //     "Paquete ID {$p->package_id} actualizado a estatus {$newStatus}. Motorista: {$motoristaNombre}",
            //     $userId
            // );

            // Actualizar estatus y fecha en la tabla packages
            $packageModel->update($p->package_id, $updateData);
        }

        // Finalmente, marcar el tracking como finalizado
        $this->headerModel->update($trackingId, ['status' => 'finalizado']);

        return redirect()->to('tracking/' . $trackingId)
            ->with('success', 'Rendición procesada con éxito.');
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