<?php

namespace App\Controllers;
use App\Models\BranchModel;

class Home extends BaseController
{
    protected $groupModel;
    protected $imageModel;
    protected $branchModel = null;
    public function __construct()
    {
        $this->branchModel = new BranchModel();
    }
    public function index()
    {
        $session = session();

        if ($session->get('logged_in')) {
            return redirect()->to('/dashboard');
        }

        return view('welcome_message', [
        ]);
    }
    public function rutas()
    {
        // Traer todos los grupos activos, excepto el grupo con ID 1
        $groups = $this->groupModel
            ->where('is_active', 1)
            ->where('id !=', 1)
            ->orderBy('created_at', 'ASC')
            ->findAll();

        // Para cada grupo, traer sus imágenes
        foreach ($groups as &$group) {
            $group->images = $this->imageModel
                ->where('group_id', $group->id)
                ->where('is_active', 1)
                ->orderBy('position', 'ASC')
                ->findAll();
        }
        unset($group);

        $data = [
            'groups' => $groups
        ];

        return view('welcome_rutas', $data);
    }
    public function sucursales()
    {
        $branches = $this->branchModel->where('status', 1)->findAll();
        return view('welcome_sucursales', ['branches' => $branches]);
    }
    public function quienes_somos()
    {
        return view('welcome_quienes_somos');
    }
}
