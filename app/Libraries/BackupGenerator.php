<?php

namespace App\Libraries;

use Config\Database;

class BackupGenerator
{
    public function generate()
    {
        $db = Database::connect();
        $tables = $db->listTables();

        $backup = "-- Backup generado el " . date('Y-m-d H:i:s') . "\n\n";
        $backup .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {

            // 🔹 estructura
            $query = $db->query("SHOW CREATE TABLE `$table`");
            $row = $query->getRowArray();

            $backup .= "\n\n" . $row['Create Table'] . ";\n\n";

            // 🔹 datos
            $data = $db->table($table)->get()->getResultArray();

            foreach ($data as $rowData) {

                $columns = array_keys($rowData);

                $values = array_map(function ($value) use ($db) {
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

        $backup .= "\nSET FOREIGN_KEY_CHECKS=1;\n";

        return $backup;
    }
}