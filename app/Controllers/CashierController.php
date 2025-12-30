<?php

namespace App\Controllers;

use App\Models\CashierModel;
use App\Models\CashierSessionsModel;
use App\Models\CashierMovementModel;
use CodeIgniter\Controller;
use Config\Database;

class CashierController extends Controller
{
    // Carga los modelos necesarios para asegurar que se usen correctamente
    protected $cashierModel;
    protected $branchModel;
    protected $userModel;

    public function __construct()
    {
        // Inicializa los modelos para su uso en las funciones
        $this->cashierModel = new CashierModel();
        $this->branchModel = new \App\Models\BranchModel();
        $this->userModel = new \App\Models\UserModel();
    }

    /**
     * Muestra el listado de todas las cajas con información de sucursal y usuario.
     */
    public function index()
    {
        $chk = requerirPermiso('ver_cajas');
        if ($chk !== true) return $chk;

        $cashiers = $this->cashierModel
            ->select('cashier.*, users.user_name, branches.branch_name')
            ->join('users', 'users.id = cashier.user_id', 'left')
            ->join('branches', 'branches.id = cashier.branch_id')
            ->findAll();

        $data = [
            'title' => 'Listado de Cajas',
            'cashiers' => $cashiers
        ];

        return view('cashier/index', $data);
    }

    /**
     * Muestra el formulario para crear una nueva caja.
     */
    public function new()
    {
        $chk = requerirPermiso('crear_caja');
        if ($chk !== true) return $chk;

        $branches = $this->branchModel->findAll();
        $users = $this->userModel->findAll();

        $data = [
            'title' => 'Crear caja',
            'branches' => $branches,
            'users' => $users
        ];
        return view('cashier/new', $data);
    }

    /**
     * Guarda la información de la nueva caja en la base de datos.
     */
    public function create()
    {
        helper(['form']);
        $session = session();
        $data = [
            'name' => $this->request->getPost('name'),
            'initial_balance' => $this->request->getPost('initial_balance'),
            'branch_id' => $this->request->getPost('branch_id'),
            'user_id' => $this->request->getPost('user_id'),
        ];

        $this->cashierModel->insert($data);
        registrar_bitacora(
            'Crear caja',
            'Caja',
            'Se creó una nueva caja.',
            $session->get('user_id')
        );
        return redirect()->to('/cashiers')->with('success', 'Caja creada exitosamente.');
    }

    // --- FUNCIONES DE EDICIÓN Y ELIMINACIÓN SOLICITADAS ---

    /**
     * Muestra el formulario de edición con los datos de una caja específica.
     * @param int $id El ID de la caja a editar.
     */
    public function edit($id)
    {
        $chk = requerirPermiso('editar_caja');
        if ($chk !== true) return $chk;

        // 1. Obtener la caja a editar
        $cashier = $this->cashierModel->find($id);

        if (!$cashier) {
            return redirect()->to('/cashiers')->with('error', 'Caja no encontrada.');
        }

        // 2. Obtener la lista de ramas y usuarios (para los dropdowns)
        $branches = $this->branchModel->findAll();
        $users = $this->userModel->findAll();

        $data = [
            'cashier' => $cashier,
            'branches' => $branches,
            'users' => $users,
        ];

        // Se asume que tienes una vista en 'cashier/edit'
        return view('cashier/edit', $data);
    }

    /**
     * Procesa y actualiza los datos de la caja.
     * @param int $id El ID de la caja a actualizar (viene del segmento de la URL).
     */
    public function update($id)
    {
        helper(['form']);
        $session = session();
        // 1. Definir las reglas de validación (deben coincidir con tu modelo, o definirlas aquí)
        if (!$this->validate([
            'name' => 'required|min_length[3]|max_length[100]',
            'initial_balance' => 'required|numeric',
            'branch_id' => 'required|integer',
            'user_id' => 'required|integer',
        ])) {
            // 2. Si la validación falla, redirigir de vuelta al formulario con los errores
            return redirect()->back()
                ->withInput() // Mantiene los datos que el usuario ingresó
                ->with('errors', $this->validator->getErrors()); // Envía los errores a la vista
        }

        // 3. Si la validación es exitosa, se procede a la actualización
        $data = [
            'name' => $this->request->getPost('name'),
            'initial_balance' => $this->request->getPost('initial_balance'),
            'branch_id' => $this->request->getPost('branch_id'),
            'user_id' => $this->request->getPost('user_id'),
        ];

        $this->cashierModel->update($id, $data);
        registrar_bitacora(
            'Editar caja',
            'Caja',
            'Se editó la caja con ID ' . esc($id) . '.',
            $session->get('user_id')
        );
        return redirect()->to('/cashiers')->with('success', 'Caja actualizada exitosamente.');
    }

    /**
     * Elimina una caja de la base de datos.
     * @param int $id El ID de la caja a eliminar.
     */
    public function delete()
    {
        helper(['form']);
        $session = session();
        $id = $this->request->getPost('id');
        $cashierModel = new CashierModel();

        if (!$id) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID inválido.']);
        }

