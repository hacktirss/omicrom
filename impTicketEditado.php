<?php
session_start();

include_once ("check_report.php");
include_once ("libnvo/lib.php");
include_once ("phpqrcode/qrlib.php");
include_once ("data/SysFilesDAO.php");

require_once ("com/softcoatl/cfdi/utils/NumericalCurrencyConverter.php");
require_once ("com/softcoatl/cfdi/utils/Currency.php");
require_once ("com/softcoatl/cfdi/utils/SpanishNumbers.php");

use com\softcoatl\utils as utils;
use com\softcoatl\cfdi\utils\NumericalCurrencyConverter;
use com\softcoatl\cfdi\utils\SpanishNumbers;
use com\softcoatl\cfdi\utils\Currency;

$request = utils\HTTPUtils::getRequest();
$mysqli = iconnect();

$busca = $request->getAttribute("busca");

$ciaDAO = new CiaDAO();
$sysFilesDAO = new SysFilesDAO();
$sysFilesVO = $sysFilesDAO->retrieve("fc_img");
$usuarioSesion = getSessionUsuario();

$ciaVO = $ciaDAO->retrieve(1);
$logo = $sysFilesVO->getFile();

$Fecha = date("Y-m-d H:i");

$converter = new NumericalCurrencyConverter(new SpanishNumbers(), new Currency('PESOS', 'PESO'));
error_log($converter->convert("58465987"));

if ($request->hasAttribute("Boton")) {
    $SqlRm = "SELECT uuid FROM rm WHERE id = $busca";
    $valRm = utils\IConnection::execSql($SqlRm);
    if ($valRm["uuid"] === "-----") {
        $mysqli->query("UPDATE rm SET comprobante = comprobante + 1, enviado = 0 WHERE id = '$busca'");
    } else {
        $mysqli->query("UPDATE rm SET comprobante = comprobante + 1 WHERE id = '$busca'");
    }
    BitacoraDAO::getInstance()->saveLogSn($usuarioSesion->getNombre(), "ADM", "IMPRESION DE TICKET " . $busca);
}

if ($request->hasAttribute("op") && $request->getAttribute("op") == 1) {
    BitacoraDAO::getInstance()->saveLogSn($usuarioSesion->getNombre(), "ADM", "CONSULTA DE TICKET PARA IMPRESION, FOLIO: " . $busca);
}
$saldo_cli = "SELECT valor FROM omicrom.variables_corporativo where llave = 'pos_saldo_cliente'";
$saldoC = utils\IConnection::getRowsFromQuery($saldo_cli);
$saldoCli = $saldoC[0];
$sald = $saldoCli["valor"];
error_log("*******************impticket         Saldo : " . $sald);

$selectConsumo = "
SELECT 
rm.posicion,
rm.manguera,
CONCAT(com.descripcion,' ', com.clave) producto,
IF( DATE( fin_venta ) = CURDATE() OR importe = pesos OR (uuid IS NOT NULL AND uuid != '-----'), pesos, importe) pesos,
IF( DATE( fin_venta ) = CURDATE() OR importe = pesos OR (uuid IS NOT NULL AND uuid != '-----'), 
    IF( rm.tipo_venta = 'D' AND ABS( rm.diferencia ) > 0.00, 
        ROUND( ( rm.importec + IF( IFNULL( desgloseieps, 'N' ) = 'S', 0.00, importeieps ) + rm.diferencia )/( preciouu + IF( IFNULL( desgloseieps, 'N' ) = 'S', 0.0000, rm.ieps ) ), 4 ), rm.volumen ), 
    ROUND( rm.importe/rm.precio, 4 ) ) volumen,
