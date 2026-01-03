<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\SettingModel;

class SettingsController extends BaseController
{
    protected $settings;

    public function __construct()
    {
        $this->settings = new SettingModel();
    }

    public function index()
    {
        // 🔹 Datos de la empresa
        $settings = $this->settings->find(1);

        // 🔹 Uso de almacenamiento
        $folderPath = FCPATH . 'upload';
        $bytes = $this->getFolderSize($folderPath);
        $storageUsed = round($bytes / 1024 / 1024, 2);

        return view('system_settings/index', [
            'settings'     => $settings,
            'storageUsed'  => $storageUsed
        ]);
    }

    /**
     * Formulario de edición
     */
    public function edit()
    {
        return view('system_settings/edit', [
            'settings' => $this->settings->find(1)
        ]);
    }

    /**
     * Guardar cambios
     */
    public function update()
    {
        $data = [
            'company_name'    => $this->request->getPost('company_name'),
            'company_address' => $this->request->getPost('company_address'),
            'primary_color'   => $this->request->getPost('primary_color'),
        ];

        // 🔹 Logo
        if ($logo = $this->request->getFile('logo')) {
            if ($logo->isValid() && !$logo->hasMoved()) {
                $name = $logo->getRandomName();
                $logo->move(FCPATH . 'upload/settings', $name);
                $data['logo'] = $name;
            }
        }

        // 🔹 Favicon
        if ($favicon = $this->request->getFile('favicon')) {
            if ($favicon->isValid() && !$favicon->hasMoved()) {
                $name = $favicon->getRandomName();
                $favicon->move(FCPATH . 'upload/settings', $name);
                $data['favicon'] = $name;
            }
        }

        $this->settings->update(1, $data);

        return redirect()
            ->to(base_url('settings'))
            ->with('success', 'Configuración actualizada correctamente');
    }

    protected function getFolderSize($dir)
    {
        $size = 0;
        foreach (glob(rtrim($dir, '/') . '/*', GLOB_NOSORT) as $file) {
            $size += is_file($file) ? filesize($file) : $this->getFolderSize($file);
        }
        return $size;
    }
}
