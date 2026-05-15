<?php

namespace App\Controllers;

use App\Models\RecuperosModel;
use App\Models\RecuperosDetalleModel;
use App\Models\FacturaHeadModel;
use App\Models\FacturaDetalleModel;
use App\Models\ClienteModel;

class RecuperosController extends BaseController
{
    private function puedeConsultarRecuperos(): bool
    {
        return tienePermiso('ver_recuperos') || tienePermiso('crear_recupero');
    }

    // ─── LISTADO ──────────────────────────────────────────────────

    public function index()
    {
        $chk = requerirPermiso('ver_recuperos');
        if ($chk !== true) return $chk;

        $model   = new RecuperosModel();
        $filtros = [
            'cliente_id'  => $this->request->getGet('cliente_id'),
            'estado'      => $this->request->getGet('estado'),
            'fecha_desde' => $this->request->getGet('fecha_desde'),
            'fecha_hasta' => $this->request->getGet('fecha_hasta'),
        ];

        if (!puedeVerDocumentosTodosVendedores()) {
            $filtros['seller_id'] = vendedorUsuarioActual() ?? -1;
        }

        $recuperos = $model->getListado($filtros, 20);
        $pager     = $model->pager;
        $clientes  = (new ClienteModel())->orderBy('nombre')->findAll();

        return view('recuperos/index', [
            'recuperos' => $recuperos,
            'pager'     => $pager,
            'clientes'  => $clientes,
            'filtros'   => $filtros,
        ]);
    }

    // ─── FORMULARIO NUEVO ─────────────────────────────────────────

    public function nuevo()
    {
        $chk = requerirPermiso('crear_recupero');
        if ($chk !== true) return $chk;

        return view('recuperos/nuevo');
    }

    // ─── GUARDAR ──────────────────────────────────────────────────

    public function store()
    {
        $chk = requerirPermiso('crear_recupero');
        if ($chk !== true) return $chk;

        $payload = $this->request->getPost('payload');
        $data = $payload ? json_decode($payload, true) : $this->request->getJSON(true);

        if (!is_array($data)) {
            return $this->response->setJSON(['success' => false, 'message' => 'No se pudo leer la informacion enviada.']);
        }

        if (empty($data['cliente_id']) || empty($data['fecha']) || empty($data['facturas'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Datos incompletos. Verifica cliente, fecha y facturas.']);
        }

        if (count($data['facturas']) < 1) {
            return $this->response->setJSON(['success' => false, 'message' => 'Agrega al menos una factura al recupero']);
        }

        $recuperosModel = new RecuperosModel();
        $detalleModel   = new RecuperosDetalleModel();
        $facturaModel   = new FacturaHeadModel();
        $db             = \Config\Database::connect();
        $verTodos       = puedeVerDocumentosTodosVendedores();
        $sellerScope    = $verTodos ? null : vendedorUsuarioActual();

        if (!$verTodos && !$sellerScope) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tu usuario no tiene vendedor asociado para registrar recuperos.']);
        }

        // Validar montos antes de la transacción
        $total = 0;
        foreach ($data['facturas'] as $f) {
            $monto     = (float)($f['monto'] ?? 0);
            $facturaId = (int)($f['factura_id'] ?? 0);

            if ($monto <= 0) {
                return $this->response->setJSON(['success' => false, 'message' => 'Todos los montos deben ser mayores a cero']);
            }

            $factura = $facturaModel->find($facturaId);
            if (!$factura) {
                return $this->response->setJSON(['success' => false, 'message' => "Factura ID {$facturaId} no encontrada"]);
            }
            if ($sellerScope && (int)$factura->vendedor_id !== (int)$sellerScope) {
                return $this->response->setJSON(['success' => false, 'message' => 'No puedes registrar recuperos sobre facturas de otro vendedor.']);
            }
            if ($monto > (float)$factura->saldo + 0.01) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => "El monto \$" . number_format($monto, 2) . " supera el saldo \$" . number_format($factura->saldo, 2) . " de {$factura->numero_control}",
                ]);
            }

