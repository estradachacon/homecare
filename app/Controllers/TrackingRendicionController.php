<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\TrackingHeaderModel;
use App\Models\TrackingDetailsModel;
use Dompdf\Dompdf;
use Dompdf\Options;

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

        return view('trackings/rendicion_index', [
            'tracking' => $header,
            'paquetes' => $paquetes,
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
            if (!empty($p->destino_personalizado)) $destinoCount++;
            if (!empty($p->puntofijo_nombre)) $destinoCount++;
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

        // Registrar en bitácora incluyendo el nombre del motorista
        registrar_bitacora(
            'Rendición de Paquete',
            'Paquetería',
            "Paquete ID {$p->package_id} actualizado a estatus {$newStatus}. Motorista: {$motoristaNombre}",
            $userId
        );

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
    $header = $this->headerModel->getHeaderWithRelations($trackingId);
    $paquetes = $this->detailModel->getDetailsWithPackages($trackingId);

    $tiposServicio = [
        1 => 'Punto fijo',
        2 => 'Personalizado',
        3 => 'Recolecta de paquete',
        4 => 'Casillero'
    ];

    $html = view('trackings/pdf_tracking', [
        'tracking' => $header,
        'detalles' => $paquetes,
        'tiposServicio' => $tiposServicio
    ]);

    // Configuración Dompdf
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Esto envía el PDF al navegador con las cabeceras correctas
    return $dompdf->stream("tracking_{$trackingId}.pdf", [
        "Attachment" => false // true para descargar automáticamente
    ]);
}
}
