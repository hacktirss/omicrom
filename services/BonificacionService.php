<?php

include_once ('data/ClientesDAO.php');
include_once ('data/ProductoDAO.php');
include_once ('data/BonificacionDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "bonificacion.php?";

$ciaDAO = new CiaDAO();
$clienteDAO = new ClientesDAO();
$productoDAO = new ProductoDAO();
$bonificacionDAO = new BonificacionDAO();

if ($request->hasAttribute("id")) {
    $returnLink = urlencode("bonificacione.php?");
    $backLink = urlencode("bonificacion.php?criteria=ini");
    header("Location: clientes.php?criteria=ini&Facturar=3&backLink=$backLink&returnLink=$returnLink");
    exit();
}

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {

            $cliente = $sanitize->sanitizeInt("Cliente");
            $producto = $sanitize->sanitizeInt("Producto");
            $Msj = $bonificacionDAO->calculaBonificacionClientes_vPuntos($cliente, $producto, "Consumo");
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_DELETE) {
            $ciaVO = $ciaDAO->retrieve(1);

            if ($ciaVO->getMaster() === $sanitize->sanitizeString("Password")) {

                $updatePuntos = "UPDATE puntos SET status='Cancelado' WHERE id='$busca'";

                if (($mysqli->query($updatePuntos))) {
                    $Msj = utils\Messages::RESPONSE_VALID_CANCEL;
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } else {
                $Msj = utils\Messages::RESPONSE_PASSWORD_INCORRECT;
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en bonificacion: " . $ex);
    } finally {
        if ($mysqli->errno > 0) {
            error_log($mysqli->error);
        }
        header("Location: $Return");
    }
}