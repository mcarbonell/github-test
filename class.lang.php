<?php

require_once("funciones.inc.php");

class LANG {
  public static $lang = null; // idioma seleccionado
  public static $texts = null;
  // public static $valid_langs = array('fr', 'en', 'de', 'nl', 'it', 'es');
  public static $valid_langs = array('fr', 'es', 'en', 'de', 'nl', 'ru');
  public static $valid_langs_hosts = array(
    'fr' => 'www.eudomi.fr',
    'es' => 'www.eudomi.es',
    'en' => 'www.eudomi.com',
    'de' => 'www.eudomi.de',
    'nl' => 'www.eudomi.nl',
    'ru' => 'www.eudomi.ru'
    );

  public static function read_lang_file($lang) {
    if (($_SERVER["HTTP_HOST"] == 'www.spanischeimmobilien.com') && ($lang = 'de')) $lang = 'de2';
    
    $filename = "/var/www/vhosts/default/htdocs/texts/".$lang.".txt";
    print_debug (" *LANG::read_file($filename)* ");
    if (!file_exists($filename)) die("No existe $filename");
    $readed_array = parse_ini_file($filename);
    foreach ($readed_array as $key => $value) {
      self::$texts['{{'.$key.'}}'] = $value;
    }
  }

  public static function get_text($index) {
    // print_debug (" *LANG::get_text($index)* ");
    if (empty(LANG::$lang)) {
      print_debug (" *empty(lang)* ");
      LANG::$lang = LANG::get_lang();
    }
    if (is_null(LANG::$texts)) {
      self::read_lang_file(self::get_lang());
    }
    if (!isset(self::$texts[$index])) return $index;
    return self::$texts[$index];
  }

  public static function set_lang($idioma) {
    print_debug (" *LANG::set_lang($idioma)* ");
    if (empty($idioma)) return;
    // if (!in_array($idioma, self::$valid_langs)) die("Idioma no valido $idioma ");
    self::$lang = $idioma;
  }

  public static function get_lang() {
    // print_debug (" *LANG::get_lang()* ");
    if (empty(LANG::$lang)) {
      if (isset($_REQUEST['lang'])) {
        LANG::$lang = $_REQUEST['lang'];
      } else {
        print_debug (" *asignando* ");
        LANG::$lang = LANG::get_default_lang();
      }
    }
    return LANG::$lang;
  }

  public static function get_default_lang() {
    // print_debug (" *LANG::get_default_lang()* ");
    // if (is_debug()) var_dump(debug_backtrace());
    // self::$lang = self::$valid_langs[0];
    if ($_SERVER["HTTP_HOST"] == 'www.espagneimmobilier.net') return 'fr';
    if ($_SERVER["HTTP_HOST"] == 'www.eudomi.fr') return 'fr';
    if ($_SERVER["HTTP_HOST"] == 'www.eudomi.es') return 'es';
    if ($_SERVER["HTTP_HOST"] == 'www.eudomi.ru') return 'ru';
    if ($_SERVER["HTTP_HOST"] == 'www.eudomi.co.uk') return 'en';
    if ($_SERVER["HTTP_HOST"] == 'www.eudomi.com') return 'en';
    if ($_SERVER["HTTP_HOST"] == 'www.eudomi.de') return 'de';
    if ($_SERVER["HTTP_HOST"] == 'www.eudomi.at') return 'de';
    if ($_SERVER["HTTP_HOST"] == 'www.eudomi.ch') return 'de';
    if ($_SERVER["HTTP_HOST"] == 'www.eudomi.nl') return 'nl';
    if ($_SERVER["HTTP_HOST"] == 'www.eudomi.be') return 'nl';
    if ($_SERVER["HTTP_HOST"] == 'www.eudomi.fr') return 'fr';
    if ($_SERVER["HTTP_HOST"] == 'www.eudomi.it') return 'en';
    if ($_SERVER["HTTP_HOST"] == 'www.eudomi.gr') return 'en';
    if ($_SERVER["HTTP_HOST"] == 'www.eudomi.net') return 'en';
    if ($_SERVER["HTTP_HOST"] == 'www.eudomi.asia') return 'en';
    if ($_SERVER["HTTP_HOST"] == 'www.spanischeimmobilien.com') return 'de';
    
    return self::$valid_langs[0]; // El primero
  }
  
  public static function do_lang_translation($text) {
    if (is_null(LANG::$texts)) {
      self::read_lang_file(self::get_lang());
    }

    // $text = str_replace("{{", "", $text);
    // $text = str_replace("}}", "", $text);
    
    $text = strtr($text, self::$texts);
    return $text;
  }
  
}

function get_text($index) {
  return LANG::get_text($index);
}

function set_lang($idioma) {
  print_debug ("set_lang($idioma)");
  LANG::set_lang($idioma);
}

function get_lang() {
  return LANG::get_lang();
}

function get_default_lang() {
  return LANG::get_default_lang();
}

////////////////////////////////////////////////////////////////////////////////

?>
