<?php

header("Cache-Control: no-cache,no-store");
include_once ("libnvo/lib.php");
include_once ("data/RelacionCfdiDAO.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$jsonString = Array();
$jsonString["success"] = false;
$jsonString["message"] = "Sin proceso registrado";
$Op = $request->getAttribute("Op");
/* Archivo JS para estas vistas */
$Html = '<script type="text/javascript" src="getPagosDif.js"></script>';
switch ($Op) {
    case "Visor":
        $Html .= '
        <div id="ScrollM"><table class="paginador" style="width:100%;margin-top:10px;margin-bottom:10px;">
            <tr style="background-color:#E1E1E1;font-size:15px;">
                 <th>Relacionado</th><th>Relacion</th><th style="width:150px;">Importe</th><th class="Facturado">Edit</th><th class="Facturado">Eliim</th>
            </tr>
            <tbody>
        ';
        $RelacionesCfdi = "SELECT * FROM relacion_cfdi WHERE id_fc = '" . $request->getAttribute("IdFactura") . "'";
        $RowCfdi = utils\IConnection::getRowsFromQuery($RelacionesCfdi);
        foreach ($RowCfdi as $row) {
            $Html .= '<tr>
                <td> ' . $row["uuid_relacionado"] . ' </td>
                <td> ' . $row["tipo_relacion"] . ' </td>
                <td style="text-align:right;"> ' . number_format($row["importe"], 2) . ' </td>
                <td style="text-align:center;" class="Facturado"><i class="fa fa-pencil-square EditaAbonos" aria-hidden="true" data-id="' . $row["id"] . '"></i></td>
                <td style="text-align:center;" class="Facturado"><i class="fa fa-trash EliminaAbono" aria-hidden="true" data-id="' . $row["id"] . '"></i></td>
            </tr>';
            $TT += $row["importe"];
        }
        $Html .= "<tr>"
                . "<td colspan='3' style='text-align:right;'>" . number_format($TT, 2) . "</td><td colspan='2'  class='Facturado'></td>"
                . "</tr>
                </tbody>
                <input type='hidden' name='TtSuma' id='TtSuma' value='$TT'>";
        $Html .= "</table>";
        $ImpPago = "SELECT importe FROM fc WHERE id = '" . $request->getAttribute("CliHd") . "'";
        $ImpP = utils\IConnection::execSql($ImpPago);
        $Sig = true;
        if ($ImpP["importe"] == $TT) {
            $Html .= "Importe Cuadrado";
            $Sig = false;
        }
        $Html .= '<table  class="paginador" style="width:100%;"><tr style="background-color:#E1E1E1;font-size:15px;"><th>Disponible</th><th>Importe</th><th>Restante</th><th class="Facturado"></th></tr><tbody>';
        $Cliente = strval($request->getAttribute("CliHd"));
        $Sql = "SELECT * FROM (SELECT p.id,p.uuid uuidpc,p.serie,p.importe,IFNULL(rc.uuid,'-') uuid,
            p.importe - SUM(IFNULL(rc.importe,0)) res,IFNULL(rc.importe,'Nuevo') rs
            FROM pagos p LEFT JOIN relacion_cfdi rc ON rc.uuid_relacionado=p.uuid WHERE p.status_pago=3 AND p.uuid <> '-----' 
            AND cliente = $Cliente 
            group by p.uuid ) Sub WHERE res > 0 AND (importe > res || rs ='Nuevo');";
        error_log($Sql);
        $RowSql = utils\IConnection::getRowsFromQuery($Sql);
        foreach ($RowSql as $row) {
            $Html .= '<tr><td style="text-align:left;">Folio : ' . $row["id"] . ' Serie : ' . $row["serie"] . '</td><td style="text-align:right;">' . number_format($row["total"], 2) . '</td>'
                    . '<td style="text-align:right;">' . number_format($row["res"], 2) . '</td>'
                    . '<td style="text-align:center;" class="Facturado"><i class="fa fa-arrow-circle-up GuardaFactura" aria-hidden="true" data-id="' . $row["id"] . '" data-uuid="' . $row["uuidpc"] . '" data-total="' . $row["res"] . '"></i></td>'
                    . '</tr>';
        }
        $Html .= "</tbody></table></div>";

        $jsonString["Html"] = $Html;
        break;
    case "AddFactura":
        $RelacionCfdiDAO = new RelacionCfdiDAO();
        $RelacionCfdiVO = new RelacionCfdiVO();
        $sql = "SELECT serie,folio FROM fc WHERE id = " . $request->getAttribute("IdFactura");
        $Serie = utils\IConnection::execSql($sql);
        $RelacionCfdiVO->setSerie($Serie["serie"]);
        $RelacionCfdiVO->setFolio_factura($Serie["folio"]);
        $RelacionCfdiVO->setOrigen(1);
        $RelacionCfdiVO->setUuid("-----");
        $RelacionCfdiVO->setUuid_relacionado($request->getAttribute("UuidPago"));
        $RelacionCfdiVO->setTipo_relacion("07");
        $RelacionCfdiVO->setImporte($request->getAttribute("Total"));
        $RelacionCfdiVO->setId_fc($request->getAttribute("IdFactura"));
        if ($RelacionCfdiDAO->create($RelacionCfdiVO)) {
            
        }
        break;
    case "CambioImporte" :
        $RelacionCfdiDAO = new RelacionCfdiDAO();
        $RelacionCfdiVO = new RelacionCfdiVO();
        $RelacionCfdiVO = $RelacionCfdiDAO->retrieve($request->getAttribute("IdRelacion"));
        $RelacionCfdiVO->setImporte($request->getAttribute("Importe"));
        $Sql = "SELECT * FROM pagos WHERE uuid = '" . $RelacionCfdiVO->getUuid_relacionado() . "'";
        $rsImporte = utils\IConnection::execSql($Sql);
        error_log($rsImporte["importe"] . " <= " . $RelacionCfdiVO->getImporte());
        if ($rsImporte["importe"] >= $RelacionCfdiVO->getImporte()) {
            $RelacionCfdiDAO->update($RelacionCfdiVO);
            $jsonString["Msj"] = "Registro actualizado con exito";
            $jsonString["sts"] = true;
        } else {
            $jsonString["Msj"] = "Error el importe ingresado es mayor al pago";
            $jsonString["sts"] = false;
        }

        break;
    case "EliminaRelacion":
        $RelacionCfdiDAO = new RelacionCfdiDAO();
        $RelacionCfdiDAO->remove($request->getAttribute("IdRelacion"));
        break;
}

echo json_encode($jsonString);