rm.precio,
rm.fin_venta,
rm.iva,
rm.descuento,
rm.ieps,
com.descripcion,
com.clave,
comprobante,
rm.cliente,
rm.placas,
rm.importe,
round((rm.importe/rm.precio),3) volC,
rm.codigo,
rm.kilometraje,
cli.nombre,
cli.tipodepago,
cli.id  cliente,
SUBSTR(UPPER(SHA1(CONCAT('|', LPAD(rm.id, 7, '0'), '|', LPAD(rm.posicion, 2, '0'), '|', LPAD(rm.manguera, 2, '0'), '|', LPAD(cia.idfae, 5, '0'), '|', DATE_FORMAT(rm.fin_venta, '%Y-%m-%dT%H:%i:%s'), '|', CAST( ROUND(rm.volumen,4) AS DECIMAL(10, 4)), '|', CAST( ROUND(rm.pesos, 2) AS DECIMAL(10, 2)), '|'))), 1, 23) FOLIO_FAE,
variables.odometro,
variables.fae,
IFNULL(vc.valor,0) nipticket
FROM  
(  
SELECT
  rm.id,
  rm.uuid,
  rm.tipo_venta,
  rm.posicion,
  rm.manguera,
  rm.fin_venta,
  rm.precio,
  ROUND( rm.volumen, 4 ) volumen,
  ROUND( rm.pesos, 2 ) pesos,
  ROUND( rm.importe, 2 ) importe,
  rm.producto,
  rm.iva,
  rm.ieps,
  rm.descuento,
  rm.comprobante,
  rm.cliente,
  rm.placas,
  rm.codigo,
  rm.kilometraje,
  ROUND( (rm.precio-rm.ieps)/(1+rm.iva), 4 ) preciouu,
  ROUND( rm.volumen * ROUND( (rm.precio-rm.ieps)/(1+rm.iva), 4 ), 2 ) importec, 
  ROUND( rm.volumen * ROUND( (rm.precio-rm.ieps)/(1+rm.iva), 4 ) * rm.iva, 2 ) importeiva,
  ROUND( rm.volumen * rm.ieps, 2 ) importeieps,
  ROUND( rm.pesos, 2 )
    -ROUND( rm.volumen * ROUND( (rm.precio-rm.ieps)/(1+rm.iva), 4 ), 2 )
    -ROUND( rm.volumen * ROUND( (rm.precio-rm.ieps)/(1+rm.iva), 4 ) * rm.iva, 2 )
    -ROUND( rm.volumen * rm.ieps, 2 ) diferencia
FROM rm
WHERE rm.id = " . $busca . "
) rm
JOIN com ON rm.producto = com.clavei
JOIN cia ON TRUE
JOIN variables ON TRUE
LEFT JOIN cli ON rm.cliente = cli.id
LEFT JOIN variables_corporativo vc ON vc.llave = 'nip_ticket_omicrom'";

$registros = utils\IConnection::getRowsFromQuery($selectConsumo);
$Vt = $registros[0];

