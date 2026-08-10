<?php

namespace App\Models;

use CodeIgniter\Model;

class ClienteModel extends Model
{
    protected $table            = 'clientes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;

    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'tipo_documento',
        'numero_documento',
        'nrc',
        'gran_contribuyente',
        'exento_iva',
        'cod_actividad',
        'desc_actividad',
        'nombre',
        'telefono',
        'correo',
        'departamento',
        'municipio',
        'direccion',
        'cuenta_contable_id',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $skipValidation = false;

    /*
    |--------------------------------------------------------------------------
    | Métodos útiles
    |--------------------------------------------------------------------------
    */

    /**
     * Busca un cliente por número de documento (DUI/NIT). Se ignora el tipo
     * de documento en la comparación porque no está normalizado en toda la
     * BD: unos flujos guardan el código de Hacienda ('13', '36', ...) y
     * otros la sigla ('DUI', 'NIT'), lo que antes producía clientes (y sus
     * cuentas contables) duplicados cuando ambos formatos se cruzaban para
     * el mismo número de documento.
     *
     * La comparación además ignora guiones/espacios: el JSON del DTE que
     * envía Hacienda trae el número sin formato ("13151005741014"), mientras
     * que un cliente ya registrado a mano puede tenerlo con guiones
     * ("1315-100574-101-4"). Sin esto, cada carga de factura para un cliente
     * ya existente generaba un cliente (y cuenta contable) duplicado nuevo.
     * El valor guardado NUNCA se reformatea — se respeta tal cual venga.
     */
    public function buscarPorDocumento($tipo, $numero)
    {
        $numero = trim((string)$numero);

        if ($numero === '') {
            return null;
        }

        $soloDigitos = preg_replace('/[^0-9]/', '', $numero);

        if ($soloDigitos === '') {
            return $this->where('numero_documento', $numero)->first();
        }

        $db = \Config\Database::connect();

        return $db->query(
            "SELECT * FROM clientes
             WHERE REPLACE(REPLACE(numero_documento, '-', ''), ' ', '') = ?
             LIMIT 1",
            [$soloDigitos]
        )->getRow();
    }

    /**
     * Igual que buscarPorDocumento(): compara el NRC ignorando guiones/espacios,
     * ya que también puede llegar sin formato desde el JSON de Hacienda.
     */
    public function buscarPorNRC($nrc)
    {
        $nrc = trim((string)$nrc);

        if ($nrc === '') {
            return null;
        }

        $soloDigitos = preg_replace('/[^0-9]/', '', $nrc);

        if ($soloDigitos === '') {
            return $this->where('nrc', $nrc)->first();
        }

        $db = \Config\Database::connect();

        return $db->query(
            "SELECT * FROM clientes
             WHERE REPLACE(REPLACE(nrc, '-', ''), ' ', '') = ?
             LIMIT 1",
            [$soloDigitos]
        )->getRow();
    }

    /**
     * Normaliza el tipo de documento a la sigla legible ('DUI'/'NIT'/'PASAPORTE')
     * que usa el formulario de edición. Algunos flujos automáticos (carga de DTE
     * de Hacienda) guardan el código numérico del catálogo de Hacienda en su lugar
     * (13=DUI, 36=NIT, 03=Pasaporte); sin esta conversión, el <select> del
     * formulario no reconoce el valor, muestra la primera opción por defecto y
     * lo sobrescribe en cuanto se guarda cualquier edición del cliente.
     */
    public static function normalizarTipoDocumento(?string $valor): ?string
    {
        $valor = trim((string)$valor);

        return match ($valor) {
            '13', 'DUI'                 => 'DUI',
            '36', 'NIT'                 => 'NIT',
            '03', 'PASAPORTE'           => 'PASAPORTE',
            ''                          => null,
            default                     => $valor,
        };
    }
}