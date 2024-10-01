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

require_once './services/TrasladosCartaPorteService.php';

$CpVO = new \com\detisa\omicrom\CartaPorteVO();
$CpDAO = new \com\detisa\omicrom\CartaPorteDAO($busca, "TCP");
if (is_numeric($busca)) {
    $CpVO = $CpDAO->retrieve($busca, "origen = 'TCP' AND  id_origen");
}
$TrasladosDAO = new TrasladosDAO();
$TrasladosVO = $TrasladosDAO->retrieve($busca);
$ActuNvo = is_numeric($CpVO->getId()) ? "Actualizar" : "Nuevo";
$CiaDAO = new CiaDAO();
$ciaVO = $CiaDAO->retrieve(1);
$clienteVO = new ClientesVO();
$clienteDAO = new ClientesDAO();
$clienteVO = $clienteDAO->retrieve($TrasladosVO->getId_cli());
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
                } else {
                    $("#HoraSalida").val("<?= $CpVO->getFechaHoraSalidaLlegada() ?>");
                    $("#Operador").val("<?= $CpVO->getId_operador() ?>");
                    $("#Direccion").val("<?= $CpVO->getId_direccion() ?>");
                    $("#Vehiculo").val("<?= $CpVO->getId_vehiculo() ?>");
                    $("#Embalaje").val("<?= $CpVO->getEmbalaje() ?>");
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
                    if ($TrasladosVO->getUuid() === OrigenFacturaTraslados::SINTIMBRAR) {
                        ?>
                        <div style="padding-left: 70%;width: 100%;background-color: #52BE80; color: white; text-align:center; font-family: Helvetica, Arial, Verdana, Tahoma, sans-serif; font-size:14px; font-weight:bold;border-radius: 10px;">
                            <a href="TrasladosCartaPorte.php?op=Timbra">Timbrar Ingreso Carta Porte</a>
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
                                        </div>
                                        <div class="col-4 ">
                                            <div style="height: 17px;"><strong>Cantidad : </strong> <?= number_format($TrasladosVO->getCantidad(), 2) ?></div>
                                        </div>
                                        <div class="col-4 ">                                            
                                            <div style="height: 17px;"><strong>Uso de CFDI : </strong> <?= $TrasladosVO->getUsoCfdi() ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <form name="formCartaPorte" id="formCartaPorte" method="post" action="TrasladosCartaPorte.php">
                            <div id="FormulariosBoots">
                                <div class="container no-margin">
                                    <div class="row no-padding">
                                        <div class="col-12 background no-margin">
                                            <div class="row no-padding">
                                                <div class="col-6 ComprobanteShow" style="padding-top: 15px;">
                                                    Fecha y hora de expedicion:<br>
                                                    <input type="datetime-local" name="HoraSalida" id="HoraSalida" style="font-family: sans-serif;font-size: 11px;color: #55514e;" required>
                                                </div>
                                                <div class="col-3 ComprobanteShow" style="padding-top: 15px;">Moneda: 
                                                    <select name="Moneda" id="Moneda">
                                                        <option value="XXX" selected=""/>XXX</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-12 ConceptosShow">
                                                    <table title="Conceptos de la carta porte" style="background-color: #F2F4F4;width: 80%;margin-left: 10%;margin-top: 20px; border: 1px #566573 solid;border-radius: 10px;">
                                                        <caption>Concepto a transportar</caption>
                                                        <tr>
                                                            <th>Descripcion</th>
                                                            <th>Clave</th>
                                                            <th>Unidad</th>
                                                            <th>Cantidad</th>
                                                            <th>Importe</th>
                                                        </tr>
                                                        <?php
                                                        $SelectCpds = "SELECT inv.descripcion,td.cantidad,td.importe,inv.inv_cproducto,inv.inv_cunidad FROM traslados_detalle td "
                                                                . "LEFT JOIN inv ON td.producto = inv.id WHERE td.id = " . $busca . " AND producto > 0";
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
                                                            <td colspan="3"  style="text-align: right;"><strong>Total:</strong></td>
                                                            <td style="text-align: right;"><strong><?= $Cnt ?></strong></td>
                                                            <td style="text-align: right;"><strong><?= $Imp ?></strong></td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-6 OperadoresShow" style="padding-top: 15px;">
                                                    Operador :
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
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-6 VehiculosShow" style="padding-top: 15px;">
                                                    Vehiculo :
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
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-8 VehiculosShow" style="padding-top: 15px;">Embalaje
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
                                                <div class="col-4 VehiculosShow align-right" style="padding-top: 15px;">
                                                    <br>
                                                    <?php
                                                    if ($TrasladosVO->getUuid() === OrigenFacturaTraslados::SINTIMBRAR) {
                                                        ?>
                                                        <input type="submit" name="Comprobante" value="<?= $ActuNvo ?>" style="margin-right: 25px;">
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
                            <form name="formCartaPorteRemolques" id="formCartaPorteRemolques" method="post" action="TrasladosCartaPorte.php">
                                <div id="FormulariosBoots">
                                    <div class="container no-margin">
                                        <div class="row no-padding">
                                            <div class="col-12 background no-margin">
                                                <div class="row no-padding">
                                                    <div id="DireccionesHead" class="col-12" style="height: 35px;background-color: #ABB2B9; padding-top: 10px;padding-left: 40%;font-family: sans-serif; font-size: 15px;">
                                                        Remoques
                                                    </div>
                                                    <div class="col-7 DireccionesShow" style="padding-top: 15px;">
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
                                                    <div class="col-3 DireccionesShow" style="padding-top: 15px;">
                                                        Placa :
                                                        <input type="text" name="Placa" id="Placa" style="font-family: sans-serif;font-size: 11px;color: #55514e;">
                                                    </div>
                                                    <div class="col-1 DireccionesShow" style="padding-top: 15px;">
                                                        <br>
                                                        <?php
                                                        if ($TrasladosVO->getUuid() === OrigenFacturaTraslados::SINTIMBRAR) {
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
                                                                <div style="width: 95%;padding-left: 75%;">
                                                                    <?= $co["tipo"] ?>
                                                                    <a href="TrasladosCartaPorte.php?opDR=Si&nvoId=<?= $co["id"] ?>">
                                                                        <i class="fa fa-window-close fa-lg" aria-hidden="true"></i>
                                                                    </a>
                                                                </div>
                                                                <div><strong>Remolque :</strong> <?= $co["SubTipoRem"] ?></div>
                                                                <div><strong>Placa :</strong> <?= $co["placas"] ?></div>
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
                        <form name="formCartaPorte5" id="formCartaPorte2" method="post" action="TrasladosCartaPorte.php">
                            <div id="FormulariosBoots">
                                <div class="container no-margin">
                                    <div class="row no-padding">
                                        <div class="col-12 background no-margin">
                                            <div class="row no-padding">
                                                <div id="DireccionesHead" class="col-12" style="height: 35px;background-color: #ABB2B9; padding-top: 10px;padding-left: 40%;font-family: sans-serif; font-size: 15px;">
                                                    Direcciones
                                                </div>
                                                <div class="col-3 DireccionesShow" style="padding-top: 15px;">
                                                    Direccion :
                                                    <select name="Direccion" id="Direccion">
                                                        <?php
                                                        $arrayDatos = CatalogosSelectores::getDireccion();
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
                                                    <input type="datetime-local" name="HoraLlegada" id="HoraLlegada" style="font-family: sans-serif;font-size: 11px;color: #55514e;">
                                                </div>
                                                <div class="col-2 DireccionesShow" style="padding-top: 15px;">Distancia :
                                                    <input type="text" name="Distancia" id="Distancia" placeholder="Km"> 
                                                </div>
                                                <div class="col-2 DireccionesShow" style="padding-top: 15px;">
                                                    <br>
                                                    <select name="TipoT">
                                                        <option value="Destino">Destino</option>
                                                        <option id="Origen" value="Origen">Origen</option>
                                                    </select>
                                                </div>
                                                <div class="col-1 DireccionesShow" style="padding-top: 15px;">
                                                    <br>

                                                    <?php
                                                    if ($TrasladosVO->getUuid() === OrigenFacturaTraslados::SINTIMBRAR) {
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
                                                WHERE cp.id=" . $IdNvo . " AND cpd.origen='TRA' AND cpc.codigo_postal = cpds.codigo_postal  
                                                GROUP BY cpd.id ORDER BY cpd.tipo;";
                                                if ($Cot = $mysqli->query($sql)) {
                                                    while ($co = $Cot->fetch_array()) {
                                                        $Or = $co["tipo"] == "Origen" ? 1 : 0;
                                                        ?>
                                                        <div class="ExisteDirecciones" style="background:#FFF;border-radius: 5px;border:1px solid #AFB0B2;padding: 5px;margin: 5px;" class="col-5 DireccionesShow">
                                                            <div style="width: 95%;padding-left: 75%;">
                                                                <?= $co["tipo"] ?>
                                                                <?php
                                                                if ($TrasladosVO->getUuid() === OrigenFacturaTraslados::SINTIMBRAR) {
                                                                    ?>
                                                                    <a href="TrasladosCartaPorte.php?opD=Si&nvoId=<?= $co["id"] ?>">
                                                                        <i class="fa fa-window-close fa-lg" aria-hidden="true"></i>
                                                                    </a>
                                                                    <?php
                                                                }
                                                                ?>
                                                            </div>
                                                            <div><strong>Estado :</strong> <?= $co["estado"] ?></div>
                                                            <div><strong>Colonia :</strong> <?= $co["nombre"] ?></div>
                                                            <div><strong>Calle :</strong> <?= $co["calle"] ?></div>
                                                            <div><strong>Codigo Postal :</strong> <?= $co["codigo_postal"] ?></div>
                                                            <div><strong>Referencia :</strong> <?= $co["descripcion"] ?></div>
                                                            <div><strong>No. Int. </strong><?= $co["num_interior"] ?></div>
                                                            <div><strong>No. Ext. </strong><?= $co["num_exterior"] ?></div>
                                                            <div><strong>Fecha de Llegada :</strong> <?= $co["fecha"] ?></div>
                                                            <div><strong>Distancia : </strong><?= $co["distancia"] ?> Km.</div>
                                                        </div>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                </td>
            </tr>
        </table>
        <?php
        BordeSuperiorCerrar();

        PieDePagina();
        ?>

    </body>
    <script type="text/javascript">
        $(document).ready(function () {
            if ("<?= $Or ?>" == 1) {
                $("#Origen").prop('disabled', true);
            }
            if ("<?= $r ?>" == 2) {
                $("#Remolque").hide();
            }
            $("#AddDireccion").hide();
            $("#NvaDireccion").click(function () {
                $("#AddDireccion").show();
            });
            AjaxEstado($("#Estado").val(), "Estado");
            $("#Estado").change(function () {
                AjaxEstado($("#Estado").val(), "Estado");
            });
            $("#ClaveCP").val("<?= $TrasladosVO->getClaveProductoServicio() ?>");
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