        if ($cashierModel->delete($id)) {
            registrar_bitacora(
                'Eliminó caja',
                'Caja',
                'Se eliminó la caja con ID ' . esc($id) . '.',
                $session->get('user_id')
            );
            return $this->response->setJSON(['status' => 'success', 'message' => 'Caja eliminada correctamente.']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'No se pudo eliminar la caja.']);
    }
    public function sessionStatus()
    {
        $userId = session()->get('id');
        $db = db_connect();

        // 🔹 Buscar sesión abierta
        $session = $db->table('cashier_sessions cs')
            ->select('cs.*, c.name, c.current_balance')
            ->join('cashier c', 'c.id = cs.cashier_id')
            ->where('cs.user_id', $userId)
            ->where('cs.status', 'open')
            ->get()
            ->getRowArray();

        if ($session) {
            return $this->response->setJSON([
                'hasOpenSession' => true,
                'cashier' => [
                    'id' => $session['cashier_id'],
                    'name' => $session['name'],
                    'current_balance' => $session['current_balance'],
                ],
                'session' => [
                    'id' => $session['id'],
                    'initial_amount' => $session['initial_amount'],
                    'open_time' => $session['open_time'],
                ]
            ]);
        }

        // 🔹 NO hay sesión → buscar caja asignada
        $cashier = $db->table('cashier')
            ->where('user_id', $userId)
            ->where('is_open', 0)
            ->get()
            ->getRowArray();

        return $this->response->setJSON([
            'hasOpenSession' => false,
            'initial_amount' => $cashier ? $cashier['initial_balance'] : 0
        ]);
    }


    public function open()
    {
        helper(['form', 'transaction']);
        $session = session();

        $userId = session()->get('id');

        $db = db_connect();
        $db->transStart();

        // 1️⃣ Buscar caja asignada al usuario
        $cashier = $db->table('cashier')
            ->where('user_id', $userId)
            ->where('is_open', 0)
            ->get()
            ->getRowArray();

        $exists = $db->table('cashier_sessions')
            ->where('user_id', $userId)
            ->where('status', 'open')
            ->countAllResults();

        if ($exists > 0) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ya existe una sesión abierta'
            ]);
        }


        if (!$cashier) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No hay caja disponible para abrir'
            ]);
        }

        // 💰 Monto de apertura
        $openingAmount = (float) $cashier['initial_balance'];

        // 🔹 Obtener cuenta efectivo (ID = 1)
        $account = $db->table('accounts')
            ->where('id', 1)
            ->get()
            ->getRowArray();

        if (!$account) {
            $db->transRollback();
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Cuenta de efectivo no encontrada'
            ]);
        }

        // ❌ Saldo insuficiente
        if ((float)$account['balance'] < $openingAmount) {
            $db->transRollback();
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Saldo insuficiente en la cuenta de efectivo'
            ]);
        }

        // 🔻 Actualizar cuenta efectivo
        $db->table('accounts')
            ->where('id', 1)
            ->update([
                'balance'        => $account['balance'] - $openingAmount,
                'cashier_reserv' => $account['cashier_reserv'] + $openingAmount,
            ]);

        // 2️⃣ Abrir caja
        $db->table('cashier')
            ->where('id', $cashier['id'])
            ->update([
                'is_open' => 1,
                'current_balance' => $cashier['initial_balance'],
            ]);

        // 3️⃣ Crear sesión
        $db->table('cashier_sessions')->insert([
            'cashier_id'     => $cashier['id'],
            'user_id'        => $userId,
            'branch_id'      => $cashier['branch_id'],
            'initial_amount' => $cashier['initial_balance'],
            'status'         => 'open',
            'open_time'      => date('Y-m-d H:i:s'),
        ]);

        $cashierSessionId = $db->insertID();

        // 4️⃣ Crear registro de movimiento de apertura
        $db->table('cashier_movements')->insert([
            'cashier_id'         => $cashier['id'],
            'cashier_session_id' => $cashierSessionId, // ✅ ya existe
            'user_id'            => $userId,
            'branch_id'          => $cashier['branch_id'],
            'type'               => 'in',
            'amount'             => $cashier['initial_balance'],
            'balance_after'      => $cashier['initial_balance'],
            'concept'            => 'Apertura de caja',
            'reference_type'     => 'Reserva de efectivo en caja',
            'reference_id'       => $cashierSessionId,
            'created_at'         => date('Y-m-d H:i:s'),
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al abrir la caja'
            ]);
        }
        // Registrar bitácora
        registrar_bitacora(
            'Apertura de caja',
            'Remuneraciones',
            'Se abrió la caja con ID ' . esc($cashier['id']) . '.',
            $userId
        );

        registrarSalida(
            1,
            $openingAmount,
            "Apertura (Reserva) de caja ID {$cashier['id']}",
            "Apertura de caja con ID de Sesión {$cashierSessionId}",
            $cashierSessionId
        );

        return $this->response->setJSON([
            'success' => true,
            'amount'  => $cashier['initial_balance']
        ]);
    }
    public function transactions()
    {
        $chk = requerirPermiso('ver_historicos_de_caja');
        if ($chk !== true) return $chk;
        $session = session();

        $db = db_connect();

        $transactions = $db->table('cashier_movements cm')
            ->select('cm.*, u.user_name')
            ->join('users u', 'u.id = cm.user_id', 'left')
            ->orderBy('cm.created_at', 'DESC')
            ->get()
            ->getResultObject();

        $data = [
            'title' => 'Movimientos de Caja',
            'transactions' => $transactions
        ];

        return view('cashier/movements', $data);
    }
    public function summary(int $cashierId)
    {
        if (!tienePermiso('hacer_corte')) {
            return $this->response->setJSON(['error' => 'No autorizado'])->setStatusCode(403);
        }

        $sessionModel  = new CashierSessionsModel();
        $movementModel = new CashierMovementModel();

        $session = $sessionModel
            ->where('cashier_id', $cashierId)
            ->where('status', 'open')
            ->first();

        if (!$session) {
            return $this->response->setJSON(['error' => 'No hay sesión abierta'])->setStatusCode(404);
        }

        $totalIn = $movementModel
            ->selectSum('amount')
            ->where('cashier_session_id', $session->id)
            ->where('type', 'in')
            ->get()->getRow()->amount ?? 0;

        $totalOut = $movementModel
            ->selectSum('amount')
            ->where('cashier_session_id', $session->id)
            ->where('type', 'out')
            ->get()->getRow()->amount ?? 0;

        $expected = $totalIn - $totalOut;

        return $this->response->setJSON([
            'session_id'     => $session->id,
            'initial_amount' => number_format($session->initial_amount, 2),
            'total_in'       => number_format($totalIn, 2),
            'total_out'      => number_format($totalOut, 2),
            'expected'       => number_format($expected, 2),
            'expected_raw'   => $expected,
        ]);
    }

    public function close()
    {
        helper(['form', 'transaction']);

        // ✅ sesión CI
        $ciSession = session();

        if (!tienePermiso('hacer_corte')) {
            return $this->response->setJSON(['error' => 'No autorizado'])->setStatusCode(403);
        }

        $sessionId = $this->request->getPost('session_id');

        $sessionModel  = new CashierSessionsModel();
        $cashierModel  = new CashierModel();
        $movementModel = new CashierMovementModel();

        // ✅ sesión de caja
        $cashierSession = $sessionModel->find($sessionId);

        if (!$cashierSession || $cashierSession->status !== 'open') {
            return $this->response->setJSON(['error' => 'Estado inválido'])->setStatusCode(400);
        }

        // ✅ caja real
        $cashier = $cashierModel->find($cashierSession->cashier_id);

        if (!$cashier) {
            return $this->response->setJSON(['error' => 'Caja no encontrada'])->setStatusCode(404);
        }

        $db = Database::connect();
        $db->transStart();

        /* 🔹 cerrar sesión */
        $sessionModel->update($cashierSession->id, [
            'status'         => 'closed',
            'closing_amount' => $cashier->current_balance,
            'close_time'     => date('Y-m-d H:i:s'),
        ]);

        /* 🔹 cerrar caja */
        $cashierModel->update($cashier->id, [
            'is_open'         => 0,
            'current_balance' => 0,
        ]);

        /* 🔹 movimiento de cierre */
        $movementModel->insert([
            'cashier_id'         => $cashier->id,
            'cashier_session_id' => $cashierSession->id,
            'user_id'            => $ciSession->get('id'),
            'branch_id'          => $ciSession->get('branch_id'),
            'type'               => 'out',
            'amount'             => $cashier->current_balance,
            'balance_after'      => 0,
            'concept'            => 'Cierre de caja',
            'reference_type'     => 'Reintegración de efectivo',
            'reference_id'       => $cashierSession->id,
            'created_at'         => date('Y-m-d H:i:s'),
        ]);

        // 🔹 Obtener cuenta efectivo (ID = 1)
        $account = $db->table('accounts')
            ->where('id', 1)
            ->get()
            ->getRowArray();

        // 🔻 Actualizar reserva de efectivo (sale dinero de caja)
        $db->table('accounts')
            ->where('id', 1)
            ->set('balance', 'balance + ' . $cashier->current_balance, false)
            ->set('cashier_reserv', 'cashier_reserv - ' . $cashier->current_balance, false)
            ->update();


        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['error' => 'Error al cerrar la caja'])->setStatusCode(500);
        }

        /* 🔹 bitácora */
        registrar_bitacora(
            'Cierre de caja',
            'Finanzas',
            'Se cerró la caja con ID ' . $cashier->id,
            $ciSession->get('id')
        );
        
        registrarEntrada(
            1,
            $cashier->current_balance,
            "Cierre de caja ID {$cashier->id} con los valores: ". $cashier->current_balance . " - " . $cashierSession->initial_amount . " = " . ($cashier->current_balance - $cashierSession->initial_amount),
            "Cierre de caja con ID de Sesión {$cashierSession->id}",
            $cashierSession->id
        );

        return $this->response->setJSON(['success' => true]);
    }
}
