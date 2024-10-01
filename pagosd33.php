<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "services/PagosdService.php";

$request = utils\HTTPUtils::getRequest();
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$pagoVO = new PagoVO();
$clienteVO = new ClientesVO();
if (is_numeric($cVarVal)) {
    $pagoVO = $pagoDAO->retrieve($cVarVal);
    $clienteVO = $clienteDAO->retrieve($pagoVO->getCliente());
}

$BuscaPPD = "SELECT id FROM pagose WHERE id = " . $pagoVO->getId() . " LIMIT 1";
$IniConCP = utils\IConnection::execSql($BuscaPPD);

$paginador = new Paginador(5, "", "", "", "", "idfae", "idfae", "", "ASC", 0, "REGEX", "pagos.php");
//echo print_r($request, true);
if ($IniConCP["id"] > 0) {
    utils\HTTPUtils::setSessionValue("ComplementoPago", 1);
} else {
    if ($request->getAttribute("criteria") == "ini") {
        utils\HTTPUtils::setSessionValue("ComplementoPago", 0);
    } else if ($request->getAttribute("PPD") == "Pago Diferido") {
        utils\HTTPUtils::setSessionValue("ComplementoPago", 1);
    }
}
if ($clienteVO->getTipodepago() === TiposCliente::CREDITO || (utils\HTTPUtils::getSessionValue("ComplementoPago") == 1 && $clienteVO->getTipodepago() !== TiposCliente::TARJETA && $clienteVO->getTipodepago() !== TiposCliente::MONEDERO)) :
    $session = new OmicromSession("pagose.folio", "pagose.folio", $nameVariableSession);
//    echo print_r($session, true);
    $Id = 48;
    $Titulo = "Facturas que afecta el presente pago";

    $paginador = new Paginador($Id,
            "pagose.idnvo,pagose.total_nc,pagose.importe",
            ", (
                SELECT p.id,p.idnvo,p.factura,p.fecha,p.total,p.importe,p.abonos,IFNULL(nc.total_nc,0) total_nc,p.folio
                FROM( 
                    SELECT pagose.id, pagose.idnvo, pagose.factura, fc.folio, fc.fecha, fc.total, pagose.importe, sub.abonos
                    FROM 
                    pagose,fc,(
                        SELECT SUM(pagose.importe) abonos,fc.id 
                        FROM pagose,fc 
                        WHERE pagose.factura = fc.id AND fc.cliente = '" . $pagoVO->getCliente() . "'
                        GROUP BY pagose.factura
                    ) AS sub
                    WHERE
                    pagose.factura = fc.id
                    AND pagose.factura = sub.id
                    GROUP BY pagose.idnvo
                ) p
                LEFT JOIN(
                    SELECT factura,total total_nc FROM nc WHERE nc.status = 1  AND nc.cliente = '" . $pagoVO->getCliente() . "'
                ) nc ON p.factura = nc.factura
                WHERE p.id = '" . $cVarVal . "' 
                GROUP BY p.factura,IFNULL(nc.total_nc,0) DESC
            ) pagose",
            "GROUP BY pagose.factura",
            "pagos.id = pagose.id AND pagos.id = '" . $cVarVal . "'",
            $session->getSessionAttribute("sortField"),
            $session->getSessionAttribute("criteriaField"),
            utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
            strtoupper($session->getSessionAttribute("sortType")),
            $session->getSessionAttribute("page"),
            "REGEXP",
            "pagos.php");

elseif ($clienteVO->getTipodepago() === TiposCliente::TARJETA || $clienteVO->getTipodepago() === TiposCliente::MONEDERO) :
    $session = new OmicromSession("rm.id", "rm.id", $nameVariableSession);

    $Id = 121;
    $Titulo = "Despachos que competen al presente pago";

    $conditions = "rm.pagado = '$cVarVal'";

    $from = "(SELECT 
            1 tipo, man.isla_pos, rm.corte, rm.id, rm.posicion, com.descripcion producto, 
            rm.fin_venta fecha, rm.cliente, cli.alias, rm.volumen, rm.pagoreal, rm.pagado, rm.uuid
            FROM rm
            LEFT JOIN com ON rm.producto = com.clavei AND com.activo = 'Si'
            LEFT JOIN cli ON rm.cliente = cli.id 
            LEFT JOIN man ON man.posicion = rm.posicion AND man.activo = 'Si' 
            WHERE  
            rm.cliente in ( select cliente from pagos where id = '$cVarVal' )
            and rm.id in ( select referencia from pagose where id = '$cVarVal'  )
            and 
            cli.tipodepago IN ('Tarjeta', 'Monedero')
            AND rm.pagado > 0
            UNION ALL
            SELECT 2 tipo, man.isla_pos, vt.corte, vt.id, vt.posicion, vt.descripcion producto, 
            vt.fecha, vt.cliente, cli.alias, vt.cantidad volumen, vt.total pagoreal, vt.pagado, vt.uuid
            FROM vtaditivos vt
            LEFT JOIN cli ON vt.cliente = cli.id 
            LEFT JOIN man ON man.posicion = vt.posicion AND man.activo = 'Si' 
            WHERE TRUE 
            AND cli.tipodepago IN ('Tarjeta', 'Monedero') 
            AND vt.pagado > 0 AND vt.referencia = 0 AND vt.tm = 'C'
        ) rm";

    $paginador = new Paginador($Id,
            "rm.tipo, rm.uuid, IFNULL(GROUP_CONCAT(vt.id),'') aceites_ligados, pagose.idnvo",
            "LEFT JOIN vtaditivos vt ON vt.referencia = rm.id AND vt.tm = 'C' AND rm.tipo = 1 
             LEFT JOIN pagose ON rm.id = pagose.referencia AND rm.tipo = pagose.tipo AND pagose.id = '$cVarVal' ",
            "GROUP BY rm.id, rm.tipo",
            $conditions,
            $session->getSessionAttribute("sortField"),
            $session->getSessionAttribute("criteriaField"),
            utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
            strtoupper($session->getSessionAttribute("sortType")),
            $session->getSessionAttribute("page"),
            "REGEXP",
            "pagos.php",
            $from);
