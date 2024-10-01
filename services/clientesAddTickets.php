<?php

#Librerias
include_once ("data/BitacoraDAO.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();

if ($request->hasAttribute("Boton")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $Return = "clientesAgregaVentas.php?";

    try {
        if ($request->hasAttribute("Boton")) {
            if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
                $VvalD = $request->getAttribute("Combustible") !== "" ? " AND producto = '" . $request->getAttribute("Combustible") . "' " : "";
                $VentasDelCorte = "SELECT * FROM rm  WHERE corte = $idCorte AND uuid = '-----' AND cliente = 0 $VvalD AND importe > 0  AND importe = pesos "
                        . "AND enviado = 0 ORDER BY importe DESC";
                error_log($VentasDelCorte);
                $RsVdc = utils\IConnection::getRowsFromQuery($VentasDelCorte);
                $c = 0;
                $_SESSION['Ventas'] = $_SESSION['VentasBorra'] = array();
                foreach ($RsVdc as $vdc) {
                    if ($Importe > $vdc["importe"]) {
                        $Importe = $Importe - $vdc["importe"];
                        $_SESSION['Ventas'][$c] = $vdc["id"];
                        $_SESSION['VentasBorra'][$vdc["id"]] = 1;
                        $c++;
                    } else {
                        $_SESSION['VentasBorra'][$vdc["id"]] = 0;
                    }
                }
                $Msj = "¡Registros agregados con exito!";
            } elseif ($request->getAttribute("Boton") === "AddTicket") {
                $_SESSION["Ventas"][count($_SESSION['Ventas'])] = $request->getAttribute("idTicket");
                $_SESSION['VentasBorra'][$request->getAttribute("idTicket")] = 1;
                $Msj = "Registro agregado con exito";
            } elseif ($request->getAttribute("Boton") === "DeleteTicket") {
                $_SESSION["Ventas"][$request->getAttribute("idTicket")] = 0;
                $_SESSION['VentasBorra'][$request->getAttribute("idRlTicket")] = 0;
                $e = 0;
                $_SESSION["VentasPass"] = array();
                foreach ($_SESSION["Ventas"] as $vs => $val) {
                    if ($val > 0) {
                        $_SESSION["VentasPass"][$e] = $val;
                        $e++;
                    }
                }
                $_SESSION['Ventas'] = array();
                $_SESSION['Ventas'] = $_SESSION["VentasPass"];
                $Msj = "¡Registro eliminado con exito!";
            } elseif ($request->getAttribute("Boton") === "LanzaProcesoCxcRm") {
                $UnidadCli = "SELECT id,codigo,placas FROM unidades WHERE id = $idUnidad";
                $RsUnidades = utils\IConnection::execSql($UnidadCli);
                if (empty($RsUnidades)) {
                    $Placas = "-----";
                    $Codigo = "";
                } else {
                    $Placas = $RsUnidades["placas"];
                    $Codigo = $RsUnidades["codigo"];
                }
                $BitacoraDAO = new BitacoraDAO();
                $BitacoraVO = new BitacoraVO();
                foreach ($_SESSION["Ventas"] as $vs => $val) {
                    /* Process Cxc */
                    $InsertIntoCxc = "INSERT INTO cxc (cliente,placas,referencia,fecha,hora,tm,concepto,cantidad,importe,recibo,corte,producto,rubro,factura) "
                            . "SELECT $busca,'" . $Placas . "',id,'" . date("Y-m-d") . "','" . date("H:i:s") . "','C','Venta a cliente desde proceso addTickets',volumen,importe,0,corte,producto,"
                            . "'-----',0 FROM rm WHERE id =  " . $val;
                    utils\IConnection::execSql($InsertIntoCxc);
                    /* Process Rm */
                    $UpdateRm = "UPDATE rm SET cliente = $busca, enviado = 0,codigo='" . $Codigo . "',placas='" . $Placas . "',comprobante = comprobante + 1 WHERE id = $val";
                    utils\IConnection::execSql($UpdateRm);
                    $BitacoraDAO->saveLogSn($usuarioSesion->getNombre(), "ADM", "Utiliza proceso TicketAdd para ticket no. " . $val);
                    $_SESSION['VentasBorra'] = array();
                    $_SESSION['Ventas'] = array();
                }
            }
        }
        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en parametros: " . $ex);
    } finally {
        header("Location: $Return");
    }
}
