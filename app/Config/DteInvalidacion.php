<?php

namespace App\Config;

use CodeIgniter\Config\BaseConfig;

class DteInvalidacion extends BaseConfig
{
    // CAT-024 Tipo de Invalidación		
    public $tipoAnulacion = [
        1 => 'Error en la Información del Documento Tributario Electrónico a invalidar.',
        2 => 'Rescindir de la operación realizada.',
        3 => 'Otro',
    ];

    // CAT-025 Título a que se remiten los bienes		
    public $tipoDte = [
        '01' => 'Factura',
        '03' => 'Comprobante de Crédito Fiscal',
        '04' => 'Nota de Remisión',
        '05' => 'Nota de Crédito',
        '06' => 'Nota de Débito',
        '07' => 'Comprobante de retención',
        '08' => 'Comprobante de liquidación',
        '09' => 'Documento contable de liquidación',
        '11' => 'Facturas de exportación',
        '14' => 'Factura de sujeto excluido',
        '15' => 'Comprobante de donación',
    ];

    // CAT-003 Modelo de Facturación	
    public $tipoModelo = [
        1 => 'Modelo Facturación previo',
        2 => 'Modelo Facturación diferido'
    ];

    // CAT-004 Tipo de Transmisión		
    public $tipoOperacion = [
        1 => 'Transmisión normal',
        2 => 'Transmisión por contingencia'
    ];

    // CAT-005 Tipo de Contingencia		
    public $tipoContingencia = [
        1 => 'No disponibilidad de sistema del MH',
        2 => 'No disponibilidad de sistema del emisor',
        3 => 'Falla en el suministro de servicio de Internet del Emisor',
        4 => 'Falla en el suministro de servicio de energía eléctrica del emisor que impida la transmisión de los DTE',
        5 => 'Otro (deberá digitar un máximo de 500 caracteres explicando el motivo)'
    ];

    // CAT-006 Retención IVA MH		
    /** NO HAY VARIABLES EN LOS ESQUEMAS OFICIALES PARA ESTO, SE APLICARÁN DINAMICAMENTE */


    // CAT-007 Tipo de Generación del Documento			
    public $tipoGeneracion = [
        1 => 'Físico',
        2 => 'Electrónico'
    ];

    // CAT-009 Tipo de establecimiento				
    public $tipoEstablecimiento = [
        '01' => 'Sucursal',
        '02' => 'Casa Matriz',
        '03' => 'Bodega',
        '04' => 'Patio'
    ];

    // CAT-010 Código tipo de Servicio (Médico)					
    public $tipoServicio = [
        1 => 'Cirugía',
        2 => 'Operación',
        3 => 'Tratamiento médico',
        4 => 'Cirugía instituto salvadoreño de Bienestar Magisterial',
        5 => 'Operación Instituto Salvadoreño de Bienestar Magisterial',
        6 => 'Tratamiento médico Instituto Salvadoreño de Bienestar Magisterial'
    ];

    // CAT-011 Tipo de ítem						
    public $tipoItem = [
        1 => 'Bienes',
        2 => 'Servicios',
        3 => 'Ambos (Bienes y Servicios, incluye los dos inherente a los Productos o servicios)',
        4 => 'Otros tributos por ítem'
    ];

    // CAT-012 Departamento							
    public $departamento = [
        '00' => 'Otro (Para extranjeros)',
        '01' => 'Ahuachapán',
        '02' => 'Santa Ana',
        '03' => 'Sonsonate',
        '04' => 'Chalatenango',
        '05' => 'La Libertad',
        '06' => 'San Salvador',
        '07' => 'Cuscatlán',
        '08' => 'La Paz',
        '09' => 'Cabañas',
        '10' => 'San Vicente',
        '11' => 'Usulután',
        '12' => 'San Miguel',
        '13' => 'Morazán',
        '14' => 'La Unión'
    ];

