<?php

require_once 'funciones.inc.php';

class Yandex_Translator {

  public static function do_translate($original_text, $new_language, $source_language = "") {
    $data = array(
      'lang'   => $new_language,
      'format' => 'plain',
      'text'   => $original_text,
    );

    $json_response = get_url("http://translate.yandex.net/api/v1/tr.json/translate", 'POST', $data);
    echo $json_response;
    
    $response = json_decode($json_response);
    if ($response->code != 200) { die("$response"); };

    return join("\n", $response->text);
  }

  public static function detect_language($original_text) {
    $data = array(
      'format' => 'plain',
      'text'   => $original_text,
    );

    $json_response = get_url("http://translate.yandex.net/api/v1/tr.json/detect", 'POST', $data);
    $response = json_decode($json_response);
    // print_r($google_response);
    if ($response->code != 200) { die("$response"); };
    return $response->lang;
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
echo "Lang: ".Yandex_Translator::detect_language($texto);

echo "\nTraduciendo al de\n";
echo Yandex_Translator::do_translate($texto, 'de');

*/