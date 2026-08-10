<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ClienteModel;
use App\Models\ContPlanCuentasModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ClienteController extends BaseController
{
    /**
     * Tablas y columnas que apuntan a clientes.id (sin FK a nivel de BD,
     * así que la integridad depende de recorrerlas manualmente al fusionar/eliminar).
     */
    private array $tablasReferencia = [
        'facturas_head'          => 'receptor_id',
        'pedidos_head'           => 'cliente_id',
        'consignaciones_head'    => 'cliente_id',
        'consignaciones_precios' => 'cliente_id',
        'pagos_head'             => 'cliente_id',
        'quedans'                => 'cliente_id',
        'recuperos'              => 'cliente_id',
    ];

    private function contarReferencias(int $clienteId): array
    {
        $db = db_connect();
        $conteos = [];

        foreach ($this->tablasReferencia as $tabla => $columna) {
            $conteos[$tabla] = $db->table($tabla)->where($columna, $clienteId)->countAllResults();
        }

        return $conteos;
    }

    public function index()
    {
        $clienteModel = new ClienteModel();
        $q = $this->request->getGet('q');

        $builder = $clienteModel
            ->select('clientes.*')
            ->join('cont_plan_cuentas', 'cont_plan_cuentas.id = clientes.cuenta_contable_id', 'left');

        if ($q) {
            $builder->groupStart()
                ->like('clientes.nombre', $q)
                ->orLike('clientes.numero_documento', $q)
                ->orLike('clientes.nrc', $q)
                ->orLike('cont_plan_cuentas.codigo', $q)
                ->groupEnd();
        }

        $data = [
            'clientes' => $builder->paginate(10),
            'pager' => $builder->pager,
            'q' => $q,
            'departamentos' => db_connect()
                ->table('hacienda_departamentos')
                ->where('activo', 1)
                ->orderBy('codigo', 'ASC')
                ->get()
                ->getResult(),
        ];

        return view('clientes/index', $data);
    }

    public function storeAjax()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Solicitud no permitida',
            ]);
        }

        $model = new ClienteModel();
        $codActividad = $this->request->getPost('cod_actividad') ?: null;

        $nombre = trim((string)$this->request->getPost('nombre'));

        if ($nombre === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'El nombre del cliente es obligatorio.',
                'csrf' => csrf_hash(),
            ]);
        }

        $data = [
            'tipo_documento'     => ClienteModel::normalizarTipoDocumento($this->request->getPost('tipo_documento')),
            'numero_documento'   => $this->request->getPost('numero_documento'),
            'nrc'                => $this->request->getPost('nrc'),
            'gran_contribuyente' => (int)(bool)$this->request->getPost('gran_contribuyente'),
            'exento_iva'         => (int)(bool)$this->request->getPost('exento_iva'),
            'cod_actividad'      => $codActividad,
            'desc_actividad'     => $codActividad
                ? (config('ActividadesEconomicas')->actividades[$codActividad] ?? $this->request->getPost('desc_actividad'))
                : null,
            'nombre'             => $nombre,
            'telefono'           => $this->request->getPost('telefono'),
            'correo'             => $this->request->getPost('correo'),
            'departamento'       => $this->request->getPost('departamento'),
            'municipio'          => $this->request->getPost('municipio'),
            'direccion'          => $this->request->getPost('direccion'),
            'cuenta_contable_id' => $this->request->getPost('cuenta_contable_id') ?: null,
        ];

        $clienteId = $model->insert($data);

        if (!$clienteId) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'No se pudo crear el cliente.',
                'errors' => $model->errors(),
                'csrf' => csrf_hash(),
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Cliente creado correctamente.',
            'cliente' => [
                'id' => $clienteId,
                'nombre' => $data['nombre'],
            ],
            'csrf' => csrf_hash(),
        ]);
    }
    public function searchAjax()
{
    $q = trim($this->request->getGet('q') ?? '');

    $model = new ClienteModel();

    $clientes = $model
        ->select('id, nombre, numero_documento, nrc')
        ->groupStart()
            ->like('nombre', $q)
            ->orLike('numero_documento', $q)
            ->orLike('nrc', $q)
        ->groupEnd()
        ->orderBy('nombre', 'ASC')
        ->findAll(20);

    $results = [];

    foreach ($clientes as $c) {
        $doc = $c->numero_documento ? ' | Doc: ' . $c->numero_documento : '';
        $nrc = $c->nrc ? ' | NRC: ' . $c->nrc : '';

        $results[] = [
            'id'  => $c->id,
            'text' => $c->nombre . $doc . $nrc,
            'nrc' => $c->nrc ?: '',
        ];
    }

    return $this->response->setJSON([
        'results' => $results
    ]);
}
    public function show($id)
    {
        $clienteModel = new ClienteModel();
        $facturaHeadModel = new \App\Models\FacturaHeadModel();

        $desde = $this->request->getGet('desde');
        $hasta = $this->request->getGet('hasta');

        $cliente = $clienteModel
            ->select('
                clientes.*,
                cont_plan_cuentas.codigo AS cuenta_codigo,
                cont_plan_cuentas.nombre AS cuenta_nombre
            ')
            ->join('cont_plan_cuentas', 'cont_plan_cuentas.id = clientes.cuenta_contable_id', 'left')
            ->where('clientes.id', $id)
            ->first();

        if (!$cliente) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $facturasQuery = $facturaHeadModel
            ->where('receptor_id', $id);

        if (!empty($desde)) {
            $facturasQuery->where('fecha_emision >=', $desde);
        }

        if (!empty($hasta)) {
            $facturasQuery->where('fecha_emision <=', $hasta);
        }

        $facturas = $facturasQuery
            ->orderBy('fecha_emision', 'DESC')
            ->paginate(10);

        return view('clientes/show', [
            'cliente'  => $cliente,
            'facturas' => $facturas,
            'pager'    => $facturaHeadModel->pager,
            'desde'    => $desde,
            'hasta'    => $hasta,
        ]);
    }
    public function buscar()
    {
        $q = $this->request->getGet('q');

        $clientes = (new ClienteModel())
            ->groupStart()
            ->like('nombre', $q ?? '')
            ->orLike('numero_documento', $q ?? '')
            ->orLike('nrc', $q ?? '')
            ->groupEnd()
            ->limit(10)
            ->findAll();

        $data = [];

        foreach ($clientes as $c) {

            $data[] = [
                // 🔹 requerido por select2
                'id'   => $c->id,
                'text' => $c->nombre,

                // 🔥 datos extras para DTE
                'tipo_documento'   => $c->tipo_documento,
                'numero_documento' => $c->numero_documento,
                'nrc'              => $c->nrc,
                'cod_actividad'    => $c->cod_actividad,
                'desc_actividad'   => $c->desc_actividad,
                'nombre'           => $c->nombre,
                'telefono'         => $c->telefono,
                'correo'           => $c->correo,

                // dirección formateada
                'direccion' => trim(
                    ($c->departamento ?? '') . ' ' .
                        ($c->municipio ?? '') . ' ' .
                        ($c->direccion ?? '')
                )
            ];
        }

        return $this->response->setJSON($data);
    }
    public function buscarparaDTE()
    {
        $q = $this->request->getGet('q');

        $clientes = (new ClienteModel())
            ->groupStart()
            ->like('nombre', $q ?? '')
            ->orLike('numero_documento', $q ?? '')
            ->orLike('nrc', $q ?? '')
            ->groupEnd()
            ->limit(10)
            ->findAll();

        $data = [];

        foreach ($clientes as $c) {

            $data[] = [
                // 🔹 requerido por select2
                'id'   => $c->id,
                'text' => $c->nombre,

                // 🔥 datos extras para DTE
                'tipo_documento'   => $c->tipo_documento,
                'numero_documento' => $c->numero_documento,
                'nrc'              => $c->nrc,
                'cod_actividad'    => $c->cod_actividad,
                'desc_actividad'   => $c->desc_actividad,
                'nombre'           => $c->nombre,
                'telefono'         => $c->telefono,
                'correo'           => $c->correo,

                // características tributarias
                'gran_contribuyente' => (int)($c->gran_contribuyente ?? 0),
                'exento_iva'         => (int)($c->exento_iva ?? 0),

                // dirección formateada
                'direccion' => trim(
                    ($c->departamento ?? '') . ' ' .
                        ($c->municipio ?? '') . ' ' .
                        ($c->direccion ?? '')
                )
            ];
        }

        return $this->response->setJSON($data);
    }
    public function municipiosPorDepartamento()
    {
        $departamento = $this->request->getGet('departamento');

        $municipios = db_connect()
            ->table('hacienda_municipios')
            ->select('codigo, nombre')
            ->where('activo', 1)
            ->where('departamento_codigo', $departamento)
            ->orderBy('codigo', 'ASC')
            ->get()
            ->getResult();

        return $this->response->setJSON($municipios);
    }
    public function edit($id)
    {
        $clienteModel = new ClienteModel();
        $cuentaModel  = new ContPlanCuentasModel();

        $cliente = $clienteModel->find($id);

        if (!$cliente) {
            return redirect()->to('/clientes')->with('error', 'Cliente no encontrado');
        }

        // Algunos clientes quedaron con el código numérico de Hacienda (13, 36, ...)
        // en vez de la sigla ('DUI'/'NIT') que reconoce el <select> de este formulario.
        // Se normaliza solo para mostrarlo correctamente seleccionado; no se guarda aquí.
        $cliente->tipo_documento = ClienteModel::normalizarTipoDocumento($cliente->tipo_documento);

        $cuentaSeleccionada = null;

        if (!empty($cliente->cuenta_contable_id)) {
            $cuentaSeleccionada = $cuentaModel
                ->select('id, codigo, nombre')
                ->find($cliente->cuenta_contable_id);
        }

        $departamentos = db_connect()
            ->table('hacienda_departamentos')
            ->where('activo', 1)
            ->orderBy('codigo', 'ASC')
            ->get()
            ->getResult();

        $municipios = [];

        if (!empty($cliente->departamento)) {
            $municipios = db_connect()
                ->table('hacienda_municipios')
                ->where('activo', 1)
                ->where('departamento_codigo', $cliente->departamento)
                ->orderBy('codigo', 'ASC')
                ->get()
                ->getResult();
        }

        return view('clientes/edit', [
            'cliente'            => $cliente,
            'cuentaSeleccionada' => $cuentaSeleccionada,
            'departamentos'      => $departamentos,
            'municipios'         => $municipios,
        ]);
    }
    public function update($id)
    {
        $model = new ClienteModel();

        $cliente = $model->find($id);

        if (!$cliente) {
            return redirect()->to('/clientes')->with('error', 'Cliente no encontrado');
        }

        $codActividad = $this->request->getPost('cod_actividad') ?: null;

        $data = [
            'tipo_documento'     => ClienteModel::normalizarTipoDocumento($this->request->getPost('tipo_documento')),
            'numero_documento'   => $this->request->getPost('numero_documento'),
            'nrc'                => $this->request->getPost('nrc'),
            'gran_contribuyente' => (int)(bool)$this->request->getPost('gran_contribuyente'),
            'exento_iva'         => (int)(bool)$this->request->getPost('exento_iva'),
            'cod_actividad'      => $codActividad,
            'desc_actividad'     => $codActividad
                ? (config('ActividadesEconomicas')->actividades[$codActividad] ?? $this->request->getPost('desc_actividad'))
                : null,
            'nombre'             => $this->request->getPost('nombre'),
            'telefono'           => $this->request->getPost('telefono'),
            'correo'             => $this->request->getPost('correo'),
            'departamento'       => $this->request->getPost('departamento'),
            'municipio'          => $this->request->getPost('municipio'),
            'direccion'          => $this->request->getPost('direccion'),
            'cuenta_contable_id' => $this->request->getPost('cuenta_contable_id') ?: null,
        ];

        $model->update($id, $data);

        return redirect()->to('/clientes')
            ->with('success', 'Cliente actualizado correctamente');
    }

    public function actualizarGiroAjax($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Solicitud no permitida',
            ]);
        }

        $model = new ClienteModel();
        $cliente = $model->find($id);

        if (!$cliente) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Cliente no encontrado',
            ]);
        }

        $codActividad = trim((string) $this->request->getPost('cod_actividad'));
        $actividades = config('ActividadesEconomicas')->actividades;

        if ($codActividad === '' || !isset($actividades[$codActividad])) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Seleccione un giro valido.',
            ]);
        }

        $descActividad = $actividades[$codActividad];

        if (!$model->update($id, [
            'cod_actividad'  => $codActividad,
            'desc_actividad' => $descActividad,
        ])) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'No se pudo actualizar el giro del cliente.',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'cod_actividad' => $codActividad,
            'desc_actividad' => $descActividad,
        ]);
    }

    public function crearCuentaContableAjax()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Solicitud no permitida'
            ]);
        }

        $cuentaModel = new ContPlanCuentasModel();

        $nombre = trim($this->request->getPost('nombre'));

        if ($nombre === '') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'El nombre de la cuenta es obligatorio'
            ]);
        }

        $padre = $cuentaModel->where('codigo', '110201')->first();

        if (!$padre) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No existe la cuenta padre 110201 CLIENTES LOCALES'
            ]);
        }

        $ultima = $cuentaModel
            ->like('codigo', '110201', 'after')
            ->where('cuenta_padre_id', $padre->id)
            ->orderBy('codigo', 'DESC')
            ->first();

        if ($ultima) {
            $correlativo = (int) substr($ultima->codigo, 6);
            $nuevoCodigo = '110201' . str_pad($correlativo + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nuevoCodigo = '1102010001';
        }

        $data = [
            'codigo'              => $nuevoCodigo,
            'nombre'              => mb_strtoupper($nombre),
            'tipo'                => $padre->tipo,
            'naturaleza'          => $padre->naturaleza,
            'nivel'               => $padre->nivel + 1,
            'cuenta_padre_id'     => $padre->id,
            'acepta_movimientos'  => 1,
            'activo'              => 1,
        ];

        $cuentaId = $cuentaModel->insert($data);

        if (!$cuentaId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se pudo crear la cuenta contable'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Cuenta creada correctamente',
            'cuenta' => [
                'id'   => $cuentaId,
                'text' => $nuevoCodigo . ' - ' . $data['nombre'],
            ],
            'csrf' => csrf_hash()
        ]);
    }
    public function asignarCuentaAjax($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false]);
        }

        $model     = new ClienteModel();
        $cuentaId  = $this->request->getPost('cuenta_contable_id') ?: null;

        if (!$model->update($id, ['cuenta_contable_id' => $cuentaId])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error al actualizar']);
        }

        if ($cuentaId) {
            $cuenta = (new ContPlanCuentasModel())->select('id, codigo, nombre')->find((int)$cuentaId);
            return $this->response->setJSON([
                'success' => true,
                'cuenta'  => ['id' => $cuenta->id, 'text' => $cuenta->codigo . ' - ' . $cuenta->nombre],
                'csrf'    => csrf_hash(),
            ]);
        }

        return $this->response->setJSON(['success' => true, 'cuenta' => null, 'csrf' => csrf_hash()]);
    }

    public function cuentasContablesSelect2()
    {
        $q = trim($this->request->getGet('q') ?? '');

        $cuentaModel = new ContPlanCuentasModel();

        $builder = $cuentaModel
            ->select('id, codigo, nombre')
            ->where('activo', 1)
            ->where('acepta_movimientos', 1)
            ->like('codigo', '110201', 'after');

        if ($q !== '') {
            $builder->groupStart()
                ->like('codigo', $q)
                ->orLike('nombre', $q)
                ->groupEnd();
        }

        $cuentas = $builder
            ->orderBy('codigo', 'ASC')
            ->findAll(30);

        $results = [];

        foreach ($cuentas as $cuenta) {
            $results[] = [
                'id'   => $cuenta->id,
                'text' => $cuenta->codigo . ' - ' . $cuenta->nombre,
            ];
        }

        return $this->response->setJSON([
            'results' => $results
        ]);
    }

    public function exportarExcel($id)
    {
        $facturaModel = new \App\Models\FacturaHeadModel();

        $desde = $this->request->getGet('desde');
        $hasta = $this->request->getGet('hasta');

        $query = $facturaModel->where('receptor_id', $id);

        if (!empty($desde)) {
            $query->where('fecha_emision >=', $desde);
        }

        if (!empty($hasta)) {
            $query->where('fecha_emision <=', $hasta);
        }

        $facturas = $query->orderBy('fecha_emision', 'DESC')->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 🔥 Encabezados
        $sheet->setCellValue('A1', '#');
        $sheet->setCellValue('B1', 'Correlativo');
        $sheet->setCellValue('C1', 'Fecha');
        $sheet->setCellValue('D1', 'Hora');
        $sheet->setCellValue('E1', 'Total');
        $sheet->setCellValue('F1', 'Saldo');
        $sheet->setCellValue('G1', 'Estado');

        // Estilo encabezado
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);

        $row = 2;

        foreach ($facturas as $f) {
            $sheet->setCellValue('A' . $row, $f->id);
            $sheet->setCellValue('B' . $row, substr($f->numero_control, -6));
            $sheet->setCellValue('C' . $row, date('d/m/Y', strtotime($f->fecha_emision)));
            $sheet->setCellValue('D' . $row, date('H:i:s', strtotime($f->hora_emision)));
            $sheet->setCellValue('E' . $row, $f->total_pagar);
            $sheet->setCellValue('F' . $row, $f->saldo);
            $sheet->setCellValue('G' . $row, ($f->anulada ?? 0) == 1 ? 'ANULADA' : 'ACTIVA');

            $row++;
        }

        // 💅 Formato de moneda
        $sheet->getStyle('E2:E' . ($row - 1))
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');

        $sheet->getStyle('F2:F' . ($row - 1))
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');

        // 📏 Auto width
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // 📊 Total al final (BONUS PRO)
        $sheet->setCellValue('D' . $row, 'TOTAL:');
        $sheet->setCellValue('E' . $row, '=SUM(E2:E' . ($row - 1) . ')');
        $sheet->setCellValue('F' . $row, '=SUM(F2:F' . ($row - 1) . ')');

        $sheet->getStyle('D' . $row . ':F' . $row)->getFont()->setBold(true);

        // 🔽 Descargar
        $filename = 'facturas_cliente_' . $id . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // ──────────────────────────────────────────────
    //  Detección y fusión de clientes duplicados
    // ──────────────────────────────────────────────

    public function duplicados()
    {
        $chk = requerirPermiso('fusionar_clientes');
        if ($chk !== true) {
            return redirect()->to('/clientes')->with('error', 'Sin permiso para gestionar clientes duplicados.');
        }

        $db    = db_connect();
        $model = new ClienteModel();

        $docsDuplicados = $db->table('clientes')
            ->select('numero_documento')
            ->where('numero_documento IS NOT NULL')
            ->where('numero_documento !=', '')
            ->groupBy('numero_documento')
            ->having('COUNT(*) >', 1)
            ->get()
            ->getResultArray();

        $grupos = [];

        foreach ($docsDuplicados as $row) {
            $doc = $row['numero_documento'];

            $clientesGrupo = $model
                ->where('numero_documento', $doc)
                ->orderBy('id', 'ASC')
                ->findAll();

            foreach ($clientesGrupo as $c) {
                $c->referencias       = $this->contarReferencias((int)$c->id);
                $c->total_referencias = array_sum($c->referencias);
                $c->cuenta_contable   = $c->cuenta_contable_id
                    ? $db->table('cont_plan_cuentas')->select('codigo, nombre')->where('id', $c->cuenta_contable_id)->get()->getRow()
                    : null;
            }

            $grupos[] = [
                'documento' => $doc,
                'clientes'  => $clientesGrupo,
            ];
        }

        return view('clientes/duplicados', ['grupos' => $grupos]);
    }

    public function fusionarAjax()
    {
        $chk = requerirPermiso('fusionar_clientes');
        if ($chk !== true) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin permiso para fusionar clientes.']);
        }

        $principalId = (int)$this->request->getPost('principal_id');
        $duplicadoId = (int)$this->request->getPost('duplicado_id');

        if (!$principalId || !$duplicadoId || $principalId === $duplicadoId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Selecciona dos clientes distintos.']);
        }

        $model     = new ClienteModel();
        $principal = $model->find($principalId);
        $duplicado = $model->find($duplicadoId);

        if (!$principal || !$duplicado) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no encontrado.']);
        }

        $db = db_connect();
        $db->transStart();

        $movidos = [];
        foreach ($this->tablasReferencia as $tabla => $columna) {
            $cnt = $db->table($tabla)->where($columna, $duplicadoId)->countAllResults();
            if ($cnt > 0) {
                $db->table($tabla)->where($columna, $duplicadoId)->update([$columna => $principalId]);
                $movidos[$tabla] = $cnt;
            }
        }

        // Cuenta contable del cliente duplicado (si tenía una propia)
        $cuentaInfo        = null;
        $cuentaDuplicadoId = $duplicado->cuenta_contable_id ? (int)$duplicado->cuenta_contable_id : null;
        $cuentaPrincipalId = $principal->cuenta_contable_id ? (int)$principal->cuenta_contable_id : null;

        if ($cuentaDuplicadoId && $cuentaDuplicadoId !== $cuentaPrincipalId) {
            if (!$cuentaPrincipalId) {
                // El principal no tenía cuenta contable propia: adopta la del duplicado.
                $db->table('clientes')->where('id', $principalId)->update(['cuenta_contable_id' => $cuentaDuplicadoId]);
                $cuentaInfo = "cuenta contable #{$cuentaDuplicadoId} adoptada por el cliente conservado";
            } else {
                // Ambos tenían cuenta propia: la cuenta del duplicado es huérfana/redundante.
                // Se mueven sus movimientos (asientos, saldos, histórico) hacia la cuenta real y se desactiva.
                $cuentaInfo = $this->fusionarCuentaContable($cuentaDuplicadoId, $cuentaPrincipalId);
            }
        }

        $db->table('clientes')->where('id', $duplicadoId)->delete();

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Error al fusionar los clientes.']);
        }

        $resumen = $movidos
            ? implode(', ', array_map(fn($t, $c) => "$t: $c", array_keys($movidos), $movidos))
            : 'sin registros vinculados';

        $detalle = "Cliente #{$duplicadoId} ({$duplicado->nombre}) fusionado dentro de #{$principalId} ({$principal->nombre}). Registros movidos → {$resumen}."
            . ($cuentaInfo ? " Cuenta contable → {$cuentaInfo}." : '');

        registrar_bitacora('Fusionar clientes', 'Ventas', $detalle, $principalId);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Clientes fusionados correctamente.',
            'movidos' => $movidos,
            'cuenta_contable' => $cuentaInfo,
        ]);
    }

    /**
     * Mueve todos los movimientos contables (asientos, saldos por período,
     * histórico mensual y transacciones con saldo acumulado) de una cuenta
     * contable huérfana hacia la cuenta real del cliente conservado, y
     * desactiva la cuenta huérfana para que no se vuelva a usar.
     *
     * Replica exactamente la aritmética que usa ContAsientosHeadModel::aprobarConSaldos()
     * y ContSaldosCuentasModel::upsert() para no dejar saldos/saldo_acumulado
     * inconsistentes en la cuenta destino.
     */
    private function fusionarCuentaContable(int $origenId, int $destinoId): string
    {
        $db      = db_connect();
        $resumen = [];

        // 1) Líneas de asientos ya aprobados: son la fuente de verdad del libro diario.
        $cntAsientos = $db->table('cont_asientos_detalle')->where('cuenta_id', $origenId)->countAllResults();
        if ($cntAsientos > 0) {
            $db->table('cont_asientos_detalle')->where('cuenta_id', $origenId)->update(['cuenta_id' => $destinoId]);
            $resumen[] = "{$cntAsientos} línea(s) de asiento";
        }

        // 2) Saldos por período: se suman al saldo existente del destino (o se adoptan si no tenía).
        $saldosModel = new \App\Models\ContSaldosCuentasModel();
        $filasSaldo  = $db->table('cont_saldos_cuentas')->where('cuenta_id', $origenId)->get()->getResult();
        foreach ($filasSaldo as $fila) {
            $saldosModel->upsert($destinoId, (int)$fila->periodo_id, (float)$fila->total_debe, (float)$fila->total_haber);
        }
        if (!empty($filasSaldo)) {
            $db->table('cont_saldos_cuentas')->where('cuenta_id', $origenId)->delete();
            $resumen[] = count($filasSaldo) . ' saldo(s) de período';
        }

        // 3) Histórico mensual (se llena en el cierre anual; puede no tener filas).
        $filasHist = $db->table('cont_saldos_historicos')->where('cuenta_id', $origenId)->get()->getResult();
        foreach ($filasHist as $fila) {
            $existente = $db->table('cont_saldos_historicos')
                ->where('cuenta_id', $destinoId)
                ->where('anio', $fila->anio)
                ->where('mes', $fila->mes)
                ->get()->getRow();

            if ($existente) {
                $db->table('cont_saldos_historicos')->where('id', $existente->id)->update([
                    'total_debe'  => (float)$existente->total_debe + (float)$fila->total_debe,
                    'total_haber' => (float)$existente->total_haber + (float)$fila->total_haber,
                    'saldo_final' => (float)$existente->saldo_inicial + (float)$existente->total_debe + (float)$fila->total_debe
                        - ((float)$existente->total_haber + (float)$fila->total_haber),
                ]);
            } else {
                $db->table('cont_saldos_historicos')->insert([
                    'cuenta_id'     => $destinoId,
                    'anio'          => $fila->anio,
                    'mes'           => $fila->mes,
                    'saldo_inicial' => $fila->saldo_inicial,
                    'total_debe'    => $fila->total_debe,
                    'total_haber'   => $fila->total_haber,
                    'saldo_final'   => $fila->saldo_final,
                ]);
            }
        }
        if (!empty($filasHist)) {
            $db->table('cont_saldos_historicos')->where('cuenta_id', $origenId)->delete();
            $resumen[] = count($filasHist) . ' histórico(s) mensual(es)';
        }

        // 4) Transacciones con saldo acumulado: se reinsertan en el destino, en el mismo
        //    orden relativo, recalculando el acumulado igual que aprobarConSaldos().
        $filasTrans = $db->table('cont_transacciones_hist')->where('cuenta_id', $origenId)->orderBy('id', 'ASC')->get()->getResult();
        foreach ($filasTrans as $fila) {
            $prevAcum = (float)($db->query(
                'SELECT COALESCE(SUM(debe)-SUM(haber),0) AS s FROM cont_transacciones_hist WHERE cuenta_id=?',
                [$destinoId]
            )->getRow()->s ?? 0);

            $db->table('cont_transacciones_hist')->insert([
                'asiento_id'      => $fila->asiento_id,
                'cuenta_id'       => $destinoId,
                'fecha'           => $fila->fecha,
                'descripcion'     => $fila->descripcion,
                'debe'            => $fila->debe,
                'haber'           => $fila->haber,
                'saldo_acumulado' => $prevAcum + (float)$fila->debe - (float)$fila->haber,
                'anio'            => $fila->anio,
                'mes'             => $fila->mes,
                'tipo_asiento'    => $fila->tipo_asiento,
                'created_at'      => date('Y-m-d H:i:s'),
            ]);
        }
        if (!empty($filasTrans)) {
            $db->table('cont_transacciones_hist')->where('cuenta_id', $origenId)->delete();
            $resumen[] = count($filasTrans) . ' movimiento(s) histórico(s)';
        }

        // 5) Retenciones de pagos que apuntaban a la cuenta huérfana (poco común en cuentas de cliente).
        $cntRet = $db->table('pagos_details')->where('retencion_cuenta_id', $origenId)->countAllResults();
        if ($cntRet > 0) {
            $db->table('pagos_details')->where('retencion_cuenta_id', $origenId)->update(['retencion_cuenta_id' => $destinoId]);
            $resumen[] = "{$cntRet} retención(es) de pago";
        }

        // 6) Desactivar la cuenta duplicada para que no se vuelva a seleccionar.
        $db->table('cont_plan_cuentas')->where('id', $origenId)->update(['activo' => 0]);

        return "cuenta #{$origenId} fusionada dentro de #{$destinoId} y desactivada ("
            . ($resumen ? implode(', ', $resumen) : 'sin movimientos contables que mover') . ')';
    }

    public function eliminarAjax($id)
    {
        $chk = requerirPermiso('eliminar_clientes');
        if ($chk !== true) {
            return $this->response->setJSON(['success' => false, 'message' => 'Sin permiso para eliminar clientes.']);
        }

        $model   = new ClienteModel();
        $cliente = $model->find((int)$id);

        if (!$cliente) {
            return $this->response->setJSON(['success' => false, 'message' => 'Cliente no encontrado.']);
        }

        $referencias = $this->contarReferencias((int)$id);
        $total       = array_sum($referencias);

        if ($total > 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se puede eliminar: el cliente tiene registros asociados (facturas, pedidos, consignaciones, etc.). Usa "Fusionar" en su lugar.',
            ]);
        }

        $model->delete((int)$id);

        registrar_bitacora('Eliminar cliente', 'Ventas', "Cliente #{$id} ({$cliente->nombre}) eliminado (sin registros asociados).", null);

        return $this->response->setJSON(['success' => true, 'message' => 'Cliente eliminado correctamente.']);
    }
}
