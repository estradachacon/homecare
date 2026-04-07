<?php

namespace App\Models;

use CodeIgniter\Model;

class HaciendaAuthModel extends Model
{
    protected $table = 'hacienda_autenticacion';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'ambiente',
        'nit',
        'token',
        'token_expira_en',
        'ultimo_login',
        'estado',
        'http_code',
        'respuesta_raw'
    ];
}