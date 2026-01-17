<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\AccountModel;
use App\Models\TransactionModel;

class AccountController extends BaseController
{
    protected $accountModel;
    protected $transactionModel;

    public function __construct()
    {
        $this->accountModel = new AccountModel();
        $this->transactionModel = new TransactionModel();
    }

    public function index()
    {
        $chk = requerirPermiso('ver_cuentas');
        if ($chk !== true) return $chk;

        $q = trim($this->request->getGet('q') ?? '');
        $alpha = trim($this->request->getGet('alpha') ?? '');
        $perPage = intval($this->request->getGet('perPage') ?? 10);

        $builder = $this->accountModel;

        if ($q !== '') {
            $builder = $builder
                ->groupStart()
                ->like('name', $q)
                ->orLike('id', $q)
                ->groupEnd();
        }

        if ($alpha !== '') {
            $builder = $builder->like('name', $alpha, 'after');
        }

        $data = [
            'q' => $q,
            'alpha' => $alpha,
            'perPage' => $perPage,
            'accounts' => $builder->paginate($perPage),
            'pager' => $builder->pager,
        ];

        return view('accounts/index', $data);
    }

    public function new()
    {
        $chk = requerirPermiso('crear_cuenta');
        if ($chk !== true) return $chk;

        return view('accounts/create');
    }

    public function create()
    {
        helper(['form']);
        $session = session();

        $this->accountModel->save([
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'type' => $this->request->getPost('type')
        ]);

        registrar_bitacora(
            'Crear cuenta',
            'Finanzas',
            'Se creó una nueva cuenta con ID ' . esc($this->accountModel->insertID()) . '.',
            $session->get('user_id')
        );

        return redirect()->to('/accounts')->with('success', 'Cuenta creada correctamente.');
    }

    public function edit($id)
    {

        if ($id == 1) {
            return redirect()->to('/accounts')
                ->with('error', 'Este registro no se puede editar.');
        }
        // 1. Obtener la caja a editar
        $account = $this->accountModel->find($id);

        if (!$account) {
            return redirect()->to('/accounts')->with('error', 'Cuenta no encontrada.');
        }

        $data = [
            'accounts' => $account,
        ];

        return view('accounts/edit', $data);
    }
    public function update($id)
    {
        helper(['form']);
        $session = session();
        $accountModel = $this->accountModel;

        // 1. Obtener los datos nuevos del formulario
        $newData = [
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'type'        => $this->request->getPost('type'),
        ];

        $oldAccount = $accountModel->find($id);

        if (!$oldAccount) {
            return redirect()->to('/accounts')->with('error', 'Cuenta no encontrada.');
        }

        $accountModel->update($id, $newData);

        $changesSummary = [];
        foreach ($newData as $key => $value) {
            if (isset($oldAccount->$key) && $oldAccount->$key != $value) {
                $changesSummary[] = ucfirst($key) . " de '{$oldAccount->$key}' a '{$value}'";
            }
        }

        $logTitle = 'Cuenta Actualizada: ' . $oldAccount->name;

        if (empty($changesSummary)) {
            $logDetails = "Se intentó editar la cuenta '{$oldAccount->name}' (ID: {$id}), pero no se detectaron cambios en los campos clave.";
        } else {
            $logDetails = "Se editaron los siguientes campos en la cuenta '{$oldAccount->name}' (ID: {$id}): " . implode(', ', $changesSummary) . ".";
        }

        registrar_bitacora(
            $logTitle,
            'Finanzas/Cuentas',
            $logDetails,
            $session->get('user_id')
        );

        return redirect()->to('/accounts')->with('success', 'Cuenta actualizada exitosamente.');
    }

    public function delete()
    {
        helper(['form']);
        $session = session();
        $id = $this->request->getPost('id');
        $accountModel = new AccountModel();

        if (!$id) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID inválido.']);
        }

