<?php

// require_once('includes.inc.php');
$memcache_obj = memcache_connect("localhost", 11211);
define ("DEFAULT_CACHE_TIME", 60 * 30);


function array_count($array_to_count) {
  if (!is_array($array_to_count)) return 0;
  return count($array_to_count);
}

function mysql_die($error) {
  // mail("marioraulcarbonell@gmail.com", "ERROR sql", $error);
  die("Mysql Error: $error");
}
  
  

// Clase para acceso a la base de datos.

class SQL {

  private static $db_host = "localhost";
  private static $db_user = "eudomi";
  private static $db_pass = "marioraul12";
  private static $db_data = "eudomidb";
  
  // private static $connected = false;
  private static $db_link = null;
  
  public function __construct() {
    // Clase absctracta, no permitimos contructores ni desctructores
    mysql_die("No se permite el constructor");
  }
  
  public static function escape_string($string) {
    return mysqli_real_escape_string(self::$db_link, $string);
  }
  
  public static function connect_db($db_host = '', $db_user = '', $db_pass = '', $db_data = '') {
      if (!empty($db_host)) self::$db_host = $db_host;
      if (!empty($db_user)) self::$db_user = $db_user;
      if (!empty($db_pass)) self::$db_pass = $db_pass;
      if (!empty($db_data)) self::$db_data = $db_data;
      // Conexion, seleccion de base de datos
      self::$db_link = mysql_connect(self::$db_host, self::$db_user, self::$db_pass)
        or mysql_die('ERROR: No pudo conectarse : ' . mysql_error());
      mysql_select_db(self::$db_data) or mysql_die('ERROR: No pudo seleccionarse la BD.');
      mysql_query("SET NAMES utf8");
  }
  
  public static function check_connected() {
    if (is_null(self::$db_link)) self::connect_db();
    if (is_null(self::$db_link)) mysql_die ("No conectado a la BD");
  }

  public static function select_array_nocache($sql) {
    if (empty($sql)) return null;
    self::check_connected();

    $resultado = mysql_query($sql) or mysql_die("ERROR: La consulta falló: $sql".mysql_error());
    if (!mysql_num_rows($resultado)) return null;

    $lineas_resultado = array();
    while ($linea = mysql_fetch_array($resultado, MYSQL_ASSOC)) {
      $lineas_resultado[] = $linea;
    }
    return $lineas_resultado;
  }
  
  public static function select_array($sql) {
    global $memcache_obj;

    if (empty($sql)) return null;

    $cached_value = $memcache_obj->get(md5($sql));
    if ($cached_value !== false) { // Cacheada
      return $cached_value;
    }

    // No cacheada
    $value = SQL::select_array_nocache($sql);
    $memcache_obj->add(md5($sql), $value, false, DEFAULT_CACHE_TIME);
    return $value;
  }


  public static function select_one_nocache($sql) {
    if (empty($sql)) return null;
    self::check_connected();

    $resultado = mysql_query($sql) or mysql_die("ERROR: La consulta falló: $sql".mysql_error());
    if (!mysql_num_rows($resultado)) return null;

    $linea = mysql_fetch_array($resultado, MYSQL_ASSOC);
    return $linea;
  }
  
  public static function select_one($sql) {
    global $memcache_obj;
    
    if (empty($sql)) return null;

    $cached_value = $memcache_obj->get(md5($sql));
    if ($cached_value !== false) { // Cacheada
      return $cached_value;
    }

    // No cacheada
    $value = SQL::select_one_nocache($sql);
    $memcache_obj->add(md5($sql), $value, false, DEFAULT_CACHE_TIME);
    return $value;
  }
  
  
  public static function count($sql) {
    if (empty($sql)) return null;
    self::check_connected();

    $resultado = mysql_query($sql) or mysql_die("ERROR: La consulta falló: $sql".mysql_error());
    return (mysql_num_rows($resultado));
  }
  

  public static function select($sql) {
    return self::select_array($sql);
  }
  
  public static function insert($sql) {
    if (empty($sql)) return null;
    self::check_connected();

    $resultado = mysql_query($sql) or mysql_die("ERROR: La consulta falló: $sql".mysql_error());
    return mysql_insert_id();
  }
  
  public static function update($sql) {
    if (empty($sql)) return null;
    self::check_connected();

    $resultado = mysql_query($sql) or mysql_die("ERROR: La consulta falló: $sql".mysql_error());
    return mysql_affected_rows();
  }

  public static function replace($sql) {
    if (empty($sql)) return null;
    self::check_connected();

    $resultado = mysql_query($sql) or mysql_die("ERROR: La consulta falló: $sql".mysql_error());
    return ;
  }
  
  public static function delete($sql) {
    if (empty($sql)) return null;
    self::check_connected();

    $resultado = mysql_query($sql) or mysql_die("ERROR: La consulta falló: $sql".mysql_error());
    return true;
  }
  

  
  
  public static function check_unique($table, $field, $value) {
    $db_data = self::select_one(" SELECT * FROM $table WHERE $field = '$value' ");
    return (is_null($db_data));
  }

}




?>
