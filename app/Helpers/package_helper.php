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
        'entregado'     => 'success',
        'asignado_para_entrega'      => 'info',
        'asignado_para_recolecta'      => 'info',
        'recolecta_fallida'      => 'warning',
        'asignado'      => 'info',
        'devuelto'      => 'danger',
        'no_retirado'      => 'danger',
        'en_casillero'  => 'info',
        'recolectado'  => 'info',
        'finalizado'  => 'success'
    ];

    $color = $map[$status] ?? 'secondary';

    // Transformar el texto para mostrarlo bonito
    $label = ucwords(str_replace('_', ' ', $status));

    return "<span class='badge badge-$color'>$label</span>";
}