    // CAT-013 Municipio								
    public $municipios = [

        '00' => [ // Extranjero
            '00' => 'Otro (Para extranjeros)',
        ],
        '01' => [ // Ahuachapán
            '13' => 'AHUACHAPAN NORTE',
            '14' => 'AHUACHAPAN CENTRO',
            '15' => 'AHUACHAPAN SUR',
        ],
        '02' => [ // Santa Ana
            '14' => 'SANTA ANA NORTE',
            '15' => 'SANTA ANA CENTRO',
            '16' => 'SANTA ANA ESTE',
            '17' => 'SANTA ANA OESTE',
        ],
        '03' => [ // Sonsonate
            '17' => 'SONSONATE NORTE',
            '18' => 'SONSONATE CENTRO',
            '19' => 'SONSONATE ESTE',
            '20' => 'SONSONATE OESTE',
        ],
        '04' => [ // Chalatenango
            '34' => 'CHALATENANGO NORTE',
            '35' => 'CHALATENANGO CENTRO',
            '36' => 'CHALATENANGO SUR',
        ],
        '05' => [ // La Libertad
            '23' => 'LA LIBERTAD NORTE',
            '24' => 'LA LIBERTAD CENTRO',
            '25' => 'LA LIBERTAD OESTE',
            '26' => 'LA LIBERTAD ESTE',
            '27' => 'LA LIBERTAD COSTA',
            '28' => 'LA LIBERTAD SUR',
        ],
        '06' => [ // San Salvador
            '20' => 'SAN SALVADOR NORTE',
            '21' => 'SAN SALVADOR OESTE',
            '22' => 'SAN SALVADOR ESTE',
            '23' => 'SAN SALVADOR CENTRO',
            '24' => 'SAN SALVADOR SUR',
        ],
        '07' => [ // Cuscatlán
            '17' => 'CUSCATLAN NORTE',
            '18' => 'CUSCATLAN SUR',
        ],
        '08' => [ // La Paz
            '23' => 'LA PAZ OESTE',
            '24' => 'LA PAZ CENTRO',
            '25' => 'LA PAZ ESTE',
        ],
        '09' => [ // Cabañas
            '10' => 'CABAÑAS OESTE',
            '11' => 'CABAÑAS ESTE',
        ],
        '10' => [ // San Vicente
            '14' => 'SAN VICENTE NORTE',
            '15' => 'SAN VICENTE SUR',
        ],
        '11' => [ // Usulután
            '24' => 'USULUTAN NORTE',
            '25' => 'USULUTAN ESTE',
            '26' => 'USULUTAN OESTE',
        ],
        '12' => [ // San Miguel
            '21' => 'SAN MIGUEL NORTE',
            '22' => 'SAN MIGUEL CENTRO',
            '23' => 'SAN MIGUEL OESTE',
        ],
        '13' => [ // Morazán
            '27' => 'MORAZAN NORTE',
            '28' => 'MORAZAN SUR',
        ],
        '14' => [ // La Unión
            '19' => 'LA UNION NORTE',
            '20' => 'LA UNION SUR',
        ],
    ];

    // CAT-014 Unidad de Medida  	
    public $uniMedida = [
        1 => 'Metro',
        2 => 'Yarda',
        6 => 'Milímetro',
        9 => 'Kilómetro cuadrado',
        10 => 'Hectárea',
        13 => 'Metro cuadrado',
        15 => 'Vara cuadrada',
        18 => 'Metro cúbico',
        20 => 'Barril',
        22 => 'Galón',
        23 => 'Litro',
        24 => 'Botella',
        26 => 'Mililitro',
        30 => 'Tonelada',
        32 => 'Quintal',
        33 => 'Arroba',
        34 => 'Kilogramo',
        36 => 'Libra',
        37 => 'Onza troy',
        38 => 'Onza',
        39 => 'Gramo',
        40 => 'Miligramo',
        42 => 'Megawatt',
        43 => 'Kilowatt',
        44 => 'Watt',
        45 => 'Megavoltio-amperio',
        46 => 'Kilovoltio-amperio',
        47 => 'Voltio-amperio',
        49 => 'Gigawatt-hora',
        50 => 'Megawatt-hora',
        51 => 'Kilowatt-hora',
        52 => 'Watt-hora',
        53 => 'Kilovoltio',
        54 => 'Voltio',
        55 => 'Millar',
        56 => 'Medio millar',
        57 => 'Ciento',
        58 => 'Docena',
        59 => 'Unidad',
        99 => 'Otra',
    ];

