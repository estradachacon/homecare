<?php

use App\Models\BitacoraSistemaModel;

if (!function_exists('registrar_bitacora')) {
    function registrar_bitacora(string $accion, string $modulo, string $descripcion = null, $referencia_id = null)
    {
        $model = new BitacoraSistemaModel();
        $request = service('request');

        $model->insert([
            'user_id'      => session('id') ?? null,
            'accion'       => $accion,
            'modulo'       => $modulo,
            'descripcion'  => $descripcion,
            'referencia_id' => $referencia_id,
            'ip_address'   => $request->getIPAddress(),
            'user_agent'   => $request->getUserAgent()->getAgentString(),
            'created_at'  => date('Y-m-d H:i:s'), // Hora local correcta
            'updated_at'  => date('Y-m-d H:i:s'), // Hora local correcta
        ]);
        $db = \Config\Database::connect();
        $row = $db->query("SELECT @@session.time_zone AS tz, NOW() AS hora")->getRow();
        log_message('info', "Bitácora timezone actual: {$row->tz} — Hora: {$row->hora}");
    }
}
