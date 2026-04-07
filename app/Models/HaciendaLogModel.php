<?php

namespace App\Models;

use CodeIgniter\Model;

class HaciendaLogModel extends Model
{
    protected $table = 'hacienda_logs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'ambiente',
        'nit',
        'tipo',
        'endpoint',
        'request_json',
        'response_json',
        'http_code',
        'exito',
        'mensaje_error',
        'fecha'
    ];
}