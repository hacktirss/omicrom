<?php

#Librerias
include_once ('data/GenbolDAO.php');
include_once ('data/RmDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "genboletos.php?";

$genbolDAO = new GenbolDAO();
$ciaDAO = new CiaDAO();
$ciaVO = $ciaDAO->retrieve(1);

if ($request->hasAttribute("cVarVal")) {
    utils\HTTPUtils::setSessionBiValue($nameVariableSession, "cVarVal", $request->getAttribute("cVarVal"));
}

$cVarVal = utils\HTTPUtils::getSessionBiValue($nameVariableSession, "cVarVal");
$lBd = false;

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $busca = $sanitize->sanitizeInt("busca");

    $genbolVO = new GenbolVO();
    $genbolVO->setId($busca);
    if (is_numeric($genbolVO->getId())) {
        $genbolVO = $genbolDAO->retrieve($genbolVO->getId());
    }
    $genbolVO->setFecha(date("Y-m-d"));
    $genbolVO->setFechav($sanitize->sanitizeString("Fechav"));
    $genbolVO->setCliente($sanitize->sanitizeInt("Cliente"));
    $genbolVO->setRecibe($sanitize->sanitizeString("Recibe"));

    //error_log(print_r($genbolVO, TRUE));
    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            $genbolVO->setImporte($sanitize->sanitizeFloat("Importe"));
            $genbolVO->setStatus(StatusVales::ABIERTO);
            if (($id = $genbolDAO->create($genbolVO)) > 0) {
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                $Return = "genboletosd.php?criteria=ini&cVarVal=" . $id;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            if ($genbolDAO->update($genbolVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_CANCEL) {

            $password = $sanitize->sanitizeString("Password");

            if ($password === $ciaVO->getMaster()) {
                $genbolVO->setStatus(StatusVales::CANCELADO);
                if ($genbolDAO->update($genbolVO)) {
                    $mysqli->query("UPDATE boletos SET importe = 0 WHERE id = '$busca'");
                    $mysqli->query("UPDATE genbold SET boletos = 0,vigente = 'No' WHERE id = '$busca'");
                    $Msj = utils\Messages::RESPONSE_VALID_CANCEL;
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } else {
                $Msj = utils\Messages::MESSAGE_NO_PASSWORD_VALID;
            }
        } elseif ($request->getAttribute("Boton") === "AgregarD") {
            $boletos = $sanitize->sanitizeInt("Boletos");
            $precio = $sanitize->sanitizeFloat("Precio");
            $insertGenbold = "INSERT INTO genbold (id,boletos,precio)
                              VALUES ('$cVarVal','$boletos','$precio')";
            if (($mysqli->query($insertGenbold))) {
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                TotalizaVales($cVarVal);
                $Return = "genboletosd.php?";
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === "Liberar") {
            $Return = "boletos.php?";
            $T1 = $sanitize->sanitizeInt("T1");
            $T2 = $sanitize->sanitizeInt("T2") ;
            $impB = $sanitize->sanitizeInt("impBoleto");
            $cId = $sanitize->sanitizeInt("cId");
            $rmDAO = new RmDAO();
            $rmVO1 = $rmDAO->retrieve($T1);
            $rmVO2 = $rmDAO->retrieve($T2);
            $importe1=$rmVO1->getPesos();
            $importe2=$rmVO2->getPesos();
            $impCargado = $importe1 + $importe2;
            $dif = (int)$impB - $impCargado;
            error_log("el valor de importe es".$impB." El valor de  ".$impCargado);
            error_log("la diferencia es".$dif);


            error_log("importe Boleto".$impB);
            error_log("ticket 1:". $T1. " importe 1: ".$importe1);
            error_log("ticket 2:". $T2. " importe 2: ".$importe2);

            error_log("importe Total: ".$impCargado);

            if($dif == 0){
                $vig = 'No';
            }else{
                $vig = 'Si';
            }
            if (is_numeric($rmVO1->getId())) {
                if (is_numeric($rmVO2->getId())) {
                    error_log("Entro a 1 update");
                    $updateVales = "UPDATE boletos SET vigente = 'No',ticket='$T1',ticket2='$T2',importe1 = '$importe1',importe2 = '$importe2',importecargado = '$impCargado'
                            WHERE idnvo = '$cId'";
                }else{
                error_log("Entro a 2 update");
                $updateVales = "UPDATE boletos SET vigente = '$vig',ticket='$T1',importe1 = '$importe1',importecargado = '$impCargado'
                    WHERE idnvo = '$cId'";
                }
                if ($mysqli->query($updateVales)) {
                    $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                } else {
                    error_log($mysqli->error);
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } else {
                $Msj = "El ticket 1 ingresado es invalido o no existe";
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en vales: " . $ex);
    } finally {
        header("Location: $Return");
    }
}


if ($request->hasAttribute("op")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $cId = $sanitize->sanitizeInt("cId");

    try {
        if ($request->getAttribute("op") === utils\Messages::OP_DELETE) {
            $Return = "genboletosd.php?";
            if (($mysqli->query("DELETE FROM genbold WHERE idnvo = '$cId' LIMIT 1"))) {
                $Msj = utils\Messages::RESPONSE_VALID_DELETE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("op") === "Genera") {
            $genbolVO = $genbolDAO->retrieve($cVarVal);

            $selectMax = "SELECT COUNT(*) + 1 cont FROM genbol,boletos WHERE genbol.id = boletos.id AND genbol.cliente ='" . $genbolVO->getCliente() . "'";
            $Max = $mysqli->query($selectMax)->fetch_array();
            error_log("Max: " . $Max["cont"]);

            $selectTotales = "SELECT IFNULL(SUM(boletos), 0) boletos,IFNULL(SUM(boletos*precio),0) importe FROM genbold WHERE id = $cVarVal";
            $Cpo = $mysqli->query($selectTotales)->fetch_array();
            error_log("Sum: " . $Cpo["boletos"]);

            $nBoletos = $Cpo["boletos"];
            $nNum = $Max["cont"];
            $Fin = cZeros($nBoletos, 3);
            $cCodId = cZeros($cVarVal, 4);

            $selectVales = "SELECT precio,boletos FROM genbold WHERE id = $cVarVal";
            $result = $mysqli->query($selectVales);
            $genera = false;
            while ($row = $result->fetch_array()) {

                $nImp = $row["precio"];
                $nBol = $row["boletos"];

                for ($i = 1; $i <= $nBol; $i = $i + 1) {
                    $Fol = cZeros($nNum, 3);
                    $cSec = $Fol . "/" . $Fin;
                    $nCod = rand(1, 999999);
                    $cCod = cZeros($nCod, 6);
                    $cCodigo = $cCodId . $cCod;

                    $insertVale = "INSERT INTO boletos (id,secuencia,codigo,importe,vigente)
                           VALUES ($cVarVal,'$cSec','$cCodigo',$nImp,'Si')";
                    if (($mysqli->query($insertVale))) {
                        $genera = true;
                    } else {
                        error_log($mysqli->error);
                        error_log($insertVale);
                        break;
                    }
                    $nNum++;
                }
            }

            $genbolVO->setStatus(StatusVales::CERRADO);
            if ($genera && $genbolDAO->update($genbolVO)) {
                $Msj = utils\Messages::MESSAGE_CLOSE;
                $Return .= "criteria=ini";
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("op") === "cr") {
            $lBd = true;
            $Return = null;
            $Msj = "Iniciando generación de vales";
        }
    } catch (Exception $ex) {
        error_log("Error en vales: " . $ex);
    } finally {
        if ($Return != null) {
            $Return .= "&Msj=" . urlencode($Msj);
            header("Location: $Return");
        }
    }
}

function TotalizaVales($busca) {

    $mysqli = iconnect();

    $selectVales = "SELECT IFNULL(SUM(boletos), 0) cant,IFNULL(SUM(boletos*precio),0) importe FROM genbold WHERE id = '$busca'";
    $DddA = $mysqli->query($selectVales);
    $Ddd = $DddA->fetch_array();

    $cSql = "UPDATE genbol SET cantidad='$Ddd[cant]' WHERE id='$busca'";
    if (!($mysqli->query($cSql))) {
        error_log($mysqli->error);
    }
}
