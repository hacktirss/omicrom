<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("data/CtDAO.php");
include_once ("data/VariablesDAO.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();

require './services/ReportesVentasService.php';

$Corte = utils\HTTPUtils::getSessionValue("busca");

$ctDAO = new CtDAO();
$ctVO = $ctDAO->retrieve($Corte);

$Titulo = " Ventas credito, tarjeta y vales, Corte: $Corte  / " . $ctVO->getFecha() . " Turno: " . $ctVO->getTurno() . "";

require_once './services/VentasService.php';

/* Consultas para reporte de corte, segunda impresora */

$DespachadoresByCorte = array();
$DispensariosActivos["*"] = "Todos";
$selectDespachadoresByCorte = "SELECT ven.id, 
        IFNULL(GROUP_CONCAT(DISTINCT rm.posicion ORDER BY rm.posicion ASC), GROUP_CONCAT(DISTINCT man.posicion ORDER BY man.posicion ASC) )posicion , 
        IFNULL(GROUP_CONCAT(DISTINCT rm.vendedor ORDER BY rm.vendedor ASC), GROUP_CONCAT(DISTINCT man.despachador ORDER BY man.despachador ASC)) despachador,
        IFNULL(GROUP_CONCAT(DISTINCT ven.alias), 'NO DISPONIBLE') alias
        FROM man
        LEFT JOIN rm ON man.posicion = rm.posicion AND rm.corte = $Corte
        LEFT JOIN ven ON rm.vendedor = ven.id 
        WHERE man.activo = 'Si' AND ven.id IS NOT NULL
        GROUP BY ven.id;";
if (($result = $mysqli->query($selectDespachadoresByCorte))) {
    while ($row = $result->fetch_array()) {
        $DespachadoresByCorte[$row["id"]] = $row["alias"];
    }
}

if (!empty($IslaPosicion) || !empty($Despachador)) {
    if ($TipoCliente === "*") {
        $selectVentasCorte_2da = "
            SELECT cli.tipodepago, rm.posicion, cli.nombre, rm.volumen, rm.pesos, rm.pagoreal importe,
            rm.id,rm.cliente, IFNULL(SUM(vt.total), 0) aceites
            FROM man, rm
            LEFT JOIN cli ON rm.cliente = cli.id
            LEFT JOIN vtaditivos vt ON vt.corte = $Corte AND rm.id = vt.referencia AND vt.cantidad > 0
            WHERE 1= 1 
            AND man.posicion = rm.posicion AND man.activo = 'Si'
            AND rm.corte = $Corte AND rm.cliente > 0 AND rm.tipo_venta in ('D','N')
            AND cli.tipodepago NOT REGEXP 'Contado|Puntos'";
    } else {
        $selectVentasCorte_2da = "
            SELECT cli.tipodepago, rm.posicion, cli.nombre, rm.volumen, rm.pesos, rm.pagoreal importe,
            rm.id,rm.cliente, IFNULL(SUM(vt.total), 0) aceites
            FROM man, rm
            LEFT JOIN cli ON rm.cliente = cli.id
            LEFT JOIN vtaditivos vt ON vt.corte = $Corte AND rm.id = vt.referencia AND vt.cantidad > 0
            WHERE 1= 1 
            AND man.posicion = rm.posicion AND man.activo = 'Si'
            AND rm.corte = $Corte AND rm.cliente <> 0 AND rm.tipo_venta = 'D'
            AND cli.tipodepago = '$TipoCliente' ";
    }
    if (!empty($IslaPosicion) && $IslaPosicion !== "*") {
        $selectVentasCorte_2da .= " AND man.isla_pos = $IslaPosicion";
    }
    if (!empty($Despachador) && $Despachador !== "*") {
        $selectVentasCorte_2da .= " AND rm.vendedor = $Despachador";
    }
    if ($Posicion !== "*") {
        $selectVentasCorte_2da .= " AND rm.posicion = $Posicion";
    }
    $selectVentasCorte_2da .= "
            GROUP BY rm.id
            ORDER BY cli.tipodepago,cli.id,rm.posicion,rm.id ";

    $selectCobroTarjetasCorte_2da = "
            SELECT cli.tipodepago,cttarjetas.banco,cli.nombre,cttarjetas.importe,
            cttarjetas.idnvo,ven.alias,cttarjetas.vaucher
            FROM ven,cttarjetas 
            LEFT JOIN cli ON cttarjetas.banco=cli.id 
            WHERE cttarjetas.id = $Corte AND  cttarjetas.vendedor = ven.id";

    $selectVentaAditivosCorte_2da = "
            SELECT cli.tipodepago, vt.clave,inv.descripcion,vt.cantidad,vt.unitario,vt.total
            FROM cli, man, vtaditivos vt
            LEFT JOIN inv ON vt.clave = inv.id
            WHERE 1 = 1
            AND man.posicion = vt.posicion AND man.activo = 'Si'
            AND vt.corte = $Corte AND vt.cliente = cli.id 
            AND cli.tipodepago <> 'Contado' AND vt.tm = 'C' AND vt.cantidad > 0";

    $selectVentaAditivosCorteC_2da = "
            SELECT cli.tipodepago, vt.posicion, vt.clave, vt.descripcion, 
            SUM( vt.cantidad ) cantidad, SUM( total ) importe
            FROM man,vtaditivos vt
            LEFT JOIN cli ON vt.cliente = cli.id
            WHERE 1 = 1
            AND man.posicion = vt.posicion AND man.activo = 'Si'
            AND vt.corte = $Corte AND vt.cliente = 0 AND vt.tm = 'C' AND vt.cantidad > 0
            GROUP BY vt.posicion, vt.clave";

    $selectAbonosCorte_2da = "
            SELECT egr.clave,bancos.cuenta,bancos.concepto cptcuenta,egr.concepto,
            egr.importe,egr.id 
            FROM egr 
            LEFT JOIN bancos ON egr.clave=bancos.id 
            WHERE egr.corte = $Corte AND egr.importe <> 0
            ORDER BY egr.id";

    $selectCargosCorte_2da = "
            SELECT ctpagos.concepto,ctpagos.importe,cli.nombre,cli.id
            FROM ctpagos,cli 
            WHERE ctpagos.corte = $Corte AND ctpagos.cliente = cli.id";

    $registros = utils\IConnection::getRowsFromQuery($selectVentasCorte_2da);

    $registrosT = utils\IConnection::getRowsFromQuery($selectCobroTarjetasCorte_2da);

    $registrosA = utils\IConnection::getRowsFromQuery($selectVentaAditivosCorte_2da);

    $registrosAC = utils\IConnection::getRowsFromQuery($selectVentaAditivosCorteC_2da);

    $registrosAB = utils\IConnection::getRowsFromQuery($selectAbonosCorte_2da);

    $registrosCA = utils\IConnection::getRowsFromQuery($selectCargosCorte_2da);
}

$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$self = utils\HTTPUtils::self();
$returnLink = "impaceites.php";
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports.php'; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                let msj = "<?= $Msj ?>";
                var cliente = "<?= html_entity_decode($SCliente) ?>";
                $("#autocomplete").val(cliente.replace("Array", ""))
                        .attr("placeholder", "* Favor de buscar al cliente *")
                        .click(function () {
                            this.select();
                        }).focus()
                        .activeComboBox(
                                $("[name=\"form1\"]"),
                                "SELECT id as data, CONCAT(id, ' | ', tipodepago, ' | ', nombre) value FROM cli " +
                                "WHERE cli.id >= 10 AND cli.tipodepago NOT REGEXP 'Contado|Puntos'",
                                "nombre");

                $("#TipoCliente").val("<?= $TipoCliente ?>");
                $("#Posicion").val("<?= $Posicion ?>");
                $("#IslaPosicion").val("<?= $IslaPosicion ?>");
                $("#Despachador").val("<?= $Despachador ?>");
                $("#Corte").val("<?= $Corte ?>");
                $("#IslaPosicion").focus();

                $("#IslaPosicion").change(function () {
                    $("#Despachador").val("");
                });
                $("#Despachador").change(function () {
                    $("#IslaPosicion").val("");
                });

                $(".Corte").val("<?= $Corte ?>");
                $(".returnLink").val("<?= $returnLink ?>");
                $(".IslaPos").val("<?= $IslaPosicion ?>");
                $(".BotonHidden").hide();

                $("#Msj").val(msj);

                if ($("#IslaPosicion").val() > 0) {
                    $("#onlyPosition").show();
                    $("#BotonAgregar").hide();
                    $("#Ticket").focus();
                } else {
                    $("#onlyPosition").hide();
                }

            });

            function liberarRegistro(direccion, identificador, variable) {
                var mensaje = "Esta seguro que quiere liberar el consumo " + identificador + "?";
                if (confirm(mensaje)) {
                    var url = direccion + "?op=Si&" + variable + "=" + identificador;
                    document.location.href = url;
                }
            }
        </script>
    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo); ?>
            <div id="Reportes">
                <table aria-hidden="true">
                    <thead>
                        <tr class="titulo">
                            <td colspan="10">Detalle</td>
                            <td colspan="3">Totales</td>
                        </tr>
                        <tr>
                            <td>#</td>
                            <td>Rubro</td>
                            <td>Posición</td>
                            <td>No.ticket</td>
                            <td>Nombre</td>
                            <td>Litros</td>
                            <td>Combustible</td>
                            <td>Pago real</td>
                            <td>Aceites</td>
                            <td>Total</td>
                            <td>Subtotal</td>
                            <td>Total</td>
                            <td>Dif.</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $cCli = "";
                        $nPos = $nPosImp = $nPosLit = 0;
                        $nSubPesos = $nSubImp = $nSubAce = $nSubLit = $nCount = 0;
                        $nGcount = $GranT = $GDiff = 0;
                        $i = 0;
                        foreach ($registros as $rg) {
                            $style = "";
                            $form = "form" . $cCli;
                            $next = $registros[$i + 1];
                            $nPosImp += $rg["importe"];
                            $nPosLit += $rg["volumen"];

                            if ($rg["cliente"] != $cCli) {
                                if (!empty($cCli)) {
                                    ?>
                                    <tr class="subtotal">
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td>Total</td>
                                        <td><?= number_format($nSubLit, 2) ?></td>
                                        <td><?= number_format($nSubPesos, 2) ?></td>
                                        <td><?= number_format($nSubImp, 2) ?></td>
                                        <td><?= number_format($nSubAce, 2) ?></td>
                                        <td><?= number_format($nSubImp + $nSubAce, 2) ?></td>
                                        <td><?= number_format($nSubImp + $nSubAce, 2) ?></td>
                                        <td><?= number_format($GranT, 2) ?></td>
                                        <td><?= number_format($nSubPesos - $nSubImp, 2) ?></td>
                                    </tr>
                                    <?php if ($IslaPosicion > 0) { ?>
                                        <tr style="background-color: white;">
                                            <td colspan="100%">
                                                <form name="<?= $form ?>" class="form" method="post" action="">
                                                    <div id="Controles" style="width: 90%;">
                                                        <table aria-hidden="true">
                                                            <tbody>
                                                                <tr style="height: 25px;">
                                                                    <td>
                                                                        <table style="width: 100%" aria-hidden="true">
                                                                            <tr>
                                                                                <td>#Ticket:</td>
                                                                                <td style="text-align: left;padding-left: 5px">
                                                                                    <input type="number" name="Ticket" class="Ticket" min="1" max="10000000" required=""/>
                                                                                    <span class="BotonEnviar"><input type="submit" name="Boton" value="Enviar" class="BotonConsularE"></span>
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                    <td>
                                                                        <table style="width: 100%" aria-hidden="true">
                                                                            <tr>
                                                                                <td>Posición:</td>
                                                                                <td style="text-align: left;padding-left: 5px">
                                                                                    <div class="Posicion">0</div>
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                    <td>
                                                                        <table style="width: 100%" aria-hidden="true">
                                                                            <tr>
                                                                                <td>Importe:</td>
                                                                                <td style="text-align: left;padding-left: 5px">
                                                                                    <div class="Importe">$ 0.00</div>
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                    <td>
                                                                        <table style="width: 100%" aria-hidden="true">
                                                                            <tr>
                                                                                <td>Pago real:</td>
                                                                                <td style="text-align: left;padding-left: 5px">
                                                                                    <input type="text" name="Pagoreal" class="Pagoreal" style="width: 40%;" placeholder="0"/>
                                                                                    <span class="BotonHidden"><input type="submit" name="Boton" value="Agregar"></span>
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        <input type="hidden" name="Corte" class="Corte">
                                                        <input type="hidden" name="IslaPos" class="IslaPos">
                                                        <input type="hidden" name="Cliente" value="<?= $cCli ?>">
                                                        <input type="hidden" name="returnLink" class="returnLink">
                                                    </div>
                                                </form>
                                                <div><br></div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <?php
                                }
                                $nSubPesos = $nSubImp = $nSubAce = $nSubLit = $nCount = 0;
                                $cCli = $rg["cliente"];
                            }
                            $diferencia = "";
                            if (abs($rg["pesos"] - $rg["importe"]) > 0.5 || $rg["aceites"] > 0) {
                                $style = "style='background-color: #F7FF7C' title='El importe fue modificado o aceite incluido'";
                                $diferencia = number_format($rg["pesos"] - $rg["importe"], 2);
                                $GDiff += $rg["pesos"] - $rg["importe"];
                            }
                            ?>
                            <tr <?= $style ?>>
                                <td><?= $nCount + 1 ?></td>
                                <td><?= $rg["tipodepago"] ?></td>
                                <td><?= $rg["posicion"] ?></td>
                                <td style="cursor: pointer" onclick="liberarRegistro('<?= $self ?>', '<?= $rg["id"] ?>', 'cId');"><?= $rg["id"] ?></td>
                                <td><?= ucwords(strtolower(substr($rg["nombre"], 0, 40))) ?></td>
                                <td class="numero"><?= number_format($rg["volumen"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["pesos"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["aceites"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["importe"] + $rg["aceites"], 2) ?></td>
                                <td class="numero">
                                    <?php
                                    if ($next["posicion"] != $rg["posicion"] || empty($next)) {
                                        echo number_format($nPosImp, 2);
                                        $nPosImp = $nPosLit = 0;
                                    }
                                    ?>
                                </td>
                                <td></td>
                                <td class="numero"><?= $diferencia ?></td>
                            </tr>

                            <?php
                            $nPos = $rg["posicion"];

                            $nSubPesos += $rg["pesos"];
                            $nSubImp += $rg["importe"];
                            $nSubAce += $rg["aceites"];
                            $nSubLit += $rg["volumen"];

                            $nCnt += $rg["volumen"];
                            $nImpR += $rg["importe"];
                            $GranT += $rg["importe"] + $rg["aceites"];

                            $nCount++;
                            $nGcount++;
                            $i++;
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5">Total</td>
                            <td><?= number_format($nSubLit, 2) ?></td>
                            <td><?= number_format($nSubPesos, 2) ?></td>
                            <td><?= number_format($nSubImp, 2) ?></td>
                            <td><?= number_format($nSubAce, 2) ?></td>
                            <td><?= number_format($nSubImp + $nSubAce, 2) ?></td>
                            <td><?= number_format($nSubImp + $nSubAce, 2) ?></td>
                            <td><?= number_format($GranT, 2) ?></td>
                            <td><?= number_format($nSubPesos - $nSubImp, 2) ?></td>
                        </tr>
                        <?php if ($IslaPosicion > 0 && $cCli > 0) { ?>
                            <tr style="background-color: white;">
                                <td colspan="100%">
                                    <form name="<?= $form ?>" class="form" method="post" action="">
                                        <div id="Controles" style="width: 90%;">
                                            <table aria-hidden="true">
                                                <tbody>
                                                    <tr style="height: 25px;">
                                                        <td>
                                                            <table style="width: 100%" aria-hidden="true">
                                                                <tr>
                                                                    <td>#Ticket:</td>
                                                                    <td style="text-align: left;padding-left: 5px">
                                                                        <input type="number" name="Ticket" class="Ticket" min="1" max="10000000" required=""/>
                                                                        <span class="BotonEnviar"><input type="submit" name="Boton" value="Enviar" class="BotonConsularE"></span>
                                                                        <input type="hidden" name="OpX" class="OpX" value="1">
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                        <td>
                                                            <table style="width: 100%" aria-hidden="true">
                                                                <tr>
                                                                    <td>Posición:</td>
                                                                    <td style="text-align: left;padding-left: 5px">
                                                                        <div class="Posicion">0</div>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                        <td>
                                                            <table style="width: 100%" aria-hidden="true">
                                                                <tr>
                                                                    <td>Importe:</td>
                                                                    <td style="text-align: left;padding-left: 5px">
                                                                        <div class="Importe">$ 0.00</div>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                        <td>
                                                            <table style="width: 100%" aria-hidden="true">
                                                                <tr>
                                                                    <td>Pago real:</td>
                                                                    <td style="text-align: left;padding-left: 5px">
                                                                        <input type="text" name="Pagoreal" class="Pagoreal" style="width: 40%;" placeholder="0"/>
                                                                        <span class="BotonHidden"><input type="submit" name="Boton" value="Agregar"></span>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <input type="hidden" name="Corte" class="Corte">
                                            <input type="hidden" name="IslaPos" class="IslaPos">
                                            <input type="hidden" name="Cliente" value="<?= $cCli ?>">
                                            <input type="hidden" name="returnLink" class="returnLink">
                                        </div>
                                    </form>
                                    <div><br></div>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td colspan="5">Total de ventas: <?= $nGcount ?></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><?= number_format($GranT, 2) ?></td>
                            <td><?= number_format($GDiff, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>


                <?php if ($TipoCliente === "*") { ?>
                    <table aria-hidden="true">
                        <thead>
                            <tr class="titulo">
                                <td colspan="7">Venta de Tarjeta</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $nImpTar = 0;
                            foreach ($registrosT as $rg) {
                                ?>
                                <tr>
                                    <td><?= $rg["tipodepago"] ?></td>
                                    <td><?= $rg["idnvo"] ?></td>
                                    <td><?= substr(ucwords(strtolower($rg["nombre"] . " " . $rg["vaucher"])), 0, 60) ?></td>
                                    <td></td>
                                    <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <?php
                                $nImpTar += $rg["importe"];
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr class="subtotal">
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td><?= number_format($nImpTar, 2) ?></td>
                                <td><?= number_format($nImpTar, 2) ?></td>
                                <td><?= number_format($GranT, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                    <table aria-hidden="true">
                        <thead>
                            <tr class="titulo">
                                <td colspan="7">Venta de aceites</td>
                            </tr>
                            <tr>
                                <td>Rubro</td>
                                <td>Producto</td>
                                <td>Descripcion</td>
                                <td>Piezas</td>
                                <td>Importe</td>
                                <td>Total</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $nSubCnt = $nSubImp = 0;
                            foreach ($registrosA as $rg) {
                                ?>
                                <tr>
                                    <td><?= $rg["tipodepago"] ?></td>
                                    <td><?= $rg["clave"] ?></td>
                                    <td><?= $rg["descripcion"] ?></td>
                                    <td class="numero"><?= number_format($rg["cantidad"], 0) ?></td>
                                    <td class="numero"><?= number_format($rg["total"], 2) ?></td>
                                    <td></td>
                                </tr>
                                <?php
                                $nSubCnt += $rg["cantidad"];
                                $nSubImp += $rg["total"];
                                $GranT += $rg["total"];
                            }
                            ?>

                            <tr class="subtotal">
                                <td></td>
                                <td></td>
                                <td>Total</td>
                                <td><?= number_format($nSubCnt, 0) ?></td>
                                <td><?= number_format($nSubImp, 2) ?></td>
                                <td></td>
                            </tr>
                            <tr class="subtitulo">
                                <td colspan="6">Venta de aceites de contado</td>
                            </tr>
                            <?php
                            $nCnt = $nImp = 0;
                            foreach ($registrosAC as $rg) {
                                ?>
                                <tr>
                                    <td><?= $rg["tipodepago"] ?></td>
                                    <td><?= $rg["clave"] ?></td>
                                    <td><?= $rg["descripcion"] ?></td>
                                    <td class="numero"><?= $rg["cantidad"] ?></td>
                                    <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                    <td></td>
                                </tr>
                                <?php
                                $nCnt += $rg["cantidad"];
                                $nImp += $rg["importe"];
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td>
                                <td></td>
                                <td>Total</td>
                                <td><?= number_format($nCnt, 0) ?></td>
                                <td><?= number_format($nImp, 2) ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>

                    <table aria-hidden="true">
                        <thead>
                            <tr class="titulo">
                                <td colspan="6">Gastos</td>
                            </tr>
                            <tr>
                                <td>Clave</td>
                                <td>Cuenta</td>
                                <td>Descripcion</td>
                                <td>Concepto</td>
                                <td>Importe</td>
                                <td></td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $nImpBco = 0;
                            foreach ($registrosCA as $rg) {
                                ?>
                                <tr>
                                    <td></td>
                                    <td><?= $rg["id"] ?></td>
                                    <td><?= substr(ucwords(strtolower($rg["nombre"])), 0, 40) ?></td>
                                    <td><?= substr(ucwords(strtolower($rg["concepto"])), 0, 60) ?></td>
                                    <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                    <td></td>
                                </tr>
                                <?php
                                $nImpBco += $rg["importe"];
                                $GranT += $rg["importe"];
                            }
                            ?> 
                            <tr class="subtotal">
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>Total</td>
                                <td><?= number_format($nImpBco, 2) ?></td>
                                <td><?= number_format($GranT, 2) ?></td>
                            </tr>
                            <tr class="subtitulo">
                                <td colspan="6">Bancos</td>
                            </tr>
                            <?php
                            $nImpBco = 0;
                            foreach ($registrosAB as $rg) {
                                ?>
                                <tr>
                                    <td><?= $rg["clave"] ?></td>
                                    <td><?= $rg["cuenta"] ?></td>
                                    <td><?= substr(ucwords(strtolower($rg["cptcuenta"])), 0, 40) ?></td>
                                    <td><?= substr(ucwords(strtolower($rg["concepto"])), 0, 60) ?></td>
                                    <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                    <td></td>
                                </tr>
                                <?php
                                $nImpBco += $rg["importe"];
                                $GranT += $rg["importe"];
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>Total</td>
                                <td><?= number_format($nImpBco, 2) ?></td>
                                <td><?= number_format($GranT, 2) ?></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>GRAN TOTAL</td>
                                <td></td>
                                <td><?= number_format($GranT + $nImpTar, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                <?php } ?>
            </div>
        </div>

        <div id="footer">
            <form name="formActions" method="post" action="" id="form" class="oculto">
                <div id="Msj" style="color: red; text-align: center; font-weight: bold"></div>
                <div id="Controles">
                    <table aria-hidden="true">
                        <tr style="height: 40px;" id="onlyPosition">
                            <td align="left" colspan="2">
                                <div style="position: relative;">
                                    <input style="width: 100%;" type="search" id="autocomplete" name="ClienteS">
                                </div>
                                <div id="autocomplete-suggestions"></div>
                            </td>
                            <td align="left" colspan="2">
                                <table style="width: 100%" aria-hidden="true">
                                    <tr>
                                        <td>#Ticket:</td>
                                        <td style="text-align: left;padding-left: 5px">
                                            <input type="number" name="Ticket" id="Ticket" min="1" max="10000000"/>
                                            <span><input type="submit" value="Enviar" id="BotonEnviar"></span>
                                        </td>

                                        <td>Pago real:</td>
                                        <td style="text-align: left;padding-left: 5px">
                                            <input type="text" name="Pagoreal" id="Pagoreal" style="width: 40%;" placeholder="0"/>
                                            <span><input type="submit" name="Boton" value="Agregar" id="BotonAgregar"></span>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr style="height: 40px;">
                            <td>
                                <table style="width: 100%" aria-hidden="true">
                                    <tr>
                                        <td>Isla o Dispensario</td>
                                        <td style="text-align: left;
                                            padding-left: 5px">
                                            <select id="IslaPosicion" name="IslaPosicion">
                                                <option value="">SELECCIONAR</option>
                                                <?php
                                                foreach ($IslasPosicion as $key => $value) {
                                                    echo "<option value='$key'>$value</option>";
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="text-align: center"><strong>ó</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Despachador</td>
                                        <td style="text-align: left;
                                            padding-left: 5px">
                                            <select id="Despachador" name="Despachador">
                                                <option value="">SELECCIONAR</option>
                                                <?php
                                                foreach ($DespachadoresByCorte as $key => $value) {
                                                    echo "<option value='$key'>$value</option>";
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <table style="width: 100%" aria-hidden="true">
                                    <tr>
                                        <td>Posición</td>
                                        <td style="text-align: left;
                                            padding-left: 5px">
                                            <select id="Posicion" name="Posicion" onchange="submit();">
                                                <?php
                                                foreach ($PosicionesActivas as $key => $value) {
                                                    echo "<option value='$key'>$value</option>";
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <table style="width: 100%" aria-hidden="true">
                                    <tr>
                                        <td>Tipo de cliente: </td>
                                        <td style="text-align: left;
                                            padding-left: 5px">
                                            <select id="TipoCliente" name="TipoCliente" onchange="submit();">
                                                <?php
                                                foreach ($TiposClienteArray as $key => $value) {
                                                    echo "<option value='$key'>$value</option>";
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <span><input type="submit" name="Boton" value="Enviar"></span>
                                <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                            </td>
                        </tr>
                    </table>
                    <input type="hidden" name="Corte" id="Corte">
                </div>
            </form>
            <?php topePagina() ?>
        </div>
        <script>
            $(document).ready(function () {

                $(".Ticket").change(function () {
                    //$(".BotonC").val("Enviar");
                    $(".BotonConsularE").show();
                    $(".BotonConsularE").val("Enviar");
                    $(".BotonHidden").hide();
                    $(".Posicion").html(0);
                    $(".Importe").html("$ 0.00");
                    $(".Pagoreal").val("");
                });

                $(".form").submit(function (e) {

                    let value = $(this).find(".BotonConsularE").val();

                    if (value === "Enviar") {
                        e.preventDefault();
                        let ticket = $(this).find(".Ticket").val();
                        let isla_pos = $(this).find(".IslaPos").val();
                        let corte = $(this).find(".Corte").val();

                        let posicion = $(this).find(".Posicion");
                        let importe = $(this).find(".Importe");
                        let pagoReal = $(this).find(".Pagoreal");
                        let boton = $(this).find(".BotonHidden");
                        let botonE = $(this).find(".BotonConsularE");

                        if (ticket > 0) {
                            $.ajax({
                                url: "getTicket.php",
                                type: "post",
                                data: $(this).serialize(),
                                dataType: "json",
                                beforeSend: function (xhr) {
                                    clicksForm = 0;
                                },
                                success: function (response) {
                                    console.log(response);
                                    console.log("Isla-Posicion: " + isla_pos);

                                    if (response.corte !== corte) {
                                        alert("El ticket no pertenece al corte " + corte);
                                    } else if (response.isla_pos !== isla_pos) {
                                        alert("El ticket no pertenece a la isla " + isla_pos);
                                    } else if (response.cliente > 0) {
                                        alert("El ticket ya ha sido asignado al cliente " + response.nombre);
                                    } else {
                                        botonE.val("");
                                        botonE.hide();
                                        posicion.html(response.posicion);
                                        importe.html("$ " + response.importe);
                                        pagoReal.val(response.importe);
                                        boton.show();
                                        pagoReal.focus();
                                    }
                                },
                                error: function (jqXHR, ex) {
                                    console.log("Status: " + jqXHR.status);
                                    console.log("Uncaught Error.\n" + jqXHR.responseText);
                                    console.log(ex);
                                }
                            });
                        } else {
                            alert("Ticket invalido");
                        }
                    } else {
                        return;
                    }
                });

                $("#Ticket").change(function () {
                    $("#BotonEnviar").val("Enviar");
                    $("#BotonEnviar").show();
                    $("#BotonAgregar").hide();
                    $("#Pagoreal").val("");
                });

                $("#BotonEnviar").click(function (e) {
                    e.preventDefault();
                    let ticket = $("#Ticket").val();
                    let isla_pos = $("#IslaPosicion").val();
                    let corte = $("#Corte").val();

                    let pagoReal = $("#Pagoreal");
                    let boton = $("#BotonAgregar");
                    let botonE = $("#BotonEnviar");

                    if (ticket > 0) {
                        $.ajax({
                            url: "getTicket.php",
                            type: "post",
                            data: $("#form").serialize(),
                            dataType: "json",
                            beforeSend: function (xhr) {
                                clicksForm = 0;
                            },
                            success: function (response) {
                                console.log(response);
                                console.log("Isla-Posicion: " + isla_pos);
                                console.log("Corte: " + corte);

                                if (response.corte !== corte) {
                                    alert("El ticket no pertenece al corte " + corte);
                                } else if (response.isla_pos !== isla_pos) {
                                    alert("El ticket no pertenece a la isla " + isla_pos);
                                } else if (response.cliente > 0) {
                                    alert("El ticket ya ha sido asignado al cliente " + response.nombre);
                                } else {
                                    botonE.val("");
                                    botonE.hide();
                                    pagoReal.val(response.importe);
                                    boton.show();
                                    pagoReal.focus();
                                }
                            },
                            error: function (jqXHR, ex) {
                                console.log("Status: " + jqXHR.status);
                                console.log("Uncaught Error.\n" + jqXHR.responseText);
                                console.log(ex);
                            }
                        });
                    } else {
                        alert("Ticket invalido");
                    }
                });
            });
        </script>
    </body>
</html>

