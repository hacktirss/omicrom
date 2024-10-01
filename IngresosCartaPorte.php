<?php
session_start();
set_time_limit(720);

include_once ("check.php");
include_once ("./comboBoxes.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$self = utils\HTTPUtils::self();

$Titulo = "Detalle complemento Carta Porte";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

require_once './services/IngresosCartaPorteService.php';

$CpVO = new \com\detisa\omicrom\CartaPorteVO();
$CpDAO = new \com\detisa\omicrom\CartaPorteIngresoDAO($busca, "CPI");
if (is_numeric($busca)) {
    $CpVO = $CpDAO->retrieve($busca, "origen = 'CPI' AND  id_origen");
}
$IngresosDAO = new IngresosDAO();
$IngresosVO = $IngresosDAO->retrieve($busca);
$ActuNvo = is_numeric($CpVO->getId()) ? "Actualizar" : "Nuevo";
$CiaDAO = new CiaDAO();
$ciaVO = $CiaDAO->retrieve(1);
$clienteVO = new ClientesVO();
$clienteDAO = new ClientesDAO();
$clienteVO = $clienteDAO->retrieve($IngresosVO->getId_cli());
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script type="text/javascript">
            $(document).ready(function () {
                if ("<?= $ActuNvo ?>" == "Nuevo") {
                    $("#HoraSalida").val("<?= date("Y-m-d\TH:i") ?>");
                    $("#Moneda").val("MXN");
                } else {
                    $("#HoraSalida").val("<?= $CpVO->getFechaHoraSalidaLlegada() ?>");
                    $("#Moneda").val("<?= $CpVO->getMoneda() ?>");
                    $("#Operador").val("<?= $CpVO->getId_operador() ?>");
                    $("#Direccion").val("<?= $CpVO->getId_direccion() ?>");
                    $("#Vehiculo").val("<?= $CpVO->getId_vehiculo() ?>");
                    $("#Embalaje").val("<?= $CpVO->getEmbalaje() ?>");

                    $("#Metododepago").val("<?= $IngresosVO->getMetodoPago() ?>");
                    $("#Formadepago").val("<?= $IngresosVO->getFormadepago() ?>");
                    $("#cuso").val("<?= $IngresosVO->getUsocfdi() ?>");
                    $("#Observaciones").val("<?= $IngresosVO->getObservaciones() ?>");
                    if ($("#CUnidad").val() === "") {
                        $("#CUnidad").val("LTR");
                    }
                }
                $("#Traslado").hide();
            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="trasladosd.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">
                    <?php
                    $pacA = $mysqli->query("SELECT * FROM proveedor_pac WHERE activo = 1");
                    $pac = $pacA->fetch_array();
                    if ($pac["pruebas"] == "1") {
                        ?>
                        <div  class="Factura_Modo_Demo">
                            ALERTA MODO DE DEMOSTRACIÓN
                        </div>
                        <?php
                    }
                    if ($IngresosVO->getUuid() === OrigenFacturaIngreso::SINTIMBRAR && $ActuNvo !== "Nuevo") {
                        ?>
                        <div class="facturaCp">
                            <a aria-hidden="true" data-toggle="modal"  data-target="#modal-de-carga" href="IngresosCartaPorte.php?op=Timbra">Timbrar Ingreso Carta Porte <i class="fa-solid fa-rocket"></i></a>
                        </div>
                        <?php
                    }
                    ?>
                    <div id="FormulariosBoots">
                        <div class="container no-margin">
                            <div class="row no-padding">
                                <div class="col-12 background no-margin">
                                    <div class="row no-padding">
                                        <div class="col-4 ">
                                            <div style="height: 17px;"><strong>Id : </strong> <?= $busca ?> <strong> Carta Porte</strong> <?= $CpVO->getId() ?> </div> 
                                            <div style="height: 17px;"><strong>Cantidad : </strong> <?= number_format($IngresosVO->getCantidad(), 2) ?></div>
                                        </div>
                                        <div class="col-4 row no-padding">
                                            <div style="width: 100%;height: 25px;"><strong>Nombre / R. Social :</strong> <?= $clienteVO->getNombre() ?></div>
                                            <?php
                                            $sql = "SELECT descripcion FROM cfdi33_c_regimenes WHERE clave = '" . ucwords(strtolower($clienteVO->getRegimenFiscal())) . "'";
                                            $rs = $mysqli->query($sql)->fetch_array();
                                            ?>
                                            <div style="width: 100%;height: 25px;"><strong>Regimen :</strong> <?= $rs["descripcion"] . ".- " . $clienteVO->getRegimenFiscal() ?></div>
                                        </div>
                                        <div class="col-4 row no-padding">
                                            <div style="width: 100%;height: 25px;"><strong>RFC :</strong> <?= $clienteVO->getRfc() ?></div>
                                            <div style="width: 100%;height: 25px;"><strong>Codigo Postal : </strong> <?= $clienteVO->getCodigo() ?></div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                if ($ActuNvo !== "Nuevo") {
                                    ?>
                                    <form name="formCartaPorteFc" id="formCartaPorte" method="post" action="IngresosCartaPorte.php" style="width: 100%;">
                                        <input type="hidden" name="BuscaCp" value="<?= $busca ?>">
                                        <div class="col-12 background no-margin" style="margin-top: 5px;">
                                            <div class="row no-padding">  
                                                <div id="DireccionesHead" class="col-12" style="font-weight: bold;height: 35px;background-color: #099; padding-top: 10px;padding-left: 40%;font-family: sans-serif; font-size: 15px;color:white">
                                                    Información fiscal
                                                </div>
                                            </div>
                                            <div class="row no-padding">  
                                                <div class="col-2 align-right">Metodo de pago: </div>
                                                <div class="col-6"><?php ComboboxMetodoDePago::generate("Metododepago", "350px"); ?></div>
                                            </div>
                                            <div class="row no-padding">  
                                                <div class="col-2 align-right">Forma de pago: </div>
                                                <div class="col-6"><?php ComboboxFormaDePago::generate("Formadepago", "350px"); ?></div>
                                            </div>
                                            <div class="row no-padding">  
                                                <div class="col-2 align-right">Uso CFDI: </div>
                                                <div class="col-3"><?= ComboboxUsoCFDI::generateByTypeCli("cuso", strlen($clienteVO->getRfc())); ?></div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-2 align-right">Observaciones: </div>
                                                <div class="col-6"><input type="text" name="Observaciones" id="Observaciones"></div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-2 align-right"> </div>
                                                <div class="col-6"><input type="submit" name="BotonFc" value="Actualizar"></div>
                                            </div>
                                        </div>
                                    </form>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                        <form name="formCartaPorte" id="formCartaPorte" method="post" action="IngresosCartaPorte.php">
                            <div id="FormulariosBoots">
                                <div class="container no-margin">
                                    <div class="row no-padding">
                                        <div class="col-12 background no-margin">
                                            <div class="row no-padding">
                                                <div id="DireccionesHead" class="col-12" style="font-weight: bold;height: 35px;background-color: #099; padding-top: 10px;padding-left: 40%;font-family: sans-serif; font-size: 15px;color:white">
                                                    Información general
                                                </div>
                                                <div class="col-2 ComprobanteShow align-right" style="padding-top: 15px;">
                                                    Fecha y hora de expedicion:
                                                </div>
                                                <div class="col-2 ComprobanteShow" style="padding-top: 15px;">
                                                    <input type="datetime-local" name="HoraSalida" id="HoraSalida" style="font-family: sans-serif;font-size: 11px;color: #55514e;" required>
                                                </div>
                                                <div class="col-2 ComprobanteShow align-right" style="padding-top: 15px;">
                                                    Moneda: 
                                                </div>
                                                <div class="col-4 ComprobanteShow" style="padding-top: 15px;">
                                                    <select name="Moneda" id="Moneda" style="width: 60%;">
                                                        <?php
                                                        $arrayDatos = CatalogosSelectores::getMonedas();
                                                        foreach ($arrayDatos as $key => $value) {
                                                            ?>
                                                            <option value="<?= $key ?>"/><?= $value ?></option>
                                                            <?php
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-2 ComprobanteShow align-right" style="padding-top: 5px;">
                                                    Clave del tipo de transporte:
                                                </div>
                                                <div class="col-8 ComprobanteShow " style="padding-top: 5px;">
                                                    <?php CatalogosSelectores::generateClaveCP("ClaveCP", "width:60%;"); ?>
                                                </div>
                                                <?php
                                                $BuscaP0 = "SELECT * FROM ingresos_detalle WHERE id = $busca AND producto = 0";
                                                $stArray = utils\IConnection::execSql($BuscaP0);
                                                ?>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-2 ComprobanteShow align-right" style="padding-top: 5px;">Costo:</div>
                                                <div class="col-2 ComprobanteShow " style="padding-top: 5px;">
                                                    <input type="number" name="CostoServicio" id="CostoServicio" value="<?= $stArray['preciob'] ?>">
                                                </div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-2 ComprobanteShow align-right" style="padding-top: 5px;">% de Retencion:</div>
                                                <div class="col-2 ComprobanteShow " style="padding-top: 5px;">
                                                    <input type="number" max="14" min="0" name="RetencionServicio" id="RetencionServicio" value="<?= $stArray["ieps"] ?>">
                                                </div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-12 ConceptosShow">
                                                    <table title="Conceptos de la carta porte" style="background-color: #F2F4F4;width: 80%;margin-left: 10%;margin-top: 20px; border: 1px #566573 solid;border-radius: 10px;">
                                                        <caption>Concepto a transportar</caption>
                                                        <tr>
                                                            <th style="text-align: center;">Descripcion</th>
                                                            <th style="text-align: center;">Clave</th>
                                                            <th style="text-align: center;">Unidad</th>
                                                            <th style="text-align: center;">Cantidad</th>
                                                            <th style="text-align: center;">Importe</th>
                                                        </tr>
                                                        <?php
                                                        $SelectCpds = "SELECT inv.descripcion,id.cantidad,id.importe,inv.inv_cproducto,inv.inv_cunidad FROM ingresos_detalle id "
                                                                . "LEFT JOIN inv ON id.producto = inv.id WHERE id.id = " . $busca . " AND producto > 0";
                                                        $Ts = $mysqli->query($SelectCpds);
                                                        while ($dt = $Ts->fetch_array()) {
                                                            (($nRng % 2) > 0) ? $Fdo = "" : $Fdo = "#D5D8DC";
                                                            ?>
                                                            <tr  style="background-color: <?= $Fdo ?>">
                                                                <td style="text-align: center;"><?= $dt["descripcion"] ?></td>
                                                                <td style="text-align: center;"><?= $dt["inv_cproducto"] ?></td>
                                                                <td style="text-align: center;"><?= $dt["inv_cunidad"] ?></td>
                                                                <td style="text-align: right;"><?= number_format($dt["cantidad"], 2) ?></td>
                                                                <td style="text-align: right;"><?= number_format($dt["importe"], 2) ?></td>
                                                            </tr>
                                                            <?php
                                                            $nRng++;
                                                            $Cnt = $Cnt + $dt["cantidad"];
                                                            $Imp = $Imp + $dt["importe"];
                                                        }
                                                        ?>
                                                        <tr>
                                                            <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                                                            <td style="text-align: right;"><strong><?= $Cnt ?></strong></td>
                                                            <td style="text-align: right;"><strong><?= $Imp ?></strong></td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-2 OperadoresShow align-right" style="padding-top: 5px;">
                                                    Operador :
                                                </div>
                                                <div class="col-7 OperadoresShow" style="padding-top: 5px;">
                                                    <select name="Operador" id="Operador">
                                                        <?php
                                                        $arrayDatos = CatalogosSelectores::getOperadores();
                                                        foreach ($arrayDatos as $key => $value) {
                                                            ?>
                                                            <option value="<?= $key ?>"/><?= $value ?></option>
                                                            <?php
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-2 OperadoresShow"  style="padding-top: 5px;">

                                                    <?php
                                                    $DireccionOperadores = 'operadorese.php?buscaO=' . $CpVO->getId_operador() . '&ReturnD=IngresosCartaPorte.php';
                                                    if ($ActuNvo !== "Nuevo") {
                                                        ?>
                                                        <a href="<?= $DireccionOperadores ?>"><i class="fa-solid fa-circle-question fa-lg"></i></a>
                                                        <?php
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-2 VehiculosShow align-right" style="padding-top: 5px;"> Vehiculo :</div>
                                                <div class="col-7 VehiculosShow" style="padding-top: 5px;">

                                                    <select name="Vehiculo" id="Vehiculo">
                                                        <?php
                                                        $arrayDatos = CatalogosSelectores::getVehiculos();
                                                        foreach ($arrayDatos as $key => $value) {
                                                            ?>
                                                            <option value="<?= $key ?>"/><?= $value ?></option>
                                                            <?php
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-2 OperadoresShow"  style="padding-top: 5px;">
                                                    <?php
                                                    if ($ActuNvo !== "Nuevo") {
                                                        ?>
                                                        <a href="vehiculose.php?buscaV=<?= $CpVO->getId_vehiculo() ?>&ReturnD=IngresosCartaPorte.php&busca=<?= $request->getAttribute("busca") ?>"><i class="fa-solid fa-circle-question fa-lg"></i></a>
                                                        <?php
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-2 VehiculosShow align-right" style="padding-top: 5px;">
                                                    Embalaje
                                                </div>
                                                <div class="col-7 VehiculosShow" style="padding-top: 5px;">
                                                    <select name="Embalaje" id="Embalaje">
                                                        <?php
                                                        $arrayDatos = CatalogosSelectores::getEmbalaje();
                                                        foreach ($arrayDatos as $key => $value) {
                                                            ?>
                                                            <option value="<?= $key ?>"/><?= $value ?></option>
                                                            <?php
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-2 VehiculosShow" style="padding-top: 5px;"></div>
                                                <div class="col-2 VehiculosShow" style="padding-top: 5px;">

                                                    <?php
                                                    if ($IngresosVO->getUuid() === OrigenFacturaIngreso::SINTIMBRAR) {
                                                        ?>
                                                        <input type="submit" name="Comprobante" value="<?= $ActuNvo ?>" >
                                                        <?php
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <?php
                        $BuscaTipot = "SELECT remolque FROM omicrom.cp_config_autotransp WHERE clave = (select conf_vehicular from catalogo_vehiculos WHERE id =" . $CpVO->getId_vehiculo() . ");";
                        $TipoConf = utils\IConnection::execSql($BuscaTipot);
                        if ($TipoConf["remolque"] == 1) {
                            ?>
                            <form name="formCartaPorteRemolques" id="formCartaPorteRemolques" method="post" action="IngresosCartaPorte.php">
                                <div id="FormulariosBoots">
                                    <div class="container no-margin">
                                        <div class="row no-padding">
                                            <div class="col-12 background no-margin">
                                                <div class="row no-padding">
                                                    <div id="DireccionesHead" class="col-12" style="font-weight: bold;height: 35px;background-color: #099; padding-top: 10px;padding-left: 40%;font-family: sans-serif; font-size: 15px;color:white">
                                                        Remoques
                                                    </div>
                                                    <div class="col-7 DireccionesShow CompleteOrg" style="padding-top: 15px;">
                                                        Remolque :
                                                        <select name="RemolqueCve" id="RemolqueCve">
                                                            <?php
                                                            $arrayDatos = CatalogosSelectores::getRemolque();
                                                            foreach ($arrayDatos as $key => $value) {
                                                                ?>
                                                                <option value="<?= $key ?>"/><?= $value ?></option>
                                                                <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-3 DireccionesShow CompleteOrg" style="padding-top: 15px;">
                                                        Placa :
                                                        <input type="text" name="Placa" id="Placa" style="font-family: sans-serif;font-size: 11px;color: #55514e;" required>
                                                    </div>
                                                    <div class="col-1 DireccionesShow CompleteOrg" style="padding-top: 15px;">
                                                        <br>
                                                        <?php
                                                        if ($IngresosVO->getUuid() === OrigenFacturaIngreso::SINTIMBRAR) {
                                                            ?>
                                                            <input type="submit" name="Remolque" value="Agregar Remolque" id="Remolque">
                                                            <?php
                                                        }
                                                        ?>
                                                    </div>
                                                    <?php
                                                    $IdNvo = is_numeric($CpVO->getId()) ? $CpVO->getId() : "";
                                                    $sql = "SELECT * FROM carta_porte_remolques WHERE id_carta_porte_fk = " . $CpVO->getId();
                                                    $r = 0;
                                                    if ($Cot = $mysqli->query($sql)) {
                                                        while ($co = $Cot->fetch_array()) {
                                                            $Or = $co["tipo"] == "Origen" ? 1 : 0;
                                                            ?>
                                                            <div class="ExisteDirecciones" style="background:#FFF;border-radius: 5px;border:1px solid #AFB0B2;padding: 5px;margin: 5px;width: 45%;">
                                                                <div style="width: 40%;display: inline-block;"><strong>Remolque :</strong> <?= $co["SubTipoRem"] ?></div>
                                                                <div style="width: 40%;display: inline-block;"><strong>Placa :</strong> <?= $co["placas"] ?></div>
                                                                <div style="width: 17%;display: inline-block;"> 
                                                                    <a href="IngresosCartaPorte.php?opDR=Si&nvoId=<?= $co["id"] ?>">
                                                                        <i class="fa fa-window-close fa-lg" aria-hidden="true"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                            <?php
                                                            $r++;
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        <?php } ?>
                        <?php
                        if ($ActuNvo !== "Nuevo") {
                            ?>
                            <div id="FormulariosBoots">
                                <div class="container no-margin">
                                    <div class="row no-padding"  style="width: 100%;">
                                        <div class="col-12 background no-margin"  style="width: 100%;">
                                            <form name="formCartaPorte5" id="formCartaPorte2" method="post" action="IngresosCartaPorte.php" title="Mostramos solo direcciónes de proveedores de gasolina">
                                                <div class="row no-padding">
                                                    <div id="DireccionesHead" class="col-12" style="font-weight: bold;height: 35px;background-color: #099; padding-top: 10px;padding-left: 40%;font-family: sans-serif; font-size: 15px;color: white;">
                                                        Direccion de origen
                                                        <input type="hidden" name="TipoT" value="Origen">
                                                    </div>
                                                    <div class="col-4 DireccionesShow QuitamosAddOrigen" style="padding-top: 15px;">
                                                        Direccion :
                                                        <select name="Direccion" id="Direccion" required>
                                                            <?php
                                                            $arrayDatos = CatalogosSelectores::getDireccion("'P'");
                                                            foreach ($arrayDatos as $key => $value) {
                                                                ?>
                                                                <option value="<?= $key ?>"/><?= $value ?></option>
                                                                <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-3 DireccionesShow QuitamosAddOrigen" style="padding-top: 15px;">
                                                        Fecha de partida:
                                                        <input type="datetime-local" name="HoraLlegada" id="HoraLlegada" required style=" width: 95%;font-family: sans-serif;font-size: 11px;color: #55514e;">
                                                    </div>
                                                    <div class="col-5 DireccionesShow QuitamosAddOrigen" style="padding-top: 15px;">
                                                        <br>

                                                        <?php
                                                        if ($IngresosVO->getUuid() === OrigenFacturaIngreso::SINTIMBRAR) {
                                                            ?>
                                                            <input type="submit" name="Direcciones" value="<?= $ActuNvo ?>">
                                                            <?php
                                                        }
                                                        ?>
                                                    </div>
                                                    <?php
                                                    $IdNvo = is_numeric($CpVO->getId()) ? $CpVO->getId() : "";
                                                    $sql = "SELECT cpd.tipo,cpds.estado,cpc.nombre,cpds.calle,cpds.codigo_postal,cpl.descripcion,
                                                cpds.descripcion,cpds.num_exterior,cpds.num_interior,cpd.fecha,cpd.distancia,cpd.id FROM carta_porte cp 
                                                LEFT JOIN carta_porte_destino cpd ON cpd.id_carta_porte_fk = cp.id 
                                                LEFT JOIN catalogo_direcciones cpds ON cpd.id_destino_fk = cpds.id 
                                                LEFT JOIN cp_colonia cpc ON cpds.colonia = cpc.colonia
                                                LEFT JOIN cp_localidad cpl ON cpds.localidad = cpl.localidad
                                                WHERE cp.id=" . $IdNvo . " AND cpd.origen='ING' AND cpc.codigo_postal = cpds.codigo_postal  AND cpd.tipo='Origen'
                                                GROUP BY cpd.id ORDER BY cpd.tipo;";
                                                    $HiddenHeaderOrigen = "";
                                                    if ($Cot = $mysqli->query($sql)) {
                                                        while ($co = $Cot->fetch_array()) {
                                                            $Or = $co["tipo"] == "Origen" ? 1 : 0;
                                                            $HiddenHeaderOrigen = "QuitamosAddOrigen";
                                                            ?>
                                                            <div class="ExisteDirecciones col-12 DireccionesShow" style="background:#FFF;border-radius: 5px;border:1px solid #AFB0B2;padding: 5px;margin: 5px;" class="">
                                                                <div style="width: 95%;padding-left: 95%;">
                                                                    <?php
                                                                    if ($IngresosVO->getUuid() === OrigenFacturaIngreso::SINTIMBRAR) {
                                                                        ?>
                                                                        <a href="IngresosCartaPorte.php?opD=Si&nvoId=<?= $co["id"] ?>">
                                                                            <i class="fa fa-window-close fa-2x" aria-hidden="true"></i>
                                                                        </a>
                                                                        <?php
                                                                    }
                                                                    ?>
                                                                </div>
                                                                <div style="width: 50%;display: inline-block;"><strong>Estado :</strong> <?= $co["estado"] ?></div>
                                                                <div style="display: inline-block;"><strong>Colonia :</strong> <?= $co["nombre"] ?></div>
                                                                <div style="width: 50%;display: inline-block;"><strong>Calle :</strong> <?= $co["calle"] ?></div>
                                                                <div style="display: inline-block;"><strong>Codigo Postal :</strong> <?= $co["codigo_postal"] ?></div>
                                                                <div style="width: 50%;display: inline-block;"><strong>Referencia :</strong> <?= $co["descripcion"] ?></div>
                                                                <div style="display: inline-block;"><strong>No. Int. </strong><?= $co["num_interior"] ?></div>
                                                                <div style="width: 50%;display: inline-block;"><strong>No. Ext. </strong><?= $co["num_exterior"] ?></div>
                                                                <div style="display: inline-block;"><strong>Fecha de Llegada :</strong> <?= $co["fecha"] ?></div>
                                                            </div>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                            </form>
                                            <form name="formCartaPorte6" id="formCartaPorte" method="post" action="IngresosCartaPorte.php" style="width: 100%;">
                                                <div class="row no-padding">
                                                    <div id="DireccionesHead" class="col-12" style="font-weight: bold;height: 35px;background-color: #099; padding-top: 10px;padding-left: 40%;font-family: sans-serif; font-size: 15px;color: white">
                                                        Direcciones de destino
                                                        <input type="hidden" name="TipoT" value="Destino">
                                                    </div>
                                                    <div class="col-3 DireccionesShow" style="padding-top: 15px;">
                                                        Direccion :
                                                        <select name="Direccion" id="Direccion">
                                                            <?php
                                                            $arrayDatos = CatalogosSelectores::getDireccion("'C','D'");
                                                            foreach ($arrayDatos as $key => $value) {
                                                                ?>
                                                                <option value="<?= $key ?>"/><?= $value ?></option>
                                                                <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-3 DireccionesShow" style="padding-top: 15px;">
                                                        Fecha de llegada:
                                                        <input type="datetime-local" name="HoraLlegada" id="HoraLlegada" style=" width: 95%;font-family: sans-serif;font-size: 11px;color: #55514e;" required>
                                                    </div>
                                                    <div class="col-2 DireccionesShow" style="padding-top: 15px;">Distancia :
                                                        <input type="text" name="Distancia" id="Distancia" required> 
                                                    </div>
                                                    <div class="col-1 DireccionesShow" style="padding-top: 15px;">
                                                        <br>

                                                        <?php
                                                        if ($IngresosVO->getUuid() === OrigenFacturaIngreso::SINTIMBRAR) {
                                                            ?>
                                                            <input type="submit" name="Direcciones" value="<?= $ActuNvo ?>">
                                                            <?php
                                                        }
                                                        ?>
                                                    </div>
                                                    <?php
                                                    $IdNvo = is_numeric($CpVO->getId()) ? $CpVO->getId() : "";
                                                    $sql = "SELECT cpd.tipo,cpds.estado,cpc.nombre,cpds.calle,cpds.codigo_postal,cpl.descripcion,
                                                cpds.descripcion,cpds.num_exterior,cpds.num_interior,cpd.fecha,cpd.distancia,cpd.id FROM carta_porte cp 
                                                LEFT JOIN carta_porte_destino cpd ON cpd.id_carta_porte_fk = cp.id 
                                                LEFT JOIN catalogo_direcciones cpds ON cpd.id_destino_fk = cpds.id 
                                                LEFT JOIN cp_colonia cpc ON cpds.colonia = cpc.colonia
                                                LEFT JOIN cp_localidad cpl ON cpds.localidad = cpl.localidad
                                                WHERE cp.id=" . $IdNvo . " AND cpd.origen='ING' AND cpc.codigo_postal = cpds.codigo_postal    AND cpd.tipo='Destino'
                                                GROUP BY cpd.id ORDER BY cpd.tipo;";
                                                    if ($Cot = $mysqli->query($sql)) {
                                                        while ($co = $Cot->fetch_array()) {
                                                            $Or = $co["tipo"] == "Origen" ? 1 : 0;
                                                            ?>
                                                            <div class="ExisteDirecciones col-5 DireccionesShow" style="background:#FFF;border-radius: 5px;border:1px solid #AFB0B2;padding: 5px;margin: 5px 75px 5px 5px;">
                                                                <div style="width: 95%;padding-left: 95%;">
                                                                    <?php
                                                                    if ($IngresosVO->getUuid() === OrigenFacturaIngreso::SINTIMBRAR) {
                                                                        ?>
                                                                        <a href="IngresosCartaPorte.php?opD=Si&nvoId=<?= $co["id"] ?>">
                                                                            <i class="fa fa-window-close fa-2x" aria-hidden="true"></i>
                                                                        </a>
                                                                        <?php
                                                                    }
                                                                    ?>
                                                                </div>
                                                                <div style="width: 50%;display: inline-block;"><strong>Estado :</strong> <?= $co["estado"] ?></div>
                                                                <div style="display: inline-block;"><strong>Colonia :</strong> <?= $co["nombre"] ?></div>
                                                                <div style="width: 50%;display: inline-block;"><strong>Calle :</strong> <?= $co["calle"] ?></div>
                                                                <div style="display: inline-block;"><strong>Codigo Postal :</strong> <?= $co["codigo_postal"] ?></div>
                                                                <div style="width: 50%;display: inline-block;"><strong>Distancia : </strong><?= $co["distancia"] ?> Km.</div>
                                                                <div  style="display: inline-block;"><strong>No. Int. </strong><?= $co["num_interior"] ?></div>
                                                                <div style="width: 50%;display: inline-block;"><strong>No. Ext. </strong><?= $co["num_exterior"] ?></div>
                                                                <div  style="display: inline-block;"><strong>Fecha de Llegada :</strong> <?= $co["fecha"] ?></div>
                                                                <div style="width: 100%;display: inline-block;"><strong>Referencia :</strong> <?= $co["descripcion"] ?></div>
                                                            </div>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </form>
                        <?php
                    }
                    ?>
                </td>
            </tr>
        </table>   
        <link rel="stylesheet" href="bootstrap/bootstrap-4.0.0/dist/css/bootstrap-modal.css" type="text/css">
        <?php include_once ("bootstrap/modals/modal_carcss.html"); ?>
        <?php
        BordeSuperiorCerrar();

        PieDePagina();
        ?>
    </body>
    <script type="text/javascript">
        $("body").on("shown.bs.modal", "#modal-de-carga", function (e) {
            var event = $(e.relatedTarget);
            console.log("ON");
        });
        $(document).ready(function () {
            if ("<?= $Or ?>" == 1) {
                $("#Origen").prop('disabled', true);
            }
            if ("<?= $r ?>" == 2) {
                $(".CompleteOrg").hide();
            }
            $("#AddDireccion").hide();
            $("#NvaDireccion").click(function () {
                $("#AddDireccion").show();
            });
            AjaxEstado($("#Estado").val(), "Estado");
            $("#Estado").change(function () {
                AjaxEstado($("#Estado").val(), "Estado");
            });
            $("#ClaveCP").val("<?= $IngresosVO->getClaveProdServ() ?>");
            $("#CodigoPostal").change(function () {
                if ($("#CodigoPostal").val().length == 5) {
                    AjaxCodigoPostal($("#CodigoPostal").val());
                }

            });
            if ("<?= $request->getAttribute("Buscar") ?>" == "Busca") {
                $("#Estado").val("<?= $request->getAttribute("Estado") ?>");
                $("#CodigoPostal").val("<?= $request->getAttribute("CodigoPostal") ?>");
                $("#AddDireccion").show();
            }
            $(".<?= $HiddenHeaderOrigen ?>").hide();
        });
        function AjaxEstado(dt) {
            jQuery.ajax({
                type: 'GET',
                url: 'getDirecciones.php',
                dataType: 'json',
                cache: false,
                data: {"Var": dt, "Origen": "Estado"},
                beforeSend: function (xhr) {
                    $('#Localidad').empty();
                    $('#Municipio').empty();
                },
                success: function (data) {
                    for (var dt of data)
                    {
                        for (var d of dt) {
                            if (typeof d["localidad"] != "string") {

                                $('#Municipio').append($('<option>', {
                                    value: d["clave"],
                                    text: d["clave"] + ".- " + d["descripcion"]
                                }));
                            } else {
                                $('#Localidad').append($('<option>', {
                                    value: d["id"],
                                    text: d["localidad"] + ".- " + d["descripcion"]
                                }));
                            }

                        }
                    }
                },
                error: function (jqXHR) {
                    console.log(jqXHR);
                }
            });
        }

        function AjaxCodigoPostal(dt) {
            jQuery.ajax({
                type: 'GET',
                url: 'getDirecciones.php',
                dataType: 'json',
                cache: false,
                data: {"Var": dt, "Origen": "CodigoPostal"},
                beforeSend: function (xhr) {
                    $('#Colonia').empty();
                },
                success: function (data) {
                    for (var dt of data)
                    {
                        console.log(dt["colonia"]);
                        $('#Colonia').append($('<option>', {
                            value: dt["colonia"],
                            text: dt["codigo_postal"] + ".- " + dt["nombre"]
                        }));
                    }
                },
                error: function (jqXHR) {
                    console.log(jqXHR);
                }
            });
        }

    </script>
</html>
