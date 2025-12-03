<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\AccountModel;

class AccountController extends BaseController
{
    protected $accountModel;

    public function __construct()
    {
        $this->accountModel = new AccountModel();
    }

    public function index()
    {
        $q = trim($this->request->getGet('q') ?? '');
        $alpha = trim($this->request->getGet('alpha') ?? '');
        $perPage = intval($this->request->getGet('perPage') ?? 10);

        $builder = $this->accountModel;

        // BÚSQUEDA GENERAL
        if ($q !== '') {
            $builder = $builder
                ->groupStart()
                ->like('name', $q)
                ->orLike('id', $q)
                ->groupEnd();
        }

        // FILTRO ALFABÉTICO
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

        // Se asume que tienes una vista en 'accounts/edit'
        return view('accounts/edit', $data);
    }

    /**
     * Procesa y actualiza los datos de la caja.
     * @param int $id El ID de la caja a actualizar (viene del segmento de la URL).
     */
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

    // 2. Obtener la cuenta antigua para referencia de nombre y comparación
    $oldAccount = $accountModel->find($id);

    if (!$oldAccount) {
        return redirect()->to('/accounts')->with('error', 'Cuenta no encontrada.');
    }

    // 3. Usar el método update de CodeIgniter. 
    // Por defecto, solo actualiza si hay cambios, y el modelo solo permite campos 'allowedFields'.
    $accountModel->update($id, $newData);
    
    // El método update de CI4 devuelve true si se actualizó al menos 1 fila, o false si no hubo cambios.
    // Aunque el modelo de CI4 no siempre indica *si* hubo cambios, podemos asumir que si llegamos aquí, 
    // la intención fue actualizar, o revisar si el modelo lo permite. 
    // Para simplificar, nos centraremos en los campos clave.

    // 4. Crear un resumen de los cambios (más conciso)
    $changesSummary = [];
    foreach ($newData as $key => $value) {
        // Asumiendo que $oldAccount es un objeto
        if (isset($oldAccount->$key) && $oldAccount->$key != $value) {
            $changesSummary[] = ucfirst($key) . " de '{$oldAccount->$key}' a '{$value}'";
        }
    }

    // Título descriptivo para la bitácora
    $logTitle = 'Cuenta Actualizada: ' . $oldAccount->name;
    
    if (empty($changesSummary)) {
        $logDetails = "Se intentó editar la cuenta '{$oldAccount->name}' (ID: {$id}), pero no se detectaron cambios en los campos clave.";
    } else {
        $logDetails = "Se editaron los siguientes campos en la cuenta '{$oldAccount->name}' (ID: {$id}): " . implode(', ', $changesSummary) . ".";
    }

    // 5. Registrar en la Bitácora
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

        // Formato que Select2 necesita
        $results = array_map(function ($s) {
            return [
                'id'   => $s->id,      // 👈 Ahora se enviará el ID real
                'text' => $s->name   // 👈 Lo que verá el usuario

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
        // Obtenemos el término de búsqueda y la página actual
        $q = trim($this->request->getGet('q') ?? '');
        $perPage = intval($this->request->getGet('perPage') ?? 10); // Mantener el límite de paginación

        $builder = $this->accountModel;

        // BÚSQUEDA GENERAL
        if ($q !== '') {
            $builder = $builder
                ->groupStart()
                ->like('name', $q)
                ->orLike('id', $q)
                ->groupEnd();
        }

        // Si necesitas ordenar, hazlo aquí antes de paginar:
        $builder = $builder->orderBy('id', 'DESC');

        $data = [
            'q' => $q,
            'accounts' => $builder->paginate($perPage),
            'pager' => $builder->pager,
            // No pasamos 'perPage' ni 'alpha' ya que esta función solo refresca la tabla.
        ];

        // Importante: Devolvemos una vista parcial que solo contiene la tabla.
        // Tienes que crear esta nueva vista.
        return view('accounts/_account_table', $data);
    }
}