if (!empty($Vt["codigo"])) {
    error_log("************DISTINTO DE VACIO ");
    $TarA = $mysqli->query("SELECT impreso,descripcion FROM unidades WHERE codigo='" . $Vt["codigo"] . "'");
    $Tar = $TarA->fetch_array();
    $salT = $Vt["codigo"];
    error_log("*********+ Codigo : " . $salT);

    $selectSaldoT = "SELECT 
    U.importe - IF( U.periodo = 'B', 0.00, IFNULL( ROUND( SUM( C.pesos ), 2 ), 0.00 ) ) permitido,
    IF( U.importe=0, 'L', 'P' ) tipo,
    ROUND( abonos - cargos +
        CASE WHEN U.tipodepago = 'Pospago' OR U.tipodepago = 'Credito' THEN IFNULL( limite, 0 ) ELSE 0 END, 2 ) saldo
FROM (
        SELECT ROUND( importe, 2 ) importe, ROUND( litros, 2 ) litros, periodo, unidades.codigo, cliente, cli.limite, cli.tipodepago
        FROM unidades USE INDEX ( codigo_UNIQUE )
        JOIN cli ON cli.id = unidades.cliente
        WHERE unidades.codigo = " . $salT . " AND unidades.estado = 'a'
) U
LEFT JOIN rm C ON C.cliente = U.cliente AND C.codigo = U.codigo
AND
(
       ( U.periodo = 'M' AND DATE_FORMAT( DATE( fin_venta ), '%Y-%m' ) = DATE_FORMAT( CURDATE(), '%Y-%m' ) ) -- Mensual
   OR  ( U.periodo = 'S' AND DATE( fin_venta ) BETWEEN ( SELECT CURDATE() + INTERVAL - WEEKDAY( CURDATE() ) DAY ) AND ( SELECT CURDATE() + INTERVAL 6 - WEEKDAY( CURDATE() ) DAY E ) ) -- Semanal
   OR  ( U.periodo = 'Q' AND DATE_FORMAT( CURDATE(), '%Y-%m' ) = DATE_FORMAT( DATE( fin_venta ), '%Y-%m' ) AND ( ( DATE_FORMAT( CURDATE(), '%d' ) < 16 AND DATE_FORMAT( DATE( fin_venta ), '%d' ) BETWEEN 1 AND 15 ) OR ( DATE_FORMAT( DATE( fin_venta ), '%d' ) > 15 ) ) ) -- Quincenal
   OR  ( U.periodo = 'D' AND DATE( fin_venta ) = CURDATE() ) -- Diario)
   OR  ( U.periodo = 'B' AND U.importe > 0 )
)
LEFT JOIN (
    SELECT 
            cxc.cliente, IFNULL( SUM( CASE WHEN cxc.tm = 'H' THEN cxc.importe ELSE 0 END ), 0 ) abonos, IFNULL( SUM( CASE WHEN cxc.tm = 'C' THEN cxc.importe ELSE 0 END ), 0 ) cargos
    FROM cxc
    JOIN unidades ON unidades.codigo = " . $salT . " AND unidades.cliente = cxc.cliente
) cxc ON cxc.cliente = U.cliente";

    $saldoT = utils\IConnection::getRowsFromQuery($selectSaldoT);
    $saldoTarj = $saldoT[0];
    $saldoTarjeta = $saldoTarj["permitido"];
}
$cliente = $Vt["cliente"];
error_log("*************impticket       Cliente : " . $cliente);

$selectSaldo = "SELECT
    nombre NOMBRE,
    activo ACTIVO,
    limite LIMITE,
    codigo CODIGO,
    formadepago FORMADEPAGO,
    puntos PUNTOS,
    cia CIA,
    CASE 
      WHEN IFNULL( ucorporativo.valor, '0' ) = '1' AND IFNULL( acorporativo.valor, 'N' ) = 'S' THEN '1'
      ELSE cli.autorizaCorporativo
    END CORPORATIVO,
    IFNULL(ABONOS.IMPORTE, 0) AS ABONOS,
    IFNULL(CARGOS.IMPORTE, 0) AS CARGOS,
    CASE 
        WHEN tipodepago = 'Contado' OR tipodepago = 'Puntos' THEN 'N'
        ELSE 'S' 
    END AS CHECK_PARAMETERS,
    CASE 
        WHEN tipodepago = 'Prepago' OR tipodepago = 'Pospago' OR tipodepago = 'Credito' THEN 'S'
        ELSE 'N' 
    END AS CHECK_IMPORTES,
    CASE 
        WHEN tipodepago = 'Prepago' OR tipodepago = 'Credito' OR tipodepago = 'Pospago' THEN 'S'
        ELSE 'N' 
    END AS CHECK_BALANCE,
    CASE 
        WHEN tipodepago = 'Prepago' THEN 
            ROUND(IFNULL(ABONOS.IMPORTE, 0)-IFNULL(CARGOS.IMPORTE, 0), 2)
        WHEN tipodepago = 'Pospago' OR tipodepago = 'Credito' THEN
            ROUND(IFNULL(cli.limite, 0)+IFNULL(ABONOS.IMPORTE, 0)-IFNULL(CARGOS.IMPORTE, 0), 2) 
        ELSE 0
    END AS SALDO
