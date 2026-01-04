<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ContentGroupModel;
use App\Models\ContentImagesModel;

class ContentController extends BaseController
{
    protected $groupModel;
    protected $imageModel;

    public function __construct()
    {
        $this->groupModel = new ContentGroupModel();
        $this->imageModel = new ContentImagesModel();
        helper(['form', 'url']);
    }
    public function index()
    {
        $data['groups'] = $this->groupModel->orderBy('id', 'ASC')->findAll();
        return view('content/index', $data);
    }
    /** Guardar o actualizar grupo */
    public function saveGroup()
    {
        $post = $this->request->getPost();

        $groupData = [
            'title'       => $post['title'],
            'slug'        => $post['slug'],
            'description' => $post['description'] ?? null,
            'type'        => $post['type'] ?? 'gallery',
            'is_active'   => 1
        ];

        if (!empty($post['id'])) {
            $this->groupModel->update($post['id'], $groupData);
            $message = "Grupo actualizado correctamente.";
        } else {
            $this->groupModel->insert($groupData);
            $message = "Grupo creado correctamente.";
        }

        return redirect()->back()->with('success', $message);
    }

    /** Subir imagen a un grupo */
    public function uploadImage()
    {
        $post = $this->request->getPost();
        $file = $this->request->getFile('image'); // <- debe coincidir con FormData
        $groupId = $post['group_id'] ?? null;
        $title = $post['title'] ?? '';

        if (!$groupId || !$file || !$file->isValid()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Faltan datos o el archivo no es válido.'
            ]);
        }

        $newName = $file->getRandomName();
        $file->move('upload/content', $newName);

        $imageModel = new ContentImagesModel();
        $imageModel->insert([
            'group_id' => $groupId,
            'image'    => $newName,
            'caption'  => $title,
            'is_active' => 1
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Imagen subida correctamente.'
        ]);
    }



    public function deleteImage()
    {
        $id = $this->request->getPost('id');

        if (!$id) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'ID no proporcionado.'
            ]);
        }

        if ($this->imageModel->find($id)) {
            $this->imageModel->delete($id);
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Imagen eliminada correctamente.'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'El grupo no existe.'
        ]);
    }
    public function edit($id)
    {
        // 1. Obtener la caja a editar
        $group = $this->groupModel->find($id);

        if (!$group) {
            return redirect()->to('/content')->with('error', 'Grupo no encontrado.');
        }

        $data = [
            'group' => $group,
        ];

        // Se asume que tienes una vista en 'content/edit'
        return view('content/edit', $data);
    }
    public function update()
    {
        $post = $this->request->getPost();

        $groupData = [
            'title'       => $post['title'],
            'description' => $post['description'] ?? null,
        ];

        $this->groupModel->update($post['id'], $groupData);

        return redirect()->to('/content')->with('success', 'Grupo actualizado correctamente.');
    }
    public function deleteGroup()
    {
        $id = $this->request->getPost('id');

        if (!$id) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'ID no proporcionado.'
            ]);
        }

        if ($this->groupModel->find($id)) {
            $this->groupModel->delete($id);
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Grupo eliminado correctamente.'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'El grupo no existe.'
        ]);
    }
    public function updateImage()
    {
        $post = $this->request->getPost();

        if (empty($post['id']) || !isset($post['caption'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Datos incompletos.'
            ]);
        }

        $image = $this->imageModel->find($post['id']);
        if (!$image) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Imagen no encontrada.'
            ]);
        }

        $this->imageModel->update($post['id'], ['caption' => $post['caption']]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Título actualizado correctamente.'
        ]);
    }

    public function manageImages($groupId)
    {
        $group = $this->groupModel->find($groupId);

        if (!$group) {
            return redirect()->back()->with('error', 'El grupo no existe.');
        }

        $imageModel = new ContentImagesModel();

        // Traer las imágenes de este grupo
        $images = $imageModel->where('group_id', $groupId)->findAll();

        return view('content/gestion', [
            'group' => $group,
            'images' => $images
        ]);
    }
}
