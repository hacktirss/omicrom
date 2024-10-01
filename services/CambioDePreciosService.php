<?php

#Librerias
include_once ('data/IslaDAO.php');
include_once ('data/CambioPreciosDAO.php');
include_once ('data/CombustiblesDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "cambiopre.php?";

$islaDAO = new IslaDAO();
$cambioPreciosDAO = new CambioPreciosDAO();
$combustiblesDAO = new CombustiblesDAO();

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    echo print_r($request,true);
    try {
        if ($request->getAttribute("Boton") === "Confirmar") {
            $DispensariosA = $mysqli->query("SELECT Dispensarios FROM variables")->fetch_array();
            $Dispensarios = $DispensariosA["Dispensarios"];

            $idTarea = IdTarea();

            $Precio = $sanitize->sanitizeString("Pesos") . "." . $sanitize->sanitizeString("Centavos");
            $Hora = $sanitize->sanitizeString("Horaapli");
            $Minuto = $sanitize->sanitizeString("Minutoapli");
            $Producto = $sanitize->sanitizeString("Producto");
            $HoraApliacion = $Hora . ":" . $Minuto . ":00";
            $FechaAplicacion = $sanitize->sanitizeString("Fechaapli") . " " . $HoraApliacion;

            $cambioPreciosVO = new CambioPreciosVO();
            $cambioPreciosVO->setFechaapli($FechaAplicacion);
            $cambioPreciosVO->setHora($HoraApliacion);
            $cambioPreciosVO->setProducto($Producto);
            $cambioPreciosVO->setPrecio($Precio);
            $cambioPreciosVO->setStatus("Agregado");
            $cambioPreciosVO->setIdtarea($idTarea);


            if (($id = $cambioPreciosDAO->create($cambioPreciosVO)) > 0) {
                $cambioPreciosVO->setId($id);
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                $combustiblesVO = $combustiblesDAO->retrieve($Producto, "clavei");

                if ($Dispensarios !== "LC") {
                    $date1 = new DateTime(date("Y-m-d H:i:s", strtotime('-2 minute', strtotime(date("Y-m-d H:i:s")))));
                    $date2 = new DateTime($FechaAplicacion);
                    if ($date2 < $date1) {
                        $Msj = str_replace("?", $FechaAplicacion, utils\Messages::MESSAGE_DATE_INCORRECT);
                    } else {

                        $Precio = $sanitize->sanitizeString("Pesos") . $sanitize->sanitizeString("Centavos");

                        $insertComandos = "INSERT INTO comandos (posicion,manguera,comando,fecha_insercion,fecha_programada,descripcion,idtarea)
                        SELECT LPAD(man_pro.posicion,2,0) posicion,man_pro.manguera,CONCAT('P',LPAD(man_pro.posicion,2,0),man_pro.manguera,'$Precio') comando, NOW() fecha_insercion, 
                        '$FechaAplicacion' fecha_programada,'Cambio de precio' descripcion, '$idTarea' idtarea
                        FROM man,man_pro
                        WHERE man.activo = 'Si' AND man.posicion=man_pro.posicion AND man_pro.activo='Si'
                        AND man_pro.producto = '$Producto'";

                        if (!($mysqli->query($insertComandos))) {
                            $Msj = utils\Messages::RESPONSE_ERROR;
                        }
                    }
                }

                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "CAMBIO DE PRECIO PARA: " . $Producto);
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en cambio de precios: " . $ex);
    } finally {
        header("Location: $Return");
    }
}


if ($request->hasAttribute("op")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    //$bancosDAO = new BancosDAO();
    $cId = $sanitize->sanitizeInt("cId");

    try {
        if ($request->getAttribute("op") === utils\Messages::OP_DELETE) {

            $sqlCp = "SELECT idtarea FROM cp WHERE id='$cId'";
            $Cp = $mysqli->query($sqlCp)->fetch_array();
            error_log(print_r($Cp, TRUE));
            $Comandos = $mysqli->query("SELECT ejecucion FROM comandos WHERE idtarea = '$Cp[idtarea]'");
            $ejecutados = $pendientes = 0;
            while ($Com = $Comandos->fetch_array()) {
                if ($Com[ejecutado] == 0) {
                    $pendientes++;
                }
                if ($Com[ejecutado] == 1) {
                    $ejecutados++;
                }
                $nNum++;
            }

            $Fecha = date("Y-m-d H:i:s");
            $updateCp = "UPDATE cp SET status ='Cancelado' WHERE id ='$cId'";
            $updateComandos = "UPDATE comandos SET ejecucion = '9',intentos = '9',fecha_ejecucion = NOW(), replica = 3
     			       WHERE idtarea = '$Cp[idtarea]'";

            if ($ejecutados == 0 && ($mysqli->query($updateCp)) && ($mysqli->query($updateComandos))) {
                $Msj = utils\Messages::RESPONSE_VALID_CANCEL;
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "CANCELACION DE CAMBIO DE PRECIO " . $cId);
            } else {
                $Tot = $ejecutados + $pendientes;
                $Msj = "Lo siento, no es posible cancelar, se han procesado: $ejecutados comandos de:  $Tot ";
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en pagos: " . $ex);
    } finally {
        header("Location: $Return");
    }
}