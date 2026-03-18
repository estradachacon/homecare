<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use App\Libraries\BackupGenerator;

class BackupSystem extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'backup:run';
    protected $description = 'Genera backup del sistema cada 2 horas y limpia backups antiguos';

    public function run(array $params)
    {
        $db = Database::connect();

        // 🔹 1. Obtener último backup
        $row = $db->table('tareas_sistema')
            ->where('nombre', 'backup_sistema')
            ->get()
            ->getRow();

        $now = new \DateTime();

        if ($row && $row->ultima_ejecucion) {
            $lastRun = new \DateTime($row->ultima_ejecucion);
            $diff = $now->getTimestamp() - $lastRun->getTimestamp();

            if ($diff < 300) {
                CLI::write('⏱️ Aún no han pasado 2 horas', 'yellow');
                return;
            }
        }

        CLI::write('🚀 Generando backup...', 'green');

        // 🔹 2. Crear carpeta
        $dateFolder = date('Y-m-d');
        $basePath = WRITEPATH . 'backups/' . $dateFolder . '/';

        if (!is_dir($basePath)) {
            mkdir($basePath, 0777, true);
        }

        // 🔹 3. Backup DB (NUEVO MÉTODO)

        $generator = new BackupGenerator();
        $backup = $generator->generate();

        $fileName = 'backup_' . date('H-i-s') . '.sql';
        $filePath = $basePath . $fileName;

        file_put_contents($filePath, $backup);

        if (file_exists($filePath) && filesize($filePath) > 1000) {
            CLI::write('✅ Backup guardado: ' . $fileName, 'green');
        } else {
            CLI::error('❌ Error al generar el backup');
        }

        // Verificar si se creó
        if (file_exists($filePath)) {
            CLI::write('✅ Backup guardado: ' . $fileName, 'green');
        } else {
            CLI::error('❌ Error al generar el backup');
        }

        // 🔹 4. Limpiar antiguos
        $this->cleanOldBackups();

        // 🔹 5. Guardar ejecución
        if ($row) {
            $db->table('tareas_sistema')
                ->where('nombre', 'backup_sistema')
                ->update([
                    'ultima_ejecucion' => date('Y-m-d H:i:s')
                ]);
        } else {
            $db->table('tareas_sistema')->insert([
                'nombre' => 'backup_sistema',
                'ultima_ejecucion' => date('Y-m-d H:i:s')
            ]);
        }

        CLI::write('🧹 Limpieza completada', 'blue');
    }

    private function cleanOldBackups()
    {
        $path = WRITEPATH . 'backups/';

        if (!is_dir($path)) return;

        $folders = scandir($path);

        foreach ($folders as $folder) {
            if ($folder === '.' || $folder === '..') continue;

            $folderPath = $path . $folder;

            if (is_dir($folderPath)) {
                $folderDate = \DateTime::createFromFormat('Y-m-d', $folder);

                if ($folderDate) {
                    $now = new \DateTime();
                    $diff = $now->diff($folderDate)->days;

                    if ($diff > 5) {
                        $this->deleteFolder($folderPath);
                        CLI::write("🗑️ Eliminado backup antiguo: $folder", 'red');
                    }
                }
            }
        }
    }

    private function deleteFolder($folder)
    {
        $files = array_diff(scandir($folder), ['.', '..']);

        foreach ($files as $file) {
            $fullPath = $folder . '/' . $file;

            if (is_dir($fullPath)) {
                $this->deleteFolder($fullPath);
            } else {
                unlink($fullPath);
            }
        }

        rmdir($folder);
    }
}
