<?php

namespace App\Controllers;

use App\Models\ContentGroupModel;
use App\Models\ContentImagesModel;

class Home extends BaseController
{
    protected $groupModel;
    protected $imageModel;
    public function __construct()
    {
        $this->groupModel = new ContentGroupModel();
        $this->imageModel = new ContentImagesModel();
    }
    public function index()
    {
        $session = session();

        // ✅ Si ya inició sesión, enviarlo directo al dashboard
        if ($session->get('logged_in')) {
            return redirect()->to('/dashboard');
        }

        $groupModel = new ContentGroupModel();
        $imageModel = new ContentImagesModel();

        // Traer el grupo 'welcome'
        $welcomeGroup = $groupModel->where('id', 1)->first();

        $images = [];
        if ($welcomeGroup) {
            $images = $imageModel
                ->where('group_id', $welcomeGroup->id)
                ->where('is_active', 1)
                ->orderBy('position', 'ASC')
                ->findAll();
        }

        return view('welcome_message', [
            'group' => $welcomeGroup,
            'images' => $images
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
}
