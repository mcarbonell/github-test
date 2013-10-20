<?php

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

require_once("funciones.inc.php");
require_once("class.html.php");
require_once("class.lang.php");
require_once("class.config.php");
require_once("class.sql.php");
require_once("class.template.php");
require_once("class.property.php");
require_once("class.visitor.php");
require_once("class.agency.php");
require_once 'inc.places.php';
require_once 'inc.translator.php';

// basic_autenticate("marc", "marc");

////////////////////////////////////////////////////////////////////////////////
/////// WEBSITE CLASS                     //////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class website {

  public $charset = 'utf-8';
  public $stylesheet = "";
  public $lang = "es";
  public $title = "{{HOME_TITLE}}";
  public $meta_description = "";
  public $meta_keywords = "";
  public $main_content = "";
  
  public $status_code = "200";
  public $cacheable = false;

  public $visitor = null;
  public $property = null;

  private $template_file = 'template.tpl.html';
  private $zone = 'front';
  
  //////////////////////////////////////////////////////////////////////////////
  function __construct($zone = 'front') {

    $this->zone = $zone;

    // $version_imprimir = ( isset($_GET['print']) && $_GET['print']== 1 );
    // $hoja_estilos = ($version_imprimir)?"print.css":"style.css";
    // $this->add_css_file($this->base_url."css/easy.css", "screen");
    // $this->add_css_file($this->base_url."css/easyprint.css", "print");
    // if ($zone == 'admin') set_lang('es'); else set_lang($_GET['lang']);
    // $this->set_lang(get_lang());

    if ($this->zone == 'admin') {
      $this->template_file = 'admin.tpl.html';
    }
    if ($this->zone == 'admin_agencias') {
      $this->template_file = 'admin_agencias.tpl.html';
    }
    
    switch ($_SERVER['HTTP_HOST']) {
        case 'www.espagneimmobilier.net':
        case 'www.eudomi.fr':
        case 'www.eudomi.net':
        case 'www.espagnevillas.net':
            $this->meta_description = "Propiétés bord de mer a vendre en Espagne. Costa Blanca, Costa Brava, Costa del Sol. Achat villas, maisons, fincas, appartements et terreins pas cher a vendre bord de mer";
            $this->meta_keywords    = "villa vendre espagne,espagne vente maison villa,immobilier espagne bord mer,espagne vue mer,achat villa espagne,vente villa espagne,maison vendre espagne,alicante,costa del sol,costa blanca,costa brava,majorque";
            break;
        case 'www.eudomi.com':
        case 'www.eudomi.co.uk':
        case 'www.eudomi.it':
        case 'www.eudomi.gr':
            $this->meta_description = "Seaview properties for sale in Spain. Costa Blanca, Costa Brava, Costa del Sol. Cheap bargains, villas, houses, fincas, apartments and seaside terreins for sale.";
            $this->meta_keywords    = "villa for sale spain,spain villa home sales,real estate spain seaside,spain sea view villa for sale,spain villa for sale,house for sale spain,alicante,costa del sol,costa blanca,costa brava,mallorca";
            break;
        case 'www.eudomi.nl':
        case 'www.eudomi.be':
            $this->meta_description = "Zeezicht Huizen te koop in Spanje. Costa Blanca, Costa Brava, Costa del Sol. Goedkope koopjes, villa's, huizen, finca's, appartementen en kust terreins te koop.";
            $this->meta_keywords    = "villa te koop spanje,spanje villa huizenverkopen,onroerend goed spanje kust,spanje zeezicht villa te koop,villa te koop spanje,huis te koop spanje,alicante,costa del sol,costa blanca,costa brava,mallorca";
            break;
        case 'www.eudomi.ru':
            $this->meta_description = "Вид на море недвижимость в Испании. Коста-Бланка, Коста Брава, Коста дель Соль. Дешевые сделок, виллы, дома, виллы, квартиры и приморские terreins для продажи.";
            $this->meta_keywords    = "вилла на продажу испания, испания вилла продажи дома, недвижимости испании моря, испания вид на море вилла на продажу, испания вилла на продажу, дома на продажу испания, аликанте, коста дель соль, коста бланка, коста брава, майорка";
            break;
        case 'www.eudomi.de':
        case 'www.eudomi.at':
        case 'www.eudomi.ch':
        case 'www.spanischeimmobilien.com':
        case 'www.immobilieninspanien.org':
            $this->meta_description = "Seaview Immobilien zum Verkauf in Spanien. Costa Blanca, Costa Brava, Costa del Sol. Günstige Schnäppchen, Villen, Häuser, Fincas, Ferienwohnungen und Meer terreins zum Verkauf.";
            $this->meta_keywords    = "villa zu verkaufen spanien,spanien villa hausverkäufe,immobilien spanien am meer,spanien villa mit meerblick zum verkauf,spanien villa zu verkaufen,haus zum verkauf spanien,alicante,costa del sol,costa blanca,costa brava,mallorca";
            break;
        default:
            $this->meta_description = "Propiétés bord de mer a vendre en Espagne. Costa Blanca, Costa Brava, Costa del Sol, Achat villas, maisons, fincas, appartements et terreins pas cher a vendre bord de mer";
            $this->meta_keywords    = "villa vendre espagne,espagne vente maison villa,immobilier espagne bord mer,espagne vue mer,achat villa espagne,vente villa espagne,maison vendre espagne,alicante,costa del sol,costa blanca,costa brava,majorque";
            break;
    }
  }
  
  public function __destruct(){

  }
  
  //////////////////////////////////////////////////////////////////////////////
  public function send_response_headers(){
    header("Status: ".$this->status_code);
    header("Content-Type: text/html; charset: ".$this->charset);
    if (!$this->cacheable) {
      header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
      header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Fecha en el pasado
    }
  }
  
  public function render($content_type = 'html') {
  
    $this->send_response_headers();
      
    if ($content_type == 'html') {
      echo $this->get_html();
    } else {
      die("Content Type ($content_type) no aceptado");
    }
  }
  //////////////////////////////////////////////////////////////////////////////
  
  public function add_content($content) {
    $this->main_content .= $content;
  }

  public function add_text_content($content) {
    $this->main_content .= nl2br($content);
  }
  
  
  
  public function set_title($new_title) {
    $this->title = $new_title;
  }
  
  

  public function get_html() {

      $translation = array(
        '{{HTML_CHARSET}}'          => $this->charset,
        '{{HTML_TITLE}}'            => $this->title,
        '{{HTML_META_DESCRIPTION}}' => $this->meta_description,
        '{{HTML_META_KEYWORDS}}'    => $this->meta_keywords,
        '{{HTML_HEAD_STYLESHEET}}'  => $this->stylesheet,
        '{{GOOGLE_ANALYTICS}}'      => $this->get_html_google_analytics(),

        '{{HTML_BODY_HEADER}}'      => $this->get_body_header(),
        '{{HTML_BODY_NAVIGATION}}'  => $this->get_body_navigation(),

        '{{HTML_BODY_MAIN}}'        => $this->get_body_main(),
        
        '{{HTML_BODY_FOOTER}}'      => $this->get_body_footer(),
        '{{HTML_BODY_INCLUDES}}'    => $this->get_body_includes(),
      );
      $html = template::load($this->template_file, $translation);
      if (($this->zone == 'admin') || ($this->zone == 'admin_agencias')) {
        $server_info = var_export(iconv_get_encoding('all'), true);
        $server_info .= print_r(myserverinfo(), true);
        // $html = str_replace("{{SERVER_INFO}}", nl2br($server_info), $html);
        $html = str_replace("{{VAR_USUARIO_NOMBRE}}", $_SERVER['PHP_AUTH_USER'], $html);
      }
      $html = str_replace('{{HTML_LANG}}', get_lang(), $html);
      $html = LANG::do_lang_translation($html);
      return $html;
  }

