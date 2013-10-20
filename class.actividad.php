<?php

require_once ("class.sql.php");


class ACTIVIDAD {

  public static function insert($tipo = 0, $descripcion = '', $estado_antes = '', $estado_despues = '') {
    $usuario = $_SERVER['PHP_AUTH_USER'];
    SQL::insert("
      INSERT INTO actividad
      SET
        actividad_id = NULL,
        timestamp = NOW(),
        usuario_id = '$usuario',
        tipo = '$tipo',
        descripcion = '$descripcion',
        estado_antes = '$estado_antes',
        estado_despues = '$estado_despues'
    ");
  }
  
  public static function search($actividad_id = 0, $timestamp = 0, $usuario_id = '', $tipo = 0, $descripcion = '') {
    $sql = 'SELECT * FROM actividad';
    $sql_where = " WHERE (1 = 1) ";
    if (!empty($actividad_id)) $sql_where .= " AND (actividad_id > '$actividad_id') ";
    if (!empty($timestamp))    $sql_where .= " AND (timestamp > '$timestamp') ";
    if (!empty($usuario_id))   $sql_where .= " AND (usuario_id = '$usuario_id') ";
    if (!empty($tipo))         $sql_where .= " AND (tipo = '$tipo') ";
    if (!empty($descripcion))  $sql_where .= " AND (descripcion LIKE '%$descripcion%') ";
    $listado = SQL::select($sql.$sql_where);
    return $listado;
  }

}

?>
