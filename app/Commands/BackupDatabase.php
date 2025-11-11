<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class BackupDatabase extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:backup';
    protected $description = 'Genera un respaldo de la base de datos en writable/backups.';

    public function run(array $params)
    {
        // 📁 Asegurar carpeta destino
        $backupPath = WRITEPATH . 'backups/';
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        // 📦 Obtener configuración de base de datos
        $config = new Database();
        $defaultGroup = $config->defaultGroup;
        $dbConfig = $config->$defaultGroup;

        $user   = $dbConfig['username'] ?? 'root';
        $pass   = $dbConfig['password'] ?? '';
        $host   = $dbConfig['hostname'] ?? 'localhost';
        $dbname = $dbConfig['database'] ?? '';

        // 📄 Nombre del archivo
        $filename = 'backup-' . date('Y-m-d_H-i-s') . '.sql';
        $filePath = $backupPath . $filename;

        // ⚙️ Ruta a mysqldump
        // 🟡 Ajusta esta línea según tu entorno
        $mysqldumpPath = 'C:\xampp\mysql\bin\mysqldump.exe'; // Windows (XAMPP)
        // $mysqldumpPath = '/usr/bin/mysqldump'; // Linux o Mac

        // 🧠 Comando mysqldump
        $command = "\"{$mysqldumpPath}\" --user={$user} --password={$pass} --host={$host} {$dbname} > \"{$filePath}\"";

        // 🧩 Ejecutar el comando
        $output = null;
        $resultCode = null;
        exec($command, $output, $resultCode);

        if ($resultCode === 0) {
            CLI::write("✅ Respaldo creado con éxito: " . $filePath, 'green');
        } else {
            CLI::error("❌ No se pudo generar el respaldo. Verifica que mysqldump esté instalado y accesible.");
        }
    }
}
