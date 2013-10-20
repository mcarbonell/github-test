<?php

require_once("funciones.inc.php");
require_once("class.thumbnail.php");


/*

libXMLError {
  public int $level ;
  public int $code ;
  public int $column ;
  public string $message ;
  public string $file ;
  public int $line ;
}

level
La gravedad del error (una de las siguientes constantes: LIBXML_ERR_WARNING, LIBXML_ERR_ERROR o LIBXML_ERR_FATAL)

code
El código del error.

column
La columna donde ocurrió el error.

message
El mensaje del error, si es que hay.

file
El nombre del fichero, o vacio si el XML fue cargado desde una cadena.

line
La línea en la que ocurrió el error.

*/

function fix_latin1_mangled_with_utf8_maybe_hopefully_most_of_the_time($str)
{
    return preg_replace_callback('#[\\xA1-\\xFF](?![\\x80-\\xBF]{2,})#', 'utf8_encode_callback', $str);
}

function utf8_encode_callback($m)
{
    return utf8_encode($m[0]);
}



class xml_importer {
  public $agency_id;
  public $agency = null;
  
  public $xml_format = '';
  public $xml_filename = '';
  public $xml_content = '';
  public $xml_lines = null;
  public $xml_bytes = 0;

  public $xml = null;
  public $xml_errors = null;
  public $xml_num_errors = 0;

  public function __construct($agency_id, $xml_filename = '', $xml_format = '') {
    $this->agency_id = $agency_id;
    $this->agency = new agency ($agency_id);
    $this->xml_filename = $xml_filename;
    $this->xml_format = $xml_format;
    
    if (empty($this->xml_filename)) $this->xml_filename = $this->agency->xml_url;
    if (empty($this->xml_format)) $this->xml_format = $this->agency->xml_format;
  
    libxml_use_internal_errors(true);
    $this->load_xml();

    if ($this->xml_format == 'kyero') $this->kyero_construct();
    if ($this->xml_format == 'xml2u') $this->xml2u_construct();
    if ($this->xml_format == 'arkadia') $this->arkadia_construct();
  }
  
  public function kyero_construct() {
    if ($this->xml_bytes) {
      // var_dump($this->xml); die(" ***FIN DEBUG XML***");
      
      $this->properties = $this->xml->property;
      $this->num_properties = count($this->properties);
    }
  }
  
  public function xml2u_construct() {
    if ($this->xml_bytes) {
      $this->properties = $this->xml->Clients->Client->properties->Property;
      $this->num_properties = count($this->properties);
    }
  }
  
  public function arkadia_construct() {
    if ($this->xml_bytes) {
      $this->properties = $this->xml->transac;
      $this->num_properties = count($this->properties);
    }
  }

  public function load_xml() {
    if (empty($this->xml_filename)) die("ERROR load_xml(): Xml vacío");
    
    $this->xml_content = file_get_contents_curl($this->xml_filename);
    // $this->xml_content = utf8_encode(file_get_contents($this->xml_filename));
    // $this->xml_content = fix_latin1_mangled_with_utf8_maybe_hopefully_most_of_the_time(file_get_contents($this->xml_filename));

    if ($this->agency_id == 34) {
      $this->xml_content = str_replace("&lt;", "<", $this->xml_content);
      $this->xml_content = str_replace("&gt;", ">", $this->xml_content);
      $this->xml_content = str_replace("<<", "<", $this->xml_content);
      // $this->xml_content = str_replace("&amp;amp;", "&amp;", $this->xml_content);
      $this->xml_content = str_replace('&quot;', "'", $this->xml_content);
    }
    
    if ($this->agency_id == 32) {
      $this->xml_content = str_replace("<Property>", "\n<Property>", $this->xml_content);
      $this->xml_content = str_replace("<image>", "\n<image>", $this->xml_content);
      // print_r($this->xml_content); die(" ***FIN DEBUG XML agency_id == 32 ***");
    }
    
    // print_r($this->xml_content); die(" ***FIN DEBUG XML***");

    $this->xml_bytes = strlen($this->xml_content);
    
    if ($this->xml_bytes) {
      echo "Bytes leidos: {$this->xml_bytes}\n<br />";
      
      $this->xml_lines = explode("\n", $this->xml_content);
      libxml_clear_errors();
      $this->xml = simplexml_load_string($this->xml_content);
      $this->xml_errors = libxml_get_errors();
      $this->xml_num_errors = count($this->xml_errors);
          foreach ($this->xml_errors as $error) {
              echo display_xml_error($error, $this->xml_lines);
          }
      libxml_clear_errors();
    } else {
      die ("load_xml(): No hay bytes leidos!!");
    }
    return ($this->xml_bytes); // Devolvemos el número de bytes leidos
  }

  public function save_xml($xml_filename = '') {
    if (!empty($xml_filename)) $this->xml_filename = $xml_filename;
    file_put_contents($this->xml_filename, $this->xml_content);
  }
  
  public function get_xml_errors() {
    $errors = array();
    if (!empty($this->xml_errors))
      foreach ($this->xml_errors as $error) {
        $errors[] = $error->message;
      }
    return $errors;
  }
  
  public function get_xml_errors_string() {
    $errors = $this->get_xml_errors();
    return join("\n", $errors);
  }
  
  
  public function do_import() {
    echo nl2br("Bytes leidos: {$this->xml_bytes} ".format_size($this->xml_bytes)."\n");
    myflush();
    // No tocamos nada si está vacio
    if ($this->xml_bytes < 1024) die("No se importa nada");

    $local_xml_filename = "xml/".$this->agency_id.".xml";
    
    echo nl2br("Encontradas {$this->num_properties} propiedades\n");
    echo nl2br("Guardando fichero $local_xml_filename \n");
    myflush();

    $this->save_xml($local_xml_filename);
    echo nl2br("Errores: ".$this->get_xml_errors_string()."\n");


    
    $cont = 0;

    foreach ($this->properties as $propiedad) {
      if ($this->xml_format == 'kyero')   $this->insert_property_kyero($propiedad, $this->agency_id);
      if ($this->xml_format == 'xml2u')   $this->insert_property_xml2u($propiedad, $this->agency_id);
      if ($this->xml_format == 'arkadia') $this->insert_property_arkadia($propiedad, $this->agency_id);
      
      // if (++$cont > 0 ) die("-----------------");
    }
  }
  