        if ($accountModel->delete($id)) {
            registrar_bitacora(
                'Eliminó cuenta',
                'Finanzas',
                'Se eliminó la cuenta con ID ' . esc($id) . '.',
                $session->get('user_id')
            );
            return $this->response->setJSON(['status' => 'success', 'message' => 'Registro de cuenta eliminado correctamente.']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'No se pudo eliminar la cuenta.']);
    }
    public function search()
    {
        $term = $this->request->getGet('q');

        $model = new AccountModel();
        $accounts = $model->searchAccounts($term);

        $results = array_map(function ($s) {
            return [
                'id'   => $s->id,     
                'text' => $s->name   
            ];
        }, $accounts);
        return $this->response->setJSON($results);
    }

    public function createAjax()
    {
        $accountModel = new AccountModel();
        $session = session();
        $data = [
            'name' => $this->request->getPost('name'),
        ];

        if (empty($data['name'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'El nombre de la cuenta es obligatorio.'
            ]);
        }

        try {
            $id = $accountModel->insert($data);

            if (!$id) {
                throw new \Exception('No se pudo guardar la cuenta.');
            }
            registrar_bitacora(
                'Creación de cuenta',
                'Paquetería',
                'Se creó la cuenta ' . esc($data['name']) . ' en el registro de paquete.',
                $session->get('user_id')
            );

            return $this->response->setJSON([
                'status' => 'success',
                'data' => [
                    'id' => $id,
                    'text' => $data['name']
                ]
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
    public function searchAjax()
    {
        $q = trim($this->request->getGet('q') ?? '');
        $perPage = (int)($this->request->getGet('perPage') ?? 10);

        $builder = $this->accountModel;

        if ($q !== '') {
            $builder->groupStart()
                ->like('name', $q)
                ->orLike('id', $q)
                ->groupEnd();
        }

        $builder->orderBy('id', 'DESC');

        $accounts = $builder->paginate($perPage);

        $data = [
            'q'        => $q,
            'accounts' => $accounts,
            'pager'    => $builder->pager,
        ];

        return view('accounts/_account_results', $data);
    }

    public function list()
    {
        $accountModel = new AccountModel();

        $q = $this->request->getGet('q');

        $builder = $accountModel
            ->select('id, name, balance')
            ->where('is_active', 1);

        if (!empty($q)) {
            $builder->like('name', $q);
        }

        return $this->response->setJSON($builder->findAll());
    }

    public function processTransfer()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Solicitud inválida.'
            ]);
        }

        $request = $this->request;
        $session = session(); 

        $origenId = (int) $request->getPost('account_source');
        $destinoId = (int) $request->getPost('account_destination');
        $monto     = (float) $request->getPost('monto');
        $descripcion = $request->getPost('descripcion') ?? '';

        if ($origenId <= 0 || $destinoId <= 0) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Cuenta origen o destino inválida.']);
        }
        if ($origenId === $destinoId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'La cuenta origen y destino no pueden ser la misma.']);
        }
        if ($monto <= 0) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'El monto debe ser positivo.']);
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $transactionModel = new TransactionModel();
            $accountModel = new AccountModel();

            $originAccount = $accountModel->find($origenId);
            $destAccount = $accountModel->find($destinoId);

            if (!$originAccount || !$destAccount) {
                throw new \Exception('Cuenta origen o destino no encontrada.');
            }

            if (isset($originAccount->balance) && $originAccount->balance < $monto) {
                throw new \Exception('Saldo insuficiente en la cuenta origen.');
            }

            $transactionModel->insert([
                'account_id' => $origenId,
                'tipo'       => 'salida',
                'monto'      => $monto, 
                'origen' => 'Transferencia enviada a cuenta ' . $destinoId . ' (' . $destAccount->name . '): ' . $descripcion,
                'referencia' => 'Transferencia entre cuentas',
            ]);

            $transactionModel->insert([
                'account_id'    => $destinoId,
                'tipo'       => 'entrada',
                'monto'      => $monto, 
                'origen' => 'Transferencia recibida desde cuenta ' . $origenId . ' (' . $originAccount->name . '): ' . $descripcion,
                'referencia' => 'Transferencia entre cuentas',
            ]);

            $db->table('accounts')
                ->set('balance', "balance - {$monto}", false)
                ->where('id', $origenId)
                ->update();

            $db->table('accounts')
                ->set('balance', "balance + {$monto}", false)
                ->where('id', $destinoId)
                ->update();

            if ($db->transStatus() === false) {
                throw new \Exception('Error al ejecutar la transferencia.');
            }

            $db->transCommit();

            $logDetails = "Se transfirieron **{$monto}** de la cuenta **{$originAccount->name}** (ID: {$origenId}) a la cuenta **{$destAccount->name}** (ID: {$destinoId}). Descripción: " . ($descripcion ?: 'Sin descripción.');

            registrar_bitacora(
                'Transferencia exitosa',
                'Finanzas/Transferencias',
                $logDetails,
                $session->get('user_id') 
            );
            return $this->response->setJSON([
                'status' => 'success',
                'message' => '¡Transferencia realizada con éxito!'
            ]);
        } catch (\Exception $e) {
            $db->transRollback();

            $errorDetails = "Transferencia fallida entre ID {$origenId} y ID {$destinoId}. Error: " . $e->getMessage();
            registrar_bitacora(
                'Error de Transferencia',
                'Finanzas/Transferencias',
                $errorDetails,
                $session->get('user_id')
            );

            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
