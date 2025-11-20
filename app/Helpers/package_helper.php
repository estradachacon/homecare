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
        'pendiente'   => 'warning',
        'entregado' => 'success',
        'asignado'   => 'info',
        'devuelto'  => 'danger'
    ];

    $color = $map[$status] ?? 'secondary';

    return "<span class='badge badge-$color'>"
         . ucfirst($status)
         . "</span>";
}