  public function insert_property_kyero($property, $agency_id) {
      // print_r($property);
      if ($property->town == "") return;
      
      $property->ref = str_replace("'", "", $property->ref);
      // return;

      // echo " {$property->type->en} ";
      // $property_type = array_search($property->type->en, $tipo_propiedades);
      // $property_area = array_search($property->town, $poblaciones);

      $price_freq = $property->price_freq;
      if (!(($price_freq == 'sale') || ($price_freq == 'new_build'))) {
        echo " No es venta !!! \n";
        return;
      }

      $built = $property->surface_area->built;
      $plot  = $property->surface_area->plot;
      if (empty($built) && empty($plot)) {
        // print_r($property);
        // die("ERROR built and plot ");
        echo " No tiene metros cuadrados !!! \n";
      }

      $property->type->en = str_replace("- ", "", $property->type->en);
      $property->type->en = trim($property->type->en);
      if (empty($property->type->en)) $property->type->en = get_english_type($property->type->es);
      if (empty($property->type->en)) {
        echo "Tipo de Casa Vacio !!!";
        return;
      }
      $property_type = get_property_type_id($property->type->en);
      $country_id    = get_place_id("España");
      
      $property->province        = str_replace("'", "", trim($property->province));
      $property->town            = str_replace("'", "", trim($property->town));
      $property->location_detail = str_replace("'", "", trim($property->location_detail));


      if ($property->province == 'Griona') $property->province = 'Gerona';
      if ($property->province == 'Girona') $property->province = 'Gerona';
      if ($property->province == 'Lleida') $property->province = 'Lerida';
      
      if ($property->town == "") return;
      if ($property->province == "") return;
      if ($property->province == "Panamá") return;
      if ($property->province == "Andalusia") return;
      if ($property->province == "Canary Islands") return;
      if ($property->province == "Castile-La Mancha") return;
      if ($property->province == "Balearic Islands") return;


      



      // DP
      if ($agency_id == 1) {
        $comunidad_id  = get_place_id("Comunidad Valenciana", $country_id);
        $province_id   = get_place_id($property->province, $comunidad_id);
        $lugares       = explode(",", $property->town);
        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          $property_area = get_place_id($lugar, $property_area);
        }
      }