            $total += $monto;
        }

        $archivoData = [];
        $archivo = $this->request->getFile('archivo');

        if ($archivo && $archivo->isValid() && !$archivo->hasMoved()) {
            $maxSize = 8 * 1024 * 1024;
            $allowed = [
                'image/jpeg', 'image/png', 'image/webp', 'image/gif',
                'application/pdf',
            ];

            $archivoSize = $archivo->getSize();
            $archivoMime = $archivo->getMimeType() ?: $archivo->getClientMimeType();
            $archivoNombre = $archivo->getClientName();

            if ($archivoSize > $maxSize) {
                return $this->response->setJSON(['success' => false, 'message' => 'El archivo no debe superar 8 MB.']);
            }

            if (!in_array($archivoMime, $allowed, true)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Solo se permiten imagenes o PDF como respaldo.']);
            }

            $uploadPath = WRITEPATH . 'uploads/recuperos';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0775, true);
            }

            $fileName = $archivo->getRandomName();

            if (in_array($archivoMime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                $fileName = pathinfo($fileName, PATHINFO_FILENAME) . '.jpg';
                $targetPath = $uploadPath . DIRECTORY_SEPARATOR . $fileName;

                try {
                    \Config\Services::image('gd')
                        ->withFile($archivo->getTempName())
                        ->resize(1600, 1600, true, 'auto')
                        ->save($targetPath, 78);

                    $archivoMime = 'image/jpeg';
                    $archivoSize = is_file($targetPath) ? filesize($targetPath) : $archivoSize;
                } catch (\Throwable $e) {
                    log_message('error', 'No se pudo optimizar comprobante de recupero: ' . $e->getMessage());
                    $fileName = $archivo->getRandomName();
                    $archivo->move($uploadPath, $fileName);
                }
            } else {
                $archivo->move($uploadPath, $fileName);
            }

            $archivoData = [
                'archivo_ruta'   => 'uploads/recuperos/' . $fileName,
                'archivo_nombre' => $archivoNombre,
                'archivo_tipo'   => $archivoMime,
                'archivo_tamano' => $archivoSize,
            ];
        }

        $db->transStart();

        $numero     = $recuperosModel->getSiguienteNumero();
        $recuperoData = [
            'numero_recupero' => $numero,
            'cliente_id'      => (int)$data['cliente_id'],
            'fecha'           => $data['fecha'],
            'forma_cobro'     => $data['forma_cobro'] ?? 'efectivo',
            'referencia'      => $data['referencia'] ?? null,
            'total'           => round($total, 2),
            'observaciones'   => $data['observaciones'] ?? null,
            'estado'          => 'ACTIVO',
            'usuario_id'      => (int)session()->get('id'),
        ];

        $recuperoId = $recuperosModel->insert(array_merge($recuperoData, $archivoData));
        if (!$recuperoId) {
            $db->transRollback();
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se pudo guardar el encabezado del recupero.',
                'errors'  => $recuperosModel->errors(),
            ]);
        }

        foreach ($data['facturas'] as $f) {
            $monto     = round((float)$f['monto'], 2);
            $facturaId = (int)$f['factura_id'];

            $detalleId = $detalleModel->insert([
                'recupero_id'    => $recuperoId,
                'factura_id'     => $facturaId,
                'monto_aplicado' => $monto,
            ]);

            if (!$detalleId) {
                $db->transRollback();
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No se pudo guardar el detalle del recupero.',
                    'errors'  => $detalleModel->errors(),
                ]);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error de base de datos al guardar el recupero']);
        }

        registrar_bitacora(
            'Crear recupero',
            'Recuperos',
            "Se creó el recupero {$numero} por \$" . number_format($total, 2),
            session()->get('user_id')
        );

        return $this->response->setJSON([
            'success' => true,
            'message' => "Recupero {$numero} guardado correctamente",
            'id'      => $recuperoId,
            'numero'  => $numero,
        ]);
    }

    public function archivo($id)
    {
        $chk = requerirPermiso('ver_recuperos');
        if ($chk !== true) return $chk;

        $recuperosModel = new RecuperosModel();
        $recupero = $recuperosModel->find((int)$id);

        if (!$recupero || empty($recupero->archivo_ruta)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if (!puedeVerDocumentosTodosVendedores()) {
            $sellerScope = vendedorUsuarioActual();
            if (!$sellerScope || !$recuperosModel->perteneceAVendedor((int)$id, $sellerScope)) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
            }
        }

        $path = WRITEPATH . $recupero->archivo_ruta;
        if (!is_file($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $disposition = str_starts_with((string)$recupero->archivo_tipo, 'image/') || $recupero->archivo_tipo === 'application/pdf'
            ? 'inline'
            : 'attachment';

        return $this->response
            ->setHeader('Content-Type', $recupero->archivo_tipo ?: 'application/octet-stream')
            ->setHeader('Content-Disposition', $disposition . '; filename="' . addslashes($recupero->archivo_nombre ?: basename($path)) . '"')
            ->setBody(file_get_contents($path));
    }

    // ─── DETALLE ──────────────────────────────────────────────────

    public function show($id)
    {
        $chk = requerirPermiso('ver_recuperos');
        if ($chk !== true) return $chk;

        $recuperosModel = new RecuperosModel();
        $detalleModel   = new RecuperosDetalleModel();

        $recupero = $recuperosModel->getConCliente((int)$id);
        if (!$recupero) {
            return redirect()->to(base_url('recuperos'))->with('error', 'Recupero no encontrado');
        }

        $sellerScope = puedeVerDocumentosTodosVendedores() ? null : vendedorUsuarioActual();
        if (!puedeVerDocumentosTodosVendedores()) {
            if (!$sellerScope || !$recuperosModel->perteneceAVendedor((int)$id, $sellerScope)) {
                return redirect()->to(base_url('recuperos'))->with('error', 'No tienes acceso a este recupero');
            }
        }

        $detalles = $detalleModel->getByRecupero((int)$id, $sellerScope);

        return view('recuperos/show', [
            'recupero' => $recupero,
            'detalles' => $detalles,
        ]);
    }

    // ─── ANULAR ───────────────────────────────────────────────────

    public function anular($id)
    {
        $chk = requerirPermiso('anular_recupero');
        if ($chk !== true) return $chk;

        $motivo = trim($this->request->getPost('motivo') ?? '');
        if (!$motivo) {
            return $this->response->setJSON(['success' => false, 'message' => 'El motivo de anulación es obligatorio']);
        }

        $recuperosModel = new RecuperosModel();
        $db             = \Config\Database::connect();

        $recupero = $recuperosModel->find((int)$id);
        if (!$recupero) {
            return $this->response->setJSON(['success' => false, 'message' => 'Recupero no encontrado']);
        }
        if (!puedeVerDocumentosTodosVendedores()) {
            $sellerScope = vendedorUsuarioActual();
            if (!$sellerScope || !$recuperosModel->perteneceAVendedor((int)$id, $sellerScope)) {
                return $this->response->setJSON(['success' => false, 'message' => 'No tienes acceso a este recupero']);
            }
        }
        if ($recupero->estado === 'ANULADO') {
            return $this->response->setJSON(['success' => false, 'message' => 'Este recupero ya está anulado']);
        }
        if ($recupero->estado === 'APLICADO') {
            $ref = $recupero->pago_id ? ' (vinculado al pago #' . $recupero->pago_id . ')' : '';
            return $this->response->setJSON(['success' => false, 'message' => 'Este recupero ya fue aplicado a un pago' . $ref . ' y no puede anularse']);
        }

        $db->transStart();

        $recuperosModel->update((int)$id, [
            'estado'           => 'ANULADO',
            'anulado_por'      => (int)session()->get('id'),
            'fecha_anulacion'  => date('Y-m-d H:i:s'),
            'motivo_anulacion' => $motivo,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error al anular el recupero']);
        }

        registrar_bitacora(
            'Anular recupero',
            'Recuperos',
            "Se anuló el recupero {$recupero->numero_recupero}. Motivo: {$motivo}",
            session()->get('user_id')
        );

        return $this->response->setJSON([
            'success' => true,
            'message' => "Recupero {$recupero->numero_recupero} anulado. Los saldos de las facturas fueron restaurados.",
        ]);
    }

    // ─── AJAX: recuperos activos de un cliente ────────────────────────

    public function activosPorCliente($clienteId)
    {
        $chk = requerirPermiso('ver_recuperos');
        if ($chk !== true) return $chk;

        $db             = \Config\Database::connect();
        $recuperosModel = new RecuperosModel();
        $recuperos      = $recuperosModel->getActivosByCliente((int)$clienteId);
        $sellerScope    = puedeVerDocumentosTodosVendedores() ? null : vendedorUsuarioActual();

        if (!puedeVerDocumentosTodosVendedores()) {
            if (!$sellerScope) {
                return $this->response->setJSON([]);
            }

            $recuperos = array_values(array_filter($recuperos, function ($rec) use ($recuperosModel, $sellerScope) {
                return $recuperosModel->perteneceAVendedor((int)$rec->id, $sellerScope);
            }));
        }

        foreach ($recuperos as $rec) {
            $params = [$rec->id];
            $sellerSql = '';
            if ($sellerScope) {
                $sellerSql = ' AND fh.vendedor_id = ?';
                $params[] = $sellerScope;
            }

            $rec->detalles = $db->query(
                "SELECT rd.factura_id, rd.monto_aplicado, fh.numero_control
                 FROM recuperos_detalle rd
                 LEFT JOIN facturas_head fh ON fh.id = rd.factura_id
                 WHERE rd.recupero_id = ?{$sellerSql}",
                $params
            )->getResult();
        }

        return $this->response->setJSON($recuperos);
    }

    // ─── AJAX: detalle de una factura (modal) ────────────────────────

    public function detalleFactura($id)
    {
        if (!$this->puedeConsultarRecuperos()) {
            $chk = requerirPermiso('ver_recuperos');
            if ($chk !== true) return $chk;
        }

        $db = \Config\Database::connect();
        $params = [(int)$id];
        $sellerSql = '';

        if (!puedeVerDocumentosTodosVendedores()) {
            $sellerScope = vendedorUsuarioActual();
            if (!$sellerScope) {
                return $this->response->setJSON(['success' => false, 'message' => 'Tu usuario no tiene vendedor asociado.']);
            }

            $sellerSql = ' AND fh.vendedor_id = ?';
            $params[] = $sellerScope;
        }

        $factura = $db->query(
            "SELECT fh.id, fh.numero_control, fh.tipo_dte, fh.fecha_emision,
                    fh.total_pagar, fh.saldo,
                    COALESCE(s.seller, '—') AS vendedor
             FROM facturas_head fh
             LEFT JOIN sellers s ON s.id = fh.vendedor_id
             WHERE fh.id = ?{$sellerSql}",
            $params
        )->getRow();

        if (!$factura) {
            return $this->response->setJSON(['success' => false, 'message' => 'Factura no encontrada']);
        }

        $lineas = (new FacturaDetalleModel())->getByFactura((int)$id);

        return $this->response->setJSON([
            'success' => true,
            'factura' => $factura,
            'lineas'  => $lineas,
        ]);
    }

    // ─── AJAX: facturas pendientes del cliente ─────────────────────

    public function facturasPendientes($clienteId)
    {
        if (!$this->puedeConsultarRecuperos()) {
            $chk = requerirPermiso('ver_recuperos');
            if ($chk !== true) return $chk;
        }

        $db = \Config\Database::connect();
        $params = [(int)$clienteId];
        $sellerSql = '';

        if (!puedeVerDocumentosTodosVendedores()) {
            $sellerScope = vendedorUsuarioActual();
            if (!$sellerScope) {
                return $this->response->setJSON([]);
            }

            $sellerSql = ' AND fh.vendedor_id = ?';
            $params[] = $sellerScope;
        }

        $facturas = $db->query(
            "SELECT fh.id,
                    fh.numero_control,
                    fh.tipo_dte,
                    fh.fecha_emision,
                    fh.total_pagar,
                    fh.saldo,
                    DATEDIFF(NOW(), fh.fecha_emision) AS dias_pendiente,
                    (SELECT r.id FROM recuperos r
                     JOIN recuperos_detalle rd ON rd.recupero_id = r.id
                     WHERE rd.factura_id = fh.id AND r.estado = 'ACTIVO'
                     LIMIT 1) AS recupero_id,
                    (SELECT r.numero_recupero FROM recuperos r
                     JOIN recuperos_detalle rd ON rd.recupero_id = r.id
                     WHERE rd.factura_id = fh.id AND r.estado = 'ACTIVO'
                     LIMIT 1) AS numero_recupero,
                    (SELECT rd2.monto_aplicado FROM recuperos_detalle rd2
                     JOIN recuperos r2 ON r2.id = rd2.recupero_id
                     WHERE rd2.factura_id = fh.id AND r2.estado = 'ACTIVO'
                     LIMIT 1) AS monto_recuperado
             FROM facturas_head fh
             WHERE fh.receptor_id = ?
               AND fh.saldo > 0.001
               AND fh.anulada = 0
               {$sellerSql}
             ORDER BY fh.fecha_emision ASC",
            $params
        )->getResult();

        return $this->response->setJSON($facturas);
    }
}
