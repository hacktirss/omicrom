<?php
#Librerias
#include_once ('data/BoletosDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "asigvale.php?cli=$cli&idT=$idT&nomb=$nombre&importe=$importe";

#$objectDAO = new BoletosDAO();

if ($request->hasAttribute("criteria")) {
    utils\HTTPUtils::setSessionValue("cli", $cli);
    utils\HTTPUtils::setSessionValue("idT", $idT );
    utils\HTTPUtils::setSessionValue("nomb", $nombre);
    utils\HTTPUtils::setSessionValue("importe", $importe );
}

if ($request->hasAttribute("cli")) {
    utils\HTTPUtils::setSessionValue("cli", $sanitize->sanitizeString("cli"));
}
if ($request->hasAttribute("idT")) {
    utils\HTTPUtils::setSessionValue("idT", $sanitize->sanitizeString("idT"));
}
if ($request->hasAttribute("nomb")) {
    utils\HTTPUtils::setSessionValue("nomb", $sanitize->sanitizeString("nomb"));
}
if ($request->hasAttribute("importe")) {
    utils\HTTPUtils::setSessionValue("importe", $sanitize->sanitizeString("importe"));
}

$cli = utils\HTTPUtils::getSessionValue("cli");
$idT = utils\HTTPUtils::getSessionValue("idT");
$nomb = utils\HTTPUtils::getSessionValue("nomb");
$importe = utils\HTTPUtils::getSessionValue("importe");


$importeasigando = $mysqli->query("select ifnull( (sum(b.importe1) + sum(importe2)),0) ImpAco from 
                        ( select b.id, b.idnvo, b.secuencia, b.codigo, b.importe, b.vigente, b.ticket, if(b.ticket <> rm.id,0,b.importe1) importe1 ,
                            b.ticket2, b.importe2
                            from rm inner join boletos b on b.ticket = rm.id or b.ticket2 = rm.id
                                   where rm.id =".$idT. ") b;");

$SaldoAcomulado = $importeasigando->fetch_array();

$conta = $SaldoAcomulado["ImpAco"];

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    #$objectVO = new BoletosVO();
            if(isset($_POST['Boton'])){
                if(!empty($_POST['valores'])) {
                    $saldo1 = 0;
                    $sumcodigo = '';   
                foreach($_POST['valores'] as $seleccion) { 
                    $SumaR = $mysqli->query("SELECT * FROM boletos WHERE codigo = ".$seleccion.";");
                    $SumaRes = $SumaR->fetch_array();
                    $saldo1 = $saldo1 + $SumaRes["importe"];
                } 
                $res = $saldo1 + $conta;
                error_log("el saldo a asiganr es".$res);
                error_log("el saldo importe es".$importe);
                if($res > $importe){
                    error_log("suma mayor a importe: ".$res);
                    $Msj = "La suma selecciona es mayo favor de volver a seleccionar"; 
                }else if($conta > 0){
                    $Msj = "Ticket asignado a otros vales. no se realizo la operacion";
                }else {
                    error_log("aceptado: ".$res);
                    $Msj = "Los ticket modificados son: " ; 
                foreach($_POST['valores'] as $seleccion) {
                    $ExiT = $mysqli->query("SELECT * FROM boletos WHERE codigo = ".$seleccion.";");
                    $ExiTicket1 = $ExiT->fetch_array();
                    $saldo = $ExiTicket1["importe"]-$ExiTicket1["importecargado"];
                    $importeC = $ExiTicket1["importecargado"] + $saldo;
                    #$objectVO = new BoletosVO();
                    $Msj .= "".$seleccion.", ";
                    $sumcodigo .= "|".$seleccion.""; 
                    if ($ExiTicket1["ticket"] > 0){
                        error_log("Entrar al actualizar tl ticket 2 ".$seleccion);
                        $update = "UPDATE boletos SET ticket2='$idT',importe2='$saldo',vigente='No',importecargado = '$importeC' WHERE codigo = '$seleccion';"; 
                        utils\IConnection::execSql($update);
                        error_log("Se actualizo correctamente ".$seleccion);
                    }else{
                        error_log("Entrar a actualizar el ticket 1 ".$seleccion);
                        if($saldo  == $ExiTicket1["importe"]){
                        $vigencia = 'No';
                        } else {
                        $vigencia = 'Si';
                        }
                        $update = "UPDATE boletos SET ticket='$idT',importe1='$saldo',vigente='$vigencia', importecargado = '$importeC' WHERE codigo = '$seleccion'"; 
                        utils\IConnection::execSql($update);
                        error_log("Se actualizo correctamente ".$seleccion);
                    }
                }
                $importeasigando = $mysqli->query("select ifnull( (sum(b.importe1) + sum(importe2)),0) ImpAco from 
                        ( select b.id, b.idnvo, b.secuencia, b.codigo, b.importe, b.vigente, b.ticket, if(b.ticket <> rm.id,0,b.importe1) importe1 ,
                            b.ticket2, b.importe2
                            from rm inner join boletos b on b.ticket = rm.id or b.ticket2 = rm.id
                                   where rm.id =".$idT. ") b;");
                $SaldoAcomulado = $importeasigando->fetch_array();
                $conta = $SaldoAcomulado["ImpAco"];
                $update = "UPDATE rm SET placas='Vales', codigo = '$sumcodigo' WHERE id = '$idT'"; 
                utils\IConnection::execSql($update);
                error_log("Se actualizo rm ".$idT);
                
            }
                }
            }
}