<?php

require_once 'funciones.inc.php';
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_TranslateService.php';

class Google_Translator {
  public static $client = null;
  public static $service = null;
  public static $developer_key = "AIzaSyDJmwh3BqQ842Qx4giqjxN1tJoDGSsxdU8";

/*
  public static function check_client() {
    if (is_object(Google_Translator::$client)) return;
    
    Google_Translator::$client = new Google_Client();
    Google_Translator::$client->setApplicationName('Google Translate PHP Starter Application');
    // Visit https://code.google.com/apis/console?api=translate to generate your
    // client id, client secret, and to register your redirect uri.
    Google_Translator::$client->setDeveloperKey(Google_Translator::$developer_key);
  }
  
  public static function check_service() {
    if (is_object(Google_Translator::$service)) return;
    
    Google_Translator::check_client();
    Google_Translator::$service = new Google_TranslateService(Google_Translator::$client);
  }

  public static function translate($original_text, $new_language) {
    Google_Translator::check_service();
    
    $translated_text = "";
    try {
      // echo "Durmiendo zzZZZZ\n";
      // sleep(2);
      $translations = Google_Translator::$service->translations->listTranslations($original_text, $new_language);
      $translated_text = $translations['translations'][0]['translatedText'];
      
      // echo "Texto traducido al $new_language: $translated_text\n";
      
    } catch(Exception $e) {
      echo 'Caught exception: ',  $e->getMessage(), "\n";
      die("  ERROR ");
    }
    return $translated_text;
  }
  

  public static function long_translate($original_text, $new_language) {

    if (strlen($original_text) > 200) {
      // $original_text_1 = substr($original_text, 0, strrpos($original_text, '.'));
      // $original_text_2 = strrchr($original_text, ".");
      $last_point = strrpos($original_text, '.');
      $last_point = 200;
      $original_text_1 = substr($original_text, 0, $last_point);
      $original_text_2 = substr($original_text, $last_point+1);
      $translated_text  = Google_Translator::translate($original_text_1, $new_language);
      $translated_text .= Google_Translator::long_translate($original_text_2, $new_language);
      return $translated_text;
    } else {
      return Google_Translator::translate($original_text, $new_language);
    }
  }
*/

  public static function do_translate($original_text, $new_language, $source_language = "") {
    $data = array(
      'key'    => Google_Translator::$developer_key,
      'target' => $new_language,
      'q'      => $original_text,
    );
    if (!empty($source_language)) $data['source'] = $source_language;
    
    $json_response = get_url("https://www.googleapis.com/language/translate/v2", 'POST', $data);
    $google_response = json_decode($json_response);

    return $google_response->data->translations[0]->translatedText;
  }

  public static function detect_language($original_text) {
    $data = array(
      'key'    => Google_Translator::$developer_key,
      'q'      => $original_text,
    );

    $json_response = get_url("https://www.googleapis.com/language/translate/v2/detect", 'POST', $data);
    $google_response = json_decode($json_response);
    // print_r($google_response);
    return $google_response->data->detections[0][0]->language;
  }

}

/*
$texto = "En un lugar de la Mancha, de cuyo lugar no quiero acordarme.";
$texto = "Fantastique villa moderne entièrement meublé avec des meubles design contemporain à travers. La maison offre une vue imprenable sur la mer de la baie de Palma et terrasses à tous les niveaux. ";
$texto = "Фантастических современная вилла полностью меблирована современной дизайнерской мебели во всем. В доме есть удивительный вид на море и залив Пальмы и террасы на каждом уровне.";
$texto = "Eine fantastische moderne Villa komplett mit modernen Designer-Möbeln im gesamten eingerichtet. Das Haus verfügt über erstaunliche Aussicht auf die Bucht von Palma und Terrassen auf jeder Ebene. ";
$texto = "A fantastic modern villa completely furnished with contemporary designer furniture throughout. The house has amazing sea views of the Palma bay and terraces on every level. The house has a lovely entrance hall that takes you to all different levels of the property. The living/dining and kitchen all have access to a beautiful terrace for dining in the evenings | open fire place | 3 bedrooms with bathrooms ensuite | a studio | large lounge off one of the bedrooms | all rooms have a direct access to outside terraces | private pool | close to Palma but in a lovely position";

header("Content-type: text/plain; charset: utf-8;");
echo "Texto: $texto\n";
echo "Lang: ".Google_Translator::detect_language($texto);

echo "\nTraduciendo al de\n";
echo Google_Translator::do_translate($texto, 'de');
*/