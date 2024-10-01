<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("data/VentaAditivosDAO.php");

use com\softcoatl\utils as utils;

require "./services/InventarioPorFechasService.php";

//$registros = utils\IConnection::getRowsFromQuery($selectByDia);

$Id = 32; /* Número de en el orden de la tabla submenus */
$data = array("Nombre" => "Resumen inventario por fecha", "Reporte" => $Id, "Fecha" => $Fecha, "FechaF" => $FechaF,
    "Detallado" => $Detallado, "Desglose" => $Desglose,
    "Turno" => $Turno, "Textos" => "Subtotal", "Filtro" => "1");
$tipo_cliente = Array("Credito" => "Credito", "Contado" => "Contado", "Consignacion" => "Consignacion", "Monedero" => "Monederos",
    "Prepago" => "Prepago", "Puntos" => "Puntos", "Tarjeta" => "Tarjeta Bancaria", "Vales" => "Vales");
$Titulo = "Reporte inventario por rango de fechas $FechaIni al $FechaFin";
//echo $Sql;
$query = utils\IConnection::getRowsFromQuery($Sql);
$queryA = utils\IConnection::getRowsFromQuery($cSqlA);
if ($mysqli->query($selectBalanceCreate)) {
    $registros = utils\IConnection::getRowsFromQuery($selectBalance, $mysqli);
}
$usuarioSesion = getSessionUsuario();
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <!-- Sweetalert2@10 -->
        <script type="text/javascript" src="sweetalert2/sweetalert2.all.min.js"></script>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#FechaIni").val("<?= date("Y-m-d", strtotime($FechaIni)) ?>");
                $("#FechaFin").val("<?= date("Y-m-d", strtotime($FechaFin)) ?>");
                $(".AddAditivo").click(function (data) {
                    var fila, idProducto, Corte, Cantidad;
                    fila = $(this).closest("tr");
                    idProducto = parseInt($(this).closest('tr').find('td:eq(0)').text());
                    jQuery.ajax({
                        type: "POST",
                        url: "getByAjax.php",
                        dataType: "json",
                        cache: false,
                        data: {"Origen": "GetProducto", "IdProducto": idProducto, "FechaInicial": "<?= date("Y-m-d", strtotime($FechaIni)) ?>", "FechaFin": "<?= date("Y-m-d", strtotime($FechaFin)) ?>"},
                        success: function (data) {
                            var htmlAdd = "No. Cortes disponibles : ";
                            $.each(data[1], function (index, value) {
                                htmlAdd += value.id + ", ";
                            });
                            Swal.fire({
                                title: "Ajuste al producto <br>" + data[0][0].descripcion,
                                background: "#E9E9E9",
                                showConfirmButton: true,
                                confirmButtonText: "Agregar",
                                html: '<table style="width:100%;"><tr><td colspan="2">' + htmlAdd + '</td></tr>\n\
                                        <tr><td style="text-align:right;">Corte: </td><td style="text-align:left;"><input type="number" name="Corte" id="Corte" style="width:50px;"></td></tr>\n\
                                        <tr><td style="text-align:right;">Isla: </td><td style="text-align:left;"><input type="number" name="Posicion" id="Posicion" style="width:50px;"></td></tr>\n\
                                        <tr><td style="text-align:right;">Cantidad: </td><td style="text-align:left;"><input type="number" name="Cantidad" id="Cantidad" style="width:50px;"></td></tr></table>',
                                footer: '<?= $Text ?>',
                                icon: "warning",
                                backdrop: 'swal2-backdrop-show'
                            }).then((result) => {
                                Corte = $("#Corte").val();
                                Cantidad = $("#Cantidad").val();
                                Posicion = $("#Posicion").val();
                                console.log(result.isConfirmed);
                                console.log("ENTRAMOS");
                                if (result.isConfirmed) {
                                    jQuery.ajax({
                                        type: "POST",
                                        url: "getByAjax.php",
                                        dataType: "json",
                                        cache: false,
                                        data: {"Origen": "AgregaProducto", "IdProducto": idProducto, "Cantidad": Cantidad, "Posicion": Posicion, "Corte": Corte, "idUser": "<?= $usuarioSesion->getId() ?>", "Name": "<?= $usuarioSesion->getNombre() ?>"},
                                        success: function (data) {
                                            console.log(data);
                                            Swal.fire({
                                                title: data[0],
                                                background: "#E9E9E9",
                                                icon: "success"
                                            });
                                            var href = "inventarioPorFechas.php";
                                            $(location).attr('href', href);
                                        },
                                        error: function (data) {
                                            console.log("ERROR" + data);
                                            var href = "inventarioPorFechas.php";
                                            $(location).attr('href', href);
                                        }
                                    });
                                }
                                //                    
                            });
                        }
                    });
                });
            });
        </script>
    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo); ?>
            <div id="Reportes" style="min-height: 200px;"> 

                <table aria-hidden="true" style="margin-bottom: 35px;border: 1px solid #434343;border-radius: 5px;">
                    <tbody>
                        <?php
                        $Informacion = 1;
                        $nFac = 1000;
                        foreach ($registros as $rg) {
                            if (!empty($clave) && $clave !== $rg["clavei"]) {
                                if ($Informacion == TipoInformacion::OMICROM || $Informacion == TipoInformacion::COMPARATIVO) {
                                    ?>
                                    <tr class="subtotal">
                                        <td>Resumen</td>
                                        <td><?= number_format($InventarioI / $nFac, 3) ?></td>
                                        <td><?= number_format($Cargas / $nFac, 3) ?></td> 
                                        <td><?= number_format($Jarreos / $nFac, 3) ?></td>
                                        <?php if ($balance["valor"] == 1 && $Informacion === TipoInformacion::OMICROM) { ?>
                                            <td><?= number_format($Bruto / $nFac, 3) ?></td>
                                            <td><?= number_format($Diferencia / $nFac, 3) ?></td>
                                        <?php } ?>
                                        <?php if ($Informacion == TipoInformacion::COMPARATIVO) { ?>
                                            <td><?= number_format($VentasCV / $nFac, 3) ?></td>
                                        <?php } ?>
                                        <td><?= number_format($Ventas / $nFac, 3) ?></td> 
                                        <?php if ($incluir) { ?>
                                            <td><?= number_format($VtaExtra / $nFac, 3) ?></td> 
                                        <?php } ?>
                                        <?php if ($Informacion == TipoInformacion::COMPARATIVO) { ?>
                                            <td><?= number_format($CargasCV / $nFac, 3) ?></td> 
                                        <?php } ?>
                                        <td><?= number_format($InvTeorico / $nFac, 3) ?></td>
                                        <td><?= number_format($InventarioF / $nFac, 3) ?></td>
                                        <td><?= number_format(($InventarioF - $InventarioI + $Ventas - $Cargas - $VtaExtra) / $nFac, 3) ?></td>
                                    </tr>
                                    <?php
                                    $InvTeorico = $Ventas = $Cargas = $VtaExtra = $InvFinal = $VentasCV = $CargasCV = $Bruto = $Diferencia = $Jarreos = 0;
                                } else {
                                    ?>
                                    <tr class="subtotal">
                                        <td>Resumen</td>
                                        <td><?= number_format(0, 3) ?></td>
                                        <td><?= number_format($Cargas / $nFac, 3) ?></td> 
                                        <td><?= number_format(0, 3) ?></td>
                                        <td><?= number_format($Ventas / $nFac, 3) ?></td>  
                                        <td><?= number_format(0, 3) ?></td>
                                        <td><?= number_format(0, 3) ?></td>
                                    </tr>
                                    <?php
                                    $InvTeorico = $Ventas = $Cargas = $InvFinal = $VentasCV = $CargasCV = 0;
                                }
                            }

                            if (empty($clave) || $clave !== $rg["clavei"]) {
                                ?>
                                <tr class="titulo">
                                    <td colspan="10"><?= $rg["clave"] ?> &nbsp; <?= $rg["descripcion"] ?> &nbsp; <?= $rg["um"] ?></td>
                                </tr>
                                <tr class="titulos">
                                    <td width="15%">Fecha</td>
                                    <td>Inv.inicial</td>
                                    <td>Compras</td>
                                    <td>Jarreos</td>
                                    <?php if ($balance["valor"] == 1 && $Informacion === TipoInformacion::OMICROM) { ?>
                                        <td>Bruto</td>
                                        <td>Dif.</td>
                                    <?php } ?>
                                    <?php if ($Informacion == TipoInformacion::COMPARATIVO) { ?>
                                        <td>Ventas CV</td>
                                    <?php } ?>
                                    <td>Ventas</td>
                                    <?php if ($Informacion == TipoInformacion::COMPARATIVO) { ?>
                                        <td>Compras CV</td>
                                    <?php } ?>
                                    <td>Inv.Teorico</td>
                                    <td>Inv.Final</td>
                                    <td>Diferencia</td>
                                </tr>
                                <?php
                                $InventarioI = $rg["inicial"];
                            }

                            $clave = $rg["clavei"];

                            if ($Informacion == TipoInformacion::OMICROM || $Informacion == TipoInformacion::COMPARATIVO) {

                                $FechaLF = "DATE('" . $rg["fecha"] . "') ORDER BY fecha_hora_s  DESC LIMIT " . $rg["limite"] . "";
                                if ($rg["fecha"] !== date("Y-m-d")) {
                                    $FechaLF = "DATE_ADD('" . $rg["fecha"] . "',INTERVAL 1 DAY) ORDER BY fecha_hora_s  ASC LIMIT " . $rg["limite"] . "";
                                }

                                $selectLecturaFinal = " 
                                    SELECT SUM(cantidad) cantidad,fecha,fecha_hora_s 
                                    FROM (
                                        SELECT IFNULL(volumen_actual, 0) cantidad,DATE (fecha_hora_s) fecha, fecha_hora_s
                                        FROM tanques_h
                                        WHERE TRUE AND tanque IN (" . $rg["tanques"] . ") AND DATE( fecha_hora_s ) = $FechaLF
                                    ) t ";

                                $Ifin = utils\IConnection::execSql($selectLecturaFinal);
                                $Iinicial = $rg["inicial"];
                                $Ifinal = $Ifin["cantidad"];
                                $Compras = $busca === "1" ? $rg["compras"] : $rg["volumen_docto"];

                                if ($Informacion == TipoInformacion::COMPARATIVO) {
                                    $data = leer_archivo_zip_to_xml($rg["nombrearchivo"], $rg["claveProducto"], $rg["claveSubProducto"]);
                                }
                                $InvTeorico = $Iinicial - $rg["venta"] + $Compras + ($incluir ? $Rmd["cantidad"] : 0);

                                $date1 = new DateTime($rg["fecha"] . " 23:59:59");
                                $date2 = new DateTime($Ifin["fecha_hora_s"]);
                                $diff = $date1->diff($date2);
                                $difereciaFechas = ( ($diff->days * 24 ) * 60 ) + ( $diff->i ) . " minutos";
                                $style = "";
                                if ($diff->i > 5) {
                                    $style = "background-color: #F7FF7C";
                                }
                                ?>
                                <tr style="<?= $style ?>" title="Fin de muestra: <?= $Ifin["fecha_hora_s"] ?> Dif: <?= $difereciaFechas ?>">

                                    <td><?= $rg["fecha"] ?></td>
                                    <td class="numero"><?= number_format($Iinicial / $nFac, 3) ?></td>
                                    <td class="numero"><?= number_format($Compras / $nFac, 3) ?></td>
                                    <td class="numero"><?= number_format($rg["jarreos"] / $nFac, 3) ?></td>
                                    <?php if ($balance["valor"] == 1 && $Informacion === TipoInformacion::OMICROM) { ?>
                                        <td class="numero"><?= number_format($rg["bruto"] / $nFacc, 3) ?></td>
                                        <td class="numero"><?= number_format($rg["diferencia"] / $nFac, 3) ?></td>
                                    <?php } ?>
                                    <?php if ($Informacion == TipoInformacion::COMPARATIVO) { ?>
                                        <td class="numero"><?= number_format($data["venta"] / $nFac, 3) ?></td>
                                    <?php } ?>
                                    <td class="numero"><?= number_format($rg["venta"] / $nFac, 3) ?></td>
                                    <?php if ($Informacion == TipoInformacion::COMPARATIVO) { ?>
                                        <td class="numero"><?= number_format($data["compras"] / $nFac, 3) ?></td>
                                    <?php } ?>
                                    <td class="numero"><?= number_format($InvTeorico / $nFac, 3) ?></td>
                                    <td class="numero"><?= number_format($Ifinal / $nFac, 3) ?></td>
                                    <td class="numero"><?= number_format(($Ifinal - $InvTeorico) / $nFac, 3) ?></td>

                                </tr>
                                <?php
                                $Ventas += $rg["venta"];
                                $Jarreos += $rg["jarreos"];
                                $Cargas += $Compras;
                                $VtaExtra += ($incluir ? $Rmd["cantidad"] : 0);
                                $Bruto += $rg["bruto"];
                                $Diferencia += $rg["diferencia"];

                                $InvFinal = $Ifinal;
                                $InventarioF = $Ifinal;

                                $Tot_Bruto += $rg["bruto"];
                                $Tot_Dif += $rg["diferencia"];
                                $T_Ventas += $rg["venta"];
                                $T_Jarreos += $rg["jarreos"];
                                $T_Cargas += $Compras;
                                $T_VtaExtra += ($incluir ? $Rmd["cantidad"] : 0);

                                if ($Informacion == TipoInformacion::COMPARATIVO) {
                                    $VentasCV += $data["venta"];
                                    $CargasCV += $data["compras"];

                                    $T_VentasCV += $data["venta"];
                                    $T_CargasCV += $data["compras"];
                                }
                            } elseif ($Informacion == TipoInformacion::ARCHIVOS) {

                                $data = leer_archivo_zip_to_xml($rg["nombrearchivo"], $rg["claveProducto"], $rg["claveSubProducto"]);

                                $Iinicial = $data["disponible"] + $data["extraccion"] - $data["compras"];
                                $Ifinal = $data["disponible"];
                                $Teorico = $Iinicial - $data["venta"] + $data["compras"];
                                /**
                                 * Se toma la lectura final que arroja el sensor de tanques
                                 */
                                ?>
                                <tr>

                                    <td class="numero"><?= $rg["fecha"] ?></td>
                                    <td class="numero"><?= number_format($Iinicial / $nFac, 3) ?></td>
                                    <td class="numero"><?= number_format($data["compras"] / $nFac, 3) ?></td>
                                    <td class="numero"><?= number_format(0, 3) ?></td>
                                    <td class="numero"><?= number_format($data["venta"] / $nFac, 3) ?></td>
                                    <td class="numero"><?= number_format($Teorico / $nFac, 3) ?></td>
                                    <td class="numero"><?= number_format($Ifinal / $nFac, 3) ?></td>
                                    <td class="numero"><?= number_format(($Ifinal - $Teorico) / $nFac, 3) ?></td>

                                </tr>
                                <?php
                                $InvTeorico = $Iinicial;
                                $Ventas += $data["venta"];
                                $Cargas += $data["compras"];
                                $InvFinal = $Ifinal;

                                $T_Ventas += $data["venta"];
                                $T_Cargas += $data["compras"];
                            }
                        }
                        ?>
                        <tr class="subtotal">
                            <td>Resumen</td>
                            <td><?= number_format($InventarioI / $nFac, 3) ?></td>
                            <td><?= number_format($Cargas / $nFac, 3) ?></td> 
                            <td><?= number_format($Jarreos / $nFac, 3) ?></td>
                            <?php if ($balance["valor"] == 1 && $Informacion === TipoInformacion::OMICROM) { ?>
                                <td><?= number_format($Bruto / $nFac, 3) ?></td>
                                <td><?= number_format($Diferencia / $nFac, 3) ?></td>
                            <?php } ?>
                            <?php if ($Informacion == TipoInformacion::COMPARATIVO) { ?>
                                <td><?= number_format($VentasCV / $nFac, 3) ?></td>
                            <?php } ?>
                            <td><?= number_format($Ventas / $nFac, 3) ?></td> 
                            <?php if ($Informacion == TipoInformacion::COMPARATIVO) { ?>
                                <td><?= number_format($CargasCV / $nFac, 3) ?></td> 
                            <?php } ?>
                            <td><?= number_format($InvTeorico / $nFac, 3) ?></td>
                            <td><?= number_format($InventarioF / $nFac, 3) ?></td>
                            <td><?= number_format(($InventarioF - $InventarioI + $Ventas - $Cargas - $VtaExtra) / $nFac, 3) ?></td>
                        </tr>
                    </tbody>
                </table>
                <!-- Inventario de aditivos -->
                <table  style="margin-bottom: 5px;border: 1px solid #434343;border-radius: 5px;" summary="Inventario por periodo de fechas">
                    <tr style="font-weight: bold;">
                        <th>id</th>
                        <th>Producto</th>
                        <th style="max-width: 20%;">Descripcion</th>
                        <th>Inicial</th>
                        <th>Compras</th>
                        <th>Ventas</th>
                        <th>Ajuste</th>
                        <th>Cnt</th>
                        <th>Final</th>
                    </tr>
                    <?php
                    foreach ($query as $rs) {
                        $Titlep = $rs["AddCnt"] > 0 ? "Total de productos agregados " . $rs["AddCnt"] : "";
                        $Color = $rs["AddCnt"] > 0 ? "#DC7633" : "#069283";
                        $Cnttt = $rs["AddCnt"] > 0 ? $rs["AddCnt"] : 0;
                        ?>
                        <tr>
                            <td><?= $rs["id"] ?></td>
                            <td><?= $rs["clave_producto"] ?></td>
                            <td><?= $rs["descripcion"] ?></td>
                            <td><?= $rs["invI"] <> 0 ? $rs["invI"] : 0 ?></td>
                            <td><?= $rs["Compras"] <> 0 ? $rs["Compras"] : 0 ?></td>
                            <td><?= $rs["Ventas"] <> 0 ? $rs["Ventas"] : 0 ?></td>
                            <td title="<?= $Titlep ?>" style="text-align: center;"><i class="fa fa-plus-circle fa-lg AddAditivo" aria-hidden="true" style="color: <?= $Color ?>"></i></td>
                            <td><?= $Cnttt ?></td>
                            <td><?= $rs["invF"] <> 0 ? $rs["invF"] : 0 ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>
                <!-- Factura de Aditivos -->
                <table  style="margin-bottom: 5px;border: 1px solid #434343;border-radius: 5px;" summary="Inventario por periodo de fechas">
                    <tr style="font-weight: bold;">
                        <th>Clave</th>
                        <th >Descripcion</th>
                        <th>Piezas</th>
                        <th>Fact.Mostrador</th>
                        <th>Fact.General</th>
                        <th>Por Facturar</th>
                        <th>Total</th>
                    </tr>
                    <?php
                    $tol = $pza = $fm = $fp = $pf = 0;
                    foreach ($queryA as $rs) {
                        ?>
                        <tr>
                            <td><?= $rs["clave_producto"] ?></td>
                            <td><?= $rs["descripcion"] ?></td>
                            <td><?= $rs["Piezas"] ?></td>
                            <td><?= $rs["factMost"] ?></td>
                            <td><?= $rs["factPublico"] ?></td>
                            <td><?= $rs["porFacturar"] ?></td>
                            <td><?= $rs["total"] ?></td>
                        </tr>
                        <?php
                        $pza += $rs["Piezas"];
                        $fm += $rs["factMost"];
                        $fp += $rs["factPublico"];
                        $pf += $rs["porFacturar"];
                        $tol += $rs["total"];
                    }
                    ?>
                    <tr>
                        <td></td>
                        <td>Total</td>
                        <td class="numero"><?= number_format($pza, 0) ?></td>
                        <td class="numero"><?= number_format($fm, 0) ?></td>
                        <td class="numero"><?= number_format($fp, 0) ?></td>
                        <td class="numero"><?= number_format($pf, 0) ?></td>
                        <td class="numero"><?= number_format($tol, 2) ?></td>
                    </tr>
                </table>
            </div>

        </div>
        <div id="footer">
            <form name="formActions" method="post" action="" id="form" class="oculto">
                <div id="Controles">
                    <table aria-hidden="true">
                        <tr style="height: 40px;">
                            <td style="width: 80%;">
                                <table aria-hidden="true">
                                    <tr>
                                        <td>Fecha Inicial:</td>
                                        <td>
                                            <input type="date" id="FechaIni" name="FechaIni" style="margin-right: 40px;">
                                        </td>
                                        <td>Fecha Final:</td>
                                        <td>
                                            <input type="date" id="FechaFin" name="FechaFin">
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <span style="margin-left: 5%"><input type="submit" name="Boton" value="Enviar"></span>
                                <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                                <!--<span class="ButtonExcel"><a href="report_excel_resumen.php?<?= http_build_query($data) ?>"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true"></i></a></span>-->
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
            <?php topePagina(); ?>
        </div>
    </body>
</html>