//error_log($paginador->getQueryPage());
endif;

$op = $request->getAttribute("op");
$self = utils\HTTPUtils::self();
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <?= $lBd ? "<meta http-equiv=\"refresh\" content=\"2;url=pagosd33.php?op=Genera\" />" : "" ?>
        <?= $lBd_2 ? "<meta http-equiv=\"refresh\" content=\"2;url=pagosd33.php?op=generaReciboAnticipo\" />" : "" ?>
        <?= $lBd_4 ? "<meta http-equiv=\"refresh\" content=\"2;url=pagosd33.php?op=generaNotaCredito&UsoCfdi=" . $request->getAttribute("UsoCfdi") . "\" />" : "" ?>
        <?= $lBd_5 ? "<meta http-equiv=\"refresh\" content=\"2;url=pagosd33.php?op=generaComplementoCli&total=$importe\" />" : "" ?>

        <style>
            .casilla{
                border: none;
                text-align: center;
            }
        </style>
        <script type="text/javascript">
            function winuni1(url) {
                windowUni = window.open(url, "filtros", "status=no,tollbar=yes,scrollbars=yes,menubar=no,width=790,height=550,left=250,top=80");
            }
            function alertaG02(url) {
                alertTextValidation("¿Desea generar su nota de credito con uso CFDI <br> G02 (Devoluciones, descuentos o bonificaciones)?",
                        "", "Timbrar", "2023-05-08 15:36:55", true, "question", 40000, true, "<?= date("Y-m-d H:i:s") ?>",
                        1, 19, 'Fecha minima, 71 hrs atras <?= date("Y-m-d H:i", strtotime(date("Y-m-d H:i") . "-  71 hour")) ?> '
                        + '<select id="swal-input1" class="swal2-input">'
                        + '<option value="000">Default</option>'
                        + '<option value="G02">Devoluciones, descuentos o bonificaciones</option>'
                        + '</select>'
                        + '<input id="swal-input2" class="swal2-input"  value="<?= date("Y-m-d") ?>T<?= date("H:i") ?>"  max="<?= date("Y-m-d") ?>T<?= date("H:i") ?>" min="<?= date("Y-m-d", strtotime(date("Y-m-d") . "- 3 day")) ?>T<?= date("H:i", strtotime(date("H:i") . "+ 1 hour")) ?>" type="datetime-local"> ', "");
            }
            $(document).ready(function () {
                console.log("UUID RE" + $("#UuidRelacionado").val());
                if (parseInt($("#UuidRelacionado").val()) === 1) {
                    $(".ProcesoDif").hide();
                    $("#RelacionCfdi").show();
                    $(".muestra_complemento").hide();
                } else {
                    $("#RelacionCfdi").hide();
                    $(".ProcesoDif").show();
                }
            });
        </script>
    </head>

    <body>
        <?php BordeSuperior(); ?>

        <?php if ($ppac->getPruebas() == '1') { ?>
            <div style="background-color: red; color: white; text-align:center; font-family: Helvetica, Arial, Verdana, Tahoma, sans-serif; font-size:14px; font-weight:bold;">
                ALERTA FACTURANDO EN MODO DE DEMOSTRACIÓN
            </div>
        <?php } ?>
        <?php
        $BuscaExistencia = "SELECT * FROM relacion_cfdi WHERE  uuid_relacionado = '" . $pagoVO->getUuid() . "'";
        $rsB = utils\IConnection::execSql($BuscaExistencia);
        if ($rsB["uuid_relacionado"] <> "-----" && $rsB["uuid_relacionado"] <> "") {
            echo $rsB["uuid"] === "-----" ? "<input type='hidden' name='UuidRelacionado' id='UuidRelacionado' value='0'>" : "<input type='hidden' name='UuidRelacionado' id='UuidRelacionado' value='1'>";
        }
        ?>
        <div id="DatosEncabezado">
            <table aria-hidden="true">
                <tr>
                    <td> &nbsp; Id: <?= $pagoVO->getId() ?> &nbsp;| <?= $clienteVO->getRfc() ?>  | <strong> <?= $clienteVO->getTipodepago() ?> </strong>&nbsp; </td>
                    <td> &nbsp; <?= $pagoVO->getCliente() ?> <?= substr($clienteVO->getNombre(), 0, 60) ?> &nbsp; </td>
                    <td> &nbsp; <?= $pagoVO->getFecha() ?> &nbsp; </td>
                </tr>
                <tr>
                    <td> &nbsp; Concepto: <?= $pagoVO->getConcepto() ?> </td>
                    <td><strong> &nbsp; Importe pagado por el cliente: <?= number_format($pagoVO->getImporte(), 2) ?></td>
                    <td class='seleccionar'><strong> &nbsp; Saldo por aplicar: <?= number_format($pagoVO->getAplicado(), 2) ?></td>
                </tr>
            </table>
        </div>


        <?php if ($clienteVO->getTipodepago() === TiposCliente::PREPAGO && utils\HTTPUtils::getSessionValue("ComplementoPago") == 0) { ?>
            <form name="form1" method="get" action="pagosd33.php">
                <table style="width: 100%;text-align: center;border: 2px solid gray;" aria-hidden="true">
                    <tr>
                        <td>
                            <table id="oculta_tabla" style="width: 99%;text-align: center;" class="texto_tablas" aria-hidden="true">
                                <thead style="background-color: #E1E1E1; text-align: center;" class="texto_tablas">
                                    <tr style="font-weight: bold;">
                                        <td>Paso 1</td>
                                        <td>Paso 2</td>
                                        <td class="ProcesoDif">Paso 3</td>
                                        <td class="ProcesoDif">Paso 4</td>
                                        <td class="muestra_complemento">Paso 5</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="background-color: #E1E1E1; text-align: center;" class="texto_tablas">
                                        <?php if ($pagoVO->getStatus_pago() == StatusPagoPrepago::SIN_LIBERAR) { ?>
                                            <td style="background-color: #ffdd00"> 
                                                <a href="javascript:confirmar('¿Desea liberar el saldo?', '?op=ac');" title="el cliente posteriormente pedira su factura en base a sus cargas" class="seleccionar">
                                                    <strong>Liberar saldo al cliente</strong>
                                                </a>
                                            </td>
                                        <?php } else { ?>
                                            <td style="background-color: #acecaa">  
                                                <?php
                                                if ($pagoVO->getSaldoFavor() == 1) {
                                                    ?>
                                                    <strong>Saldo a favor</strong>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <strong>Saldo Liberado</strong>
                                                    <?php
                                                }
                                                ?>
                                            </td>
                                        <?php } ?> 

                                        <?php if ($op === "generaAnticipo") { ?>
                                            <td style="background-color: #ffdd00; text-align: center;">
                                                <p class="texto_tablas">
                                                    <i class="fa fa-spinner fa-pulse fa-4x" aria-hidden="true"></i>
                                                    <span class="sr-only">Loading...</span>
                                                    <br/>favor de esperar...
                                                </p>
                                            </td>
                                        <?php } else { ?>
                                            <?php if ($pagoVO->getStatus_pago() == StatusPagoPrepago::LIBERADO) { ?>
                                                <td style="background-color: #ffdd00">
                                                    <a class="seleccionar" id="GeneraReciboAnticipo" href="#">
                                                        <strong>Generar Recibo de Anticipo</strong>
                                                    </a>
                                                </td>
                                                <?php
                                                $RepartoSaldo = true;
                                            } else if ($pagoVO->getStatus_pago() > StatusPagoPrepago::LIBERADO) {
                                                ?>
                                                <td style="background-color: #acecaa">        
                                                    <strong>Recibo de Anticipo</strong><br/>

                                                    <?php if ($pagoVO->getId() > $pagoVO->getRelacion() AND $pagoVO->getStatus_pago() <= 5 AND $pagoVO->getRelacion() <> 0) { ?>
                                                    <?php } else { ?>
                                                        <?php if ($pagoVO->getUuid() !== PagoDAO::SIN_TIMBRAR) { ?>
                                                            <a href=javascript:winuni1("enviafile.php?id=<?= $pagoVO->getUuid() ?>&type=pdf&formato=3") style="color: red;"><i class="fa icon fa-lg fa-file-pdf-o" aria-hidden="true"></i></a>
                                                            <a href=javascript:winuni1("enviafile.php?id=<?= $pagoVO->getUuid() ?>&type=xml") style="color: graytext;"><i class="fa icon fa-lg fa-file-code-o" aria-hidden="true"></i></a>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </td>
                                            <?php } else { ?>
                                                <td style="background-color: #ffdd00">
                                                    <strong>Recibo de Anticipo</strong><br/>
                                                </td>
                                                <?php
                                            }
                                        }
                                        ?>

                                        <?php if ($pagoVO->getStatus_pago() == StatusPagoPrepago::CON_ANTICIPO) { ?>
                                            <td style="background-color: #ffdd00" class="ProcesoDif">
                                                <a href="facturase.php?Boton=Agregar&Cliente=<?= $pagoVO->getCliente() ?>&Anticipo=<?= $pagoVO->getId() ?>" title="Generar Factura de consumos" class="seleccionar">
                                                    <strong>Generar Factura de Consumos</strong>
                                                </a>  
                                            </td>
                                            <?php
                                        } else if ($pagoVO->getStatus_pago() > StatusPagoPrepago::CON_ANTICIPO) {
                                            // Obtenemos el id de la factura 
                                            $cSqlFC = "SELECT uuid,total FROM fc WHERE relacioncfdi = " . $pagoVO->getId() . " AND tdoctorelacionado = 'ANT' AND uuid <> '-----' AND status = " . StatusFactura::CERRADO;
                                            $resultSqlFC = $mysqli->query($cSqlFC);
                                            $FC = $resultSqlFC->fetch_array();
                                            ?>
                                            <td style="background-color: #acecaa" class="ProcesoDif">
                                                <strong>Factura de Consumos</strong><br/>
                                                <?php if (!empty($FC['uuid']) && $FC['uuid'] !== PagoDAO::SIN_TIMBRAR) { ?>
                                                    <a href=javascript:winuni1('enviafile.php?id=<?= $FC['uuid'] ?>&type=pdf') style="color: red;"><i class="fa icon fa-lg fa-file-pdf-o" aria-hidden="true"></i></a>
                                                    <a href=javascript:winuni1('enviafile.php?id=<?= $FC['uuid'] ?>&type=xml') style="color: graytext;"><i class="fa icon fa-lg fa-file-code-o" aria-hidden="true"></i></a>
                                                <?php } ?>
                                            </td>
                                        <?php } else { ?>
                                            <td style="background-color: #ffdd00" class="ProcesoDif">
                                                <strong>Factura de Consumos</strong><br/>
                                            </td>
                                        <?php } ?>    


                                        <?php if ($op === "generaNota") { ?>
                                            <td style="background-color: #ffdd00; text-align: center;" class="ProcesoDif">
                                                <p class="texto_tablas">
                                                    <i class="fa fa-spinner fa-pulse fa-4x" aria-hidden="true"></i>
                                                    <span class="sr-only">Loading...</span>
                                                    <br/>favor de esperar...
                                                </p>
                                            </td>
                                        <?php } else { ?>    
                                            <?php if ($pagoVO->getStatus_pago() == StatusPagoPrepago::CON_FACTURA_CONSUMOS) { ?>
                                                <td style="background-color: #ffdd00" class="ProcesoDif">
                                                    <a href="javascript:alertaG02('?op=generaNota&busca=<?= $pagoVO->getId() ?>');" title="Generar el Reciboo de Anticipo al cliente de Prepago" class="seleccionar">
                                                        <strong>Generar Nota de Crédito</strong>
                                                    </a>
                                                </td>   
                                                <?php
                                            } else if ($pagoVO->getStatus_pago() > StatusPagoPrepago::CON_FACTURA_CONSUMOS) {
                                                $cSqlNC = " SELECT uuid FROM nc WHERE formadepago = '30' AND factura = '" . $pagoVO->getId() . "' AND uuid <> '-----'";
                                                $resultSqlNC = $mysqli->query($cSqlNC);
                                                $NC = $resultSqlNC->fetch_array();
                                                ?>
                                                <td style="background-color: #acecaa" class="ProcesoDif">
                                                    <strong>Nota de Credito</strong><br/>
                                                    <?php if (!empty($NC['uuid']) && $NC['uuid'] !== PagoDAO::SIN_TIMBRAR) { ?>
                                                        <a href=javascript:winuni1('enviafile.php?id=<?= $NC['uuid'] ?>&type=pdf'); style="color: red;"><i class="fa icon fa-lg fa-file-pdf-o" aria-hidden="true"></i></a>
                                                        <a href=javascript:winuni1('enviafile.php?id=<?= $NC['uuid'] ?>&type=xml'); style="color: graytext;"><i class="fa icon fa-lg fa-file-code-o" aria-hidden="true"></i></a>
                                                    <?php } ?>
                                                    <br>
                                                    <?php
                                                    if ($pagoVO->getImporte() > $FC["total"]) {
                                                        $Dif = $pagoVO->getImporte() - $FC["total"];
                                                        ?>
                                                        <a href="pagosd33.php?op=AddDif&IdPago=<?= $pagoVO->getId() ?>&Dif=<?= $Dif ?>">Crear nuevo pago por la diferencia de $<?= number_format($Dif, 2) ?></a>
                                                        <?php
                                                    } else if ($pagoVO->getSaldoFavor() > 1) {
                                                        ?>
                                                        <strong class="Acceso">
                                                            <a href="pagosd33.php?cVarVal=<?= $pagoVO->getSaldoFavor() ?>">Relación: <?= $pagoVO->getSaldoFavor() ?></a> 
                                                        </strong>
                                                        <?php
                                                    }
                                                    ?>
                                                </td>
                                            <?php } else { ?> 
                                                <td style="background-color: #ffdd00" class="ProcesoDif">
                                                    <strong>Nota de Crédito</strong><br/>
                                                </td>
                                            <?php } ?>  
                                            <?php
                                        }

                                        if ($op === "generaComplemento") {
                                            ?>
                                            <td style="background-color: #ffdd00; text-align: center;">
                                                <p class="texto_tablas">
                                                    <i class="fa fa-spinner fa-pulse fa-4x" aria-hidden="true"></i>
                                                    <span class="sr-only">Loading...</span>
                                                    <br/>favor de esperar...
                                                </p>
                                            </td>
                                            <?php
                                        } else {

                                            $selectFcRelacionado = "SELECT total FROM fc WHERE relacioncfdi = " . $pagoVO->getId() . " AND tdoctorelacionado = 'ANT' AND uuid <> '-----' AND  status = " . StatusFactura::CERRADO;
                                            $fcResult = utils\IConnection::execSql($selectFcRelacionado);

                                            $cSqlAnti = "SELECT id,uuid FROM pagos WHERE relacion = " . $pagoVO->getId();
                                            $Id_Anticipo = utils\IConnection::execSql($cSqlAnti);

                                            $total = $fcResult['total'] - $pagoVO->getImporte();

                                            if ($pagoVO->getImporte() < $fcResult['total'] && $pagoVO->getRelacion() == 0 && $pagoVO->getStatus_pago() == 5) {
                                                ?>
                                                <td  style="background-color: #ffdd00" class="muestra_complemento">
                                                    <a href="javascript:confirmar('¿Desea Generar Complemento de pago?', '?op=generaComplemento&total=<?= $total ?>');" title="Generar el Complemento de pago al cliente de Prepago" class="seleccionar">
                                                        <strong>Complemento de pago</strong>
                                                    </a>
                                                </td>   
                                                <?php
                                            } else if ($pagoVO->getRelacion() > 0) {
                                                ?>
                                                <td style="background-color: #acecaa" class="muestra_complemento">
                                                    <strong>Complemento de pago No.<?= $Id_Anticipo["id"] ?></strong><br/>
                                                    <?php if ($pagoVO->getId() > $pagoVO->getRelacion()) { ?>
                                                        <?php if (!empty($pagoVO->getUuid()) && $pagoVO->getUuid() !== PagoDAO::SIN_TIMBRAR) { ?>
                                                            <a href=javascript:winuni1('enviafile.php?id=<?= $pagoVO->getUuid() ?>&type=pdf'); style="color: red;"><i class="fa icon fa-lg fa-file-pdf-o" aria-hidden="true"></i></a>
                                                            <a href=javascript:winuni1('enviafile.php?id=<?= $pagoVO->getUuid() ?>&type=xml'); style="color: graytext;"><i class="fa icon fa-lg fa-file-code-o" aria-hidden="true"></i></a>
                                                        <?php } ?>
                                                    <?php } else { ?>
                                                        <?php if (!empty($Id_Anticipo['uuid']) && $Id_Anticipo['uuid'] !== PagoDAO::SIN_TIMBRAR) { ?>
                                                            <a href=javascript:winuni1('enviafile.php?id=<?= $Id_Anticipo['uuid'] ?>&type=pdf'); style="color: red;"><i class="fa icon fa-lg fa-file-pdf-o" aria-hidden="true"></i></a>
                                                            <a href=javascript:winuni1('enviafile.php?id=<?= $Id_Anticipo['uuid'] ?>&type=xml'); style="color: graytext;"><i class="fa icon fa-lg fa-file-code-o" aria-hidden="true"></i></a>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </td>
                                            <?php } else { ?> 
                                                <td class="muestra_complemento">
                                                    <strong>Complemento de pago</strong><br/>
                                                </td>
                                            <?php } ?>  
                                        <?php } ?>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr id="RelacionCfdi"> 
                        <td>
                            <div  style="text-align: center;width: 100%">
                                <table style="margin-left: 1%;width: 98%;"  class="paginador" summary="Facturas relacionadas">
                                    <tr style="border-bottom: 1px solid white">
                                        <th scope="col"></th>
                                        <th scope="col" colspan="3" style="padding-left: 16%;text-align: left">
                                            Factura(s)
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="col">Id: Serie - Folio</th>
                                        <th scope="col">UUID</th>
                                        <th scope="col">Importe</th>
                                        <th scope="col">N.C.</th>
                                    </tr>
                                    <?php
                                    $BuscaExistencia = "SELECT id_fc,rc.uuid,rc.importe,folio,fc.serie FROM relacion_cfdi rc LEFT JOIN fc ON fc.id=rc.id_fc WHERE  uuid_relacionado = '" . $pagoVO->getUuid() . "'";
                                    $rsB = utils\IConnection::getRowsFromQuery($BuscaExistencia);
                                    foreach ($rsB as $b) {
                                        $SqlNc = "SELECT * FROM  nc WHERE factura=" . $pagoVO->getId() . " AND relacioncfdi=" . $b["id_fc"];
                                        $NcSql = utils\IConnection::execSql($SqlNc);
                                        ?>
                                        <tr>
                                            <td><?= $b["id_fc"] ?>: <?= $b["serie"] ?>-<?= $b["folio"] ?></td> 
                                            <td> <?= $b["uuid"] ?> </td>
                                            <td style="text-align: right;padding-right: 5px;"><?= number_format($b["importe"], 2) ?></td>
                                            <td>
                                                <?php
                                                if ($NcSql["id"] > 0) {
                                                    ?>
                                                    <a href=javascript:winuni1('enviafile.php?id=<?= $NcSql['uuid'] ?>&type=pdf'); ><i class="fa fa-file-pdf-o fa-lg" aria-hidden="true" style="color: red;"></i></a>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <a data-totalfc="<?= $b["importe"] ?>" data-idfc="<?= $b["id_fc"] ?>" href="#" class="GeneraNcIndp">
                                                        Genera Nota Credito
                                                    </a>
                                                    <?php
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php
                                        $TtImp += $b["importe"];
                                    }
                                    ?>
                                    <tfoot>
                                        <tr style="border-top: 1px solid black;padding-top: 4px;">
                                            <td colspan="3" style="text-align: right;padding-right: 5px;">
                                                Total : <?= number_format($TtImp, 2) ?>
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
            </form>
            <?php
        } elseif ($clienteVO->getTipodepago() === TiposCliente::CREDITO || (utils\HTTPUtils::getSessionValue("ComplementoPago") == 1 && $clienteVO->getTipodepago() !== TiposCliente::TARJETA && $clienteVO->getTipodepago() !== TiposCliente::MONEDERO)) {
            if ($pagoVO->getStatus() === StatusPago::ABIERTO && (abs($pagoVO->getAplicado()) < 1) && !$error) {
                ?>
                <table style="width: 100%; border: 2px solid; padding: 2px;" aria-hidden="true">
                    <!-- Opciones para clientes de Credito -->         
                    <tr style="background-color: #E1E1E1; text-align: center;">
                        <td>
                            <a href="javascript:confirmar('¿Desea dar como cerrado el pago?','?op=Cerrar');" title="Con esto das por cerrado el pago"  class="seleccionar">
                                <strong>Cerrar Pago</strong>
                            </a>
                        </td>
                        <td>
                            <a href="javascript:confirmar('¿Desea timbrar el recibo y dar como cerrado el pago?','?op=CerrarTimbrar&busca=<?= $pagoVO->getId() ?>');" title="Con esto timbras el recibo y das por cerrado el pago"  class="seleccionar">
                                <strong>Cerrar Pago y Timbrar Recibo Electronico de Pago</strong>
                            </a>
                        </td>
                    </tr>
                </table>
                <?php
            } elseif ($pagoVO->getStatus() === StatusPago::CERRADO && $pagoVO->getStatus_pago() < StatusPagoPrepago::CON_NOTA_CREDITO) {
                if ($op === "Timbrar" || $op === "CerrarTimbrar") {
                    ?>
                    <table style="width: 100%; border: 1px solid;" aria-hidden="true">
                        <tr style="background-color: #E1E1E1; text-align: center;">
                            <td>
                                <p class="texto_tablas">
                                    <i class="fa fa-spinner fa-pulse fa-4x" aria-hidden="true"></i>
                                    <span class="sr-only">Loading...</span>
                                    <br/>favor de esperar...
                                </p>
                            </td>
                        </tr>
                    </table>
                <?php } else if ($pagoVO->getStatusCFDI() != StatusPagoCFDI::CERRADO) {
                    ?>
                    <form name="form1" method="get" action="pagosd33.php">
                        <table style="width: 100%; border: 1px solid;" aria-hidden="true">
                            <tr style="background-color: #E1E1E1; text-align: center;">
                                <td>
                                    <a href="javascript:confirmar('¿Deseas timbrar este movimiento?', '?op=Timbrar');"  class="seleccionar">
                                        <strong>Timbrar Recibo Electronico de Pago</strong>
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </form>
                    <?php
                }
            }
        } elseif ($clienteVO->getTipodepago() === TiposCliente::MONEDERO && $variablesCorpVO->getValor() == 0) {
            if ($pagoVO->getStatus() === StatusPago::ABIERTO) {
                ?>
                <table style="width: 100%; border: 2px solid; padding: 2px;" aria-hidden="true">
                    <!-- Opciones para clientes de Credito -->         
                    <tr style="background-color: #E1E1E1; text-align: center;">
                        <td>
                            <a href="javascript:confirmar('¿Desea dar como cerrado el pago?','?op=Cerrar');" title="Con esto das por cerrado el pago"  class="seleccionar">
                                <strong>Cerrar Pago</strong>
                            </a>
                        </td>
                        <td>
                            <a href="pagosd33.php?op=CerrarGenerar" title="Con esto timbras el recibo y das por cerrado el pago"  class="seleccionar">
                                <strong>Cerrar Pago y Generar Factura Electronica</strong>
                            </a>
                        </td>
                    </tr>
                </table>
                <?php
            } elseif ($pagoVO->getStatus() === StatusPago::CERRADO && $pagoVO->getStatus_pago() < StatusPagoPrepago::CON_NOTA_CREDITO) {
                if ($op === "Timbrar" || $op === "CerrarTimbrar") {
                    ?>
                    <table style="width: 100%; border: 1px solid;" aria-hidden="true">
                        <tr style="background-color: #E1E1E1; text-align: center;">
                            <td>
                                <p class="texto_tablas">
                                    <i class="fa fa-spinner fa-pulse fa-4x" aria-hidden="true"></i>
                                    <span class="sr-only">Loading...</span>
                                    <br/>favor de esperar...
                                </p>
                            </td>
                        </tr>
                    </table>
                <?php } else if ($pagoVO->getStatusCFDI() != StatusPagoCFDI::CERRADO) {
                    ?>
                    <form name="form1" method="get" action="pagosd33.php">
                        <table style="width: 100%; border: 1px solid;" aria-hidden="true">
                            <tr style="background-color: #E1E1E1; text-align: center;">
                                <td>
                                    <a href="facturase.php?Boton=Agregar&Cliente=<?= $pagoVO->getCliente() ?>&Pago=<?= $pagoVO->getId() ?>"  class="seleccionar">
                                        <strong>Generar Factura Electronica</strong>
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </form>
                    <?php
                }
            }
        } elseif ($clienteVO->getTipodepago() === TiposCliente::MONEDERO && $variablesCorpVO->getValor() == 1) {
            if ($pagoVO->getStatus() === StatusPago::ABIERTO && abs($pagoVO->getAplicado()) < 1) {
                ?>
                <table style="width: 100%; border: 2px solid; padding: 2px;" aria-hidden="true">
                    <tr style="background-color: #E1E1E1; text-align: center;">
                        <td>
                            <a href="javascript:confirmar('¿Desea dar como cerrado el pago?','?op=Cerrar');" title="Con esto das por cerrado el pago"  class="seleccionar">
                                <strong>Cerrar Pago</strong>
                            </a>
                        </td>
                    </tr>
                </table>
                <?php
            }
        } elseif ($clienteVO->getTipodepago() === TiposCliente::TARJETA) {
            if ($pagoVO->getStatus() === StatusPago::ABIERTO && abs($pagoVO->getAplicado()) < 1) {
                ?>
                <table style="width: 100%; border: 2px solid; padding: 2px;" aria-hidden="true">
                    <tr style="background-color: #E1E1E1; text-align: center;">
                        <td>
                            <a href="javascript:confirmar('¿Desea dar como cerrado el pago?','?op=Cerrar');" title="Con esto das por cerrado el pago"  class="seleccionar">
                                <strong>Cerrar Pago</strong>
                            </a>
                        </td>
                    </tr>
                </table>
                <?php
            }
        }
        ?>
        <div id="TablaDatos">
            <table class="paginador" aria-hidden="true">                
                <?php if ($clienteVO->getTipodepago() === TiposCliente::CREDITO || (utils\HTTPUtils::getSessionValue("ComplementoPago") == 1 && $clienteVO->getTipodepago() !== TiposCliente::TARJETA && $clienteVO->getTipodepago() !== TiposCliente::MONEDERO)) : ?>
                    <?php echo $paginador->headers(array(" ", "PDF"), array("Desc (N.C.)", "Saldo", "Borrar")); ?>
                    <tbody>
                        <?php
                        while ($paginador->next()) :
                            $row = $paginador->getDataRow();
                            ?>
                            <tr>
                                <td style="text-align: center;">
                                    <?php if ($pagoVO->getStatus() === StatusPago::ABIERTO) { ?>
                                        <a href="<?= $self ?>?change=change&cId=<?= $row["idnvo"] ?>" class="textosCualli">cambiar abono</a>
                                    <?php } ?>
                                </td>
                                <td style="text-align: center;">
                                    <a style="color: red;" href=javascript:winuni("enviafile.php?id=<?= $row["factura"] ?>&type=pdf&file=fc&formato=0")><i class="icon fa fa-lg fa-file-pdf-o" title="Obtener PDF Tamaño Carta" aria-hidden="true"></i></i></a>
                                </td>

                                <td style="text-align: center;"><?= $row["factura"] ?></td>

                                <td style="text-align: center;">
                                    <a class="textosCualli" href="<?= $self ?>?Factura=<?= $row["factura"] ?>&Imp=<?= $row["importe"] ?>&cId=<?= $row["idnvo"] ?>"><?= $row["folio"] ?></a>
                                </td>
                                <td><?= $row["fecha"] ?></td>
                                <td style="text-align: right;"><?= number_format($row["total"], 2) ?></td>
                                <td style="text-align: right;">
                                    <?php if ($request->getAttribute("change") === "change") { ?>
                                        <?php if ($request->getAttribute("cId") === $row["idnvo"]) { ?>
                                            <form name="form1" action="" method="post">
                                                <input type="text" class="casilla" name="Abono" placeholder="Ingresa el abono deseado" required>
                                                <input type="hidden" name="cId" value="<?= $row["idnvo"] ?>">
                                            </form>
                                        <?php } else { ?>
                                            <?= $row["importe"] ?>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <?= $row["importe"] ?>
                                    <?php } ?>
                                </td>
                                <td style="text-align: right;"><?= number_format($row["total_nc"], 2) ?></td>
                                <td style="text-align: right;"><?= number_format($row["total"] - ($row["abonos"] + $row["total_nc"]), 2) ?></td>
                                <td style="text-align: center;">
                                    <?php if ($pagoVO->getStatus() === StatusPago::ABIERTO) { ?>
                                        <a href=javascript:borrarRegistro("<?= $self ?>",<?= $row["idnvo"] ?>,"cId"); class="textosCualli"><i class="icon fa fa-lg fa-trash" aria-hidden="true"></i></a>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php elseif ($clienteVO->getTipodepago() === TiposCliente::TARJETA || $clienteVO->getTipodepago() === TiposCliente::MONEDERO) : ?>
                        <?php echo $paginador->headers(array("PDF"), array("Borrar")); ?>

                        <?php
                        while ($paginador->next()) :
                            $row = $paginador->getDataRow();
                            $title = $row["tipo"] == 1 && !empty($row["aceites_ligados"]) ? "Ligado a los aceites y/o aditivos (" . $row["aceites_ligados"] . ")" : "";
                            ?>
                            <tr title="<?= $title ?>">
                                <td style="text-align: center;">
                                    <?php if (!empty($row["uuid"]) && $row["uuid"] !== PagoDAO::SIN_TIMBRAR) { ?>
                                        <a style="color: red;" href=javascript:winuni("enviafile.php?id=<?= $row["uuid"] ?>&type=pdf&formato=0")><i class="icon fa fa-lg fa-file-pdf-o" aria-hidden="true"></i></a>
                                    <?php } ?>
                                </td>
                                <?php echo $paginador->formatRow(); ?>
                                <td style="text-align: center;">
                                    <?php if ($pagoVO->getStatus() === StatusPago::ABIERTO) { ?>
                                        <a href=javascript:borrarRegistro("<?= $self ?>","<?= $row["idnvo"] ?>","cId"); class="textosCualli"><i class="icon fa fa-lg fa-trash" aria-hidden="true"></i></a>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>  
            <?php
            $Count = "SELECT sum(1) count  FROM unidades WHERE cliente = " . $pagoVO->getCliente() . "  AND periodo = 'B'";
            if ($Cnttt = utils\IConnection::execSql($Count)) {
                if ($RepartoSaldo && $Cnttt["count"] !== null) {
                    ?>
                    <div id="SaldoUnidades" style="margin-top: 20%;">
                        <i class="fa fa-money fa-lg" aria-hidden="true" id="DepositoId" style="color:green" data-toggle="modal" data-target="#modal-unidades" data-cliente="<?= $pagoVO->getCliente() ?>" data-operacion="11"></i>
                        <input type="hidden" name="ClienteNo" id="ClienteNo" value="<?= $pagoVO->getCliente() ?>">
                        <input type="hidden" name="IdPago" id="IdPago" value="<?= $cVarVal ?>">
                    </div>
                    <?php
                }
            }
            ?>
        </div>

        <?php
        $nLink = Array();
        if ($pagoVO->getStatus() === StatusPago::ABIERTO && $pagoVO->getAplicado() > 0) {
            $returnLink = urlencode("pagosd33.php");
            $backLink = urlencode("pagosd33.php?criteria=ini");
            if ($clienteVO->getTipodepago() === TiposCliente::CREDITO || (utils\HTTPUtils::getSessionValue("ComplementoPago") == 1 && $clienteVO->getTipodepago() !== TiposCliente::TARJETA && $clienteVO->getTipodepago() !== TiposCliente::MONEDERO)) {
                $nLink["<i class='icon fa fa-lg fa-plus-circle' aria-hidden=\"true\"></i> Agregar factura"] = "catcxc.php?criteria=ini&backLink=$backLink&returnLink=$returnLink";
                if ($pagoVO->getAplicado() == $pagoVO->getImporte()) {
                    $nLink["<i class='icon fa fa-lg fa-warning' aria-hidden=\"true\"></i> Asentar pago en CXC"] = "javascript:confirmar('Al&nbsp;realizar&nbsp;esta&nbsp;operación&nbsp;no&nbsp;podrá&nbsp;agregar&nbsp;facturas&nbsp;¿Esta&nbsp;seguro?','?op=ac');";
                }
            } elseif (($clienteVO->getTipodepago() === TiposCliente::TARJETA || $clienteVO->getTipodepago() === TiposCliente::MONEDERO) && $variablesCorpVO->getValor() == 1) {
                $nLink["<i class='icon fa fa-lg fa-exclamation' aria-hidden=\"true\"></i> Restablecer pago"] = "javascript:confirmarOperacion('?op=reset');";
                $nLink["<i class='icon fa fa-lg fa-plus-circle' aria-hidden=\"true\"></i> Agregar consumos"] = "catrm.php?criteria=ini&backLink=$backLink&returnLink=$returnLink";
            }
            ?>
            <p class="texto_tablas" style="color: #006633;"><i class="icon fa fa-lg fa-exclamation-circle" aria-hidden="true" ></i> Monto abonado: $<?= $pagoVO->getDetalle() ?></p>
            <?php
        } elseif ($pagoVO->getAplicado() < -0.0001) {
            ?>
            <p class="texto_tablas" style="color: red;"><i class="icon fa fa-lg fa-exclamation-triangle" aria-hidden="true" ></i> Ha excedido el importe del pago</p>
            <?php
        }
        $ValidaPPD = "SELECT IFNULL(SUM(if(tm='C', -cxc.importe, cxc.importe )),0) diferencia FROM fc LEFT JOIN cxc ON fc.id = cxc.factura 
            WHERE fc.metododepago='PPD' AND fc.cliente = " . $pagoVO->getCliente() . " GROUP BY fc.id ORDER BY diferencia ASC limit 1;";

        $VPPD = utils\IConnection::execSql($ValidaPPD);

        if ($VPPD["diferencia"] < 0 && $clienteVO->getTipodepago() !== "Credito" && utils\HTTPUtils::getSessionValue("ComplementoPago") == 0 && $pagoVO->getStatus_pago() == StatusPagoPrepago::SIN_LIBERAR) {
            ?>
            <form name="form1" method="get" action="pagosd33.php">
                <input style="color: #808B96;background-color: #F6DDCC;border:1px solid #A93226;border-radius: 4px;" type="submit" name="PPD" value="Pago Diferido">
                <input type="hidden" name="cVarVal" value="<?= $cVarVal ?>">
            </form>
            <?php
        }
        echo $paginador->footer(false, $nLink, true, true);
        if ($clienteVO->getTipodepago() === TiposCliente::TARJETA) {
            echo $paginador->filter();
        }

        echo "<div class='mensajes'>$Msj</div>";
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
        <link rel="stylesheet" href="bootstrap/bootstrap-4.0.0/dist/css/bootstrap-modal.css" type="text/css">

        <?php include_once ("./bootstrap/modals/modal_unidades.html"); ?>

        <script src="./bootstrap/controller/utils.js"></script>
        <script src="./bootstrap/controller/unidades.js"></script>
        <script type="text/javascript">
            if (<?= $total ?> === 0) {
                $(".muestra_complemento").hide();
            } else if (<?= $pagoVO->getStatus_pago() ?> <= 3) {
                $(".muestra_complemento").hide();
            }

            if (<?= $total ?> <= -1) {
                $(".muestra_complemento").hide();
            }
            $(document).ready(function () {
                $("#GeneraReciboAnticipo").click(function () {
                    alertTextValidation("¿Desea Generar el recibo de anticipo?", "", "Timbrar", "2023-05-08 15:36:55", true,
                            "question", 40000, true, "<?= date("Y-m-d H:i:s") ?>", 0, 19,
                            'Fecha minima, 71 hrs atras <?= date("Y-m-d H:i", strtotime(date("Y-m-d H:i") . "-  71 hour")) ?> <input value="<?= date("Y-m-d") ?>T<?= date("H:i") ?>" max="<?= date("Y-m-d") ?>T<?= date("H:i") ?>" min="<?= date("Y-m-d", strtotime(date("Y-m-d") . "- 3 day")) ?>T<?= date("H:i", strtotime(date("H:i") . "+ 1 hour")) ?>" type="datetime-local" id="swal-input3" class="swal2-input">');
                });
                $(".GeneraNcIndp").click(function () {
                    Swal.fire({
                        title: "¿Seguro de generar tu nota de credito?",
                        icon: "question",
                        html: 'Fecha minima, 71 hrs atras <?= date("Y-m-d H:i", strtotime(date("Y-m-d H:i") . "-  71 hour")) ?>'
                                + ' <input value="<?= date("Y-m-d") ?>T<?= date("H:i") ?>"  max="<?= date("Y-m-d") ?>T<?= date("H:i") ?>" min="<?= date("Y-m-d", strtotime(date("Y-m-d") . "- 3 day")) ?>T<?= date("H:i", strtotime(date("H:i") . "+ 1 hour")) ?>" type="datetime-local" id="swal-input4" class="swal2-input">'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            console.log("ID FECHA VALUE " + $("#swal-input4").val());
                            url = "pagosd33.php?op=TimbraNcFc&TotalFc=" + this.dataset.totalfc + "&IdFc=" + this.dataset.idfc + "&FechaEmision=" + $("#swal-input4").val();
                            console.log(url);
                            document.location.href = url;
                        }
                    });
                });
            });

            function getResultado(val_Json) {
                console.log(val_Json);
                var url = "";
                var addUrl = "";
                if (val_Json.Sucess) {
                    if (val_Json.IdOrigen === 0) {
                        /*Anticipos*/
                        url = "pagosd33.php?op=generaAnticipo&busca=<?= $pagoVO->getId() ?>&FechaAnticipo=" + document.getElementById('swal-input3').value;
                    } else {
                        if (document.getElementById('swal-input1').value === "G02") {
                            addUrl = "&UsoCfdi=Si";
                        }
                        /*Notas de Credito*/
                        url = "pagosd33.php?op=generaNota&busca=<?= $pagoVO->getId() ?>&FechaAnticipo=" + document.getElementById('swal-input2').value + addUrl;
                    }
                    document.location.href = url;
                }
            }
        </script>
    </body>
</html>
