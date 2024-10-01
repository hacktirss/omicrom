<?php

include_once ("../../softcoatl/SoftcoatlHTTP.php");

//include_once ("../../libnvo/Utilerias.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$connection = utils\IConnection::getConnection();
//$usuarioSesion = getSessionUsuario();
$jsonString = array();

$jsonString["Response"] = false;

if ($request->getAttribute("Op") == 1) {
    /* Solo nos llega la cantidad a modificar, para ver que tickets tenemos disponibles */
    $BuscamosCorte = "SELECT corte,producto,dispensario,manguera,posicion,cliente,comprobante,pesosp FROM rm WHERE id = " . $request->getAttribute("Ticket");

    if (($query = $connection->query($BuscamosCorte)) && ($rows_ = $query->fetch_assoc())) {

        $LimiteM = "SELECT valor FROM variables_corporativo WHERE llave = 'limite_machaca'";
        /* Obtenemos el limite machaca para saber cuando es su maxima cantidad a extraer de los tickets */
        if (($queryLm = $connection->query($LimiteM)) && ($rowLm = $queryLm->fetch_assoc())) {
            $LimiteMax = $rowLm["valor"];
            $LimitePedido = $request->getAttribute("Monto");
        }
        $Html = "";
        if ($rows_["cliente"] == 0 && !($rows_["comprobante"] == 66 || $rows_["comprobante"] == 67) && $LimiteMax >= $LimitePedido) {

            $BuscaTickets = "SELECT ROUND(SUM(rm.importe),2) impt, ROUND(SUM(rm.importe/rm.precio),2) volumenTotal FROM rm LEFT JOIN com ON rm.producto = com.clavei "
                    . "WHERE rm.corte = " . $rows_["corte"] . " AND (rm.cliente = 0 || tipodepago='Monedero') AND rm.dispensario = " . $rows_["dispensario"] . " AND rm.posicion = " . $rows_["posicion"] . " "
                    . "AND (rm.comprobante = 0 || rm.comprobante = 66 ) AND rm.uuid = '-----'  AND rm.producto = '" . $rows_["producto"] . "' AND manguera = " . $rows_["manguera"] . " AND rm.id <> " . $request->getAttribute("Ticket");
            error_log($BuscaTickets);
            if ($query = $connection->query($BuscaTickets)) {
                if ($value = $query->fetch_assoc()) {
                    $Html = "<table style='width:100%;margin-bottom:15px;'>
                                <tr>
                                    <td>Total disponible </td>
                                    <td><strong>Importe:</strong> " . $value["impt"] . "</td>
                                    <td><strong>Volumen:</strong>" . $value["volumenTotal"] . "</td>
                                    <td>
                                        <input type='hidden' name='TotalDisponible' id='TotalDisponible' value='" . $value["impt"] . "'>
                                        <input type='hidden' name='VolumenTotal' id='VolumenTotal' value='" . $value["volumenTotal"] . "'>
                                    </td>
                                </tr>
                            </table>";
                }
            }
            $BuscaPor = $request->getAttribute("TipoValor") === "importe" ? "importe" : "volumenp";
            $BuscaTickets = "SELECT rm.id,rm.producto,rm.volumen,rm.importe,rm.inicio_venta,com.descripcion,rm.pesosp FROM rm LEFT JOIN com ON rm.producto = com.clavei "
                    . "WHERE rm.corte = " . $rows_["corte"] . " AND rm.cliente = 0  AND rm.importe > 1  AND rm.dispensario = " . $rows_["dispensario"] . " AND rm.posicion = " . $rows_["posicion"] . " "
                    . "AND (rm.comprobante = 0 || rm.comprobante = 66 )  AND rm.producto = '" . $rows_["producto"] . "' AND manguera = " . $rows_["manguera"] . " AND rm.id <> " . $request->getAttribute("Ticket") . " "
                    . "AND rm.$BuscaPor >= " . $request->getAttribute("Monto") . " AND rm.uuid = '-----' "
                    . "ORDER BY id ASC LIMIT 13;";
            if ($query = $connection->query($BuscaTickets)) {
                $Html .= '<table aria-hidden="true" style="width: 100%;border:1px solid #8591A0;">
                    <thead>
                        <tr bgcolor="#566573" style="height:30px; color:white;">
                            <th scope="col" style="width: 8%;align-content: right;">Id</th>
                            <th scope="col" style="width: 25%;">Descripcion</th>
                            <th scope="col">Volumen</th>
                            <th scope="col">Importe</th>
                            <th scope="col">Fecha/Hora</th>
                            <th scope="col">Seleccionar</th>
                        </tr>
                    </thead>
                    <tbody style="font-family: sans-serif;font-size: 12px;">';

                $i = 0;
                while ($value = $query->fetch_assoc()) {
                    $color = $i % 2 == 0 ? "#F2F3F4" : "#AEB6BF";
                    $Html = $Html . '
                <tr style="background-color:' . $color . ';color: #212F3D">
                    <td style="padding-left:5px;">' . $value["id"] . '</td>
                    <td style="padding-left:5px;">' . $value["descripcion"] . '</td>
                    <td style="padding-right:5px;" align="right">' . number_format($value["volumen"], 2) . '</td>
                    <td style="padding-right:5px;" align="right">' . number_format($value["importe"], 2) . '</td>
                    <td style="padding-left:5px;">' . $value["inicio_venta"] . '</td>
                    <td align="center"><input type="radio" class="botonAnimatedMin" name="TicketRevolvente" value="' . $value["id"] . '"></td>
                </tr>';
                    $i++;
                }
                $Html = $Html . "</tbody>
            </table>";
            }
        } else if ($rows_["comprobante"] == 66 || $rows_["comprobante"] == 67) {
            $Html = "<div style='background-color:#FADBD8;padding:10px;border:1px solid #EC7063;border-radius: 20px;'>El ticket modificado con anterioridad <i style='color:red' class='fa fa-times  fa-2x' aria-hidden='true'></i></div>";
        } else if ($LimiteMax <= $LimitePedido) {
            $Html = "<div style='background-color:#FADBD8;padding:10px;border:1px solid #EC7063;border-radius: 20px;'>"
                    . "El monto requerido es mayor al pemitido por "
                    . "el limite establecido de $" . $rowLm["valor"] . " "
                    . "<i style='color:red' class='fa fa-times  fa-2x' aria-hidden='true'></i>"
                    . "</div>";
        } else {
            $Html = "<div style='background-color:#FADBD8;padding:10px;border:1px solid #EC7063;border-radius: 20px;'>El ticket ya se encuentra en el estado de cuenta <i style='color:red' class='fa fa-times  fa-2x' aria-hidden='true'></i></div>";
        }
    }
    echo $Html;
    exit();
} else if ($request->getAttribute("Op") == 2) {
    $Monto = $_REQUEST["Monto"];
    $TicketExtraccion = $_REQUEST["TicketExt"];
    $TicketAdd = $_REQUEST["Ticket"];
    $Html = "Se resta el importe de $" . $Monto . " al ticket " . $TicketExtraccion . " y se transfiere al ticket no." . $TicketAdd;
    $SelectTk = "SELECT  importe FROM rm WHERE id = " . $TicketAdd;
    $Monto = convierteVolumenAImporte($request->getAttribute("TipoValor") === "Importe" ? "importe" : "volumen", $Monto, $connection, $TicketExtraccion);
    if (($query = $connection->query($SelectTk)) && ($rows_ = $query->fetch_assoc())) {
        $Monto = $Monto - $rows_["importe"];
    }

    $SelectVenta = "SELECT $Monto Iresta, $Monto/rm.precio Vresta,tipodepago,
                    ROUND($Monto/(1 + rm.factor * mp.enable / 100),2) IresF,
                    ROUND(($Monto/rm.precio) / ( 1 + rm.factor * mp.enable / 100 ),3) VresF 
                    FROM rm LEFT JOIN man_pro mp 
                    ON rm.dispensario = mp.dispensario 
                    AND rm.posicion = mp.posicion AND rm.manguera = mp.manguera AND rm.uuid='-----'
                    AND rm.producto = mp.producto WHERE rm.id = " . $TicketExtraccion;
    if (($query = $connection->query($SelectVenta)) && ($rows_ = $query->fetch_assoc())) {

        $TipoPagoMonedero = $rows_["tipodepago"] === "Monedero" ? 0 : $rows_["Iresta"];
        $ActualizaRmExt = "UPDATE rm SET pesos = ROUND(pesos - " . $rows_["Iresta"] . ",2), volumen = ROUND(volumen - " . $rows_["Vresta"] . ",2), "
                . "volumenp = ROUND(volumenp - " . $rows_["VresF"] . ",2), pesosp = ROUND(pesosp - " . $rows_["IresF"] . ",2), comprobante = 66,"
                . "importe = ROUND(importe - " . $rows_["Iresta"] . ",2), pagoreal = ROUND(pagoreal - " . $TipoPagoMonedero . ",2) "
                . " WHERE id = " . $TicketExtraccion . " LIMIT 1;";

        $ActualizaRmAdd = "UPDATE rm SET pesos = ROUND(pesos + " . $rows_["Iresta"] . ",2), volumen = ROUND(volumen + " . $rows_["Vresta"] . ",2), "
                . "volumenp = ROUND(volumenp + " . $rows_["VresF"] . ",2),pesosp = ROUND(pesosp + " . $rows_["IresF"] . ",2), comprobante = 67,"
                . "importe = ROUND(importe + " . $rows_["Iresta"] . ",2), pagoreal = ROUND(pagoreal + " . $rows_["Iresta"] . ",2)  "
                . "WHERE id = " . $TicketAdd . " LIMIT 1;";
        $ActualizaFactor = "UPDATE rm SET factor = ROUND((1-volumenp/volumen)*100,0) WHERE id = " . $TicketAdd;
        $connection->begin_transaction();
        $connection->query($ActualizaRmExt);
        $connection->query($ActualizaRmAdd);
        $connection->query($ActualizaFactor);
        if (!$connection->commit()) {
            error_log("ERROR SQL");
            error_log($ActualizaRmAdd);
            error_log($ActualizaRmExt);
        }
    }
    $SELECTAdd = "SELECT * FROM rm WHERE id = " . $TicketAdd;
    $SELECTExt = "SELECT * FROM rm WHERE id = " . $TicketExtraccion;
    if (($query = $connection->query($SELECTAdd)) && ($rows_ = $connection->query($SELECTExt))) {
        $query1 = $query->fetch_assoc();
        $rows_1 = $rows_->fetch_assoc();
        $Html .= "<br><div style='background-color:#ABEBC6;padding:10px;border:1px solid #58D68D;border-radius: 20px;'>"
                . "Ticket no.$TicketAdd "
                . "<strong>Cantidad </strong>: " . $query1["volumen"] . " "
                . "<strong>Importe </strong>:" . $query1["importe"] . " <i style='color:green' class='fa fa-check fa-2x' aria-hidden='true'></i></div>";
        $Html .= "<br><div style='background-color:#ABEBC6;padding:10px;border:1px solid #58D68D;border-radius: 20px;'>"
                . "Ticket no.$TicketExtraccion "
                . "<strong>Cantidad </strong>: " . $rows_1["volumen"] . " "
                . "<strong>Importe </strong>:" . $rows_1["importe"] . " <i style='color:green' class='fa fa-check fa-2x' aria-hidden='true'></i></div>";
    }
    echo $Html;
    exit();
} else if ($request->getAttribute("Op") == 3) {
    /* Proceso de tickets libres, buscamos ticket y les quitarmos su cantidad para pasar al ticket indicado */

    $IdTicket = $request->getAttribute("Ticket");
    $MontoAcumulado = $Monto = $request->getAttribute("MontoSum");
    $SelectTicket = "SELECT * FROM rm WHERE id = " . $IdTicket;
    if (($query = $connection->query($SelectTicket)) && $rows_ = $query->fetch_assoc()) {
        $BuscaTickets = "SELECT rm.importe,rm.pesosp,rm.volumen,rm.id,rm.precio FROM rm LEFT JOIN com ON rm.producto = com.clavei "
                . "WHERE rm.corte = " . $rows_["corte"] . " AND rm.cliente = 0 AND rm.dispensario = " . $rows_["dispensario"] . " AND rm.posicion = " . $rows_["posicion"] . " "
                . "AND ( rm.comprobante = 0 || rm.comprobante = 66 ) AND rm.importe > 1 AND rm.producto = '" . $rows_["producto"] . "' "
                . "AND manguera = " . $rows_["manguera"] . " AND rm.id <> " . $IdTicket . " AND rm.uuid='-----' "
                . "ORDER BY inicio_venta ASC";
        if ($RsQ = $connection->query($BuscaTickets)) {
            while ($rows1_ = $RsQ->fetch_assoc()) {
                $MontoCheck = $MontoAcumulado - $rows1_[$request->getAttribute("TipoValor")];
                /* Verificamos que el ticket sea mayor o menor a la cantidad solicitada */
                $MontoAcumuladoLg = $MontoAcumulado;
                if ($MontoCheck >= 0 && $MontoAcumulado > 0) {
                    error_log("Ticket menor a lo requerido");
                    /* Ingresamos cuando el ticket es menor a la cantidad requerida */
                    $array = GeneraTransferenciaTicket("rm.importe", $rows1_["id"], $IdTicket, $connection, $MontoAcumulado, $request->getAttribute("TipoValor"));
                    $MontoAcumulado = $array[0];
                    $Html .= $array[1];
                } else {
                    error_log("Ticket mayor a lo requerido le tiramos " . $MontoAcumulado);
                    /* Ingresamos cuando el ticket es por una cantidad mayor a lo pedido */
                    if ($MontoAcumulado > 0) {
                        $array = GeneraTransferenciaTicket($MontoAcumulado, $rows1_["id"], $IdTicket, $connection, $MontoAcumulado, $request->getAttribute("TipoValor"));
                        $MontoAcumulado = $array[0];
                        $Html .= $array[1];
                    }
                }
                $sql = " INSERT INTO  bitacora_eventos "
                        . " ( fecha_evento, hora_evento, usuario , tipo_evento , descripcion_evento, query_str) "
                        . " VALUES "
                        . " ( current_date() , current_time() , '" . $request->getAttribute("UsuarioM") . "' , 'Aumento' ,"
                        . " 'Se aumenta ticket de manera libre " . $rows1_["id"] . " -> $IdTicket = $MontoAcumuladoLg' , '') ";
                $connection->query($sql);
            }
            echo $Html;
            exit();
        }
    }
} else if ($request->getAttribute("Op") == 4) {
    /* Traemos la información, monto, y diferencia para mostrar los tickets que se puedan obtener */
    $Select = "SELECT " . $request->getAttribute("TipoValor") . " FROM rm WHERE id = " . $_REQUEST["Ticket"];

    if (($query = $connection->query($Select)) && $rows_ = $query->fetch_assoc()) {
        echo $rows_[$request->getAttribute("TipoValor")];
        exit();
    }
}

