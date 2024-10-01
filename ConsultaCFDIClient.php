<?php

/*
 * ConsultaCFDIClient
 * Detifac®
 * © 2018, Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since nov 2018
 */

/**
 * Description of ConsultaCFDIClient
 *
 * @author Rolando Esquivel
 */
class ConsultaCFDIClient {

    private static function getHeaders($post) {
        return array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: http://tempuri.org/IConsultaCFDIService/Consulta",
            "Content-length: " . strlen($post),
        );
    }

    private static function getSoap($rfcEmisor, $rfcReceptor, $total, $uuid) {
        $expresionImpresa = '?re=' . $rfcEmisor . '&rr=' . $rfcReceptor . '&tt=' . $total . '&id=' . $uuid;
        return '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tem="http://tempuri.org/">
                               <soapenv:Header/>
                               <soapenv:Body>
                                  <tem:Consulta>
                                      <tem:expresionImpresa><![CDATA[' . $expresionImpresa . ']]></tem:expresionImpresa>
                                  </tem:Consulta>
                               </soapenv:Body>
                            </soapenv:Envelope>';
    }

    public static function xml2array($xml) {
        return json_decode(json_encode(simplexml_load_string(str_replace("s:", "", str_replace("a:", "", str_replace("i:", "", '<?xml version="1.0" encoding="utf-8"?>' . $xml))))), TRUE);
    }

    static function CallAPI($rfcEmisor, $rfcReceptor, $total, $uuid, $url = 'https://consultaqr.facturaelectronica.sat.gob.mx/ConsultaCFDIService.svc?wsdl') {

        try {
            $obj = array();
            if (function_exists("curl_init")) {
                $curl = curl_init();
                $post = ConsultaCFDIClient::getSoap($rfcEmisor, $rfcReceptor, $total, $uuid);
                $headers = ConsultaCFDIClient::getHeaders($post);

                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                curl_setopt($curl, CURLOPT_TIMEOUT, 720);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

                $soap = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);
                if ($err) {
                    $obj['Error'] = $err;
                } else {
                    $soap = ConsultaCFDIClient::xml2array($soap);
                    $ConsultaResponse = "ConsultaResponse";
                    $ConsultaResult = "ConsultaResult";
                    $codigo = "CodigoEstatus";
                    $body = "Body";
                    $obj[$codigo] = $soap[$body][$ConsultaResponse][$ConsultaResult][$codigo];
                    $obj['Estado'] = $soap[$body][$ConsultaResponse][$ConsultaResult]["Estado"];
                    $obj["EsCancelable"] = $soap[$body][$ConsultaResponse][$ConsultaResult]["EsCancelable"];
                    $obj["EstatusCancelacion"] = $soap[$body][$ConsultaResponse][$ConsultaResult]["EstatusCancelacion"];
                }
            } else {
                $obj['Error'] = "CURL no está instalado, favor de informar a Soporte";
            }
        } catch (Exception $e) {
            error_log($e);
        }
        return $obj;
    }

}