    // CAT-015 Tributos	
    public $tributos = [

        // 1 - TRIBUTOS GENERALES (resumen)
        '20' => 'Impuesto al Valor Agregado 13%',
        'C3' => 'Impuesto al Valor Agregado (exportaciones) 0%',
        '59' => 'Turismo: por alojamiento (5%)',
        '71' => 'Turismo: salida del país por vía aérea $7.00',
        'D1' => 'FOVIAL ($0.20 Ctvs. por galón)',
        'C8' => 'COTRANS ($0.10 Ctvs. por galón)',
        'D5' => 'Otras tasas casos especiales',
        'D4' => 'Otros impuestos casos especiales',

        // 2 - TRIBUTOS POR ÍTEM (cuerpo del documento)
        'A8' => 'Impuesto Especial al Combustible (0%, 0.5%, 1%)',
        '57' => 'Impuesto industria de Cemento',
        '90' => 'Impuesto especial a la primera matrícula',
        'A6' => 'Impuesto ad-valorem, armas de fuego, municiones, explosivos y artículos similares',

        // (D4 y D5 ya están arriba, no duplicar)

        // 3 - IMPUESTOS AD-VALOREM (uso informativo)
        'C5' => 'Impuesto ad-valorem por diferencial de precios de bebidas alcohólicas (8%)',
        'C6' => 'Impuesto ad-valorem por diferencial de precios al tabaco cigarrillos (39%)',
        'C7' => 'Impuesto ad-valorem por diferencial de precios al tabaco cigarros (100%)',

        '19' => 'Fabricante de Bebidas Gaseosas, Isotónicas, Deportivas, Fortificantes, Energizante o Estimulante',
        '28' => 'Importador de Bebidas Gaseosas, Isotónicas, Deportivas, Fortificantes, Energizante o Estimulante',
        '31' => 'Detallistas o Expendedores de Bebidas Alcohólicas',
        '32' => 'Fabricante de Cerveza',
        '33' => 'Importador de Cerveza',
        '34' => 'Fabricante de Productos de Tabaco',
        '35' => 'Importador de Productos de Tabaco',
        '36' => 'Fabricante de Armas de Fuego, Municiones y Artículos Similares',
        '37' => 'Importador de Arma de Fuego, Munición y Artículos Similares',
        '38' => 'Fabricante de Explosivos',
        '39' => 'Importador de Explosivos',
        '42' => 'Fabricante de Productos Pirotécnicos',
        '43' => 'Importador de Productos Pirotécnicos',
        '44' => 'Productor de Tabaco',
        '50' => 'Distribuidor de Bebidas Gaseosas, Isotónicas, Deportivas, Fortificantes, Energizante o Estimulante',
        '51' => 'Bebidas Alcohólicas',
        '52' => 'Cerveza',
        '53' => 'Productos del Tabaco',
        '54' => 'Bebidas Carbonatadas o Gaseosas Simples o Endulzadas',
        '55' => 'Otros Específicos',
        '58' => 'Alcohol',
        '77' => 'Importador de Jugos, Néctares, Bebidas con Jugo y Refrescos',
        '78' => 'Distribuidor de Jugos, Néctares, Bebidas con Jugo y Refrescos',
        '79' => 'Sobre Llamadas Telefónicas Provenientes del Exterior',
        '85' => 'Detallista de Jugos, Néctares, Bebidas con Jugo y Refrescos',
        '86' => 'Fabricante de Preparaciones Concentradas o en Polvo para la Elaboración de Bebidas',
        '91' => 'Fabricante de Jugos, Néctares, Bebidas con Jugo y Refrescos',
        '92' => 'Importador de Preparaciones Concentradas o en Polvo para la Elaboración de Bebidas',

        'A1' => 'Específicos y Ad-Valorem',
        'A5' => 'Bebidas Gaseosas, Isotónicas, Deportivas, Fortificantes, Energizantes o Estimulantes',
        'A7' => 'Alcohol Etílico',
        'A9' => 'Sacos Sintéticos',

    ];

    // CAT-016 Condición de la Operación	
    public $condicionOperacion = [
        1 => 'Contado',
        2 => 'A crédito',
        3 => 'Otro',
    ];