function GeneraTransferenciaTicket($MontoAcumulado, $rows1_, $IdTicket, $connection, $MontoA, $TipoValor) {
    global $Html, $connection;
    $array[] = array();

    $MontoAcumulado = convierteVolumenAImporte($TipoValor, $MontoAcumulado, $connection, $IdTicket);

    $Selectaumenta = "SELECT $MontoAcumulado Iresta, $MontoAcumulado / rm.precio Vresta,
                                        ROUND($MontoAcumulado / (1 + rm.factor / 100),2) IresF,
                                        ROUND(($MontoAcumulado / rm.precio) / ( 1 + rm.factor / 100 ),3) VresF, tipodepago
                                        FROM rm LEFT JOIN man_pro mp 
                                        ON rm.dispensario = mp.dispensario 
                                        AND rm.posicion = mp.posicion AND rm.manguera = mp.manguera 
                                        AND rm.producto = mp.producto WHERE rm.id = " . $rows1_;

    if ($query = $connection->query($Selectaumenta)) {
        $rows2_ = $query->fetch_assoc();
        if (number_format($MontoAcumulado, 2) > 0 || $MontoAcumulado === "rm.importe") {
            ejecutaTransferencias($rows2_, $rows1_, $IdTicket, $connection);
            $Html .= "<br><div style='background-color:#ABEBC6;padding:10px;border:1px solid #58D68D;border-radius: 20px;'>"
                    . "Ticket restado no. " . $rows1_ . " Transferido a Ticket no." . $IdTicket . "</div>";
        }
        $Resta = $TipoValor === "volumen" ? $rows2_["Vresta"] : $rows2_["Iresta"];
        $MontoAcumulado = $MontoA - $Resta;
    }
    $array[0] = $MontoAcumulado;
    $array[1] = $Html;
    return $array;
}

