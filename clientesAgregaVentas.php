<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("data/MensajesDAO.php");
include_once ("comboBoxes.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();

$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));
$Titulo = "Registros de tickets";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
    utils\HTTPUtils::setSessionValue("idCorte", 0);
    utils\HTTPUtils::setSessionValue("Importe", 0);
    utils\HTTPUtils::setSessionValue("Combustible", "");
    utils\HTTPUtils::setSessionValue("idUnidad", "");
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
if ($request->hasAttribute("idCorte")) {
    utils\HTTPUtils::setSessionValue("idCorte", $request->getAttribute("idCorte"));
}
if ($request->hasAttribute("Importe")) {
    utils\HTTPUtils::setSessionValue("Importe", $request->getAttribute("Importe"));
}
if ($request->hasAttribute("idUnidad")) {
    utils\HTTPUtils::setSessionValue("idUnidad", $request->getAttribute("idUnidad"));
}
if ($request->hasAttribute("Combustible")) {
    $Combustible = $request->getAttribute("Combustible") != "" ? "" : "";
    utils\HTTPUtils::setSessionValue("Combustible", $request->getAttribute("Combustible"));
}

$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);
$idCorte = utils\HTTPUtils::getSessionValue("idCorte");
$idUnidad = utils\HTTPUtils::getSessionValue("idUnidad");
$Importe = utils\HTTPUtils::getSessionValue("Importe");
$Combustible = utils\HTTPUtils::getSessionValue("Combustible");
require "./services/clientesAddTickets.php";
$Titulo = $idCorte > 0 ? $Titulo . "; Corte :" . $idCorte : $Titulo;
$Id = 5;
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php include './config_omicrom.php'; ?> 
        <title><?= $Gcia ?></title>
        <script type="text/javascript">
            $(document).ready(function () {
                $("#ImporteBuscar").hide();
                $("#ShowTickets").hide();
                if ("<?= $idCorte ?>" > 0) {
                    $("#ShowTickets").show();
                    $(".CtShow").hide();
                    $("#ImporteBuscar").show();
                }
                $("#idUnidad").val("<?= $idUnidad ?>");
                $("#Combustible").val("<?= $Combustible ?>");
                $("#Importe").val("<?= $Importe ?>");
                $("#divCierraTickets").click(function () {
                    Swal.fire({
                        title: "Este movimiento se vera afectado en el estado de cuenta del cliente.<br> ¿Estas seguro de continuar con el proceso?",
                        icon: "question",
                        iconColor: "#E74C3C",
                        time: 5000,
                        showConfirmButton: true,
                        confirmButtonText: "Aceptar",
                        showCancelButton: true,
                        cancelButtonText: "Cancelar",
                        cancelButtonColor: "#E74C3C"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'clientesAgregaVentas.php?Boton=LanzaProcesoCxcRm';
                        }
                    });
                });
            });
        </script>
    </head>

    <body>
        <?php BordeSuperior(); ?>

        <?php
        $BuscaCli = "SELECT * FROM cli WHERE id = " . $busca;
        $Bc = utils\IConnection::execSql($BuscaCli);

        $selectAbonos = "SELECT SUM(importe) importe FROM  cxc WHERE cliente = '$busca' AND tm = 'H' AND fecha <= DATE('" . date("Y-m-d") . "')";
        $resultAbonos = utils\IConnection::execSql($selectAbonos);
        $Abono = $resultAbonos["importe"];

        $selectCargos = "SELECT SUM(importe) importe FROM  cxc WHERE cliente = '$busca' AND tm = 'C' AND fecha <= DATE('" . date("Y-m-d") . "')";
        $resultCargos = utils\IConnection::execSql($selectCargos);
        $Cargo = $resultCargos["importe"];
        ?>
        <table style="width: 100%; height: 500px;font-family: sans-serif;">
            <tr>
                <td colspan="2" style="height: 25px;">
                    <table style="width: 98%;margin-left: auto;margin-right: auto;" class="texto_tablas" aria-hidden="true">
                        <tr style="background-color: #E1E1E1;height: 20px;">
                            <td style="width: 10%;"><strong>Id : </strong><?= $Bc["id"] ?></td>
                            <td style="width: 40%;"><strong>Nombre : </strong><?= $Bc["nombre"] ?></td>
                            <td style="width: 30%;"><strong>Alias : </strong><?= $Bc["alias"] ?></td>
                            <td style="width: 20%;"><strong>Saldo : </strong> <?= number_format($Abono - $Cargo, 2) ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td valign="top" style="padding-top: 40px;font-size: 11px;text-align: center;color: #2C3E50;"><a href="clientes.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>Regresar</td>
                <td valign="top"> 
                    <?php
                    $Coortes = "SELECT id,fecha,turno FROM omicrom.ct WHERE statusctv='Abierto' ORDER BY id DESC LIMIT 16;";
                    $rsCt = utils\IConnection::getRowsFromQuery($Coortes);
                    ?>
                    <div id="TablaDatos" style="min-height: 80px !important;">
                        <table class="paginador CtShow" aria-hidden="true"  style="max-height: 100px !important;min-width: 100%;">
                            <thead>
                                <tr>
                                    <th style="font-size: 20px; border-radius: 10px;" colspan="4" title="Solo se muestran cortes sin cerrar">Selecciona el corte del que deseas obtener tickets</th>
                                </tr>
                                <tr>
                                    <th style="width: 20%;">No. Corte</th>
                                    <th style="width: 50%">Fecha</th>
                                    <th style="width: 20%;">Turno</th>
                                    <th style="width: 10%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($rsCt as $ct) {
                                    ?>
                                    <tr>
                                        <td style="text-align: right;padding-right: 15px;"><?= $ct["id"] ?></td>
                                        <td><?= $ct["fecha"] ?></td>
                                        <td style="text-align: right;padding-right: 15px;"><?= $ct["turno"] ?></td>
                                        <td style="text-align: center;"><a href="clientesAgregaVentas.php?op=SeleccionaCorte&idCorte=<?= $ct["id"] ?>">Seleccionar</a></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                        <form>
                            <table style="border-radius: 20px;" id="ImporteBuscar">
                                <?php
                                $ImpDis = "select SUM(volumen), SUM(importe) importe, COUNT(1) cnt from rm  WHERE corte = $idCorte AND uuid = '-----' AND cliente = 0 AND importe > 0  AND importe = pesos";
                                $Rst = utils\IConnection::execSql($ImpDis);
                                ?>
                                <tr style="font-weight: bold;">
                                    <td colspan="2">Importe disponible calculado <?= number_format($Rst["importe"], 2) ?></td><td> No. Ventas <?= $Rst["cnt"] ?></td>
                                </tr>
                                <tr style="height: 30px;">
                                    <td>
                                        <strong>Importe : </strong>
                                        <input type="text" name="Importe" id="Importe" max="<?= $Rst["importe"] ?>">
                                    </td>
                                    <td>
                                        <strong>Unidad : </strong>
                                        <?php
                                        $UnidadCli = "SELECT * FROM unidades WHERE cliente = $busca;";
                                        $uCli = utils\IConnection::getRowsFromQuery($UnidadCli);
                                        ?>
                                        <select name="idUnidad" id="idUnidad">
                                            <?php
                                            foreach ($uCli as $uc) {
                                                ?>
                                                <option value="<?= $uc["id"] ?>"><?= $uc["codigo"] ?></option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td>
                                        <?= ComboboxCombustibles::generate("Combustible", "350px", "", "CUALQUIER COMBUSTIBLE") ?>
                                    </td>
                                    <td><input type="submit" name="Boton" value="Agregar"></td>
                                </tr>
                            </table>
                        </form>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2" valign="top">
                    <table style="width: 100%;border: 1px solid #006666;"id="ShowTickets">
                        <tr style="font-family: sans-serif;background-color: #006666;color:white;">
                            <td style="width: 50%; text-align: center;">Tickets del corte</td>
                            <td style="text-align: center;">Tickets preseleccionados</td>
                        </tr>
                        <tr>
                            <td>
                                <?php
                                $VentasDelCorte = "SELECT * FROM rm  WHERE corte = $idCorte AND uuid = '-----' AND cliente = 0 AND "
                                        . "importe > 0 AND importe = pesos AND enviado = 0 ORDER BY importe DESC";
                                $RsVdc = utils\IConnection::getRowsFromQuery($VentasDelCorte);
                                ?>      
                                <div class="container">
                                    <div class="content">
                                        <div id="TablaDatos" style="min-width: 100px !important;">
                                            <table class="paginador">
                                                <thead>
                                                    <tr>
                                                        <th>Cnt</th>
                                                        <th>Id</th>
                                                        <th>Producto</th>
                                                        <th>Fecha</th>
                                                        <th>Cantidad</th>
                                                        <th>Importe</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $v = 0;
                                                    foreach ($RsVdc as $vdc) {
                                                        if ($_SESSION['VentasBorra'][$vdc["id"]] == 0) {
                                                            $v++;
                                                            ?>
                                                            <tr>
                                                                <td><?= $v ?></td>
                                                                <td><?= $vdc["id"] ?></td>
                                                                <td><?= $vdc["producto"] ?></td>
                                                                <td><?= $vdc["inicio_venta"] ?></td>
                                                                <td style="text-align: right;"><?= number_format($vdc["volumen"], 2) ?></td>
                                                                <td style="text-align: right;"><?= number_format($vdc["importe"], 2) ?></td>
                                                                <td style="text-align: center;"><a class="TransferenciaEfectiva" href="clientesAgregaVentas.php?Boton=AddTicket&idTicket=<?= $vdc["id"] ?>"><i class="fa-solid fa-file-import fa-lg" style="color: #006666;"></i></a></td>
                                                            </tr>
                                                            <?php
                                                            $vtt += $vdc["volumen"];
                                                            $itt += $vdc["importe"];
                                                        }
                                                    }
                                                    ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td style="text-align: right;font-weight: bold;" colspan="4">Total : </td>
                                                        <td style="text-align: right;font-weight: bold;" ><?= number_format($vtt, 2) ?></td>
                                                        <td style="text-align: right;font-weight: bold;" ><?= number_format($itt, 2) ?></td>
                                                        <td></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="container">
                                    <div class="content">
                                        <div id="TablaDatos" style="min-width: 100% !important;">
                                            <table class="paginador">
                                                <thead>
                                                    <tr>
                                                        <th>Cnt</th>
                                                        <th>Id</th>
                                                        <th>Producto</th>
                                                        <th>Fecha</th>
                                                        <th>Cantidad</th>
                                                        <th>Importe</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $vvg = $_SESSION['Ventas'];
                                                    $cn = 1;
                                                    foreach ($vvg as $vv) {
                                                        if ($vv > 0) {
                                                            $VentasDelCorte = "SELECT * FROM rm  WHERE id = " . $vv;
                                                            $Vslc = utils\IConnection::execSql($VentasDelCorte);
                                                            ?>
                                                            <tr>
                                                                <td><?= $cn ?></td>
                                                                <td><?= $Vslc["id"] ?></td>
                                                                <td><?= $Vslc["producto"] ?></td>
                                                                <td><?= $Vslc["inicio_venta"] ?></td>
                                                                <td style="text-align: right;padding-right: 10px;"><?= number_format($Vslc["volumen"], 2) ?></td>
                                                                <td style="text-align: right;padding-right: 10px;"><?= number_format($Vslc["importe"], 2) ?></td>
                                                                <td style="text-align: center;"><a class="TransferenciaEfectiva" href="clientesAgregaVentas.php?Boton=DeleteTicket&idRlTicket=<?= $Vslc["id"] ?>&idTicket=<?= $cn - 1 ?>"><i class="fa-solid fa-file-circle-minus fa-lg"></i></a></td>
                                                            </tr>
                                                            <?php
                                                            $SmV += $Vslc["volumen"];
                                                            $SmI += $Vslc["importe"];
                                                            $cn++;
                                                        }
                                                    }
                                                    ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr style="font-weight: bold;">
                                                        <td colspan="4" style="text-align: right;">Total : </td>
                                                        <td style="text-align: right;padding-right: 10px;"><?= number_format($SmV, 2) ?></td>
                                                        <td style="text-align: right;padding-right: 10px;"><?= number_format($SmI, 2) ?></td>
                                                        <td></td>
                                                    </tr>
                                                    <tr style="font-weight: bold;">
                                                        <td colspan="5" style="text-align: right;">Requerido : </td>
                                                        <td style="text-align: right;padding-right: 10px;"></td>
                                                        <td style="text-align: right;padding-right: 10px;"><?= number_format($Importe, 2) ?></td>
                                                        <td style="text-align: right;padding-right: 10px;"><?= $Importe == 0 ? "" : number_format($Importe - $SmI, 2) ?></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align: center;">
                                <?php
                                if ($SmI > 0) {
                                    ?>
                                    <div id="divCierraTickets">
                                        Relacionar tickets al cliente
                                    </div>
                                    <?php
                                }
                                ?>
                            </td>
                        </tr>
                    </table>

                </td>
            </tr>
        </table>
        <div class="texto_tablas" align="center"><?= $Msj ?></div>

        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
    </body>
</html>
<style>

    td .container {
        width: 100%;
        height: 350px;
        overflow: auto; /* Agregamos un overflow */
        border: 1px solid #ccc;
    }
    #divCierraTickets{
        width: 40%;
        margin-left: 30%;
        border:1px solid #006666;
        height: 30px;
        border-radius: 10px;
        padding-top: 5px;
        background-color: #ff6633;
        color: white;
    }
    #divCierraTickets:hover{
        font-weight: bold;
        background-color: #FFA04C;
    }

</style>