<?php

include_once ("../../softcoatl/SoftcoatlHTTP.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$connection = utils\IConnection::getConnection();
$jsonString = array();
$cargas = trim($sanitize->sanitizeString("capturas"));
$jsonString["Response"] = false;

if ($sanitize->sanitizeString("op") == 2) {
    if ($request->getAttribute("VolumenDocumentado") > 0) {
        if (!strpos($request->getAttribute("VolumenDocumentado"), ",")) {
            $Update = "UPDATE cargas SET vol_doc = '" . $request->getAttribute("VolumenDocumentado") . "' WHERE id = " . $request->getAttribute("Id");
            if ($connection->query($Update)) {
                echo json_encode("Registro actualizado con exito! Id no." . $request->getAttribute("Id"));
            } else {
                echo "Error: favor de notificar a soporte" . $connection->error;
            }
        } else {
            echo "Error tu dato contiene una coma, favor de quitarla";
        }
    } else {
        echo "Favor de ingresar valores reales Ejemplo : 19950.50";
    }
} else {
    $Qry0 = "SELECT tanque,producto FROM cargas WHERE id IN ($cargas) AND entrada = 0 ORDER BY id DESC;";

    $rows1 = $connection->query($Qry0);
    $Rst = true;
    $vale = true;
    $prd = "";
    foreach ($rows1 as $vl) {
        if ($vale) {
            $var = $vl["tanque"];
            $prd = $vl["producto"];
        }
        if ($Rst) {
            $Rprb = $prd === $vl["producto"] ? true : false;
            $Rst = $var == $vl["tanque"] ? true : false;
        }
        $vale = false;
        $var = $vl["tanque"];
    }
    if (($Rprb || $Rst) && $cargas != "") {
        if ($request->getAttribute("op") == 1) {
            $query = "SELECT * FROM cargas WHERE id IN ($cargas) AND entrada = 0 ORDER BY id DESC;";
            $rows = $connection->query($query);
            if ($rows->num_rows > 1) {
                foreach ($rows as $rg) {
                    $Fecha = date("Y-m-d H:i:s");
                    $params = Array($rg[id]);
                    $query = "SELECT tanque,clave_producto as producto,t_inicial,t_final,
                            vol_inicial,vol_final,fecha_insercion as fechae,aumento,fecha_fin 
                            FROM cargas WHERE id = " . $rg["id"] . ";";
                    $rows = $connection->query($query);
                    foreach ($rows as $row) {
                        $Car = $row;
                        $query1 = "INSERT INTO me 
                            (tanque,fecha,fechae,proveedor,producto,status,vol_inicial,vol_final,
                            terminal,clavevehiculo,documento,carga,fechafac,tipo,cuadrada,incremento,
                            horaincremento,volumenfac,entcombustible,facturas,preciou,importefac,
                            t_final) 
                            VALUES
                            (" . $Car["tanque"] . ",'$Fecha','$Fecha',1,'" . $Car["producto"] . "','Cerrada'," . $Car["vol_inicial"] . "
                            ," . $Car["vol_final"] . ",'SD','SD','Jarreo'," . $rg["id"] . ",'$Fecha','Jarreo',1,'" . $Car["aumento"] . "',
                            '" . $Car["fecha_fin"] . "', 0, 0, 0, 0, 0, 0)";
                        $FechaActual = date("m");
                        $SS = explode(" ", $Car["fechae"]);
                        $DateTime = date("m", strtotime($SS[0]));
                        if ($DateTime < $FechaActual) {
                            $DateTime = DateTime::createFromFormat('Y-m-d', $SS[0]);
                            $newDatef = $DateTime->format('Y-m-t');
                            $Insert = "INSERT INTO  resumen_reporte_sat (fecha,reporte,etiqueta,valor,producto) "
                                    . "VALUES ('$newDatef','M','Se une carga " . $rg["id"] . " desde Omicrom',"
                                    . "'" . $Car["aumento"] . "','" . $Car["producto"] . "')";
                            $connection->query($Insert);
                        }


                        $connection->query($query1);
                        error_log($connection->error);
                        $cId = $connection->insert_id;
                    }
                    $query2 = "UPDATE cargas SET entrada = $cId,tipo = 1 WHERE id = " . $rg["id"] . ";";
                    error_log($query2);
                    $connection->query($query2);
                }


                $query = "INSERT INTO cargas 
                (tanque, producto, clave_producto, t_inicial, t_final, fecha_inicio, fecha_fin,
                vol_inicial, vol_final,  fecha_insercion, inicia_carga, finaliza_carga, aumento,tcAumento) 
                SELECT tanque,producto,clave_producto,t_inicial,t_final,MIN(fecha_inicio) fecha_inicio,MAX(fecha_fin) fecha_fin,
                MIN(vol_inicial) vol_inicial,MAX(vol_final) vol_final,MAX(fecha_insercion) fecha_insercion,
                MIN(inicia_carga) inicia_carga,MAX(finaliza_carga) finaliza_carga,SUM(aumento) aumento,SUM(tcAumento) tcAumento
                FROM cargas WHERE id IN ($cargas);
                ";
                $connection->query($query);

                $id = $connection->insert_id;

                $Msj = "Se ha creado un solo movimiento, la entrada es: $id";
            } else {
                $Msj = "No se realizo ningun movimiento!!";
            }
            echo json_encode("Cargas unidas con exito!");
        }
    } elseif ($cargas === "") {
        echo "¡Favor de ingresar algun valor!";
    } else {
        echo "Las cargas necesitan ser del mismo tanque!<br> Favor de verificar con Soporte";
    }
}
exit();
