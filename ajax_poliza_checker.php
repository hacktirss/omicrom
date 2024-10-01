<?php

include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

try {
    $mysqli = iconnect();
    $request = utils\HTTPUtils::getRequest();

    $jsonResultPagos = array();
    $jsonResultCorte = array();
    $varSDate = "sDate";

    if ($request->hasAttribute($varSDate) && !empty($request->getAttribute($varSDate))) {

        $sDate = $request->getAttribute($varSDate);

        $query = "SELECT 
                    egr.clave, 
                    egr.concepto, 
                    egr.importe, 
                    IFNULL(bancos.banco, 'PAGO_SIN_REFERENCIA') banco_nombre, 
                    IFNULL(bancos.cuenta, 'PAGO_SIN_REFERENCIA') banco_cuenta
                FROM ct
                    JOIN egr ON egr.corte = ct.id AND DATE( ct.fecha ) = DATE('" . $sDate . "') 
                    LEFT JOIN bancos ON bancos.id = egr.clave";

        error_log($query);

        $result = $mysqli->query($query);

        if (!$result) {
            die('Invalid query: ' . $query . ' ' . $mysqli->error);
        }

        while ($rg = $result->fetch_array()) {
            $jsonResultPagos[] = $rg;
        }

        $query = "SELECT DISTINCT 
                ct.id, 
                ct.concepto, 
                tur.descripcion, 
                ct.status, 
                ct.statusctv
            FROM ct
            JOIN tur ON tur.turno = ct.turno
            WHERE DATE(ct.fecha) = DATE('" . $sDate . "' )";

        error_log($query);

        $result2 = $mysqli->query($query);

        if (!$result2) {
            die('Invalid query: ' . $query . ' ' . mysql_error());
        }

        while ($rg = $result2->fetch_array()) {
            $jsonResultCorte[] = $rg;
        }

        if ($mysqli) {
            $mysqli->close();
        }
    }
} catch (Exception $ex) {
    error_log("Error en poliza checker: " . $ex->getMessage());
} finally {
    $jsonString = json_encode(array('pagos' => $jsonResultPagos, 'cortes' => $jsonResultCorte));

    error_log($jsonString);

    if ($jsonString == null) {
        error_log(json_last_error());
    }

    echo $jsonString;
}
