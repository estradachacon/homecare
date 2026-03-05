<?php

namespace App\Controllers\Api;

use CodeIgniter\Controller;
use App\Models\SettingModel;

class BackupController extends Controller
{
    public function index()
    {
        $db = \Config\Database::connect();

        // 🔹 Obtener nombre de empresa
        $settingModel = new SettingModel();
        $settings = $settingModel->first();

        $companyName = $settings ? $settings->company_name : 'empresa';

        // Limpiar nombre para usarlo como archivo
        $companyName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $companyName);

        $tables = $db->listTables();

        $backup = "-- Backup generado el " . date('Y-m-d H:i:s') . "\n\n";
        $backup .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {

            // 🔹 Estructura
            $query = $db->query("SHOW CREATE TABLE `$table`");
            $row = $query->getRowArray();
            $backup .= "\n\n" . $row['Create Table'] . ";\n\n";

            // 🔹 Datos
            $data = $db->table($table)->get()->getResultArray();

            if (!empty($data)) {
                foreach ($data as $rowData) {
                    $columns = array_keys($rowData);
                    $values  = array_map(function($value) use ($db) {
                        if ($value === null) return "NULL";
                        return $db->escape($value);
                    }, array_values($rowData));

                    $backup .= "INSERT INTO `$table` (`"
                        . implode('`,`', $columns)
                        . "`) VALUES ("
                        . implode(',', $values)
                        . ");\n";
                }
                $backup .= "\n";
            }
        }

        $backup .= "\nSET FOREIGN_KEY_CHECKS=1;\n";

        $filename = $companyName . ' backup_' . '_' . date('Y-m-d_H-i-s') . '.sql';

        return $this->response
                    ->setHeader('Content-Type', 'application/sql')
                    ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                    ->setBody($backup);
    }
}