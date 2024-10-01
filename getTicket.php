<?php

session_start();
include_once ("libnvo/lib.php");

$request = com\softcoatl\utils\HTTPUtils::getRequest();
$connection = com\softcoatl\utils\IConnection::getConnection();
$jsonString = array();
$usuarioSesion = getSessionUsuario();

//error_log(print_r($request, TRUE));

if ($request->hasAttribute("Ticket")) {
    $Ticket = $request->getAttribute("Ticket");
    $query = "SELECT rm.corte,ROUND(rm.importe,2) importe,rm.posicion,man.isla_pos,rm.cliente,cli.tipodepago,cli.nombre, rm.tipo_venta,rm.descuento,pesos
              FROM rm,man,cli 
              WHERE 1=1 AND rm.posicion = man.posicion AND rm.cliente = cli.id AND rm.id = " . $Ticket;

    if (($result = $connection->query($query)) && ($row = $result->fetch_array())) {
        $jsonString["corte"] = $row["corte"];
        $jsonString["importe"] = round($row["pesos"] - $row["descuento"], 3);
        $jsonString["isla_pos"] = $row["isla_pos"];
        $jsonString["posicion"] = $row["posicion"];
        $jsonString["tipo"] = $row["tipodepago"];
        $jsonString["cliente"] = $row["cliente"];
        $jsonString["nombre"] = $row["nombre"];
        $jsonString["tipo_venta"] = $row["tipo_venta"];
        $jsonString["descuento"] = $row["descuento"];
    }
}

if ($request->hasAttribute("InvIslaPos")) {
    $cId = $request->getAttribute("InvIslaPos");
    $query = "SELECT * FROM invd WHERE 1=1 AND invd.idnvo = " . $cId;

    if (($result = $connection->query($query)) && ($row = $result->fetch_array())) {
        $jsonString["minimo"] = $row["minimo"];
        $jsonString["maximo"] = $row["maximo"];
        $jsonString["isla_pos"] = $row["isla_pos"];
    }
}

if ($request->hasAttribute("Nip")) {
    $nip = $request->getAttribute("Nip");
    $folio = $request->getAttribute("Folio");
    $query = "SELECT * FROM ven WHERE 1=1 AND ven.id >= 50 AND nip = '$nip' LIMIT 1;";
    //error_log($query);
    if (($result = $connection->query($query)) && ($row = $result->fetch_array()) && !empty($row["nip"])) {
        $jsonString["success"] = true;
        BitacoraDAO::getInstance()->saveLogSn($usuarioSesion->getNombre(), "ADM", "CONSULTA DE TICKET PARA IMPRESION, VENDEDOR: " . $row["id"] . " FOLIO: " . $folio);
    } else {
        $jsonString["false"] = true;
        BitacoraDAO::getInstance()->saveLogSn($usuarioSesion->getNombre(), "ADM", "CONSULTA DE TICKET FALLIDO PARA FOLIO: " . $folio);
    }
}

if ($request->hasAttribute("Codigos")) {
    $SCliente = $request->getAttribute("Cliente");
    $Cliente = strpos($SCliente, "|") > 0 ? trim(substr($SCliente, 0, strpos($SCliente, "|"))) : trim($SCliente);
    $selectCodigos = "
                SELECT id, CONCAT(codigo, ' | ', TRIM(impreso), ' | ', TRIM(descripcion) , ' | ', TRIM(placas)) descripcion
                FROM unidades WHERE cliente = '$Cliente' AND LOWER(estado) = 'a'
                ORDER BY impreso";
    $jsonString = array();
    if (($result = $connection->query($selectCodigos))) {
        while ($row = $result->fetch_array()) {
            $jsonString[] = array("descripcion" => $row["descripcion"]);
        }
    }
}

if ($jsonString == null) {
    error_log(json_last_error());
}

echo json_encode($jsonString);

