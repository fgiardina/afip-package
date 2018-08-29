<?php

namespace fernandogiardina\afip\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AfipController extends Controller
{
    protected $env;
    protected $key_path_cer;
    protected $key_path_key;
    protected $key_url_cms;
    protected $key_passphrase;
    protected $key_url_wsa4;
    protected $key_cuit_representada;

    public function __construct()
    {
        $this->env = config('afip.environment');
        $this->key_path_cer = config('afip.path_cer_'.$this->env);
        $this->key_path_key = config('afip.path_key_'.$this->env);
        $this->key_url_cms = config('afip.url_cms_'.$this->env);
        $this->key_passphrase = config('afip.passphrase_'.$this->env);
        $this->key_url_wsa4 = config('afip.url_wsa4_'.$this->env);
        $this->key_cuit_representada = config('afip.cuit_representada_'.$this->env);

        return $this;
    }

    // http://127.0.0.1:8000/afip/token/ws_sr_padron_a4
    public function getToken($service) 
    {
        $this->CreateTRA($service);
        $cms = $this->SignTRA();
        $ta = $this->CallWSAA($cms);
        
        return "end";
    }

    private function CreateTRA($service) 
    {
        $TRA = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><loginTicketRequest version="1.0"></loginTicketRequest>');
        $TRA->addChild('header');
        $TRA->header->addChild('uniqueId',date('U'));
        $TRA->header->addChild('generationTime',date('c',date('U')-60));
        $TRA->header->addChild('expirationTime',date('c',date('U')+60));
        $TRA->addChild('service',$service);
        $TRA->asXML('TRA.xml');
    }

    private function SignTRA()
    {
        $STATUS=openssl_pkcs7_sign("TRA.xml", "TRA.tmp", "file://".base_path().$this->key_path_cer,
            array("file://".base_path().$this->key_path_key, $this->key_passphrase),
            array(),
            !PKCS7_DETACHED
            );

        if (!$STATUS) {exit("ERROR generating PKCS#7 signature\n");}

        $inf=fopen("TRA.tmp", "r");
        $i=0;
        $CMS="";

        while (!feof($inf)) { 
            $buffer=fgets($inf);
            if ( $i++ >= 4 ) {$CMS.=$buffer;}
        }
        fclose($inf);

        unlink("TRA.xml");
        unlink("TRA.tmp");
        return $CMS;
    }

    private function CallWSAA($CMS) 
    {
        $client=new \SoapClient($this->key_url_cms."?WSDL", array(
                'soap_version'   => SOAP_1_2,
                'location'       => $this->key_url_cms,
                'trace'          => 1,
                'exceptions'     => 0
                )); 
        $results=$client->loginCms(array('in0'=>$CMS));
        // file_put_contents(base_path()."/cert/request-loginCms".date('U').".xml",$client->__getLastRequest());
        // file_put_contents(base_path()."/cert/response-loginCms.".date('U').".xml",$client->__getLastResponse());
        if (is_soap_fault($results)) {
            return $results;
        } else {
            file_put_contents(base_path()."/cert/last-loginCms.".$this->env.".".$this->key_cuit_representada.".xml",$results->loginCmsReturn);
        }

        return $results->loginCmsReturn;
    }

    // http://127.0.0.1:8000/afip/wsa4/20000000516
    // http://www.afip.gob.ar/ws/ws_sr_padron_a4/datos-prueba-padron-a4.txt
    public function CallWSA4($CUIT) 
    {
        $token = '';
        $sign = '';

        if (file_exists(base_path()."/cert/last-loginCms.".$this->env.".".$this->key_cuit_representada.".xml")) {
            $xml = simplexml_load_file(base_path()."/cert/last-loginCms.".$this->env.".".$this->key_cuit_representada.".xml");         
            foreach($xml as $item) {
                $token = $item->token;
                $sign = $item->sign;
            }
        }

        $client = new \SoapClient(base_path()."/cert/personaServiceA4.wsdl", array(
              'soap_version'   => SOAP_1_1,
              'location'       => $this->key_url_wsa4,
              'trace'          => 1,
              'exceptions'     => 0
        ));

        $params = array(
            'token'             =>  $token, 
            'sign'              =>  $sign,
            'cuitRepresentada'  =>  $this->key_cuit_representada,
            'idPersona'         =>  $CUIT);

        $results=$client->getPersona($params);
        // file_put_contents(base_path()."/cert/request-a4-".date('U').".xml",$client->__getLastRequest());
        // file_put_contents(base_path()."/cert/response-a4-".date('U').".xml",$client->__getLastResponse());
        if (is_soap_fault($results)) {
            return json_encode(['error'=>$results->faultcode,'message'=>$results->faultstring]);
        }

        $result = $results->personaReturn;

        $domicilio = isset($result->persona->domicilio) ? $result->persona->domicilio : "";
        $razon_social = isset($result->persona->razonSocial) ? $result->persona->razonSocial : "";
        $actividad = isset($result->persona->actividad->descripcionActividad) ? $result->persona->actividad->descripcionActividad : "";
        $tipo_persona = isset($result->persona->tipoPersona) ? $result->persona->tipoPersona : "";
        $forma_juridica = isset($result->persona->formaJuridica) ? $result->persona->formaJuridica : "";
        $cuit = isset($result->persona->idPersona) ? $result->persona->idPersona : "";

        $persona = [
            'domicilio' => $domicilio,
            'razon_social' => $razon_social,
            'actividad' => $actividad,
            'tipo_persona' => $tipo_persona,
            'forma_juridica' => $forma_juridica,
            'cuit' => $cuit
        ];
        
        return json_encode($persona);
    }

}
