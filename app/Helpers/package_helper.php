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
        'asignado'      => 'info',
        'devuelto'      => 'danger',
        'en_casillero'  => 'info'
    ];

    $color = $map[$status] ?? 'secondary';

    // Transformar el texto para mostrarlo bonito
    $label = ucwords(str_replace('_', ' ', $status));

    return "<span class='badge badge-$color'>$label</span>";
}
