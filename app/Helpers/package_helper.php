<?php

function serviceLabel($tipo)
{
    $labels = [
        1 => 'Punto Fijo',
        2 => 'Personalizado',
        3 => 'Recolección',
        4 => 'Casillero'
    ];

    return $labels[$tipo] ?? 'Desconocido';
}

function statusBadge($status)
{
    $map = [
        'pendiente'     => 'warning',
        'reenvio'     => 'orange',
        'devuelto'      => 'success',
        'entregado'     => 'success',
        'asignado_para_entrega'      => 'info',
        'asignado_para_recolecta'      => 'info',
        'recolecta_fallida'      => 'warning',
        'asignado'      => 'info',
        'no_retirado'      => 'danger',
        'en_casillero'  => 'info',
        'recolectado'  => 'info',
        'finalizado'  => 'success',
        'Activo'  => 'success',
        'Inactivo'  => 'danger',
        'Efectivo'  => 'success',
        'Banco'  => 'info'
    ];

    $color = $map[$status] ?? 'secondary';

    // Transformar el texto para mostrarlo bonito
    $label = ucwords(str_replace('_', ' ', $status));

    return "<span class='badge badge-$color'>$label</span>";
}
