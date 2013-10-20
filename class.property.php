<?php

require_once("class.sql.php");
require_once("class.template.php");
require_once("class.lang.php");
require_once("class.agency.php");
require_once 'inc.translator.php';

////////////////////////////////////////////////////////////////////////////////
/////// PROPERTY CLASS                    //////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class property {
//  public $id = 0;
  public $title = "";
//  public $price = "";
  public $city = "";
  public $pictures = null;
  public $description = "";
  public $features = null;
  public $basic_features = null;
//  public $built = 0;
//  public $terrain = 0;
//  public $bedrooms = 0;
//  public $bathrooms = 0;
//  public $pool = 0;
//  public $seaview = 0;
  
  public $id = 0;
  public $agency_id = 0;
  public $date = '';
  public $ref = 'UNKNOWN';
  public $price = 0;
  public $price_freq = '';
  public $type = 0;
  public $area = '';
  public $beds = 0;
  public $baths = 0;
  public $pool = 0;
  public $seaview = 0;
  public $plot = 0;
  public $built = 0;
  public $luxury = 0;
  public $reduced = 0;
  public $url = '';
  public $hits = 0;
  public $hits_new = 0;
  public $list_views = 0;
  public $leads = 0;
  public $hits_por_lead = 0;
  public $views_por_hit = 0;
  public $views_por_lead = 0;
  public $euros_built = 0;
  public $euros_plot = 0;
  public $lead_email = '';
  public $published = 0;
  public $num_fotos = 0;
  public $new = 0;
  public $last_update = '';
  
  // Objeto Agencia
  public $agency = null;
  
  // Descripcion
  public $description_lang = '';
  public $machine_translation = 0;


  public function __construct($property_id = 0){
    if (!empty($property_id))
    {
      $property_info = SQL::select_one("
        SELECT * FROM properties WHERE id = '{$property_id}'
      ");
      // print_r($property_info); die("");

      if (is_array($property_info))
      foreach ($property_info as $prop_key => $value) {
        $this->$prop_key = $value;
      }
      
      $active_lang = get_lang();

      $description_info = SQL::select("
        SELECT *
          FROM descriptions
         WHERE property_id = '{$property_id}'
           AND desc_type = 1
           AND lang = '$active_lang'
      ");
      // echo " active_lang $active_lang ";
      // print_r($description_info);

      if (!is_array($description_info) || strlen($description_info[0]['descrip'])<10 ) {
        $description_info = SQL::select("
          SELECT *
            FROM descriptions
           WHERE property_id = '{$property_id}'
             AND desc_type = 1
             AND lang = 'en'
        ");
      }
      // print_r($description_info);
      $this->description = $description_info[0]['descrip'];
      $this->description_lang = $description_info[0]['lang'];
      $this->machine_translation = $description_info[0]['machine_translation'];
      
      
      $photos_info = SQL::select("
        SELECT *
          FROM photos
         WHERE propiedad_id = '{$property_id}'
         ORDER BY views_por_hit ASC
      ");
      if (is_array($photos_info))
      foreach ($photos_info as $photo) {
        $this->pictures[] = $photo['foto_id'];
      }
      
      $this->features = explode("|", $this->features);
      $this->basic_features = array();
      if (!empty($this->beds)) $this->basic_features[] = "{$this->beds} {{CAMAS}}";
      if (!empty($this->baths)) $this->basic_features[] = "{$this->baths} {{BAÑOS}}";
      if (!empty($this->plot)) $this->basic_features[] = "{{TERRENO}} {$this->plot} m<sup>2</sup>";
      if (!empty($this->built)) $this->basic_features[] = "{{CONSTRUIDOS}} {$this->built} m<sup>2</sup>";
      if (!empty($this->euros_built)) $this->basic_features[] = number_format($this->euros_built, 0)." €/m<sup>2</sup> {{CONSTRUIDOS}}";
      if (!empty($this->euros_plot)) $this->basic_features[]  = number_format($this->euros_plot, 0)." €/m<sup>2</sup> {{TERRENO}}";
      

      $search = SQL::select(" SELECT kyero_xml FROM places WHERE place_id = '{$this->area}' ");
      $this->city = get_translation($search[0]['kyero_xml'], get_lang(), 'es');

    }
  }

  public function __destruct(){
  }
  
  
  public function agency() {
    if (!is_object($this->agency)) {
      $this->agency = new agency($this->agency_id);
    }
    
    return $this->agency;
  }
  
  

  public function get_formated_price() {
    return number_format($this->price, $decimals = 0, $dec_point = ',', $thousands_sep = '.').' €';
  }

  public function get_view_url() {
    $property_url = "property.php?id=".$this->id;
    return $property_url;
  }

  public function get_description($max_chars = null) {
    if (!is_null($max_chars)) {
      $desc_corta = mb_substr($this->description, 0, $max_chars, 'utf-8');
      $desc_corta = str_replace("'", "", $desc_corta);
      return $desc_corta.'...';
    }
    
    return $this->description;
  }

  public function get_description_html() {
      $lang_tag = ($this->machine_translation)?" lang='".$this->description_lang."-x-mtfrom-en' ":"";
      list($description, $desc_features) = explode("|", $this->description, 2);
      if (strlen($desc_features)>10) {
        $features = explode("|", $desc_features);
        $description .= "<ul>";
        foreach ($features as $feature) {
          $description .= "<li>$feature</li>\n";
        }
        $description .= "</ul>";
      }
      return "
      <h4>{{DESCRIPCION}}</h4>
      <span{$lang_tag}>{$description}</span>
      ";
  }

  public function get_features_html() {
      $html = "";

      $features = array_merge($this->basic_features, $this->features);
      foreach ($this->basic_features as $feature) {
        if (!empty($feature))
        $html .= "<li style='margin-left:10px; float:left; width: 195px;'>".ucwords($feature)."</li>";
      }
      
      foreach ($this->features as $feature) {
        if (stripos($feature, 'hits:') !== false ) continue;
        $translated_feature = get_translation($feature, get_lang(), 'en');
        if (!empty($translated_feature))
          $html .= "<li style='margin-left:10px; float:left; width: 195px;'>".ucwords($translated_feature)."</li>";
      }
/*
      $num_features = count($this->features);
      $num_columns = 4;
      $span_size = (int)(8 / $num_columns);
      $chunk_size = (int)(($num_features-1) / $num_columns) + 1;
      $features_ul = array_chunk($this->features, $chunk_size);


      if (!empty($features_ul))
      foreach ($features_ul as $feature_ul) {
        $html .= "<div class='span{$span_size}'><ul>\n";
        foreach ($feature_ul as $feature) {
          $translated_feature = get_translation($feature, get_lang(), 'en');
          $html .= "<li>{$translated_feature}</li>\n";
        }
        $html .= "</ul></div>\n";
      }
*/
      // num_features $num_features num_columns $num_columns chunk_size $chunk_size span_size $span_size
      $html = "
        <h4>{{CARACTERISTICAS}}</h4>
        <div class='row'>
          <ul>$html</ul>
				</div>
				<br />
    ";

    return $html;
  }
  
  public function get_pic_url($pic_id) {
    // if ( ($this->agency_id == 6) || ($this->agency_id == 7)) return "/fotos2/".$pic_id.".jpg";
    return "fotos/".$pic_id.".jpg?v0.1";
  }

  public function get_thumbnail_url($pic_id, $tam = 200) {
    // if ( ($this->agency_id == 6) || ($this->agency_id == 7)) return "/thumbs2/".$pic_id."_y".$tam.".jpg";
    return "thumbs/".$pic_id."_y".$tam.".jpg?v0.1";
  }
  
  public function get_gallery_html(){
      $gallery_html = "";
      foreach ($this->pictures as $picture) {
        $picture_url   = $this->get_pic_url($picture);
        $thumbnail_url = $this->get_thumbnail_url($picture);
        $gallery_html .= "
            <div class='showcase-slide'>
              <div class='showcase-content'>
                <img src='{$picture_url}' alt='{$picture}' />
              </div>
              <div class='showcase-thumbnail'>
                <img src='{$thumbnail_url}' alt='{$picture}' style='height: 100px;' />
                <div class='showcase-thumbnail-cover'></div>
              </div>
            </div>
        ";
      }

      $gallery_html = "
          <!-- Start slideshow-carousel -->
          <div id='showcase-loader'></div>
          <div id='showcase' class='showcase'>$gallery_html</div>
          <!-- // end of slideshow-carousel -->
    ";

    return $gallery_html;
  }

  public function get_other_properties(){
  /*

  */
    $similar_properties_ids1 = SQL::select("
      SELECT id FROM properties as p
        JOIN agencies as a ON a.agency_id = p.agency_id
       WHERE p.id <> {$this->id}
         AND p.published = 1
         AND a.published = 1
    ORDER BY ABS(p.price - {$this->price} - RAND(p.id) )
       LIMIT 2
    ");
    $similar_properties_ids2 = SQL::select("
      SELECT id FROM properties as p
        JOIN agencies as a ON a.agency_id = p.agency_id
       WHERE p.id <> {$this->id}
         AND p.published = 1
         AND a.published = 1
         AND p.area = '{$this->area}'
    ORDER BY RAND()
       LIMIT 2
    ");
    // var_dump($similar_properties_ids);
  
    $html = "
            <h4>{{OTRAS_PROPIEDADES_SIMILARES}}</h4>
      <ul class='thumbnails'>
    ";
    if (is_array($similar_properties_ids2))
    foreach ($similar_properties_ids2 as $other_property) {
      $property = new property($other_property['id']);
      $html .= $property->get_property_box();
    }
    if (is_array($similar_properties_ids1))
    foreach ($similar_properties_ids1 as $other_property) {
      $property = new property($other_property['id']);
      $html .= $property->get_property_box();
    }
    $html .= "</ul>";
    return $html;
  }

  public function get_enquiry_form() {
    if (($this->published == 0) || ($this->agency()->published == 0)) {
      return "<div id='contact_agent' class='center' style='top 0px;'>

			<h4>THIS PROPERTY IS RETIRED &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</h4>

		</div>";
    }
    
    $template = $this->get_by_template(template::load('form_property_enquiry.tpl.html'));
    
    $translation = array(
      '{{AGENCY_ID}}'             => $this->agency_id,
      '{{AGENCY_LOGO}}'           => $this->agency()->logo,
      '{{AGENCY_NAME}}'           => $this->agency()->name,
      '{{AGENCY_PREFIX}}'         => $this->agency()->prefix,
    );
    $template = strtr($template, $translation);
    return $template;
  }
  
  public function get_enquiries_list() {
    $enquiries_list = SQL::select ("SELECT e.date, e.consulta2 as message FROM enquiries as e WHERE e.property_id = '{$this->id}' ORDER BY e.date DESC LIMIT 5 ");
    if (!is_array($enquiries_list)) return "";
    $enquiries_str = get_table($enquiries_list, ' class=\'table table-striped table-bordered table-condensed\' ');
    return $enquiries_str;
    foreach ($enquiries_list as $enquire) {
    }
  }

  public function get_property_map() {
    // http://maps.googleapis.com/maps/api/geocode/json?address=Javea,Alicante,Comunidad%20Valenciana,%20Espa%C3%B1a&sensor=false

    $long_name = get_long_name($this->area);
    $nivel = substr_count($long_name, ",");
    $map_zoom = 5 + (2*$nivel);

    /*
    $json_geo_data = file_get_contents("http://maps.googleapis.com/maps/api/geocode/json?address=".urlencode($long_name)."&sensor=false");
    $geo_data = json_decode($json_geo_data);

    print_r($geo_data);
    echo "
      {$geo_data['results']['']}
    ";
    die("");
    
    return $this->get_by_template(template::load('div_property_map.tpl.html'));
    */
    
    $template = $this->get_by_template(template::load('div_property_map.tpl.html'));
    $translation = array(
      '{{MAP_LAT}}'               => $map_latitude,
      '{{MAP_LONG}}'              => $map_long,
      '{{MAP_ZOOM}}'              => $map_zoom,
      '{{PROPERTY_LONG_NAME}}'    => $long_name,
      '{{AGENCY_LOGO}}'           => $this->agency()->logo,
      '{{AGENCY_NAME}}'           => $this->agency()->name,
      '{{AGENCY_PREFIX}}'         => $this->agency()->prefix,
    );
    $template = strtr($template, $translation);
    return $template;
  }
  
  public function get_body_property() {
      $template = $this->get_by_template(template::load('body_property.tpl.html'));

      $breadcrumbs = get_breadcrumbs($this->area, true);
      $breadcrumbs .= "<li class='active'><span class='divider'>/</span> ".$this->agency()->prefix."-".$this->ref." </li>\n";
      $long_name = get_long_name($this->area);
  
      $translation = array(
        '{{BREADCRUMBS}}'           => $breadcrumbs,
        '{{PROPERTY_LONG_NAME}}'    => $long_name,
        '{{PROPERTY_PICTURES}}'     => $this->get_gallery_html(),
        '{{PROPERTY_DESCRIPTION}}'  => $this->get_description_html(),
        '{{PROPERTY_FEATURES}}'     => $this->get_features_html(),
        '{{PROPERTY_OTHERS}}'       => $this->get_other_properties(),
        '{{PROPERTY_MAP}}'          => $this->get_property_map(),
        '{{PROPERTY_ENQUIRY_FORM}}' => $this->get_enquiry_form(),
        '{{PROPERTY_ENQUIRIES}}'    => $this->get_enquiries_list(),
      );
      $template = strtr($template, $translation);

      return $template;
  }

////////////////////////////////////////////////////////////////////////////////


  public function get_property_listing() {
    SQL::update("
      UPDATE properties
      SET
        list_views = list_views + 1
      WHERE id = '{$this->id}'
    ");
    return $this->get_by_template(template::load('property_listing.tpl.html'));
  }

  public function get_property_box() {
    return $this->get_by_template(template::load('property_box.tpl.html'));
  }
  
  public function get_property_table_row($fields) {
  
    $html = "<tr>";
    foreach ($fields as $field) {
      $html .= "<td>".$this->$field."</td>";
    }
    $html .= "</tr>\n";
    return $html;
  }
  
  public function get_by_template($template) {
    global $tipos_propiedad;
    $translation = array(
      '{{PROPERTY_URL}}'         => $this->get_view_url(),
      '{{PROPERTY_ID}}'          => $this->id,
      '{{THUMBNAIL_IMG}}'        => $this->get_thumbnail_url($this->pictures[0]),
      '{{PROPERTY_PRICE}}'       => $this->get_formated_price(),
      '{{PROPERTY_BEDROOMS}}'    => $this->beds,
      '{{PROPERTY_BATHROOMS}}'   => $this->baths,
      '{{PROPERTY_TERRAIN}}'     => $this->plot,
      '{{PROPERTY_BUILT}}'       => $this->built,
      '{{PROPERTY_REF}}'         => $this->ref,
      '{{PROPERTY_POOL}}'        => ($this->pool == 3)?"{{TEXTO_NO}}":"{{TEXTO_SI}}",
      '{{PROPERTY_TITLE}}'       => $this->title,
      '{{PROPERTY_DESCRIPTION}}' => $this->get_description_html(),
      '{{PROPERTY_SINOPSIS}}'    => $this->get_description(250),
      '{{PROPERTY_CITY}}'        => $this->city,
      '{{PROPERTY_TYPE}}'        => $tipos_propiedad[$this->type],
      '{{AGENCY_ID}}'            => $this->agency_id,
      '{{AGENCY_LOGO}}'          => $this->agency()->logo,
      '{{AGENCY_NAME}}'          => $this->agency()->name,

    );
    $html = strtr($template, $translation);
    return $html;
  }
////////////////////////////////////////////////////////////////////////////////
}



