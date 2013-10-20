<?php


////////////////////////////////////////////////////////////////////////////////


class HTML {
  public function tag($tag = 'div', $content = '', $class = '') {
    if (is_array($content)) {
      return HTML::tag($tag, array_shift($content), $class).HTML::tag($tag, $content, $class);
    }
    if (!empty($class)) $class = " class='$class'";
    return "<$tag$class>$content</$tag>";
  }

  public function meta_tag($property_name, $name, $content) {
    return "<meta $property_name='$name' content='$content' />\n";
  }

  public function meta_http_equiv($name, $content) {
    return HTML::meta_tag('http-equiv', $name, $content);
  }

  public function meta_name($name, $content) {
    return HTML::meta_tag('name', $name, $content);
  }

  public function script($url = '', $content = '') {
    if (is_array($url)) {
      return HTML::script(array_shift($url)).HTML::script($url);
    }

    if (!empty($url)) $url = " src='$url'";
    return "<script type='text/javascript' $url>$content</script>\n";
  }

  public function link($rel = '', $href = '', $type = '', $media = '') {
    if (!empty($rel)) $rel = " rel='$rel'";
    if (!empty($href)) $href = " href='$href'";
    if (!empty($type)) $type = " type='$type'";
    if (!empty($media)) $media = " media='$media'";
    return "<link$rel$href$type$media/>\n";
  }

  public function get_select_options ($input_array, $selected_index = 0) {
    $select_str = '';
  	foreach ($input_array as $clave => $valor) {
  		$select_str .= "<option value='$clave'";
  		if ($clave == $selected_index)
  			$select_str .= " selected "; // style='background-color: #CECECE;'
  		$select_str .= ">$valor</option>\n";
  	}
  	return $select_str;
  }

  public function make_select ($select_name, $input_array, $selected_index = 0, $null = false, $disabled = false, $css_class = '', $inline_style = '' ) {
  	$disabled = ($disabled == true)?" disabled='disabled' ":"";
  	if (!empty($css_class)) $css_class = " class='$css_class' ";
  	if (!empty($selected_index)) $inline_style = " style='border: 1px solid black; $inline_style' '";
  	$select_str = "<select $disabled name='$select_name' id='$select_name' $css_class $inline_style>\n";
  	if ($null) $select_str .= "<option value=''></option>\n";
  	$select_str .= HTML::get_select_options($input_array, $selected_index);
  	$select_str .= "</select>\n";
  	return $select_str;
  }

  public function make_select_group ($select_name, $input_array, $selected_index = 0, $null = false, $disabled = false, $css_clas = '' ) {
  	$disabled = ($disabled == true)?" disabled='disabled' ":"";
  	if (!empty($css_clas)) $css_clas = " class='$css_clas' ";
  	if (!empty($selected_index)) $inline_style = " style='border: 1px solid black;' '";
  	$select_str = "<select $disabled name='$select_name' id='$select_name' $css_clas $inline_style>\n";
  	if ($null) $select_str .= "<option value=''></option>\n";
  	foreach ($input_array as $clave => $valor) {
      if (!is_array($valor)) {
    		$select_str .= "<option value='$clave'";
    		if ($clave == $selected_index)
    			$select_str .= " selected "; // style='background-color: #CECECE;'
    		$select_str .= ">$valor</option>\n";
  		} else {
  		  $select_str .=  "<optgroup label='$clave'>".HTML::get_select_options($valor, $selected_index)."</optgroup>\n";
      }
  	}
  	$select_str .= "</select>\n";
  	return $select_str;
  }

  public function make_radio ($radio_name, $input_array, $selected_index = 0, $null = false, $disabled = false ) {
  	$disabled = ($disabled == true)?" disabled='disabled' ":"";

  	$radio_str = "";
  	$br = (count($input_array)>2)?"<br/>":"";
  	foreach ($input_array as $clave => $valor) {
  		$radio_str .= "<input $disabled type='radio' name='$radio_name' value='$clave' ";
  		if ($clave == $selected_index) $radio_str .= " checked ";
  		$radio_str .= ">$valor$br\n";
  	}
  	return $radio_str;
  }
}




////////////////////////////////////////////////////////////////////////////////
class FUNCTIONS {
  public static function load_template($template_file) {
    return file_get_contents("templates/$template_file");
  }

  public static function get_current_url() {
    $url = (@$_SERVER['HTTPS'] == 'on')?'https://':'http://';
    $url .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    return $url;
  }
  public static function format_money($number) {
    // string number_format ( float $number , int $decimals = 0 , string $dec_point = '.' , string $thousands_sep = ',' )
    return number_format ($number, 0, ',', '.');
  }

  public static function format_number($number) {
    // string number_format ( float $number , int $decimals = 0 , string $dec_point = '.' , string $thousands_sep = ',' )
    return number_format ($number, 0, ',', '.');
  }

}

class HTTP {
  public static function do_redirect($new_url) {
    header('Location: '.$new_url);
    die();
  }

  public static function do_redirect_301($new_url) {
    header('HTTP/1.1 301 Moved Permanently');
    HTTP::do_redirect($new_url);
  }
}
////////////////////////////////////////////////////////////////////////////////

function make_link_url($url = '', $query_string = '', $lang = '') {
  // Si no pasamos url,
  if (empty($url)) $url = $_SERVER["SCRIPT_NAME"];
  if (empty($query_string)) $query_string = $_SERVER["QUERY_STRING"];
  if (empty($lang)) $lang = get_lang();
  $query_arr = array();
  parse_str ($query_string, $query_arr);
  $query_arr['lang'] = $lang;
  if ($lang == LANG::get_default_lang()) unset($query_arr['lang']);
  $query_str = http_build_query($query_arr, '', '&amp;');
  if (!empty($query_str)) $url .= '?'.$query_str;
  return $url;
}
