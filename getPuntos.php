<?php

/**
 * Description of getPuntos
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Ayala Gonzalez Alejandro
 * @version 1.0
 * @since ago 2023
 */
include_once ("softcoatl/SoftcoatlHTTP.php");
include_once ('data/BonificacionDAO.php');

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$bonificacionDAO = new BonificacionDAO();
/*
 * V1 .- La acumulación se basa en las ventas asignadas a los clientes
 * V2.- La acumulación se basa en la funcion tipo_monedero() de mysql tipos acumulativo "A"
 */
switch ($request->getAttribute("Op")) {
    case "ObtenPuntos":
        /* Version 1 */
        $Msj = $bonificacionDAO->calculaBonificacionClientes_vPuntos($request->getAttribute("Cliente"), $request->getAttribute("Producto"), "Consulta");

        $jsonString["InvPuntos"] = $Msj["PuntosProducto"];
        $jsonString["Puntos"] = $Msj["Puntos"];
        $jsonString["puntosConsumidos"] = $Msj["PuntosConsumidos"];
        break;
    case "CalculaBonificacion":
        /* Version 2 */
        $jsonString["Html"] = $bonificacionDAO->calculaBonificacion($request->getAttribute("CntPuntos"), $request->getAttribute("Ticket"));
        break;
    case "IngresaBonificacion":
        /* Version 2 */
        $Cc = $bonificacionDAO->ingresaBonificacion($request->getAttribute("CntPuntos"), $request->getAttribute("IdUnidad"), $request->getAttribute("Ticket"));
        $jsonString["Html"] = $Cc["Html"];
        $jsonString["Return"] = $Cc["Return"];
        break;
    case "AcumulaPuntosV2":
        $Msj = $bonificacionDAO->acumulaPuntos($request->getAttribute("Ticket"), $request->getAttribute("ClaveUnidad"));
        $jsonString["Html"] = $Msj;
        break;
    case "BonificaAditivo":
        $Cc = $bonificacionDAO->obtenBeneficioEnProductos($request->getAttribute("IdProducto"), $request->getAttribute("CntPuntos"), $request->getAttribute("IdUnidad"));
        $jsonString["Html"] = $Cc["Html"];
        $jsonString["Return"] = $Cc["Return"];
        break;
    case "InicializaPuntos":
        $Msj = $bonificacionDAO->inicializaPuntos($request->getAttribute("IdUnidad"), $request->getAttribute("Puntos"));
        break;
}
echo json_encode($jsonString);
