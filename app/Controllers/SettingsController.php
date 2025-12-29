<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class SettingsController extends BaseController
{
    public function index()
    {
        // 📁 Carpeta a medir
        $folderPath = FCPATH . 'upload';

        // 📏 Calcular tamaño
        $bytes = $this->getFolderSize($folderPath);

        // Convertir a MB
        $storageUsed = round($bytes / 1024 / 1024, 2);

        return view('system_settings/index', [
            'storageUsed' => $storageUsed
        ]);
    }

    protected function getFolderSize($dir)
    {
        $size = 0;

        foreach (glob(rtrim($dir, '/') . '/*', GLOB_NOSORT) as $file) {
            $size += is_file($file)
                ? filesize($file)
                : $this->getFolderSize($file); // 🔴 MUY IMPORTANTE
        }

        return $size;
    }
}