function convierteVolumenAImporte($TipoValor, $MontoAcumulado, $connection, $IdTicket) {
    if ($TipoValor === "volumen") {
        if ($MontoAcumulado !== "rm.importe") {
            $Sql = "SELECT $MontoAcumulado * precio imp FROM omicrom.rm WHERE id = $IdTicket;";
            $RgSql = $connection->query($Sql);
            $RsSql = $RgSql->fetch_assoc();
            $MontoAcumulado = $RsSql["imp"];
            return $MontoAcumulado;
        } else {
            return $MontoAcumulado;
        }
    } else {
        return $MontoAcumulado;
    }
}

function ejecutaTransferencias($rows2_, $rows1_, $IdTicket, $connection) {
    $TipoPagoMonedero = $rows2_["tipodepago"] === "Monedero" ? 0 : $rows2_["Iresta"];

    $ActualizaRmRst = "UPDATE rm SET pesos = ROUND(pesos - " . $rows2_["Iresta"] . ",2), volumen = ROUND(volumen - " . $rows2_["Vresta"] . ",2), "
            . "volumenp = ROUND(volumenp - " . $rows2_["VresF"] . ",2) ,pesosp = ROUND(pesosp - " . $rows2_["IresF"] . ",2), comprobante = 66,"
            . "importe = ROUND(importe - " . $rows2_["Iresta"] . ",2), pagoreal = ROUND(pagoreal - " . $TipoPagoMonedero . ",2) "
            . "WHERE id = " . $rows1_ . " LIMIT 1;";

    $ActualizaRmAdd = "UPDATE rm SET pesos = ROUND(pesos + " . $rows2_["Iresta"] . ",2), volumen = ROUND(volumen + " . $rows2_["Vresta"] . ",2), "
            . "volumenp = ROUND(volumenp +  " . $rows2_["VresF"] . ",2),pesosp = ROUND(pesosp + " . $rows2_["IresF"] . ",2), comprobante = 67,"
            . "importe = ROUND(importe + " . $rows2_["Iresta"] . ",2), pagoreal = ROUND(pagoreal + " . $rows2_["Iresta"] . ",2) "
            . "WHERE id = " . $IdTicket . " LIMIT 1;";

    $ActualizaFactor = "UPDATE rm SET factor = ROUND((1-volumenp/volumen)*100,0) WHERE id = " . $IdTicket;
    try {
        $connection->begin_transaction();
        $connection->query($ActualizaRmRst);
        $connection->query($ActualizaRmAdd);
        $connection->query($ActualizaFactor);
        $connection->commit();
    } catch (Exception $ex) {
        $connection->rollback();
    }
}
