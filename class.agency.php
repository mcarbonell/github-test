<?php

require_once("class.sql.php");
require_once("class.template.php");
require_once("class.lang.php");
require_once("class.xml_importer.php");


////////////////////////////////////////////////////////////////////////////////
/////// PROPERTY CLASS                    //////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class agency {

  public $agency_id = 0;
  public $prefix = '';
  public $name = 'UNKNOWN';
  public $logo = '';
  public $website = '';
  public $contact_email = '';
  public $contact_person = '';
  public $lead_email = '';
  public $xml_url = '';
  public $xml_format = '';
  public $telephone = '';
  public $fax = '';
  public $city = '';
  public $province = '';
  public $address = '';
  public $postal_code = '';
  public $description = '';
  public $num_properties = 0;
  public $languages = '';
  public $place_id = 0;
  public $notes = '';
  public $published = '';
  
  public $city_place = '';

  public function __construct($agency_id = 0){
    if (!empty($agency_id))
    {
      $search = SQL::select_one_nocache("
        SELECT * FROM agencies WHERE agency_id = '{$agency_id}'
      ");
      // print_r($property_info); die("");

      if (is_array($search))
      foreach ($search as $key => $value) {
        $this->$key = $value;
      }

      $search = SQL::select(" SELECT kyero_xml FROM places WHERE place_id = '{$this->place_id}' ");
      $this->city_place = $search[0]['kyero_xml'];
    }
  }

  public function __destruct(){
  }

  public function get_view_url() {
    $url = "agency.php?agency_id=".$this->agency_id;
    return $url;
  }

  public function get_description($max_chars = null) {
    if (!is_null($max_chars))
      return substr($this->description, 0, $max_chars).'...';
    else
      return $this->description;
  }

  public function get_description_html() {
      return "
      <h4>{{DESCRIPCION}}</h4>
      {$this->description}
      ";
  }

  public function get_logo_url() {
    return $this->logo;
  }

  public function get_contact_form() {
  /*
    $template = $this->get_by_template(template::load('form_agency_contact.tpl.html'));
    $translation = array(
      '{{BREADCRUMBS}}'           => $breadcrumbs,

      '{{AGENCY_ID}}'             => $this->agency_id,
      '{{AGENCY_PREFIX}}'         => $this->prefix,
      '{{AGENCY_LOGO}}'           => $this->logo,
      '{{AGENCY_NAME}}'           => $this->name,
      '{{AGENCY_CITY}}'           => $this->city,
      '{{AGENCY_ADDRESS}}'        => $this->address,
      '{{AGENCY_POSTAL_CODE}}'    => $this->postal_code,
      '{{AGENCY_DESCRIPTION}}'    => $this->get_description_html(),
      '{{AGENCY_NUM_PROPERTIES}}' => $this->num_properties,
      '{{AGENCY_WEBSITE}}'        => $this->website,
      '{{AGENCY_EMAIL}}'          => $this->email,
      '{{AGENCY_CONTACT_PERSON}}' => $this->contact_person,
      '{{AGENCY_TELEPHONE}}'      => $this->telephone,
      '{{AGENCY_FAX}}'            => $this->fax,
      '{{AGENCY_NOTES}}'          => $this->notes,
      '{{AGENCY_CONTACT_FORM}}'   => $this->get_contact_form(),
    );
    $html = template::load('form_agency_contact.tpl.html', $translation);
    return $template;
    */
    return $this->get_by_template(template::load('form_agency_contact.tpl.html'));

  }

  public function get_body_agency() {
      return $this->get_by_template(template::load('body_agency.tpl.html'));
      
      $breadcrumbs = get_breadcrumbs($this->place_id, true);
      $breadcrumbs .= "<li class='active'><span class='divider'>/</span>  ".$this->name." </li>\n";
      $translation = array(
        '{{BREADCRUMBS}}'           => $breadcrumbs,
      );
      $html = strtr($template, $translation);
      return $html;
  }

////////////////////////////////////////////////////////////////////////////////
  public function get_property_listing() {
    return $this->get_by_template(template::load('agency_listing.tpl.html'));
  }

  public function get_property_box() {
    return $this->get_by_template(template::load('agency_box.tpl.html'));
  }

  public function get_as_table_row($fields) {

    $html = "<tr>";
    foreach ($fields as $field) {
      $html .= "<td>".$this->$field."</td>";
    }
    $html .= "</tr>\n";
    return $html;
  }

  public function get_by_template($template) {
    $translation = array(
      '{{AGENCY_ID}}'             => $this->agency_id,
      '{{AGENCY_PREFIX}}'         => $this->prefix,
      '{{AGENCY_LOGO}}'           => $this->logo,
      '{{AGENCY_NAME}}'           => $this->name,
      '{{AGENCY_CITY}}'           => $this->city,
      '{{AGENCY_ADDRESS}}'        => $this->address,
      '{{AGENCY_POSTAL_CODE}}'    => $this->postal_code,
      '{{AGENCY_DESCRIPTION}}'    => $this->get_description_html(),
      '{{AGENCY_NUM_PROPERTIES}}' => $this->num_properties,
      '{{AGENCY_WEBSITE}}'        => $this->website,
      '{{AGENCY_EMAIL}}'          => $this->email,
      '{{AGENCY_CONTACT_PERSON}}' => $this->contact_person,
      '{{AGENCY_TELEPHONE}}'      => $this->telephone,
      '{{AGENCY_FAX}}'            => $this->fax,
      '{{AGENCY_NOTES}}'          => $this->notes,
      '{{AGENCY_CONTACT_FORM}}'   => $this->get_contact_form(),
    );
    $html = strtr($template, $translation);
    return $html;
  }
  
  public function import_xml() {
    $importer = new xml_importer($this->agency_id);
    $importer->pre_import();
    $importer->do_import();
    $importer->post_import();

  }
  
  
////////////////////////////////////////////////////////////////////////////////
}
;