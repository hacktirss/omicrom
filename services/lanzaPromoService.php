<?php

#Librerias
include_once ("data/BitacoraDAO.php");
include_once ("data/PromosDAO.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();

if ($request->hasAttribute("Boton")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $Return = "lanzapromo.php?";

    try {
        if ($request->getAttribute("Boton") === "AddTicket") {
            $_SESSION["CliSelecc"][count($_SESSION['CliSelecc'])] = $request->getAttribute("idCliente");
            $Msj = "Registro agregado con exito";
        } elseif ($request->getAttribute("Boton") === "LanzaPromo") {
            $PromosVO = new PromosVO();
            $PromosDAO = new PromosDAO();
            foreach ($_SESSION["CliSelecc"] as $vvl => $val) {
                $longitud = 16;
                $caracteres = '0123456789'; // Caracteres permitidos
                $codigo = '';

                for ($i = 0; $i < $longitud; $i++) {
                    $indiceAleatorio = mt_rand(0, strlen($caracteres) - 1);
                    $codigo .= $caracteres[$indiceAleatorio];
                }
                $PromosVO->setFecha_creacion(date("Y-m-d"));
                $PromosVO->setId_authuser($usuarioSesion->getId());
                $PromosVO->setFecha_limite(utils\HTTPUtils::getSessionValue("FechaExpira"));
                $PromosVO->setMinimo(utils\HTTPUtils::getSessionValue("MinimoConsumo"));
                $PromosVO->setTipo("V");
                $PromosVO->setCodigo_promo($codigo);
                $PromosVO->setStatus(0);
                $PromosVO->setId_cli($val);
                $PromosVO->setImporte(utils\HTTPUtils::getSessionValue("ImporteCli" . $val));
                $PromosDAO->create($PromosVO);
            }
            unset($_SESSION["CliSelecc"]);
            unset($_SESSION["TipoGeneracion"]);
            unset($_SESSION["Importe"]);
        } elseif ($request->getAttribute("Boton") === "Genera 1") {
            $_SESSION["TipoGeneracion"] = "Gen1";
            $_SESSION["Importe"] = $request->getAttribute("ImporteGeneral");
        } elseif ($request->getAttribute("Boton") === "Genera 2") {
            $_SESSION["TipoGeneracion"] = "Gen2";
            $_SESSION["Importe"] = $request->getAttribute("ImporteXLitro");
        } else if ($request->getAttribute("Boton") === "Actualiza Parametros") {

            utils\HTTPUtils::setSessionValue("FechaExpira", $request->getAttribute("FechaExpira"));
            utils\HTTPUtils::setSessionValue("MinimoConsumo", $request->getAttribute("ConsumoMin"));
        }
        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en parametros: " . $ex);
    } finally {
//        header("Location: $Return");
    }
}