      // PM
      if ($agency_id == 2) {
        $comunidad_id  = get_place_id($property->province, $country_id);
        $province_id   = get_place_id($property->town, $comunidad_id);
        $lugares       = explode(",", $property->location_detail);
        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          $property_area = get_place_id($lugar, $property_area);
        }
      }

      // SH
      if ($agency_id == 3) {
        $province_id   = get_place_id($property->province, 0);
        $lugares       = explode(",", $property->town);
        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          $property_area = get_place_id($lugar, $property_area);
        }
      }

      // WV
      if ($agency_id == 4) {
        $province_id   = get_place_id($property->province, 0);
        $lugares       = explode(",", $property->town.",".$property->location_detail);
        // $lugares       = explode(",", $property->location_detail);
        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }

      // CC
      if ($agency_id == 5) {
        $province_id   = get_place_id($property->province, 0);
        $lugares       = explode(",", $property->town.",".$property->location_detail);
        // $lugares       = explode(",", $property->location_detail);
        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }

      // GS
      if ($agency_id == 6) {
        $province_id   = get_place_id($property->province, 0);
        $lugares       = explode(",", $property->town.",".$property->location_detail);
        // $lugares       = explode(",", $property->location_detail);
        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }
      // GH
      if ($agency_id == 7) {
        $province_id   = get_place_id($property->province, 0);
        list($location_detail, $dummy) = explode(",", $property->location_detail, 2);
        $location_detail = trim($location_detail);
        $lugares       = explode(",", $property->town.",".$location_detail);

        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          $lugar = trim($lugar);
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }
      // FC
      if ($agency_id == 8) {
        $province_id   = get_place_id($property->province, 0);
        $lugares       = explode(",", $property->town);

        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          $lugar = trim($lugar);
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }
      // IF
      if ($agency_id == 9) {
        $province_id   = get_place_id($property->province, 0);
        $lugares       = explode(",", $property->town);

        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          $lugar = trim($lugar);
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }

      // GT
      if ($agency_id == 10) {
        $province_id   = get_place_id($property->province, 0);
        $lugares       = explode(",", $property->town);

        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          $lugar = trim($lugar);
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }

      // RD
      if ($agency_id == 12) {
        $province_id   = get_place_id($property->province, 0);
        $lugares       = explode(",", $property->town);

        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          $lugar = trim($lugar);
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }

      // B4
      if ($agency_id == 13) {
        $province_id   = get_place_id($property->province, 0);
        $lugares       = explode(",", $property->town.",".$property->location_detail);
        // $lugares       = explode(",", $property->location_detail);
        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }
      // VV
      if ($agency_id == 14) {
        $province_id   = get_place_id($property->province, 0);
        $lugares       = explode(",", $property->town.",".$property->location_detail);
        // $lugares       = explode(",", $property->location_detail);
        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }
      
      // BM
      if ($agency_id == 15) {
        if ($property->province == "INTERNACIONAL") return;
        
        if (empty($property->province)) $property->province = "Tarragona";
        $province_id   = get_place_id($property->province, 0);
        $lugares       = explode(",", $property->town.",".$property->location_detail);
        // $lugares       = explode(",", $property->location_detail);
        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }
      
      // BO
      if ($agency_id == 17) {
        $province_id   = get_place_id($property->province, 0);
        $lugares       = explode(",", $property->town.",".$property->location_detail);
        // $lugares       = explode(",", $property->location_detail);
        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }
      
      // SB
      if ($agency_id == 18) {
        $province_id   = get_place_id($property->province, 0);
        $lugares       = explode(",", $property->town.",".$property->location_detail);
        // $lugares       = explode(",", $property->location_detail);
        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }
      
      // SC
      if ($agency_id == 19) {
        $province_id   = get_place_id($property->province, 0);
        $lugares       = explode(",", $property->town.",".$property->location_detail);
        // $lugares       = explode(",", $property->location_detail);
        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }
      
      // LT
      if ($agency_id == 20) {
        if ($property->province != "Tenerife") {
          echo "No es Tenerife la pronvincia!!\n";
          return ;
        }
        
        $province_id   = get_place_id($property->province, 0);
        $lugares       = explode(",", $property->town);
        // $lugares       = explode(",", $property->location_detail);
        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }
      
      // SC
      if ($agency_id == 21) {
        $province_id   = get_place_id($property->province, 0);
        $lugares       = explode("/", $property->town);
        // $lugares       = explode(",", $property->location_detail);
        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }

      // SC
      if ($agency_id == 22) {
        $province_id   = get_place_id($property->province, 0);
        $lugares       = explode("/", $property->town);
        // $lugares       = explode(",", $property->location_detail);
        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }
      
      // SC
      if ($agency_id == 23) {
        $province_id   = get_place_id($property->province, 0);
        $lugares       = explode("/", $property->town);
        // $lugares       = explode(",", $property->location_detail);
        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }
      
      // PV
      if ($agency_id == 25) {
        $province_id   = get_place_id($property->province, 0);
        $lugares       = explode("/", $property->town);
        // $lugares       = explode(",", $property->location_detail);
        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }
      
      // SG
      if ($agency_id == 26) {
        $province_id   = get_place_id($property->province, 0);
        $lugares       = explode("/", $property->town);
        // $lugares       = explode(",", $property->location_detail);
        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }
      
      // MH
      if ($agency_id == 27) {
        $province_id   = get_place_id($property->province, 0);
        $lugares       = explode("/", $property->town);
        // $lugares       = explode(",", $property->location_detail);
        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }

      // PB
      if ($agency_id == 28) {
        $province_id   = get_place_id($property->province, 0);
        $lugares       = explode("/", $property->town);
        // $lugares       = explode(",", $property->location_detail);
        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }
      
      // VA
      if ($agency_id == 29) {
        $province_id   = get_place_id($property->province, 0);
        $lugares       = explode("/", $property->town);
        // $lugares       = explode(",", $property->location_detail);
        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }
      
      // AB
      if ($agency_id == 33) {
        $province_id   = get_place_id(ucfirst(strtolower($property->province)), 0);
        $lugares       = explode("/", $property->town);
        // $lugares       = explode(",", $property->location_detail);
        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }
      
      // VM
      if ($agency_id == 34) {
        $province_id   = get_place_id(ucfirst(strtolower($property->location_detail)), 0);
        $lugares       = explode("/", $property->town);
        // $lugares       = explode(",", $property->location_detail);
        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }
      
      // PI
      if ($agency_id == 34) {
        $province_id   = get_place_id(ucfirst(strtolower($property->location_detail)), 0);
        $lugares       = explode("/", $property->town);
        // $lugares       = explode(",", $property->location_detail);
        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }
      
      // CM
      if ($agency_id == 37) {
        $province_id   = get_place_id(ucfirst(strtolower($property->province)), 0);
        $lugares       = explode("/", $property->town);
        // $lugares       = explode(",", $property->location_detail);
        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }
      
      if (empty($property->pool)) $property->pool = 3;

      $property_reduced = $property_luxury = 0;
      $property_seaview = 2;
      $property_features = "";
      $features = @(array)$property->features->children();
      if (is_array($features) && isset($features['feature'])) {
        $features = $features['feature'];
        print_r($features);
        $property_reduced = (int)(array_search('reduced', $features) !== false);
        $property_luxury = (int)(array_search('luxury', $features) !== false);
        // $property_seaview = (array_search('seaview', $features) !== false)?1:2;
        if (array_search('seaview', $features) !== false) $property_seaview = 1;
        if (array_search('Vistas mar', $features) !== false) $property_seaview = 1;
        $property_features = implode("|", array_values($features));
        $property_features = str_replace("'", "", $property_features);
      }

      $existe_ref = SQL::select_array_nocache("SELECT id FROM properties WHERE ref = '{$property->ref}' AND agency_id = '$agency_id' ");
      $existe = is_array($existe_ref);

      if ($existe) {
           // ref        = '{$property->ref}',
        $propiedad_id = $existe_ref[0]['id'];
        $sql = "
          UPDATE properties SET
           price       = '{$property->price}',
           date        = '{$property->date}',
           price_freq  = '{$property->price_freq}',
           type        = '{$property_type}',
           area        = '{$property_area}',
           beds        = '{$property->beds}',
           baths       = '{$property->baths}',
           pool        = '{$property->pool}',
           published   = '1',
           reduced     = '$property_reduced',
           features    = '$property_features',
           seaview     = '$property_seaview',
           plot        = '{$property->surface_area->plot}',
           built       = '{$property->surface_area->built}',
           euros_built = price / (built + 1),
           euros_plot  = price /(plot + 1),
           new         = 1,
           last_update = NOW()
           WHERE id    = '$propiedad_id'
        "; // , new = 0, luxury      = '$property_luxury',
        SQL::update($sql);
      } else {
      // id         = '{$property->id}',
        $sql = "
          INSERT INTO properties SET
           id         = null,
           agency_id  = '$agency_id',
           ref        = '{$property->ref}',
           price      = '{$property->price}',
           date       = '{$property->date}',
           price_freq = '{$property->price_freq}',
           type       = '{$property_type}',
           area       = '{$property_area}',
           beds       = '{$property->beds}',
           baths      = '{$property->baths}',
           pool       = '{$property->pool}',
           plot       = '{$property->surface_area->plot}',
           built      = '{$property->surface_area->built}',
           published     = '1',
           reduced       = '$property_reduced',
           features      = '$property_features',
           luxury        = '$property_luxury',
           seaview       = '$property_seaview',
           euros_built   = price / (built + 1),
           euros_plot    = price /(plot + 1),
           lead_email    = '',
           leads         = 0,
           hits          = 0,
           hits_por_lead = 150,
           views_por_hit = 6,
           new           = 1,
           priority      = RAND() * 100,
           last_update   = NOW()
        ";
        $propiedad_id = SQL::insert($sql);
      }
      echo nl2br("*$sql*");
      // $propiedad_id = $property->id;
      echo ($existe)?"Actualizada ":"Insertada ";
      echo "$propiedad_id\n";
      flush();
      
      // return ;

      // print_r($property->desc);
      $desc = $property->desc;

      if (!empty($desc->en)) add_property_desc($propiedad_id, 'en', $desc->en, 1);
      if (!empty($desc->es)) add_property_desc($propiedad_id, 'es', $desc->es, 1);
      if (!empty($desc->fr)) add_property_desc($propiedad_id, 'fr', $desc->fr, 1);
      if (!empty($desc->nl)) add_property_desc($propiedad_id, 'nl', $desc->nl, 1);
      if (!empty($desc->de)) add_property_desc($propiedad_id, 'de', $desc->de, 1);
      if (!empty($desc->ru)) add_property_desc($propiedad_id, 'ru', $desc->ru, 1);

      $images = $property->images->image;
      foreach ($images as $foto) {
        // print_r($foto);
        // echo " lalal \n";
        // $foto->url = strtolower($foto->url);
        add_property_pic($propiedad_id, $foto->url, str_replace("'", "", $foto->title->en));
      }


    }
  
  
  public function pre_import() {
  
  // RENAME TABLE properties TO properties_backup, properties_copy TO properties

    
    SQL::delete(" DROP TABLE IF EXISTS properties_copy ");
    SQL::delete(" CREATE TABLE properties_copy LIKE properties ");
    SQL::insert(" INSERT properties_copy SELECT * FROM properties ");

    /*
    SQL::update("
      update properties_copy as p set
      p.published = 0
      where agency_id = '{$this->agency_id}'
    ");
    */

    SQL::delete(" UPDATE properties SET new = 0 WHERE agency_id = '{$this->agency_id}' ");
  }
  
  public function post_import() {
    // update properties as p set p.num_fotos = ( select count(*) from photos as f where f.propiedad_id = p.id)
    

    SQL::delete(" UPDATE properties SET published = new WHERE agency_id = '{$this->agency_id}' ");

    SQL::delete("
      DELETE FROM photos
         WHERE ancho < 50 OR alto < 50
    ");
    
    SQL::update("
      UPDATE properties as p
         SET p.num_fotos = (SELECT count(*) FROM photos f WHERE f.propiedad_id = p.id)
    ");


    SQL::update("
      UPDATE properties as p
         SET p.published = 0
       WHERE num_fotos = 0
    ");


    SQL::update("
      UPDATE properties as p
         SET euros_built = 0
       WHERE built = 0
    ");

    SQL::update("
      UPDATE properties as p
         SET euros_plot = 0
       WHERE plot = 0
    ");

    SQL::update("
      UPDATE properties
         SET views_por_hit = (list_views + 100) / (hits + 1),
             hits_por_lead = (hits + 500) / (leads + 1),
             views_por_lead = 1000
    ");

    SQL::update("
      UPDATE properties as p
         SET luxury = 1
       WHERE price > 1000000
    ");

    SQL::update("
      UPDATE properties as p
         SET p.luxury = 1
       WHERE p.luxury = 0
       AND EXISTS ( SELECT * FROM descriptions as d
                    WHERE d.descrip LIKE '%lux%'
                    AND p.id = d.property_id )
    ");
    
    SQL::update("
      UPDATE properties as p
        SET p.pool = 1
        WHERE p.pool <> 1
        AND EXISTS (SELECT * FROM descriptions as d WHERE d.property_id = p.id AND d.descrip LIKE '%pool%' )
    ");
    
    SQL::update("
      UPDATE properties as p
        SET p.pool = 1
        WHERE p.pool <> 1
        AND EXISTS (SELECT * FROM descriptions as d WHERE d.property_id = p.id AND d.descrip LIKE '%piscina%' )
    ");

    SQL::update(" UPDATE properties SET published = 0 WHERE ref = '' ");
    SQL::update(" UPDATE properties SET published = 0 WHERE price = 0 ");

    SQL::update(" UPDATE properties SET published = 0 WHERE id = 2166 ");
    SQL::update(" UPDATE properties SET published = 0 WHERE id = 10017430 ");

    SQL::update(" UPDATE properties SET published = 0 WHERE agency_id = 7 AND price <= 60000 ");
    SQL::update(" UPDATE properties SET published = 0 WHERE agency_id = 5 AND price <= 300000 ");
    
    SQL::update(" UPDATE descriptions SET descrip = REPLACE(descrip, '+34 96 645 7255', '') WHERE descrip LIKE '%+34 96 645 7255%' ");
    SQL::update(" UPDATE descriptions SET descrip = REPLACE(descrip, 'on 0034 96 645 7255', '') WHERE descrip LIKE '%on 0034 96 645 7255%' ");
    
    // SQL::update(" UPDATE properties as p1 SET p1.published = 0 WHERE p1.agency_id = 12 AND 100 > (SELECT count(*) FROM properties as p2 WHERE p2.agency_id = 12 AND p2.id > p1.id) ");
    // SQL::update(" UPDATE properties as p1 SET p1.published = 0 WHERE p1.agency_id = 10 AND 200 > (SELECT count(*) FROM properties as p2 WHERE p2.agency_id = 10 AND p2.id > p1.id) ");
    // SQL::update("update places as pl set pl.num_properties = (select count(*) from properties as pr where pr.area between pl.izq and pl.der)");
    // SQL::update("update places as pl set pl.num_properties = (SELECT count(*) from properties as p JOIN agencies as a ON a.agency_id = p.agency_id  where p.published = 1 AND a.published = 1 AND p.area between pl.izq and pl.der )");

    SQL::delete(" DROP TABLE IF EXISTS places2; ");

    SQL::update(" CREATE TABLE places2 SELECT * FROM places; ");

    SQL::update("
    UPDATE places as pl
     SET pl.num_properties =
     ( SELECT count(*) from properties as p
       JOIN agencies as a ON a.agency_id = p.agency_id
       JOIN places2 as pl2 ON p.area = pl2.place_id
      WHERE p.published = 1
        AND a.published = 1
        AND pl2.izq between pl.izq and pl.der );
    ");
    
    SQL::update("
      UPDATE agencies as a
         SET num_properties = ( SELECT count(*) FROM properties as p WHERE p.agency_id = a.agency_id AND p.published = 1 )
    ");
    

    SQL::update("
      UPDATE properties as p
         SET leads = ( SELECT count(*) FROM enquiries as e WHERE e.property_id = p.id)
    ");
    
    SQL::update("
      update properties set published = 0 where agency_id = 34 and price < 230000
    ");





    // SQL::delete(" DROP TABLE IF EXISTS properties_backup; ");
    // SQL::update(" RENAME TABLE properties TO properties_backup, properties_copy TO properties ");
  }
  
  public function insert_property_xml2u($property, $agency_id) {

      // print_r($property);

      $prop_id   = $property->propertyid;
      $category  = $property->category;
      $location  = $property->Address->location;
      $region    = $property->Address->region;
      $country   = $property->Address->country;
      $price     = $property->Price->price;
      $currency  = $property->Price->currency;
      $reference = $property->Price->reference;
      $type      = $property->Description->propertyType;
      $bedrooms  = $property->Description->bedrooms;
      $bathrooms = $property->Description->fullBathrooms;
      $title     = $property->Description->title;
      $description = $property->Description->description;
      $floorSize = (int)$property->Description->FloorSize->floorSize;
      $plotSize  = (int)$property->Description->PlotSize->plotSize;
      $features  = (array)$property->Description->Features->children();
      $features  = array_values($features);
      $features  = join("|", $features);
      
      $property_features = $features;

      $images = (array)$property->images->children();
      $images = $images['image'];
      $imagenes = array();
      foreach ($images as $image) {
        // echo "url: {$image->image} \n";
        $imagenes[] = "{$image->image}";
      }
      $pool = 3;
      if (strpos($features, 'Swimming Pool') !== false) $pool = 1;

      if (0) echo "DATOS:
        prop_id $prop_id
        category $category
        location $location
        region $region
        country $country
        price $price
        currency $currency
        reference $reference
        type $type
        bedrooms $bedrooms
        bathrooms $bathrooms
        title $title
        description $description
        floorSize $floorSize
        plotSize $plotSize
        features $features
        pool $pool
        ";
      // print_r($imagenes);

      // return;
      $built = $floorSize;
      $plot  = $plotSize;
      if (empty($built) && empty($plot)) {
        // print_r($property);
        // die("ERROR built and plot ");
        echo " No tiene metros cuadrados !!! \n";
      }

      if (empty($type)) {
        echo "Tipo de Casa Vacio !!!";
        return;
      }
      $property_type = get_property_type_id($type);
      $country_id    = get_place_id("España");

      if ($region == 'Marbella') $region = 'Malaga';

      // WE
      if ($agency_id == 11) {
        $province_id   = get_place_id($region, 0);
        $lugares       = explode(",", $location);

        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          $lugar = trim($lugar);
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }

      // TP
      if ($agency_id == 16) {
        $province_id   = get_place_id($region, 0);
        $lugares       = explode(",", $location);

        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          $lugar = trim($lugar);
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }

      // UP
      if ($agency_id == 24) {

        $reference = $prop_id;
        $region    = $property->Address->subRegion;
        
        $province_id   = get_place_id($region, 0);
        $lugares       = explode(",", $location);

        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          $lugar = trim($lugar);
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }
      
      // MD
      if ($agency_id == 30) {

        $reference = $prop_id;

        $province_id   = get_place_id($region, 0);
        $lugares       = explode(",", $location);

        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          $lugar = trim($lugar);
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }
      
      // OT
      if ($agency_id == 31) {

        $reference = $prop_id;
        
        if ($region == "Costa Blanca South") $region = "Alicante";

        $province_id   = get_place_id($region, 0);
        $lugares       = explode(",", $location);

        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          $lugar = trim($lugar);
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }
      
      // SP
      if ($agency_id == 32) {
      
        if ($location == "") return "Erronea\n";
        if ($location == "/") return "Erronea\n";
        if ($location == "Block 58") return "Erronea\n";
        if ($location == "2nd Floor") return "Erronea\n";
        if ($location == "El Bosque") return "Erronea\n";
        if ($location == "Phase 3") return "Erronea\n";

        $reference = $prop_id;
        
        $location = get_nombre_poblacion($location);
        if ($location == "Desconocido") return "Erronea\n";

        $region = get_nombre_provincia($location);
        // return;

        $province_id   = get_place_id($region, 0);
        $lugares       = explode(",", $location);

        $property_area = $province_id;
        foreach ($lugares as $lugar) {
          $lugar = trim($lugar);
          if (strlen($lugar) <= 2) continue;
          $property_area = get_place_id($lugar, $property_area);
        }
      }
      


      $property_reduced = $property_luxury = 0;
      $property_seaview = 2;
      $property_features = "";


      $existe_ref = SQL::select_array_nocache("SELECT id FROM properties WHERE ref = '{$reference}'  AND agency_id = '$agency_id' ");
      $existe = is_array($existe_ref);

      if ($existe) {
           // ref        = '{$property->ref}',
        $propiedad_id = $existe_ref[0]['id'];
        $sql = "
          UPDATE properties SET
           price       = '$price',
           date        = CURDATE(),
           price_freq  = 'sale',
           type        = '$property_type',
           area        = '{$property_area}',
           beds        = '$bedrooms',
           baths       = '$bathrooms',
           pool        = '$pool',
           published   = '1',
           reduced     = '$property_reduced',
           features    = '$property_features',
           seaview     = '$property_seaview',
           plot        = '$plot',
           built       = '$built',
           euros_built = price / (built + 1),
           euros_plot  = price /(plot + 1),
           new         = 1,
           last_update = NOW()
           WHERE id    = '$propiedad_id'
        "; // , new = 0, luxury      = '$property_luxury',
        SQL::update($sql);
      } else {
      // id         = '{$property->id}',
        $sql = "
          INSERT INTO properties SET
           id         = null,
           agency_id  = '$agency_id',
           ref        = '$reference',
           price      = '$price',
           date       = CURDATE(),
           price_freq = 'sale',
           type       = '$property_type',
           area       = '{$property_area}',
           beds       = '$bedrooms',
           baths      = '$bathrooms',
           pool       = '$pool',
           plot       = '$plot',
           built      = '$built',
           published     = '1',
           reduced       = '$property_reduced',
           features      = '$property_features',
           luxury        = '$property_luxury',
           seaview       = '$property_seaview',
           euros_built   = price / (built + 1),
           euros_plot    = price /(plot + 1),
           lead_email    = '',
           leads         = 0,
           hits          = 0,
           hits_por_lead = 150,
           views_por_hit = 6,
           new           = 1,
           priority      = RAND() * 100,
           last_update   = NOW()
        ";
        $propiedad_id = SQL::insert($sql);
      }
      echo nl2br("*$sql*");
      // $propiedad_id = $property->id;
      echo ($existe)?"Actualizada ":"Insertada ";
      echo "$propiedad_id\n";
      flush();


      
      if (!empty($description)) add_property_desc($propiedad_id, 'en', $description, 1);

      foreach ($imagenes as $foto) {
        add_property_pic($propiedad_id, $foto, '');
      }
    }
    
    public function insert_property_arkadia($property, $agency_id) {
        // print_r($property);
      /*
      t__Kind
      1=For Sale
      2=For Rent
      3=Vacation rentals
      4=Equity Release
      5=New construction

      t__p__Category
      1 = Apartment & Condo
      2 = House & Single-family home
      3 = Multi-family home
      4 = Parking space
      5 = Land
      6 = Guest house & Bed and Breakfast
      7 = Office & Commercial space
      8 = Warehouse & Industrial space
      9 = Business opportunity
      10 = Misc & Unspecified


      t__p__Kind
      / * House & Single-family home * /
      1 = House
      2 = Townhouse
      3 = Private mansion
      4 = Villa
      5 = Detached house
      6 = Chalet
      7 = Farm
      8 = Mas
      9 = Property
      10 = Castle
      11 = Hamlet
      12 = Manor
      13 = Thatched cottage
      14 = Bungalow

      / * Apartment & Condo * /
      15 = Apartment
      16 = Duplex
      17 = Loft
      18 = Studio
      19 = Triplex
      20 = Penthouse

      / * Parking space  * /
      21 = Garage
      22 = Parking

      / * Misc & Unspecified * /
      28 = Boat
      29 = Mobile home

       / * Warehouse & Industrial space * /
      30 = Warehouse
      31 = Industrial space

      / * Office & Commercial space * /
      32 = Office space
      33 = Commercial space

      / * Business Opportunity * /
      34=Bar, Hotel, Restaurant
      35=Food & Beverage
      36=Beauty & Health
      37=Sport & Fitness
      38=Nightclub, Bowling, Recreation
      39=Newsagency & Tatts
      40=Phone, Computer, Home Appliance
      41=Clothing & Accessories
      42=Jewelry
      43=Home Furniture & Furnishings
      44=Toys & Video Games
      45=Florist & Garden Center
      46=Agriculture & Horticulture
      47=Auto, Moto, Boat, Transport
      48=Animal & Pet Care
      49=Building Mat. & Hardware Stores
      50=Shoe Repair & Locksmith
      51=Cleaning & Laundry
      52=Building & Construction
      53=Services
      54=Miscellaneous


      */

        $trans_kind  = (int)$property->t__Kind;
        $reference   = (string)$property->t__ID_local;
        $price       = (int)$property->t__Total_sale_price;
        $category    = (int)$property->t__p__Category;
        $kind        = (int)$property->t__p__Kind;
        $date        = (string)$property->t__p__Last_alteration_date;
        $city        = (string)$property->t__p__City;
        $province    = (string)$property->t__p__Province;
        $country     = (string)$property->t__p__Country;
        $built       = (int)$property->t__p__Surface;
        $plot        = (int)$property->t__p__Ground_surface;
        $bedrooms    = (int)$property->t__p__Nb_bedroom;
        $pool        = (int)$property->t__p__Swimming_pool;
        $garage      = (int)$property->t__p__Garage;

        $description = array();
        if (!empty($property->t__tco__1__USA)) $description['en'] = (string)$property->t__tco__1__USA;
        if (!empty($property->t__tco__1__ESP)) $description['es'] = (string)$property->t__tco__1__ESP;
        if (!empty($property->t__tco__1__DEU)) $description['de'] = (string)$property->t__tco__1__DEU;
        if (!empty($property->t__tco__1__NLD)) $description['nl'] = (string)$property->t__tco__1__NLD;
        if (!empty($property->t__tco__1__RUS)) $description['ru'] = (string)$property->t__tco__1__RUS;
        if (!empty($property->t__tco__1__FRA)) $description['fr'] = (string)$property->t__tco__1__FRA;

        $imagenes = array();
        for ($cont = 1; $cont<20; $cont++) {
          $field_name = "t__p__pi__{$cont}__Filedata";
          if (empty($property->$field_name)) break;
          $imagenes[] = (string)$property->$field_name;
        }
        $pool = ($pool == 0)?3:1;

        $arkadia_features = array(
          1	=> 'Refrigerator',
          2	=> 'Freezer',
          3	=> 'Hotplates',
          4	=> 'Oven',
          5	=> 'Microwave',
          6	=> 'Dishes',
          7	=> 'Dishwasher',
          8	=> 'Washing machine',
          9	=> 'Tumble dryer',
          10	=> 'Pets allowed',
          11	=> 'Elevator',
          12	=> 'Door code',
          13	=> 'Entry phone',
          14	=> 'Satellite dish',
          15	=> 'Cable TV',
          16	=> 'Telephone',
          17	=> 'Internet access',
          18	=> 'Alarm',
          19	=> 'Caretaker',
          20	=> 'Air-conditioning',
          21	=> 'Fireplace',
          22	=> 'Balcony',
          23	=> 'Terrace',
          24	=> 'Barbecue set',
          25	=> 'Tennis',
          26	=> 'Veranda',
          27	=> 'Garden shelter',
          28	=> 'Automatic watering system',
          29	=> 'Attic',
          30	=> 'Mezzanine',
          31	=> 'High ceilings',
          32	=> 'Cellar',
          33	=> 'Bicycle room',
          34	=> 'Garbage chute',
          35	=> 'Wheelchair accessible',
          36	=> 'Party wall',
          37	=> 'Serviced land',
          38	=> 'Fenced land',
          39	=> 'Main sewer',
          40	=> 'Usable raw material',
          41	=> 'Company accommodation',
          42	=> 'Reception hall',
          43	=> 'Portable partitions',
          44	=> 'Network wiring',
          45	=> 'Docks',
          46	=> 'Large truck access',
          47	=> 'Television',
        );

        $features = array();
        foreach ($arkadia_features as $num_feature => $feature_str) {
          $field_name = "t__p__pef__{$num_feature}";
          if (!empty($property->$field_name)) $features[] = (string)$feature_str;
        }
        $property_features = join("|", $features);


        $arkadia_types = array(
          1 => 'House',
          2 => 'Townhouse',
          3 => 'Private mansion',
          4 => 'Villa',
          5 => 'Detached house',
          6 => 'Chalet',
          7 => 'Farm',
          8 => 'Mas',
          9 => 'Property',
          10 => 'Castle',
          11 => 'Hamlet',
          12 => 'Manor',
          13 => 'Thatched cottage',
          14 => 'Bungalow',

          /* Apartment & Condo */
          15 => 'Apartment',
          16 => 'Duplex',
          17 => 'Loft',
          18 => 'Studio',
          19 => 'Triplex',
          20 => 'Penthouse',
        );
        $type = $arkadia_types[$kind];
        echo "DATOS:
          trans_kind $trans_kind
          price $price
          category $category
          kind $kind ($type)
          date $date
          city $city
          province $province
          country $country
          built $built
          plot $plot
          bedrooms $bedrooms
          pool $pool
          garage $garage
          property_features $property_features
          ";
        print_r($description);
        print_r($imagenes);

        if ($trans_kind != 1) {
          echo "No es venta!! ";
          return;
        }

        if ($kind > 20)  {
          echo "No es casa!! ";
          return;
        }

        // return;

        if (empty($built) && empty($plot)) {
          // print_r($property);
          // die("ERROR built and plot ");
          echo " No tiene metros cuadrados !!! \n";
        }

        if (empty($type)) {
          echo "Tipo de Casa Vacio !!!";
          return;
        }


        $property_type = get_property_type_id($type);
        $country_id    = get_place_id("España");

        if ($province == 'Marbella') $province = 'Malaga';
        if ($province == 'Girona') $province = 'Gerona';
        if ($province == 'Lleida') $province = 'Lerida';


        // RD

        $province_id   = get_place_id($province, 0);
        $property_area = get_place_id($city, $province_id);
        echo "property_area: $province $city $property_area \n";


        $property_reduced = $property_luxury = 0;
        $property_seaview = 2;
        //$property_features = "";


        $existe_ref = SQL::select_array_nocache("SELECT id FROM properties WHERE ref = '{$reference}'  AND agency_id = '$agency_id' ");
        $existe = is_array($existe_ref);

        if ($existe) {
             // ref        = '{$property->ref}',
          $propiedad_id = $existe_ref[0]['id'];
          $sql = "
            UPDATE properties SET
             price       = '$price',
             date        = '$date',
             price_freq  = 'sale',
             type        = '$property_type',
             area        = '{$property_area}',
             beds        = '$bedrooms',
             baths       = '$bathrooms',
             pool        = '$pool',
             published   = '1',
             reduced     = '$property_reduced',
             features    = '$property_features',
             seaview     = '$property_seaview',
             plot        = '$plot',
             built       = '$built',
             euros_built = price / (built + 1),
             euros_plot  = price /(plot + 1),
             new         = 1,
             last_update = NOW()
             WHERE id    = '$propiedad_id'
          "; // , new = 0, luxury      = '$property_luxury',
          SQL::update($sql);
        } else {
        // id         = '{$property->id}',
          $sql = "
            INSERT INTO properties SET
             id         = null,
             agency_id  = '$agency_id',
             ref        = '$reference',
             price      = '$price',
             date       = '$date',
             price_freq = 'sale',
             type       = '$property_type',
             area       = '{$property_area}',
             beds       = '$bedrooms',
             baths      = '$bathrooms',
             pool       = '$pool',
             plot       = '$plot',
             built      = '$built',
             published     = '1',
             reduced       = '$property_reduced',
             features      = '$property_features',
             luxury        = '$property_luxury',
             seaview       = '$property_seaview',
             euros_built   = price / (built + 1),
             euros_plot    = price /(plot + 1),
             lead_email    = '',
             leads         = 0,
             hits          = 0,
             hits_por_lead = 150,
             views_por_hit = 6,
             new           = 1,
             priority      = RAND() * 100,
             last_update   = NOW()
          ";
          $propiedad_id = SQL::insert($sql);
        }
        echo nl2br("*$sql*");
        // $propiedad_id = $property->id;
        echo ($existe)?"Actualizada ":"Insertada ";
        echo "$propiedad_id\n";
        flush();

        // return ;
        foreach ($description as $lang => $descrip) {
          $descrip = trim($descrip);
          if (!empty($descrip)) add_property_desc($propiedad_id, $lang, $descrip, 1);
        }

        foreach ($imagenes as $foto) {
          add_property_pic($propiedad_id, $foto, '');
        }


      }
  
}

////////////////////////////////////////////////////////////////////////////////

function add_property_desc($property_id, $lang, $desc, $type = 1) {
    $desc = strip_tags($desc);
    $desc = mysql_real_escape_string($desc);
    // echo " --$desc-- ";
    $sql = "
      REPLACE  INTO descriptions SET
        property_id	= '$property_id',
        lang = '$lang',
        desc_type = '$type',
        descrip = '$desc'
    ";
    // echo "*$sql*";
    SQL::insert($sql);
}


function create_thumb_alto($original_name, $thumb_name, $alto = 100) {

  $thumb = new thumbnail($original_name);
  $thumb->size_height($alto);
  $thumb->jpeg_quality(80);
  $thumb->save($thumb_name);
}



function add_property_pic($property_id, $foto_url, $foto_title) {
    echo nl2br($foto_url."\n");

    $foto_url = mysql_real_escape_string(trim($foto_url));
    $foto_url = str_replace("+", "%20", $foto_url);
    $foto_url = str_replace(" ", "%20", $foto_url);
    $foto_url = str_replace("width=medium", "width=large", $foto_url);
    $foto_url = str_replace("&amp;amp;", "&", $foto_url);
    $foto_url = str_replace("&amp;", "&", $foto_url);
    $foto_url = str_replace("&amp;", "&", $foto_url);
    
    echo nl2br("Foto URL: $foto_url \n");

    
    // $foto_url = str_replace("width=600", "width=1000", $foto_url);

    $foto_info = SQL::select_array_nocache("SELECT * FROM photos WHERE path = '$foto_url' ");
    if (!is_null($foto_info)) {
      echo nl2br("La foto $foto_url ya ha sido obtenida \n");
      $foto_id = SQL::update("UPDATE photos SET propiedad_id = '$property_id', title = '$foto_title' WHERE path = '$foto_url' ");
      return;
    }

    // Traemos la foto a local.
    // $img = file_get_contents_curl($foto_url);
    $img = file_get_contents($foto_url);

    if ($img === FALSE) {
      echo nl2br("Error al obtener $foto_url \n");
      return;
    }

    // $temp_filename = "fotos/original";
    $temp_filename = "xml/foto.jpg";

    file_put_contents($temp_filename, $img);
    $bytes = filesize($temp_filename);

    if ($bytes == 0) {
      echo nl2br("Cero bytes al traer $foto_url \n");
      return;
    }

    $img_info = getimagesize($temp_filename, $img_info2);
    // print_r($img_info);
    // print_r($img_info2);

    $sql = "
      INSERT INTO photos SET
      foto_id = NULL,
      propiedad_id = '$property_id',
      path = '$foto_url',
      title = '$foto_title',
      ancho = '{$img_info['0']}',
      alto = '{$img_info['1']}',
      bytes = '$bytes',
      imagetype = '{$img_info['2']}',
      mime = '{$img_info['mime']}',
      info1 = '',
      info2 = '',
      estado = '1'
    ";
    // echo "*$sql*";
    $foto_id = SQL::insert($sql);

    rename($temp_filename, $temp_filename.image_type_to_extension($img_info['2']));
    $temp_filename .= image_type_to_extension($img_info['2']);

    $original_name = "fotos/{$foto_id}.jpg";
    create_thumb_alto($temp_filename, $original_name, 454);

    // generamos los tumbnails
    $thumb_name = "thumbs/{$foto_id}_y200.jpg";
    if (!file_exists($thumb_name) || filesize($thumb_name) == 0 ) {
      create_thumb_alto($temp_filename, $thumb_name, 200);
      echo "<img src='$thumb_name' />\n\n";
    }

    $thumb_name = "thumbs/{$foto_id}_y100.jpg";
    if (!file_exists($thumb_name) || filesize($thumb_name) == 0 ) {
      create_thumb_alto($temp_filename, $thumb_name, 100);
      echo "<img src='$thumb_name' />\n\n";
    }

    // unlink($temp_filename);
    rename($temp_filename, "fotos_originales/{$foto_id}".image_type_to_extension($img_info['2']));
}

function get_property_type_id($tipo_propiedad) {
  $search = SQL::select_array_nocache(" SELECT * FROM property_types WHERE kyero_xml LIKE '%".ucfirst($tipo_propiedad)."%' ");
  if (!is_array($search)) {
    return SQL::insert("
      INSERT INTO property_types SET
        type_id = null,
        name = '{{TIPO_".elimina_acentos(strtoupper($tipo_propiedad))."}}',
        father_id = 0,
        kyero_xml = '".ucfirst($tipo_propiedad)."'
    ");
  } else {
    return $search[0]['type_id'];
  }
}

function get_english_type($spanish_name) {

  $spanish_name = trim($spanish_name);
  $spanish_name = strtolower($spanish_name);
  $spanish_names = array(
    'aparcamiento coche' => 'Parking',
    'apartamento' => 'Apartment',
    'estudio' => 'Studio',
    'bungalow' => 'Bungalow',
    'bar' => 'Commercial',
    'casa' => 'House',
    'casa adosada' => 'Attached House',
    'casa pareada' => 'Semi Detached',
    'duplex' => 'Duplex',
    'finca rústica' => 'Country Home',
    'finca rustica' => 'Country Home',
    'local comercial' => 'Commercial',
    'loft' => 'Studio',
    'masía' => 'Country Home',
    'masia' => 'Country Home',
    'nave industrial' => 'Commercial',
    'otros' => 'Country Home',
    'piso' => 'Apartment',
    'planta baja' => 'Commercial',
    'restaurante' => 'Restaurant',
    'solar urbano' => 'Plot',
    'terreno residencial' => 'Plot',
    'torre' => 'Villa',
    'chalets' => 'Villa',
    'chalet' => 'Villa',
    'ático' => 'Penthouse',
    'atico' => 'Penthouse',
    'edificio' => 'Commercial',
  );

  if (!in_array($spanish_name, array_keys($spanish_names))) {
    // die ("Tipo $spanish_name no encontrado ! ");
  }
  echo "\nTraducción de $spanish_name es: ".$spanish_names[$spanish_name];
  // die("");

  return $spanish_names[$spanish_name];

}


function display_xml_error($error, $xml)
{
    $return  = $xml[$error->line - 1] . "\n";
    $return .= str_repeat('-', $error->column) . "^\n";

    switch ($error->level) {
        case LIBXML_ERR_WARNING:
            $return .= "Warning $error->code: ";
            break;
         case LIBXML_ERR_ERROR:
            $return .= "Error $error->code: ";
            break;
        case LIBXML_ERR_FATAL:
            $return .= "Fatal Error $error->code: ";
            break;
    }

    $return .= trim($error->message) .
               "\n  Line: $error->line" .
               "\n  Column: $error->column";

    if ($error->file) {
        $return .= "\n  File: $error->file";
    }

    return "$return\n\n--------------------------------------------\n\n";
}


function get_nombre_poblacion($poblacion) {
  $poblacion = ucwords(strtolower($poblacion));
  
  if ($poblacion == "La Manga") $poblacion = "Manga Del Mar Menor";
  if ($poblacion == "La Manga Del Mar Menor") $poblacion = "Manga Del Mar Menor";
  if ($poblacion == "La Manga Golf Resort") $poblacion = "Manga Del Mar Menor";
  
  if ($poblacion == "Banos y mendigo") $poblacion = "Baños y mendigo";
  
  if ($poblacion == "Gran Alicant") $poblacion = "Alicante";
  if ($poblacion == "Gran Alacant") $poblacion = "Alicante";
  

  

  if ($poblacion == "Hondon De Los Nieves") $poblacion = "Hondon De Las Nieves";
  if ($poblacion == "Quesada") $poblacion = "Ciudad Quesada";
  if ($poblacion == "Lo Crispen") $poblacion = "Algorfa";
  if ($poblacion == "Las Ramblas") $poblacion = "Campoamor";
  if ($poblacion == "Guardamar") $poblacion = "Guardamar Del Segura";
  if ($poblacion == "La Torre") $poblacion = "Roldan";
  if ($poblacion == "El Raso") $poblacion = "Guardamar Del Segura";
  if ($poblacion == "Pau8") $poblacion = "Villamartin";
  if ($poblacion == "Las Filipinas") $poblacion = "Villamartin";
  if ($poblacion == "Los Montesinos") $poblacion = "Villamartin";
  if ($poblacion == "Pau8") $poblacion = "Villamartin";
  if ($poblacion == "Blue Lagoon") $poblacion = "Villamartin";
  if ($poblacion == "El Galan") $poblacion = "Villamartin";
  if ($poblacion == "Playa Flamenca") $poblacion = "Orihuela";
  if ($poblacion == "Benimar") $poblacion = "Benijofar";
  if ($poblacion == "Formentera") $poblacion = "Formentera Del Segura";
  if ($poblacion == "Vista Bella Golf") $poblacion = "Orihuela";
  if ($poblacion == "La Siesta") $poblacion = "Torrevieja";
  if ($poblacion == "El Chaparral") $poblacion = "Torrevieja";
  if ($poblacion == "Monte Azul") $poblacion = "Mar Azul";

  if ($poblacion == "Condado De Alhama") $poblacion = "Alhama De Murcia";
  if ($poblacion == "Rojales Hills") $poblacion = "Rojales";
  if ($poblacion == "La Cinuelica") $poblacion = "Orihuela Costa";
  if ($poblacion == "Vista Bella Golf") $poblacion = "Orihuela";
  if ($poblacion == "San Cayetando") $poblacion = "San Cayetano";

  $nombre_poblacion = "Desconocido";
  
  $search = SQL::select(" SELECT * FROM codigos_postales WHERE ciudad LIKE '".strtoupper($poblacion)."' ");
  if (is_array($search)) {
    $nombre_poblacion = ucwords(strtolower($search[0]['ciudad']));
    echo "Encontrada $poblacion en la DB: $nombre_poblacion \n<br />";
    return $nombre_poblacion;
  }

  // Buscamos la población de menor distancia levenfish
  $search = SQL::select(" SELECT * FROM codigos_postales ");
  $min_dist = -1;
  $province = "";
  foreach ($search as $search_line) {
    similar_text ($poblacion, $search_line['ciudad'], $percent);
    if ($percent > $min_dist){
      $min_dist = $percent;
      $nombre_poblacion = ucwords(strtolower($search_line['ciudad']));
    }
  }
  echo "Mas aproximada a $poblacion en la DB: $nombre_poblacion ($min_dist) \n<br />";
  
  return $nombre_poblacion;
}

function get_nombre_provincia($poblacion) {

  $nombre_provincia = "Desconocido";
  
  $search = SQL::select(" SELECT * FROM codigos_postales WHERE ciudad LIKE '".strtoupper($poblacion)."' ");
  if (is_array($search)) {
    $nombre_provincia = ucwords(strtolower($search[0]['provincia']));
  } else {
    die("Poblacion no encontrada en la BBDD ");
  }

  echo "La pronvincia de $poblacion es $nombre_provincia\n<br />";
  // die("--FIN**");
  return $nombre_provincia;
}


?>
