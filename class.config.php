<?php

require_once("class.lang.php");
require_once("class.html.php");


class CONFIG {
    // Zona WEB
    public static $poblaciones = array();
    public static $poblaciones2 = array();
    public static $poblaciones_desc = array();
    public static $poblaciones_group = array();
    public static $tipo_propiedades = array();
    public static $price_max = array();
    public static $price_min = array();
    public static $rango_precios = array();
    public static $dormitorios = array();
    public static $piscina = array();
    public static $vistas_mar = array();
    
    public static $order = array();
    
    // Zona ADMIN
    public static $roles = array('admin', 'comercial', 'usuario', 'invitado');
}

    CONFIG::$poblaciones_desc = array(
      1 => get_text('TEXTO_ALFAS'),
      2 => get_text('TEXTO_ALICANTE'),
      3 => get_text('TEXTO_ALTEA'),
      4 => get_text('TEXTO_BENIDORM'),
      5 => get_text('TEXTO_BENISSA'),
      6 => get_text('TEXTO_CALPE'),
      7 => get_text('TEXTO_DENIA'),
      8 => get_text('TEXTO_GANDIA'),
      9 => get_text('TEXTO_JALON'),
      10 => get_text('TEXTO_JAVEA'),
      11 => get_text('TEXTO_LA_NUCIA'),
      12 => get_text('TEXTO_MORAIRA'),
      13 => get_text('TEXTO_OLIVA'),
      14 => get_text('TEXTO_ORBA_VALLE'),
      15 => get_text('TEXTO_VALENCIA'),
    );

    CONFIG::$poblaciones = array(
      0 => "&raquo; ".get_text('POBLACION_ELEGIR'),
      7 => 'Denia',
      10 => 'Javea',
      6 => 'Calpe',
      12 => 'Moraira',
      3 => 'Altea',
      5 => 'Benissa',
      4 => 'Benidorm',
      2 => 'Alicante',
      9 => 'Jalon',
      11 => 'La Nucia',
      14 => 'Orba Valle',
      1 => 'L\'Alfas del Pi',
      13 => 'Oliva',
      8 => 'Gandia',
      15 => 'Valencia',
    );
    
    CONFIG::$poblaciones2 = array(
      0 => "&raquo; ".get_text('POBLACION_ELEGIR'),
      1 => 'Alfas del Pi',
      2 => 'Alicante',
      3 => 'Altea',
      4 => 'Benidorm',
      5 => 'Benissa',
      6 => 'Calpe',
      7 => 'Denia',
      8 => 'Gandia',
      9 => 'Jalon',
      10 => 'Javea',
      11 => 'La Nucia',
      12 => 'Moraira',
      13 => 'Oliva',
      14 => 'Orba Valle',
      15 => 'Valencia',

      23 => 'Pilar de Horadada',
      22 => 'Guardamar',
      21 => 'Torrevieja',
      25 => 'Santa Pola',
      29 => 'Aspe',
      20 => 'Orihuela Costa',
      24 => 'El Campello',
      26 => 'La Mata',
      27 => 'Elche',
      28 => 'La Marina',
      30 => 'Almoradi',
      31 => 'Los Montesinos',
      32 => 'Busot',
      33 => 'Costa Blanca',
      34 => 'Algorfa',
      35 => 'San Miguel de Salinas',
      36 => 'Punta Prima',
      37 => 'Pinoso',
      38 => 'Quesada',
      39 => 'Benferri',
      40 => 'Dolores',
      41 => 'Daya Nueva Vieja',
      42 => 'Campoamor',
      43 => 'Hondon de las nieves',
      44 => 'Albatera',
      45 => 'Bonalba',
      46 => 'Vega Baja',
      47 => 'La Canalosa',
      48 => 'Rojales',
      49 => 'Villamartin',
      50 => 'Orito',
      51 => 'La Romana',
      52 => 'Hondon de las Frailles',
      53 => 'Jacarilla Hurchillo',
      54 => 'Formentera del Segura',
      55 => 'Los Montesinos',

      100 => 'Murcia',
      101 => 'Mar Menor',
      102 => 'Sucina',
      103 => 'Macisvenda',
    );

    CONFIG::$poblaciones_group = array(
      0 => "&raquo; ".get_text('POBLACION_ELEGIR'),
      'Alicante Norte' => array(
        1 => 'L\'Alfas del Pi',
        2 => 'Alicante',
        3 => 'Altea',
        4 => 'Benidorm',
        5 => 'Benissa',
        6 => 'Calpe',
        7 => 'Denia',
        9 => 'Jalon',
        10 => 'Javea',
        11 => 'La Nucia',
        12 => 'Moraira',
        14 => 'Orba Valle',
      ),

      'Alicante Sur' => array(
        20 => 'Orihuela Costa',
        21 => 'Torrevieja',
        22 => 'Guardamar',
        23 => 'Pilar de Horadada',
        24 => 'El Campello',
        25 => 'Santa Pola',
        26 => 'La Mata',
        27 => 'Elche',
        28 => 'La Marina',
        29 => 'Aspe',
        30 => 'Almoradi',
        31 => 'Los Montesinos',
        32 => 'Busot',
        33 => 'Costa Blanca',
        34 => 'Algorfa',
        35 => 'San Miguel de Salinas',
        36 => 'Punta Prima',
        37 => 'Pinoso',
        38 => 'Quesada',
        39 => 'Benferri',
        40 => 'Dolores',
        41 => 'Daya Nueva Vieja',
        42 => 'Campoamor',
        43 => 'Hondon de las nieves',
        44 => 'Albatera',
        45 => 'Bonalba',
        46 => 'Vega Baja',
        47 => 'La Canalosa',
        48 => 'Rojales',
        49 => 'Villamartin',
        50 => 'Orito',
        51 => 'La Romana',
        52 => 'Hondon de las Frailles',
        53 => 'Jacarilla Hurchillo',
        54 => 'Formentera del Segura',
        55 => 'Los Montesinos',
      ),
      
      'Murcia' => array(
        100 => 'Murcia',
        101 => 'Mar Menor',
        102 => 'Sucina',
        103 => 'Macisvenda',
      ),
      
      'Valencia' => array(
        8 => 'Gandia',
        13 => 'Oliva',
        15 => 'Valencia',
      ),
    );


    CONFIG::$tipo_propiedades = array(
      0 => "&raquo; ".get_text('TIPO_ELEGIR'),
      1 => get_text('TIPO_VILLAS'),
      2 => get_text('TIPO_APARTAMENTOS'),
      3 => get_text('TIPO_ADOSADOS'),
      4 => get_text('TIPO_FINCAS'),
      5 => get_text('TIPO_PARCELAS'),
      6 => get_text('TIPO_NUEVAS_PROM'),
      7 => 'Duplex',
      8 => 'Maison de ville',
      9 => 'Maison mitoyenne',
      10 => 'Penthouse',
    );

    CONFIG::$vistas_mar = array(
      0 => "&raquo; ".get_text('VISTAS_MAR'),
      1 => get_text('TEXTO_SI'),
      2 => get_text('TEXTO_NO'),
    );

    CONFIG::$price_max = array(
      0 => 100000000,
      2 => 99999,
      3 => 149999,
      4 => 199999,
      5 => 299999,
      6 => 499999,
      7 => 999999,
      8 => 9999999999,
    );

    CONFIG::$price_min = array(
      0 => 0,
      2 => 0,
      3 => 100000,
      4 => 150000,
      5 => 200000,
      6 => 300000,
      7 => 500000,
      8 => 1000000,
    );

    CONFIG::$rango_precios = array(
      0 => "&raquo; ".get_text('RANGO_PRECIOS'),
      2 => '< '.FUNCTIONS::format_number(CONFIG::$price_max[2]).' €',
      3 => FUNCTIONS::format_number(CONFIG::$price_min[3]).' - '.FUNCTIONS::format_number(CONFIG::$price_max[3]).' €',
      4 => FUNCTIONS::format_number(CONFIG::$price_min[4]).' - '.FUNCTIONS::format_number(CONFIG::$price_max[4]).' €',
      5 => FUNCTIONS::format_number(CONFIG::$price_min[5]).' - '.FUNCTIONS::format_number(CONFIG::$price_max[5]).' €',
      6 => FUNCTIONS::format_number(CONFIG::$price_min[6]).' - '.FUNCTIONS::format_number(CONFIG::$price_max[6]).' €',
      7 => FUNCTIONS::format_number(CONFIG::$price_min[7]).' - '.FUNCTIONS::format_number(CONFIG::$price_max[7]).' €',
      8 => '> '.FUNCTIONS::format_number(CONFIG::$price_min[8]).' €',
    );

    CONFIG::$dormitorios = array(
      0 => "&raquo; ".get_text('NUMERO_DORMITORIOS'),
      1 => '1',
      2 => '2',
      3 => '3',
      4 => '4',
      5 => '5',
      6 => '> 5',
    );
    CONFIG::$piscina = array(
      0 => "&raquo; ".get_text('PISCINA_ELEGIR'),
      1 => get_text('PISCINA_1'),
      // 2 => get_text('PISCINA_2'),
      3 => get_text('PISCINA_3'),
    );
    
    CONFIG::$order = array(
      'relevance' => get_text('TEXTO_RELEVANCE'),
      'popularity' => get_text('TEXTO_POPULAR'),
      'date' => get_text('TEXTO_MAS_RECIENTE'),
      'prix' => get_text('TEXTO_PRECIO_CRECIENTE'),
      'prix-desc' => get_text('TEXTO_PRECIO_DECRECIENTE'),
      'euro-construit' => get_text('TEXTO_EUROS_CONSTRUIDO'),
      'euro-terrain' => get_text('TEXTO_EUROS_TERRENO'),
    );