////////////////////////////////////////////////////////////////////////////////

  public function get_body_home() {
  
      $near_cities = "";
      $near_cities_arr = SQL::select("
        SELECT c.*
        FROM places as c
        WHERE c.izq <= c.der - 1
         AND c.place_id >= 2
        ORDER BY c.izq ASC
      ");
      foreach ($near_cities_arr as $near_city) {
        if ($near_city['num_properties'] > 20) {
          $near_cities .= "<a href='buscador.php?area=".$near_city['place_id']."' >".get_translation($near_city['kyero_xml'], get_lang(), 'es')." <span class='badge badge-warning'>".$near_city['num_properties']."</span></a> \n";
        }
      }

      $near_cities = "
      <div class='row'>
        <div class='span12'>
            <!-- h4 class=''>{{TODAS_POBLACIONES}}</h4 -->
            <hr />
        		<div class='nav'>
                $near_cities
        		</div>
        </div>
      </div>
      ";

      $translation = array(
        '{{HTML_BODY_SEARCH}}'        => $this->get_body_search(),
        '{{HTML_BODY_CARRUSEL}}'      => $this->get_body_carrusel(),

        '{{HTML_BODY_HOME_ROW}}'      => '', // $this->get_body_home_row(),
        '{{HTML_BODY_NEAR_CITIES}}'   => $near_cities,

        // '{{BODY_POPULAR_CITIES_MAP}}' => $this->get_popular_cities_map(),
        // '{{BODY_FEATURED_LISTING}}'   => $this->get_body_featured_listing(),
      );
      $html = template::load('body_home.tpl.html', $translation);
      return $this->main_content = $html;
  }
  
////////////////////////////////////////////////////////////////////////////////
  public function get_body_main(){
      return $this->main_content;
  }
  
  public function get_home_select_lang() {
    $active_lang = get_lang();
    $valid_langs_hosts = LANG::$valid_langs_hosts;
    $active_lang_text = "{{IDIOMA_".strtoupper($active_lang)."}}";
    
    $html_valid_langs = "";
    foreach ($valid_langs_hosts as $valid_lang => $valid_host)
    {
      if ($valid_lang == $active_lang) continue;
      if (empty($valid_lang)) continue;
      
      $valid_lang_text = "{{IDIOMA_".strtoupper($valid_lang)."}}";
      
      if (($_SERVER['HTTP_HOST'] == 'www.espagneimmobilier.net') ||
          ($_SERVER['HTTP_HOST'] == 'www.spanischeimmobilien.com')
          ) {
        $link_url = make_link_url($_SERVER["REDIRECT_URL"], $_SERVER["QUERY_STRING"], $valid_lang);
        $html_valid_langs .= "<li><a href='{$link_url}'><img src='css/images/{$valid_lang}.gif' alt='{$valid_lang_text}' /> {$valid_lang_text}</a></li>\n";
      } else {
        $link_url = "http://$valid_host";
        $html_valid_langs .= "<li><a href='{$link_url}' onclick=\"_gaq.push(['_link', '{$link_url}']); return false;\" ><img src='css/images/{$valid_lang}.gif' alt='{$valid_lang_text}' /> {$valid_lang_text}</a></li>\n";
      }
    }
    
    $html = "
              <div class='btn-group'>
                <button class='btn dropdown-toggle  pull-right ' data-toggle='dropdown' >
                  {$active_lang_text} <img src='css/images/{$active_lang}.gif' alt='{$active_lang_text}' />
                  <span class='caret'></span>
                </button>
                <ul class='dropdown-menu'>
                  $html_valid_langs
                </ul>
              </div>
    ";
    return $html;
    
  }
  
  public function get_body_header(){
      $template = template::load('body_header.tpl.html');
      $template = str_replace("{{HOME_SELECT_LANG}}", $this->get_home_select_lang(), $template);
      $template = str_replace("{{HOME_URL}}", make_link_url('/', '', get_lang()), $template);
      return $template;
  }
  
  public function get_body_navigation(){
      $template = template::load('body_navigation.tpl.html');
      return $template;
  }
  
  public function get_body_search(){
  
      $template = template::load('body_search.tpl.html');
      return $template;
  }
  
  public function get_body_carrusel(){
      $template = template::load('body_carrusel.tpl.html');
      return $template;
  }

  public function get_body_home_row(){
      $template = template::load('body_home_row.tpl.html');
      return $template;
  }
/*
  public function get_body_featured_listing(){

    $html = "
		<h3><span>Featured</span> listings</h3>
  		<table class='table table-bordered table-striped'>
  			<thead>
  				<tr>
  					<th>Description</th>
  					<th>Region</th>
  					<th>Price</th>
  					<th>Bedrooms</th>
  					<th>Buit</th>
  				</tr>
  			</thead>
  			<tbody>
      ";
      $property = new property(1);
      $fields = array('title', 'city', 'price', 'bedrooms', 'built');
      $html .= $property->get_property_table_row($fields);
      $html .= $property->get_property_table_row($fields);
      $html .= $property->get_property_table_row($fields);
      $html .= $property->get_property_table_row($fields);
      $html .= $property->get_property_table_row($fields);
      $html .= $property->get_property_table_row($fields);
      $html .= $property->get_property_table_row($fields);
      $html .= $property->get_property_table_row($fields);
      $html .= $property->get_property_table_row($fields);
      $html .= $property->get_property_table_row($fields);
      $html .= $property->get_property_table_row($fields);
      $html .= $property->get_property_table_row($fields);
      $html .= $property->get_property_table_row($fields);
      $html .= $property->get_property_table_row($fields);
      $html .= $property->get_property_table_row($fields);

      $html .= "
        </tbody>
        </table>
        More cities :
        <a href='map_properties.php'>London</a>,
        <a href='map_properties.php'>Scotland</a>,
        <a href='map_properties.php'>Wales</a>,
        <a href='map_properties.php'>Northern Ireland</a>,
        <a href='map_properties.php'>Birmingham</a>,
        <a href='map_properties.php'>Leeds</a>,
        <a href='map_properties.php'>Glasgow</a>,
        <a href='map_properties.php'>Sheffield</a>,
        <a href='map_properties.php'>Bradford</a>,
        <a href='map_properties.php'>Edinburgh</a>,
        <a href='map_properties.php'>Liverpool</a>,
        <a href='map_properties.php'>Manchester</a>
      ";
			return $html;
			// $template = template::load('body_featured_listing.tpl.html');
      // return $template;
  }
*/
  public function get_body_footer(){
      $template = template::load('body_footer.tpl.html');
      return $template;
  }
  
  public function get_body_includes(){
      $template = template::load('body_includes.tpl.html');
      return $template;
  }

  public function get_popular_cities_map() {
    return "
      		<h3><span>Popular</span> cities</h3>
      		<div id='home_map_canvas'></div>
    ";
  }

  public function get_html_google_analytics() {
    $hostname = $_SERVER["HTTP_HOST"];
    $hostname = str_replace("www.", "", $hostname);

    if ($hostname == 'espagneimmobilier.net') {
      return "
            <script type='text/javascript'>

              var _gaq = _gaq || [];
              _gaq.push(['_setAccount', 'UA-30349942-1']);
              _gaq.push(['_trackPageview']);

              (function() {
                var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
                ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
              })();
            </script>
            
            <!-- Yandex.Metrika counter -->
            <script type=\"text/javascript\">
            (function (d, w, c) {
                (w[c] = w[c] || []).push(function() {
                    try {
                        w.yaCounter22339312 = new Ya.Metrika({id:22339312,
                                webvisor:true,
                                clickmap:true,
                                trackLinks:true,
                                accurateTrackBounce:true});
                    } catch(e) { }
                });

                var n = d.getElementsByTagName(\"script\")[0],
                    s = d.createElement(\"script\"),
                    f = function () { n.parentNode.insertBefore(s, n); };
                s.type = \"text/javascript\";
                s.async = true;
                s.src = (d.location.protocol == \"https:\" ? \"https:\" : \"http:\") + \"//mc.yandex.ru/metrika/watch.js\";

                if (w.opera == \"[object Opera]\") {
                    d.addEventListener(\"DOMContentLoaded\", f, false);
                } else { f(); }
            })(document, window, \"yandex_metrika_callbacks\");
            </script>
            <noscript><div><img src=\"//mc.yandex.ru/watch/22339312\" style=\"position:absolute; left:-9999px;\" alt=\"\" /></div></noscript>
            <!-- /Yandex.Metrika counter -->
      ";
    }

    $analytics_code = "
        <script type='text/javascript'>

          var _gaq = _gaq || [];
          var pluginUrl = '//www.google-analytics.com/plugins/ga/inpage_linkid.js';
          _gaq.push(['_require', 'inpage_linkid', pluginUrl]);
          _gaq.push(['_setAccount', 'UA-36098117-1']);
          _gaq.push(['_setDomainName', '$hostname']);
          _gaq.push(['_setAllowLinker', true]);
          _gaq.push(['_trackPageview']);

          (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
          })();

        </script>
      ";

    if ($hostname == 'eudomi.ru') {
      $analytics_code .= "
      
<!-- Yandex.Metrika counter -->
<script type=\"text/javascript\">
(function (d, w, c) {
  (w[c] = w[c] || []).push(function() {
    try {
      w.yaCounter22349680 = new Ya.Metrika({id:22349680,
                                          clickmap:true,
                                          trackLinks:true,
                                          accurateTrackBounce:true});
    } catch(e) { }
  });

  var n = d.getElementsByTagName(\"script\")[0],
      s = d.createElement(\"script\"),
      f = function () {
            n.parentNode.insertBefore(s, n);
          };

      s.type = \"text/javascript\";
      s.async = true;
      s.src = (d.location.protocol == \"https:\" ? \"https:\" : \"http:\") + \"//mc.yandex.ru/metrika/watch.js\";
      if (w.opera == \"[object Opera]\") {
        d.addEventListener(\"DOMContentLoaded\", f, false);
      } else { f(); }
      })(document, window, \"yandex_metrika_callbacks\");
      
      </script><noscript>
      <div><img src=\"//mc.yandex.ru/watch/22349680\" style=\"position:absolute; left:-9999px;\" alt=\"\" /></div></noscript>
      <!-- /Yandex.Metrika counter -->
      ";
      /*
          <!-- Yandex.Metrika counter -->
          <script type='text/javascript'>
          (function (d, w, c) {
              (w[c] = w[c] || []).push(function() {
                  try {
                      w.yaCounter21973669 = new Ya.Metrika({id:21973669,
                              webvisor:true,
                              clickmap:true,
                              trackLinks:true,
                              accurateTrackBounce:true});
                  } catch(e) { }
              });

              var n = d.getElementsByTagName('script')[0],
                  s = d.createElement('script'),
                  f = function () { n.parentNode.insertBefore(s, n); };
              s.type = 'text/javascript';
              s.async = true;
              s.src = (d.location.protocol == 'https:' ? 'https:' : 'http:') + '//mc.yandex.ru/metrika/watch.js';

              if (w.opera == '[object Opera]') {
                  d.addEventListener('DOMContentLoaded', f, false);
              } else { f(); }
          })(document, window, 'yandex_metrika_callbacks');
          </script>
          <noscript><div><img src='//mc.yandex.ru/watch/21973669' style='position:absolute; left:-9999px;' alt='' /></div></noscript>
          <!-- /Yandex.Metrika counter -->

            ";
       */
     }
            
     return $analytics_code;
  }
}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////



$regiones = array(
  'Comunidad Valenciana' => array(
            280 => 'Valencia',
            256 => 'Alicante',
          ),
  'Baleares' => array(
            4 => 'Mallorca',
          ),
);


 /*
$tipos_propiedad = array(
  '' => '&raquo; {{TIPO_PROPIEDAD}}',
  1 => '{{TIPO_VILLAS}}',
  2 => '{{TIPO_APARTAMENTOS}}',
  3 => '{{TIPO_BUNGALOWS}}',
  4 => '{{TIPO_FINCAS}}',
  5 => '{{TIPO_PARCELAS}}',
  6 => '{{TIPO_NUEVAS_PROM}}',
);
*/
$tipos_propiedad_arr = SQL::select("Select * from property_types where num_properties > 0 ORDER BY num_properties DESC ");
$tipos_propiedad  = array('' => '&raquo; {{TIPO_PROPIEDAD}}');
$tipos_propiedad2 = array('' => '&raquo; {{TIPO_PROPIEDAD}}');
foreach ($tipos_propiedad_arr as $tipo_propiedad) {
  $tipos_propiedad[$tipo_propiedad['type_id']] = $tipo_propiedad['name'];
  $tipos_propiedad2[$tipo_propiedad['type_id']] = $tipo_propiedad['name']." (".$tipo_propiedad['num_properties'].")";
}



$precios = array(
  50000,
  100000,
  200000,
  250000,
  300000,
  400000,
  500000,
  600000,
  700000,
  800000,
  900000,
  1000000,
  2000000,
  3000000,
);



$numero_camas = array(
  '' => '&raquo; {{CAMAS}}',
  1 => '1',
  2 => '2',
  3 => '3',
  4 => '4',
  5 => '5',
  6 => '5+',
);

