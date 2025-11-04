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
            'referencia_id'=> $referencia_id,
            'ip_address'   => $request->getIPAddress(),
            'user_agent'   => $request->getUserAgent()->getAgentString(),
        ]);
    }
}
