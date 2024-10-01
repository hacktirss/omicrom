<?php

include_once ("../../softcoatl/SoftcoatlHTTP.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$connection = utils\IConnection::getConnection();
$jsonString = array();

$jsonString["Response"] = false;
$Op = $request->getAttribute("Op");
$sql = " INSERT INTO  bitacora_eventos "
        . " ( fecha_evento, hora_evento, usuario , tipo_evento , descripcion_evento, numero_alarma,mac) "
        . " VALUES "
        . " ( current_date() , current_time() , '" . $request->getAttribute("Modifica") . "' , 'ADM' , "
        . "'Reasignacion de despachadores Isla " . $request->getAttribute("IslaPos") . " lado " . $request->getAttribute("lado") . " Despachador " . $request->getAttribute("Despachador") . " $Op' ,"
        . " 0, '-') ";
if (!$connection->query($sql)) {
    error_log($connection->error);
}
switch ($Op) {
    case "Asigna":
        error_log("Entramos en Asigna");
        $Crt = "SELECT * FROM islas WHERE isla = 1;";
        if ($Cortes = $connection->query($Crt)->fetch_array()) {
            $Corte = $Cortes["corte"];
        }
        $Isla = $request->getAttribute("IslaPos");
        $Vl = $request->getAttribute("lado");
        $Vendedor = $request->getAttribute("Despachador");
        error_log("Cambiar venta de vendedor: " . $Vendedor);

        if (!empty($Vendedor)) {
            $update = true;
            $updateV = "UPDATE man SET despachador = $Vendedor WHERE isla_pos = $Isla AND lado = '$Vl'";
            error_log($updateV);
            if (!($connection->query($updateV))) {
                error_log($connection->error);
                $Msj = utils\Messages::RESPONSE_ERROR;
                $update = false;
            }

            if ($update) {
                $updateRm = "
                UPDATE rm, man
                SET rm.vendedor = $Vendedor
                WHERE man.isla_pos = $Isla AND man.posicion = rm.posicion AND lado = '$Vl'
                AND rm.corte = $Corte";
                $updateVtaditivos = "
                UPDATE vtaditivos, man
                SET vtaditivos.vendedor = $Vendedor
                WHERE vtaditivos.posicion = man.posicion AND man.isla_pos = $Isla AND lado = '$Vl'
                AND vtaditivos.corte = $Corte AND vtaditivos.tm = 'C';
                ";
                $updateCtdep = "
                UPDATE ctdep, man
                SET ctdep.despachador = $Vendedor
                WHERE ctdep.posicion = man.posicion AND man.isla_pos = $Isla AND lado = '$Vl'
                AND ctdep.corte = $Corte";

                if (($connection->query($updateRm)) && ($connection->query($updateVtaditivos)) && ($connection->query($updateCtdep))) {
                    $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                }
            } else {
                $Msj = "Favor de seleccionar un vendedor valido";
            }
        }
        $Sven = "SELECT alias FROM ven WHERE id = $Vendedor";
        error_log($Sven);
        if ($NVen = $connection->query($Sven)->fetch_array()) {
            $alias = $NVen["alias"];
        }
        $data["val"] = $alias;
        echo json_encode($data);
        break;
    case "Revertir":
        error_log("Entramos en Revertir");
        $update = true;
        $Crt = "SELECT * FROM islas WHERE isla = 1;";
        if ($Cortes = $connection->query($Crt)->fetch_array()) {
            $Corte = $Cortes["corte"];
        }
        $Isla = $request->getAttribute("IslaPos");
        $Vl = $request->getAttribute("lado");
        $Vendedor = $request->getAttribute("Despachador");
        $updateMan = "UPDATE man SET despachador = posicion WHERE isla_pos = $Isla AND lado = '$Vl'";
        error_log($updateMan);

        if (!($connection->query($updateMan))) {
            error_log($connection->error);
            $update = false;
        }


        if ($update) {
            $updateRm = "
                        UPDATE rm,man 
                        SET rm.vendedor = rm.posicion
                        WHERE man.isla_pos = $Isla AND man.posicion = rm.posicion
                        AND rm.corte = $Corte";
            $updateVtaditivos = "
                        UPDATE vtaditivos,man 
                        SET vtaditivos.vendedor = vtaditivos.posicion 
                        WHERE vtaditivos.posicion = man.posicion AND man.isla_pos = $Isla 
                        AND vtaditivos.corte = $Corte AND vtaditivos.tm = 'C';";
            $updateCtdep = "
                        UPDATE ctdep,man 
                        SET ctdep.despachador = ctdep.posicion 
                        WHERE ctdep.posicion = man.posicion AND man.isla_pos = $Isla
                        AND ctdep.corte = $Corte";

            if (($connection->query($updateRm)) && ($connection->query($updateVtaditivos)) && ($connection->query($updateCtdep))) {
                $Msj = "Exito";
            }
        }
        $Sven = "SELECT posicion FROM  man WHERE isla_pos = $Isla AND lado = '$Vl'";
        error_log($Sven);
        if ($NVen = $connection->query($Sven)->fetch_array()) {
            $alias = $NVen["posicion"];
        }
        $data["val"] = $alias;
        break;
    case "DespachadorSig":
        error_log("Entramos en DespachadorSig");
        $Isla = $request->getAttribute("IslaPos");
        $Vl = $request->getAttribute("lado");
        $Vendedor = $request->getAttribute("Despachador");
        $updateMan = "UPDATE man SET despachadorsig = $Vendedor WHERE isla_pos = $Isla AND lado = '$Vl'";
        error_log($updateMan);

        if (!($connection->query($updateMan))) {
            error_log($connection->error);
            $update = false;
        }
        $Sven = "SELECT alias FROM ven WHERE id = $Vendedor";
        error_log($Sven);
        if ($NVen = $connection->query($Sven)->fetch_array()) {
            $alias = $NVen["alias"];
        }
        $data["val"] = $alias;
        echo json_encode($data);
        break;
    case "ActualizaInv":
        $Isla = $request->getAttribute("IslaPos");
        $Vl = $request->getAttribute("posicion");
        error_log($request->getAttribute("Check"));
        $CheckVal = $request->getAttribute("Check") === "true" ? "Si" : "No";
        $Update = "UPDATE man SET inventario = '$CheckVal' WHERE isla_pos = $Isla AND posicion = '$Vl'";
        error_log($Update);
        if ($NVen = $connection->query($Update)) {

            $data["val"] = "Registro actualizado de inventario ($CheckVal)";
        }
        echo json_encode($data);
        break;
}

