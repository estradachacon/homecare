<?php

namespace App\Controllers;

use App\Models\ComisionConfigModel;
use App\Models\ComisionVendedorModel;
use App\Models\ComisionReglaModel;
use App\Models\ComisionMargenModel;
use App\Models\SellerModel;

class Comisiones extends BaseController
{
    public function index()
    {
        $chk = requerirPermiso('ver_comisiones');
        if ($chk !== true) return $chk;

        $db = \Config\Database::connect();

        $builder = $db->table('comisiones c');
        $builder->select('
        c.*,
        s.seller AS vendedor_nombre
    ');
        $builder->join('sellers s', 's.id = c.vendedor_id', 'left');
        $builder->orderBy('c.id', 'DESC');

        $comisiones = $builder->get()->getResult();

        return view('comisiones/index', [
            'comisiones' => $comisiones
        ]);
    }

    private function getNombreVendedor($id)
    {
        $sellerModel = new SellerModel();

        $seller = $sellerModel->find($id);

        return $seller ? $seller->seller : "ID {$id}";
    }

    public function config()
    {
        $configModel   = new ComisionConfigModel();
        $vendedorModel = new ComisionVendedorModel();
        $reglaModel    = new ComisionReglaModel();
        $margenModel   = new ComisionMargenModel();
        $sellerModel   = new SellerModel();

        $chk = requerirPermiso('configurar_comisiones');
        if ($chk !== true) return $chk;

        // 🔹 CONFIG GENERAL
        $config = $configModel->first();

        if (!$config) {
            $configModel->insert([
                'porcentaje_default' => 0
            ]);
            $config = $configModel->first();
        }

        // 🔹 COMISIONES GUARDADAS
        $comisiones = $vendedorModel->findAll();

        $vendedores = [];

        if (!empty($comisiones)) {

            // obtener ids
            $ids = array_column($comisiones, 'vendedor_id');

            // traer solo esos sellers
            $sellers = $sellerModel->whereIn('id', $ids)->findAll();

            // mapear sellers
            $mapSeller = [];

            foreach ($sellers as $s) {
                $mapSeller[$s->id] = $s;
            }

            // armar array final
            foreach ($comisiones as $c) {
                if (isset($mapSeller[$c->vendedor_id])) {
                    $vendedores[] = (object)[
                        'vendedor_id' => $c->vendedor_id,
                        'nombre'      => $mapSeller[$c->vendedor_id]->seller,
                        'porcentaje'  => $c->porcentaje
                    ];
                }
            }
        }

        // 🔹 REGLAS
        $reglas = $reglaModel->findAll();

        $productoModel = new \App\Models\ProductoModel();

        foreach ($reglas as $r) {
            if ($r->tipo === 'producto') {
                $producto = $productoModel->find($r->valor);
                $r->valor_texto = $producto
                    ? $producto->descripcion . ' (' . $producto->codigo . ')'
                    : 'Producto eliminado';
            } else {
                $r->valor_texto = $r->valor;
            }
        }

        // 🔹 MÁRGENES
        $margenes = $margenModel->orderBy('margen_min', 'ASC')->findAll();

        return view('comisiones/mantenimientos/config', [
            'config'     => $config,
            'vendedores' => $vendedores,
            'reglas'     => $reglas,
            'margenes'   => $margenes
        ]);
    }

    public function guardarGeneral()
    {
        $session = session();
        $model = new ComisionConfigModel();

        $porcentaje = $this->request->getPost('porcentaje_default');

        $config = $model->first();

        if ($config) {
            $model->update($config->id, [
                'porcentaje_default' => $porcentaje
            ]);
        } else {
            $model->insert([
                'porcentaje_default' => $porcentaje
            ]);
        }

        registrar_bitacora(
            'Actualización de porcentaje general',
            'Comisiones',
            'Se estableció el porcentaje general en ' . $porcentaje . '%',
            $session->get('user_id')
        );

        return redirect()->back()->with('success', 'General actualizado');
    }

    public function guardarVendedores()
    {
        $session = session();
        $model = new ComisionVendedorModel();

        $ids = $this->request->getPost('vendedor_ids') ?? [];
        $porcentajes = $this->request->getPost('vendedor_porcentaje') ?? [];

        // 🔹 obtener estado actual
        $actuales = $model->findAll();

        $mapActual = [];
        foreach ($actuales as $a) {
            $mapActual[$a->vendedor_id] = $a->porcentaje;
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $cambios = [];

        // 🔹 INSERT / UPDATE
        foreach ($ids as $index => $id) {

            if (!$id) continue;

            $nuevoPorcentaje = $porcentajes[$index] ?? 0;

            if (!isset($mapActual[$id])) {

                // 🟢 NUEVO
                $model->insert([
                    'vendedor_id' => $id,
                    'porcentaje'  => $nuevoPorcentaje
                ]);

                $nombre = $this->getNombreVendedor($id);
                $cambios[] = "{$nombre} agregado con {$nuevoPorcentaje}%";
            } else {

                $anterior = $mapActual[$id];

                if ((float)$anterior !== (float)$nuevoPorcentaje) {

                    // 🟡 ACTUALIZACIÓN
                    $model->where('vendedor_id', $id)
                        ->set(['porcentaje' => $nuevoPorcentaje])
                        ->update();

                    $nombre = $this->getNombreVendedor($id);
                    $cambios[] = "{$nombre}: {$anterior}% → {$nuevoPorcentaje}%";
                }
            }
        }

        // 🔴 ELIMINADOS
        $idsNuevos = array_map('intval', $ids);

        foreach ($mapActual as $id => $porcentaje) {

            if (!in_array((int)$id, $idsNuevos)) {

                $db->table('comision_vendedores')
                    ->where('vendedor_id', $id)
                    ->delete();

                $nombre = $this->getNombreVendedor($id);
                $cambios[] = "{$nombre} eliminado (tenía {$porcentaje}%)";
            }
        }

        $db->transComplete();

        // 🔹 BITÁCORA SOLO SI HUBO CAMBIOS
        if (!empty($cambios)) {
            registrar_bitacora(
                'Actualización de comisiones por vendedor',
                'Comisiones',
                implode(' | ', $cambios),
                $session->get('user_id')
            );
        }

        return redirect()->back()->with('success', 'Vendedores actualizados');
    }

    public function guardarReglas()
    {
        $session = session();
        $model = new ComisionReglaModel();

        $tipos        = $this->request->getPost('tipo') ?? [];
        $valores      = $this->request->getPost('valor') ?? [];
        $porcentajes  = $this->request->getPost('porcentaje') ?? [];

        $db = \Config\Database::connect();
        $db->transStart();

        $model->truncate();

        $count = 0;

        foreach ($tipos as $i => $tipo) {

            $valor      = $valores[$i] ?? null;
            $porcentaje = $porcentajes[$i] ?? 0;

            if (!$tipo || !$valor) continue;

            $model->insert([
                'tipo'       => $tipo,
                'valor'      => $valor,
                'porcentaje' => $porcentaje
            ]);

            $count++;
        }

        $db->transComplete();

        registrar_bitacora(
            'Actualización de reglas de comisión',
            'Comisiones',
            'Se configuraron ' . $count . ' reglas de comisión',
            $session->get('user_id')
        );

        return redirect()->back()->with('success', 'Reglas actualizadas');
    }

    public function guardarMargen()
    {
        $session = session();
        $model = new ComisionMargenModel();

        $minimos      = $this->request->getPost('margen_min') ?? [];
        $maximos      = $this->request->getPost('margen_max') ?? [];
        $porcentajes  = $this->request->getPost('margen_porcentaje') ?? [];

        $db = \Config\Database::connect();
        $db->transStart();

        $model->truncate();

        $count = 0;

        foreach ($minimos as $i => $min) {

            $max        = $maximos[$i] ?? null;
            $porcentaje = $porcentajes[$i] ?? 0;

            if ($min === null || $porcentaje === null) continue;

            $model->insert([
                'margen_min' => $min,
                'margen_max' => $max ?: null,
                'porcentaje' => $porcentaje
            ]);

            $count++;
        }

        $db->transComplete();

        registrar_bitacora(
            'Actualización de comisiones por margen',
            'Comisiones',
            'Se configuraron ' . $count . ' rangos de margen',
            $session->get('user_id')
        );

        return redirect()->back()->with('success', 'Margen actualizado');
    }

    //Para el borrado del PercXVendedor
    public function deleteVendedor()
    {
        $model = new ComisionVendedorModel();
        $session = session();

        $id = $this->request->getPost('vendedor_id');

        $actual = $model->where('vendedor_id', $id)->first();

        if (!$actual) {
            return $this->response->setJSON(['status' => 'error']);
        }

        $model->where('vendedor_id', $id)->delete();

        $nombre = $this->getNombreVendedor($id);

        registrar_bitacora(
            'Eliminación de vendedor',
            'Comisiones',
            "{$nombre} eliminado (tenía {$actual->porcentaje}%)",
            $session->get('user_id')
        );

        return $this->response->setJSON(['status' => 'ok']);
    }

    public function addVendedor()
    {
        $model = new ComisionVendedorModel();
        $session = session();

        $id = $this->request->getPost('vendedor_id');
        $porcentaje = $this->request->getPost('porcentaje');

        if (!$id) {
            return $this->response->setJSON(['status' => 'error']);
        }

        // 🔥 evitar duplicados
        $existe = $model->where('vendedor_id', $id)->first();

        if ($existe) {
            return $this->response->setJSON(['status' => 'exists']);
        }

        $model->insert([
            'vendedor_id' => $id,
            'porcentaje'  => $porcentaje
        ]);

        $nombre = $this->getNombreVendedor($id);

        registrar_bitacora(
            'Nuevo vendedor agregado',
            'Comisiones',
            "{$nombre} con {$porcentaje}%",
            $session->get('user_id')
        );

        return $this->response->setJSON(['status' => 'ok']);
    }
    public function updateVendedor()
    {
        $model = new ComisionVendedorModel();
        $session = session();

        $id = $this->request->getPost('vendedor_id');
        $porcentaje = $this->request->getPost('porcentaje');

        $actual = $model->where('vendedor_id', $id)->first();

        if (!$actual) {
            return $this->response->setJSON(['status' => 'error']);
        }

        // evitar update innecesario
        if ((float)$actual->porcentaje === (float)$porcentaje) {
            return $this->response->setJSON(['status' => 'nochange']);
        }

        $model->where('vendedor_id', $id)
            ->set(['porcentaje' => $porcentaje])
            ->update();

        $nombre = $this->getNombreVendedor($id);

        registrar_bitacora(
            'Actualización de comisión',
            'Comisiones',
            "{$nombre}: {$actual->porcentaje}% → {$porcentaje}%",
            $session->get('user_id')
        );

        return $this->response->setJSON(['status' => 'ok']);
    }
    public function generar()
    {
        $chk = requerirPermiso('generar_comisiones');
        if ($chk !== true) return $chk;

        return view('comisiones/new');
    }
    public function getDocumentos()
    {
        helper('dte');
        $tipos = dte_siglas();

        $seller = $this->request->getPost('seller_id');
        $inicio = $this->request->getPost('fecha_inicio');
        $fin = $this->request->getPost('fecha_fin');

        $db = \Config\Database::connect();

        $docs = $db->table('factura_detalles fd')
            ->select('
            fh.fecha_emision,
            fh.tipo_dte,
            fh.numero_control,
            fh.total_pagar,
            fh.vendedor_id,

            fd.codigo,
            fd.descripcion,
            fd.cantidad,
            fd.precio_unitario,
            fd.venta_gravada,
            
            c.nombre as cliente,
            
            tv.nombre_tipo_venta as tipo_venta
        ')
            ->join('facturas_head fh', 'fh.id = fd.factura_id')
            ->join('clientes c', 'c.id = fh.receptor_id', 'left')
            ->join('tipo_venta tv', 'tv.id = fh.tipo_venta', 'left')

            ->where('fh.vendedor_id', $seller)
            ->where('DATE(fh.fecha_emision) >=', $inicio)
            ->where('DATE(fh.fecha_emision) <=', $fin)
            ->where('fh.anulada', 0)
            ->orderBy('fh.fecha_emision', 'ASC')
            ->orderBy('fh.numero_control', 'ASC')
            ->get()
            ->getResult();

        $result = [];

        foreach ($docs as $d) {
            $result[] = [
                'fecha_emision'   => $d->fecha_emision,
                'tipo'            => $tipos[$d->tipo_dte] ?? $d->tipo_dte,
                'numero_control'  => $d->numero_control,
                'cliente'         => $d->cliente,
                'codigo'          => $d->codigo,
                'descripcion'     => $d->descripcion,
                'cantidad'        => $d->cantidad,
                'precio_unitario' => $d->precio_unitario,
                'venta_gravada'   => $d->venta_gravada,
                'tipo_venta'      => $d->tipo_venta,
            ];
        }

        // 🔥 NUEVO: obtener porcentaje general
        $configModel = new ComisionConfigModel();
        $config = $configModel->first();

        $porcentajeDefault = $config->porcentaje_default ?? null;

        // 🔥 CAMBIO: ya no devuelves solo $result
        return $this->response->setJSON([
            'documentos' => $result,
            'porcentaje_default' => $porcentajeDefault
        ]);
    }
}
