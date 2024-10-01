<?php

#Librerias
include_once ('data/CombustiblesDAO.php');
include_once ('data/VariablesDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "combustibles.php?";

$objectDAO = new CombustiblesDAO();
$ciaDAO = new CiaDAO();

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $Clave_admin = VariablesDAO::getVariable("clave_admin");
    $objectVO = new CombustiblesVO();

    $objectVO->setId($sanitize->sanitizeInt("busca"));
    if (is_numeric($objectVO->getId())) {
        $objectVO = $objectDAO->retrieve($objectVO->getId(), "id", false);
        $Return = "combustiblese.php?busca=" . $objectVO->getId();
    }
    //error_log(print_r($objectVO, TRUE));
    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {

            $objectVO->setCve_producto_sat($sanitize->sanitizeString("Cve_producto_sat"));
            if ($sanitize->sanitizeString("Cve_sub_producto_sat") == "") {
                $objectVO->setCve_sub_producto_sat("---");
            } else {
                $objectVO->setCve_sub_producto_sat($sanitize->sanitizeString("Cve_sub_producto_sat"));
            }


            $objectVO->setComp_fosil(0);
            $objectVO->setComOctanajeGas(0);
            $objectVO->setComp_azufre(0);
            $objectVO->setGravedad_especifica(0);
            $objectVO->setFraccion_molar(0);
            $objectVO->setPoder_calorifico(0);
            $objectVO->setComp_propano(0);
            $objectVO->setComp_butano(0);
            $objectVO->setGasConEtanol("No");
            $objectVO->setComDeEtanolEnGasolina(0);
            $objectVO->setDensidad(0);
            $objectVO->setColor($request->getAttribute("Color"));
            if ($objectVO->getCve_producto_sat() === "PR03") {
                $objectVO->setComp_fosil($sanitize->sanitizeFloat("Comp_fosil"));
            } elseif ($objectVO->getCve_producto_sat() === "PR07") {
                $objectVO->setComp_fosil($sanitize->sanitizeFloat("Comp_fosil"));
                $objectVO->setComOctanajeGas($sanitize->sanitizeFloat("ComOctanajeGas"));
            } elseif ($objectVO->getCve_producto_sat() === "PR08") {
                $objectVO->setComp_azufre($sanitize->sanitizeFloat("Comp_azufre"));
                $objectVO->setGravedad_especifica($sanitize->sanitizeFloat("Gravedad_especifica"));
                $objectVO->setDensidad($sanitize->sanitizeString("Densidad"));
            } elseif ($objectVO->getCve_producto_sat() === "PR09") {
                $objectVO->setFraccion_molar($sanitize->sanitizeFloat("Fraccion_molar"));
                $objectVO->setPoder_calorifico($sanitize->sanitizeFloat("Poder_calorifico"));
            } elseif ($objectVO->getCve_producto_sat() === "PR11") {
                $objectVO->setComp_fosil($sanitize->sanitizeFloat("Comp_fosil"));
            } elseif ($objectVO->getCve_producto_sat() === "PR12") {
                $objectVO->setComp_propano($sanitize->sanitizeFloat("Comp_propano"));
                $objectVO->setComp_butano($sanitize->sanitizeFloat("Comp_butano"));
            }
            if ($sanitize->sanitizeString("GasConEtanol") === "Si") {
                $objectVO->setGasConEtanol("Si");
                $objectVO->setComDeEtanolEnGasolina($sanitize->sanitizeInt("ComDeEtanolEnGasolina"));
            }

            //error_log(print_r($objectVO, TRUE));
            if ($objectDAO->update($objectVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en combustibles: " . $ex);
    } finally {
        header("Location: $Return");
    }
}


if ($request->hasAttribute("op")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $cId = $sanitize->sanitizeInt("busca");

    try {
        if ($request->getAttribute("op") === utils\Messages::OP_DELETE) {
            
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en combustibles: " . $ex);
    } finally {
        header("Location: $Return");
    }
}

$colors = array(
    'aliceblue' => 'F0F8FF',
    'antiquewhite' => 'FAEBD7',
    'aqua' => '00FFFF',
    'aquamarine' => '7FFFD4',
    'azure' => 'F0FFFF',
    'beige' => 'F5F5DC',
    'bisque' => 'FFE4C4',
    'black' => '000000',
    'blanchedalmond ' => 'FFEBCD',
    'blue' => '0000FF',
    'blueviolet' => '8A2BE2',
    'brown' => 'A52A2A',
    'burlywood' => 'DEB887',
    'cadetblue' => '5F9EA0',
    'chartreuse' => '7FFF00',
    'chocolate' => 'D2691E',
    'coral' => 'FF7F50',
    'cornflowerblue' => '6495ED',
    'cornsilk' => 'FFF8DC',
    'crimson' => 'DC143C',
    'cyan' => '00FFFF',
    'darkblue' => '00008B',
    'darkcyan' => '008B8B',
    'darkgoldenrod' => 'B8860B',
    'darkgray' => 'A9A9A9',
    'darkgreen' => '006400',
    'darkgrey' => 'A9A9A9',
    'darkkhaki' => 'BDB76B',
    'darkmagenta' => '8B008B',
    'darkolivegreen' => '556B2F',
    'darkorange' => 'FF8C00',
    'darkorchid' => '9932CC',
    'darkred' => '8B0000',
    'darksalmon' => 'E9967A',
    'darkseagreen' => '8FBC8F',
    'darkslateblue' => '483D8B',
    'darkslategray' => '2F4F4F',
    'darkslategrey' => '2F4F4F',
    'darkturquoise' => '00CED1',
    'darkviolet' => '9400D3',
    'deeppink' => 'FF1493',
    'deepskyblue' => '00BFFF',
    'dimgray' => '696969',
    'dimgrey' => '696969',
    'dodgerblue' => '1E90FF',
    'firebrick' => 'B22222',
    'floralwhite' => 'FFFAF0',
    'forestgreen' => '228B22',
    'fuchsia' => 'FF00FF',
    'gainsboro' => 'DCDCDC',
    'ghostwhite' => 'F8F8FF',
    'gold' => 'FFD700',
    'goldenrod' => 'DAA520',
    'gray' => '808080',
    'green' => '008000',
    'greenyellow' => 'ADFF2F',
    'grey' => '808080',
    'honeydew' => 'F0FFF0',
    'hotpink' => 'FF69B4',
    'indianred' => 'CD5C5C',
    'indigo' => '4B0082',
    'ivory' => 'FFFFF0',
    'khaki' => 'F0E68C',
    'lavender' => 'E6E6FA',
    'lavenderblush' => 'FFF0F5',
    'lawngreen' => '7CFC00',
    'lemonchiffon' => 'FFFACD',
    'lightblue' => 'ADD8E6',
    'lightcoral' => 'F08080',
    'lightcyan' => 'E0FFFF',
    'lightgoldenrodyellow' => 'FAFAD2',
    'lightgray' => 'D3D3D3',
    'lightgreen' => '90EE90',
    'lightgrey' => 'D3D3D3',
    'lightpink' => 'FFB6C1',
    'lightsalmon' => 'FFA07A',
    'lightseagreen' => '20B2AA',
    'lightskyblue' => '87CEFA',
    'lightslategray' => '778899',
    'lightslategrey' => '778899',
    'lightsteelblue' => 'B0C4DE',
    'lightyellow' => 'FFFFE0',
    'lime' => '00FF00',
    'limegreen' => '32CD32',
    'linen' => 'FAF0E6',
    'magenta' => 'FF00FF',
    'maroon' => '800000',
    'mediumaquamarine' => '66CDAA',
    'mediumblue' => '0000CD',
    'mediumorchid' => 'BA55D3',
    'mediumpurple' => '9370D0',
    'mediumseagreen' => '3CB371',
    'mediumslateblue' => '7B68EE',
    'mediumspringgreen' => '00FA9A',
    'mediumturquoise' => '48D1CC',
    'mediumvioletred' => 'C71585',
    'midnightblue' => '191970',
    'mintcream' => 'F5FFFA',
    'mistyrose' => 'FFE4E1',
    'moccasin' => 'FFE4B5',
    'navajowhite' => 'FFDEAD',
    'navy' => '000080',
    'oldlace' => 'FDF5E6',
    'olive' => '808000',
    'olivedrab' => '6B8E23',
    'orange' => 'FFA500',
    'orangered' => 'FF4500',
    'orchid' => 'DA70D6',
    'palegoldenrod' => 'EEE8AA',
    'palegreen' => '98FB98',
    'paleturquoise' => 'AFEEEE',
    'palevioletred' => 'DB7093',
    'papayawhip' => 'FFEFD5',
    'peachpuff' => 'FFDAB9',
    'peru' => 'CD853F',
    'pink' => 'FFC0CB',
    'plum' => 'DDA0DD',
    'powderblue' => 'B0E0E6',
    'purple' => '800080',
    'red' => 'FF0000',
    'rosybrown' => 'BC8F8F',
    'royalblue' => '4169E1',
    'saddlebrown' => '8B4513',
    'salmon' => 'FA8072',
    'sandybrown' => 'F4A460',
    'seagreen' => '2E8B57',
    'seashell' => 'FFF5EE',
    'sienna' => 'A0522D',
    'silver' => 'C0C0C0',
    'skyblue' => '87CEEB',
    'slateblue' => '6A5ACD',
    'slategray' => '708090',
    'slategrey' => '708090',
    'snow' => 'FFFAFA',
    'springgreen' => '00FF7F',
    'steelblue' => '4682B4',
    'tan' => 'D2B48C',
    'teal' => '008080',
    'thistle' => 'D8BFD8',
    'tomato' => 'FF6347',
    'turquoise' => '40E0D0',
    'violet' => 'EE82EE',
    'wheat' => 'F5DEB3',
    'white' => 'FFFFFF',
    'whitesmoke' => 'F5F5F5',
    'yellow' => 'FFFF00',
    'yellowgreen' => '9ACD32');