    // CAT-017 Forma de Pago	
    public $pagos = [
        '01' => 'Billetes y monedas',
        '02' => 'Tarjeta Débito',
        '03' => 'Tarjeta Crédito',
        '04' => 'Cheque',
        '05' => 'Transferencia-Depósito Bancario',
        '08' => 'Dinero electrónico',
        '09' => 'Monedero electrónico',
        '11' => 'Bitcoin',
        '12' => 'Otras Criptomonedas',
        '13' => 'Cuentas por pagar del receptor',
        '14' => 'Giro bancario',
        '99' => 'Otros (se debe indicar el medio de pago)',
    ];

    // CAT-018 Plazo		
    public $plazo = [
        '01' => 'Días',
        '02' => 'Meses',
        '03' => 'Años'
    ];

    // CAT-019 Código de Actividad Económica			
    public $codActividad = [

        // AGRICULTURA
        '01111' => 'Cultivo de cereales excepto arroz y para forrajes',
        '01112' => 'Cultivo de legumbres',
        '01113' => 'Cultivo de semillas oleaginosas',
        '01120' => 'Cultivo de arroz',
        '01131' => 'Cultivo de raíces y tubérculos',

        // CULTIVOS IMPORTANTES
        '01271' => 'Cultivo de café',
        '01230' => 'Cultivo de cítricos',

        // GANADERÍA
        '01410' => 'Cría y engorde de ganado bovino',
        '01450' => 'Cría de cerdos',
        '01460' => 'Cría de aves de corral y producción de huevos',

        // INDUSTRIA
        '10102' => 'Matanza y procesamiento de bovinos y porcinos',
        '10301' => 'Elaboración de jugos de frutas y hortalizas',
        '10711' => 'Elaboración de tortillas',
        '10712' => 'Fabricación de pan, galletas y barquillos',

        // BEBIDAS
        '11030' => 'Fabricación de cerveza',
        '11041' => 'Fabricación de aguas gaseosas',
        '11043' => 'Elaboración de refrescos',

        // COMERCIO (MUY USADOS)
        '46301' => 'Venta al por mayor de alimentos',
        '46302' => 'Venta al por mayor de bebidas',
        '47111' => 'Venta en supermercados',
        '47112' => 'Venta en tiendas de artículos de primera necesidad',
        '47211' => 'Venta al por menor de frutas y hortalizas',
        '47219' => 'Venta al por menor de alimentos n.c.p.',
        '47592' => 'Venta al por menor de artículos de bazar',
        '47711' => 'Venta al por menor de prendas de vestir',
        '47721' => 'Venta al por menor de medicamentos farmacéuticos',

        // RESTAURANTES (MUY COMÚN)
        '56101' => 'Restaurantes',
        '56106' => 'Pupusería',

        // TECNOLOGÍA
        '62010' => 'Programación Informática',
        '62020' => 'Consultoría informática',

        // FINANZAS
        '64190' => 'Bancos',

        // SALUD
        '86201' => 'Clínicas médicas',
        '86202' => 'Servicios de odontología',

        // EDUCACIÓN
        '85103' => 'Enseñanza primaria',
        '85301' => 'Enseñanza superior universitaria',

        // TRANSPORTE
        '49231' => 'Transporte de carga urbano',
        '49232' => 'Transporte nacional de carga',

        // PERSONAS NATURALES
        '10001' => 'Empleados',
        '10002' => 'Pensionado',
        '10003' => 'Estudiante',
        '10004' => 'Desempleado',
        '10005' => 'Otros',
        '10006' => 'Comerciante',
    ];

    // CAT-021 Otros Documentos Asociados	
    public $codDocAsociado = [
        1 => 'Emisor',
        2 => 'Receptor',
        3 => 'Médico (solo aplica para contribuyentes obligados a la presentación de F-958)',
        4 => 'Transporte (solo aplica para Factura de exportación)'
    ];

    // CAT-022 Tipo de documento de identificación del Receptor		
    public $tipoDocumento = [
        '36' => 'NIT',
        '13' => 'DUI',
        '37' => 'Otro',
        '03' => 'Pasaporte',
        '02' => 'Carnet de Residente'
    ];
}
