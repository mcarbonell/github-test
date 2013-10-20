<?php

////////////////////////////////////////////////////////////////////////////////
/////// TEMPLATE CLASS                    //////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

// $template_name = (isset($_GET['template']))?filter_input(INPUT_GET, 'template', FILTER_SANITIZE_STRING):'main';
// $template_name = 'main';

class template {
  // private static $templates_dir = "templates/{$template_name}/";
  private static $templates_dir = "templates/main/";
  
  public static function templates_dir(){
    $template_name = (isset($_GET['template']))?filter_input(INPUT_GET, 'template', FILTER_SANITIZE_STRING):'main2';
    if ($_SERVER['HTTP_HOST'] == 'www.espagneimmobilier.net') $template_name = 'e2';
    if ($_SERVER['HTTP_HOST'] == 'www.espagnevillas.net') $template_name = 'e2';
    if ($_SERVER['HTTP_HOST'] == 'www.spanischeimmobilien.com') $template_name = 'spanischeimmobilien';
    return "templates/{$template_name}/";
  }

  public static function load($template_file, $translation = null) {
  
    $template_contents = file_get_contents(self::templates_dir().$template_file);
    
    // El simbolo ### marca el inicio de la sección de comentarios del template
    @list($template, $comentarios) = @explode("###", $template_contents);

    if (is_array($translation))
    {
      $template = strtr($template, $translation);
    }

    return $template;
  }
  
}

?>
