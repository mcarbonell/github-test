<?php

require_once 'funciones.inc.php';

class AccessTokenAuthentication {
    /*
     * Get the access token.
     *
     * @param string $grantType    Grant type.
     * @param string $scopeUrl     Application Scope URL.
     * @param string $clientID     Application client ID.
     * @param string $clientSecret Application client ID.
     * @param string $authUrl      Oauth Url.
     *
     * @return string.
     */
    function getTokens($grantType, $scopeUrl, $clientID, $clientSecret, $authUrl){
        try {
            //Initialize the Curl Session.
            $ch = curl_init();
            //Create the request Array.
            $paramArr = array (
             'grant_type'    => $grantType,
             'scope'         => $scopeUrl,
             'client_id'     => $clientID,
             'client_secret' => $clientSecret
            );
            //Create an Http Query.//
            $paramArr = http_build_query($paramArr);
            //Set the Curl URL.
            curl_setopt($ch, CURLOPT_URL, $authUrl);
            //Set HTTP POST Request.
            curl_setopt($ch, CURLOPT_POST, TRUE);
            //Set data to POST in HTTP "POST" Operation.
            curl_setopt($ch, CURLOPT_POSTFIELDS, $paramArr);
            //CURLOPT_RETURNTRANSFER- TRUE to return the transfer as a string of the return value of curl_exec().
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
            //CURLOPT_SSL_VERIFYPEER- Set FALSE to stop cURL from verifying the peer's certificate.
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            //Execute the  cURL session.
            $strResponse = curl_exec($ch);
            //Get the Error Code returned by Curl.
            $curlErrno = curl_errno($ch);
            if($curlErrno){
                $curlError = curl_error($ch);
                throw new Exception($curlError);
            }
            //Close the Curl Session.
            curl_close($ch);
            //Decode the returned JSON string.
            $objResponse = json_decode($strResponse);

            if ($objResponse->error){
                throw new Exception($objResponse->error_description);
            }
            return $objResponse->access_token;
        } catch (Exception $e) {
            echo "Exception-".$e->getMessage();
        }
    }
}

/*
 * Class:AccessTokenAuthentication
 *
 * Create SOAP Object.
 */
class SOAPMicrosoftTranslator {
    /*
     * Soap Object.
     *
     * @var ObjectArray.
     */
    public $objSoap;
    /*
     * Create the SAOP object.
     *
     * @param string $accessToken Access Token string.
     * @param string $wsdlUrl     WSDL string.
     *
     * @return string.
     */
    public function __construct($accessToken, $wsdlUrl){
        try {
            //Authorization header string.
            $authHeader = "Authorization: Bearer ". $accessToken;
            $contextArr = array(
                'http'   => array(
                    'header' => $authHeader
            )
            );
            //Create a streams context.
            $objContext = stream_context_create($contextArr);
            $optionsArr = array (
                'soap_version'   => 'SOAP_1_2',
                'encoding'       => 'UTF-8',
                'exceptions'     => true,
                'trace'          => true,
                'cache_wsdl'     => 'WSDL_CACHE_NONE',
                'stream_context' => $objContext,
                'user_agent'     => 'PHP-SOAP/'.PHP_VERSION."\r\n".$authHeader
            );
            //Call Soap Client.
            $this->objSoap = new SoapClient($wsdlUrl, $optionsArr);
        } catch(Exception $e){
            echo "<h2>Exception Error!</h2>";
            echo $e->getMessage();
        }
    }
}


class Msn_Translator {

    //Soap WSDL Url
  public static $wsdlUrl       = "http://api.microsofttranslator.com/V2/Soap.svc";
    //Client ID of the application.
  public static $clientID       = "722e573b-f05b-4588-883c-75f85f37cd63";
    //Client Secret key of the application.
  public static $clientSecret = "e+8EkAeU42FxIhZYqwJZOkB3/qtBkQtfeIhg2D312fc=";
    //OAuth Url.
  public static $authUrl      = "https://datamarket.accesscontrol.windows.net/v2/OAuth2-13/";
    //Application Scope Url
  public static $scopeUrl     = "http://api.microsofttranslator.com";
    //Application grant type
  public static $grantType    = "client_credentials";
  
  

  public static function do_translate($original_text, $new_language, $source_language = "en") {
    try {
        //Create the Authentication object
        $authObj      = new AccessTokenAuthentication();
        //Get the Access token
        $accessToken  = $authObj->getTokens(Msn_Translator::$grantType, Msn_Translator::$scopeUrl, Msn_Translator::$clientID, Msn_Translator::$clientSecret, Msn_Translator::$authUrl);
        //Create soap translator Object
        $soapTranslator = new SOAPMicrosoftTranslator($accessToken, Msn_Translator::$wsdlUrl);

        //Set the params.//
        //Request argument list.
        $requestArg = array (
             'text'        => $original_text,
             'from'        => $source_language,
             'to'          => $new_language,
             'contentType' => 'text/plain',
             'category'    => 'general'
        );
        $responseObj = $soapTranslator->objSoap->Translate($requestArg);
        
        return $responseObj->TranslateResult;
        
    } catch (Exception $e) {
        echo "Exception: " . $e->getMessage() . "<br/>";
    }
    return;
  }

  public static function detect_language($original_text) {
    try {
        //Create the Authentication object
        $authObj      = new AccessTokenAuthentication();
        //Get the Access token
        $accessToken  = $authObj->getTokens(Msn_Translator::$grantType, Msn_Translator::$scopeUrl, Msn_Translator::$clientID, Msn_Translator::$clientSecret, Msn_Translator::$authUrl);
        //Create soap translator Object
        $soapTranslator = new SOAPMicrosoftTranslator($accessToken, Msn_Translator::$wsdlUrl);
        
        //Set the params.//
        //Request argument list.
        $requestArg = array (
             'text'  => $original_text,
        );
        // $languageCode = array();
        $responseObj = $soapTranslator->objSoap->Detect($requestArg);
        return $responseObj->DetectResult;
        // $languageCode[] = $responseObj->DetectResult;
        // return $languageCode[0];
    } catch (Exception $e) {
        echo "Exception: " . $e->getMessage() . "<br/>";
    }
    return;
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
echo "Lang: ".Msn_Translator::detect_language($texto);

echo "\nTraduciendo al de\n";
echo Msn_Translator::do_translate($texto, 'de');
*/