FROM cli
JOIN (SELECT valor FROM variables_corporativo WHERE llave = 'encrypt_fields') v ON TRUE
JOIN (SELECT valor FROM variables_corporativo WHERE llave = 'uso_corporativo') ucorporativo ON TRUE
JOIN (SELECT valor FROM variables_corporativo WHERE llave = 'autorizacion_corporativo') acorporativo ON TRUE
LEFT JOIN
      (SELECT
            cliente,
            SUM(importe) AS IMPORTE
      FROM cxc
      WHERE tm = 'H'
      GROUP BY cliente) ABONOS ON ABONOS.cliente = cli.id
LEFT JOIN
      (SELECT
            cliente,
            SUM(importe) AS IMPORTE
       FROM cxc
       WHERE tm = 'C'
       GROUP BY cliente) CARGOS ON CARGOS.cliente = cli.id
WHERE cli.id =" . $cliente;

$reg = utils\IConnection::getRowsFromQuery($selectSaldo);
//error_log("impticket        Reg : " . print_r($reg,true));
$Vt2 = $reg[0];
error_log("impticket        Saldo cliente : " . print_r($Vt2, true));
$selectAditivos = "SELECT vt.* FROM vtaditivos vt WHERE 1 = 1 AND vt.tm = 'C' AND vt.cantidad > 0 AND vt.total > 0 AND vt.referencia > 0 AND vt.referencia = $busca";
$registros2 = utils\IConnection::getRowsFromQuery($selectAditivos);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports_print.php'; ?> 
        <!--        <link rel="stylesheet" href="js/jquery-ui.css">
                <script type="text/javascript" src="js/jquery-ui.js"></script>-->
        <link rel="stylesheet" href="js/jquery-ui.css">
        <script src="js/jquery-ui.js"></script>
        <title><?= $Gcia ?></title>         
        <style>
            @page {
                size: A4-Ticket;
            }
            @media print {
                .noPrint {
                    display:none;
                }
            }
            .hideAll{
                visibility: hidden;
                background-color: white;
            }
            .showAll{
                visibility: visible;
            }
            label, input {
                display:block;
            }
            input.text {
                margin-bottom:12px;
                width:95%;
                padding: .4em;
            }
            fieldset {
                padding:0;
                border:0;
                margin-top:25px;
            }
            h1 {
                font-size: 1.2em;
                margin: .6em 0;
            }
            .ui-dialog .ui-state-error {
                padding: .3em;
            }
            .validateTips {
                border: 1px solid transparent;
                padding: 0.3em;
            }
        </style>
    </head>

    <!-- Set "A5", "A4" or "A3" for class name -->
    <!-- Set also "landscape" if you need -->

    <div style="border: 1px solid black;" id="Datos">
        Ticket <input type="number" name="TicketId" id="TicketId" style="width: 100px;display: inline-block">
        Volumen : <input type="text" name="Volumen" id="Volumen" style="width: 50px;display: inline-block">
        Importe por Litro
        <input type="text" name="ImporteL" id="ImporteL" style="width: 50px;display: inline-block">
        <button name="Listo" id="Listo">Genera</button>
    </div>
    <body class="A4-Ticket">

        <!-- Each sheet element should have the class "sheet" -->
        <!-- "padding-**mm" is optional: you can set 10, 15, 20 or 25 -->

        <div id="MiTicket" class="sheet padding-10mm hideAll">
            <form name="form1" method="get" action="" class="noPrint">
                <div style="text-align: center;position: relative;">
                    <input type="submit" name="Boton" class="nombre_cliente" value="Imprimir" onclick="print()">
                    <input type="hidden" name="busca" id="busca">
                </div>
            </form>

            <div align="center" class="text" style="align-items: flex-start">
                <table style="text-align: center" class="text" aria-hidden="true">
                    <tr>
                        <td align="left"><?= $Vt["comprobante"] < 1 ? "Original" : "Copia " . $Vt["comprobante"] ?></td>
                    </tr>
                    <tr><td align="center"><img src="data:image/jpeg;base64,<?= base64_encode($logo) ?>" style="width: 200px; height: 90px;" alt=""></td></tr>
                    <tr><td align="center" class="TextosTitulos"><strong><?= $ciaVO->getCia() ?></strong></td></tr>                
                    <tr><td align="center"><?= $ciaVO->getDireccion() . " " . $ciaVO->getNumeroext() ?></td></tr>
                    <tr><td align="center"><?= $ciaVO->getCiudad() . " " . $ciaVO->getEstado() ?> Cp. <?= $ciaVO->getCodigo() ?></td></tr>
                    <tr><td align="center">Telefono: <?= $ciaVO->getTelefono() ?></td></tr>
                    <tr><td align="center">RFC: <?= $ciaVO->getRfc() ?></td></tr>
                    <tr><td align="center">Sucursal: <?= $ciaVO->getEstacion() ?></td></tr>
                    <tr><td align="center">Permiso: <?= $ciaVO->getPermisocre() ?></td></tr>
                    <tr><td align="center">No.estacion: <strong><?= $ciaVO->getNumestacion() ?></strong></td></tr>                

                    <tr><td align="center"><br><?= "Folio: <strong>$busca</strong>" ?> </td></tr>

                    <tr><td align="center"><strong>Fecha venta <?= $Vt["fin_venta"] ?></strong></td></tr>
                    <tr><td align="center">Posicion: <?= $Vt["posicion"] ?> Manguera: <?= $Vt["manguera"] ?></td></tr>
                    <tr><td align="center"><strong>Tipo de pago:<?= $Vt["tipodepago"] ?></td></tr>

                    <tr><td align="center"><strong><?= "Cliente: " . $Vt["cliente"] . " | " . ucfirst(strtoupper(substr($Vt["nombre"], 0, 45))) ?></strong></td></tr>
                    <?php if ($sald == 1 && (strcmp($Vt["tipodepago"], 'Credito') === 0 || strcmp($Vt["tipodepago"], 'Prepago') === 0 )) { ?>                       
                        <tr><td align="center"><strong>Saldo Cliente:<?= $Vt2["SALDO"] ?></td></tr>

                        <?php
                    }
                    if (!empty($salT)) {
                        ?>
                        <tr><td align="center"><strong>Saldo Tarjeta:<?= $saldoTarjeta ?></td></tr>
                        <?php
                    }
                    // error_log("++++++++++++impticket        Prepago = " . strcmp($Vt["tipodepago"], 'Prepago'));
                    if ($Vt["cliente"] > 0 && (!empty($Vt["placas"]) || !empty($Vt["codigo"]))) {
                        echo "<tr><td align='center'>Placas: <strong>" . $Vt["placas"] . "</strong> &nbsp &nbsp Tarjeta: " . $Tar["impreso"] . "</td></tr>";
                        echo "<tr><td align='center'>" . $Tar["descripcion"] . "</td></tr>";
                        if ($Vt["odometro"] === "S") {
                            echo "<tr><td align='center'>Odometro: " . $Vt["kilometraje"] . "</td></tr>";
                        }
                    }
                    ?>
                </table><br/>

                <table style="width: 98%;text-align: center" class="text" aria-hidden="true">

                    <tr style="font-weight: bold;">
                        <td width="45%">Producto</td>
                        <td style="text-align: right;" width="15%">Litros</td>
                        <td style="text-align: right;" width="20%">Precio</td>
                        <td style="text-align: right;" width="20%">Importe</td>
                    </tr>
                    <?php
                    if (!($request->getAttribute("Volumen") > 0)) {
                        ?>
                        <tr>
                            <td><?= $Vt["producto"] ?></td>
                            <td style="text-align: right;"><?= $Vt["importe"] < 0.01 ? 0.00 : number_format($Vt["volC"], 3); ?></td>
                            <td style="text-align: right;"><?= $Vt["importe"] < 0.01 ? 0.00 : number_format($Vt["precio"], 2); ?></td>
                            <td style="text-align: right;"><?= $Vt["importe"] < 0.01 ? 0.00 : number_format($Vt["importe"], 2); ?></td>
                        </tr>
                        <?php
                    } else {
                        ?>
                        <tr>
                            <td><?= $Vt["producto"] ?></td>
                            <td style="text-align: right;"><div id="VolumenImg"></div></td>
                            <td style="text-align: right;"><div id="PrecioImg"></div></td>
                            <td style="text-align: right;"><div id="TotalImg"></div></td>
                        </tr>
                        <?php
                    }
                    $Total = $Vt["importe"] - $Vt["descuento"];
                    foreach ($registros2 as $rg):
                        $Total += $rg["total"];
                        ?>
                        <tr>
                            <td><?= $rg["descripcion"] ?></td>
                            <td style="text-align: right;"><?= $Vt["importe"] < 0.01 ? 0.00 : number_format($rg["cantidad"], 0); ?></td>
                            <td style="text-align: right;"><?= $Vt["importe"] < 0.01 ? 0.00 : number_format($rg["unitario"], 2); ?></td>
                            <td style="text-align: right;"><?= $Vt["importe"] < 0.01 ? 0.00 : number_format($rg["total"], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" style="text-align: right;">
                            Total :
                        </td>
                        <td style="text-align: right;">
                            <?php
                            if (!($request->getAttribute("Volumen") > 0)) {
                                ?>

                                <?= $Vt["importe"] < 0.01 ? 0 : number_format($Total, 2); ?>
                                <?php
                            } else {
                                ?>
                                <div id="TotalImg2"></div>
                                <?php
                            }
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>


        <script>
            let nip = "<?= $Vt["nipticket"] ?>";
            let busca = "<?= $busca ?>";

            var dialog = $("#dialogForm").dialog({
                autoOpen: false,
                height: 250,
                width: 350,
                modal: true,
                buttons: {
                    Cancel: function () {
                        dialog.dialog("close");
                        window.close();
                    }
                },
                close: function () {
                    console.log("Close dialog");
                },
                open: function () {
                }
            });

            window.onload = function () { // same as window.addEventListener('load', (event) => {
                showBoddy();
            };

            $(document).ready(function () {
                $("#busca").val(busca);
                hideBoddy();
                $("#formDialog").submit(function (e) {
                    e.preventDefault();
                });
                $("#Listo").click(function () {
                    $("#Datos").hide();
                    Ticket = $("#TicketId").val();
                    Volumen = $("#Volumen").val();
                    Importe = $("#ImporteL").val();
                    window.location.replace("impTicketEditado.php?busca=" + Ticket + "&Volumen=" + Volumen + "&Importe=" + Importe);
                });
                $("#VolumenImg").html(parseFloat(<?= $request->getAttribute("Volumen") ?>).toFixed(2));
                $("#PrecioImg").html(parseFloat(<?= $request->getAttribute("Importe") ?>).toFixed(2));
                $("#TotalImg").html(parseFloat(<?= $request->getAttribute("Importe") * $request->getAttribute("Volumen") ?>).toFixed(2));
                $("#TotalImg2").html(parseFloat(<?= $request->getAttribute("Importe") * $request->getAttribute("Volumen") ?>).toFixed(2));
                if ("<?= $busca ?>" > 0) {
                    $("#Datos").hide();
                }
            });
            function hideBoddy() {
                console.log("Body hide");
            }
            function showBoddy() {
                dialog.dialog("close");
                $("#MiTicket").removeClass("hideAll");
                $("#MiTicket").addClass("showAll");
            }
            function closeBody() {
                alert("La clave ingresada es incorrecta!");
                window.close();
            }

        </script>
    </body>
</html>

<?php

function encode($input) {
    $base = "KLMNOPQRSTUVWXYZ";
    $encoded = strtoupper($input);
    $return = "";
    for ($i = 0; $i < strlen($encoded); $i++) {
        $idx = hexdec(substr($encoded, $i, 1));
        $return .= substr($base, $idx, 1);
    }
    return $return;
}
?>