<?php
#Librerias
session_start();
set_time_limit(300);

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("data/FcDAO.php");

use com\softcoatl\utils as utils;

$request = utils\Request::instance();
$mysqli = iconnect();

$usuarioSesion = getSessionUsuario();
$arrayFilter = array("fmt" => $request->has("fmt") ? $request->get("fmt") : 0,
    "tipo" => $request->has("tipo") ? $request->get("tipo") : 1);
$nameSession = "catalogoFacturas";
$session = new OmicromSession("fc.id", "fc.id", $nameSession, $arrayFilter, "tipo");

foreach ($arrayFilter as $key => $value) {
    ${$key} = utils\HTTPUtils::getSessionBiValue($nameSession, $key);
}

$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$conditions = "";
if (!empty($session->getSessionAttribute("returnLink"))) {
    $rLink = $session->getSessionAttribute("returnLink");
    $conditions = " fc.status = '" . StatusFactura::CERRADO . "'";
}
if ($request->get("criteria") === "ini") {
    utils\HTTPUtils::setSessionValue("BusquedaStatus", 4);
}
if ($tipo == 2) {
    $Titulo = "Módulo para facturar Público en general";
    $conditions = empty($conditions) ? "cli.rfc LIKE '" . FcDAO::RFC_GENERIC . "'" : $conditions . " AND cli.rfc LIKE '" . FcDAO::RFC_GENERIC . "'";
    $conditions .= " AND fc.origen <> 3";
} elseif ($tipo == 3) {
    $Titulo = "Módulo de facturas en Linea";
    $conditions = empty($conditions) ? "cli.rfc NOT LIKE '%" . FcDAO::RFC_GENERIC . "%'" : $conditions . " AND cli.rfc NOT LIKE '%" . FcDAO::RFC_GENERIC . "%'";
    $conditions .= " AND fc.origen = 3";
} else {
    $conditions = empty($conditions) ? "cli.rfc NOT LIKE '%" . FcDAO::RFC_GENERIC . "%'" : $conditions . " AND cli.rfc NOT LIKE '%" . FcDAO::RFC_GENERIC . "%'";
    $conditions .= " AND fc.origen <> 3";
    if ($fmt == 1) {
        $Titulo = "Módulo para facturar tickets";
    } else {
        $Titulo = "Módulo para facturar";
    }
}
if ($request->has("BusquedaStatus")) {
    if ((int) $request->get("BusquedaStatus") >= 0) {
        utils\HTTPUtils::setSessionValue("BusquedaStatus", (int) $request->get("BusquedaStatus"));
    }
}

$AddSql = "";
if (utils\HTTPUtils::getSessionValue("BusquedaStatus") >= 0 && utils\HTTPUtils::getSessionValue("BusquedaStatus") <= 3) {
    $AddSql = " AND fc.status = " . utils\HTTPUtils::getSessionValue("BusquedaStatus");
}

$Id = 53;

$paginador = new Paginador($Id,
        "fc.id, fc.uuid, fc.status, fc.origen, fc.stCancelacion, cli.tipodepago, cli.rfc receptor, cfp.descripcion",
        "LEFT JOIN cli ON fc.cliente = cli.id 
        LEFT JOIN cfdi33_c_fpago cfp ON fc.formadepago = cfp.clave",
        "",
        $conditions . $AddSql,
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "REGEXP",
        "");

$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
$cLink = substr($self, 0, strrpos($self, ".")) . 'e.php';
$cLinkd = substr($self, 0, strrpos($self, ".")) . 'd.php';

$pop = 0;
$FcVO = new FcVO();
$FcDAO = new FcDAO();
$Vvl = "";
if ($request->has("pop")) {
    $pop = 1;
    $FcVO = $FcDAO->retrieve($request->get("idp"));
    $Vvl = $FcVO->getUuid();
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom.php"; ?>
        <title><?= $Gcia ?></title>
        <script>
            var popVar = "<?= $pop ?>";
            pop = function () {
                if (popVar === "1") {
                    wingral('enviafile.php?file=fc&id=<?= $Vvl ?>&type=pdf&formato=<?= $request->get("fmp") ?>');
                }
            };
            $(document).ready(function () {
                $("#autocomplete").focus();
                $("#BusquedaStatus").val("<?= utils\HTTPUtils::getSessionValue("BusquedaStatus") ?>");

                $("body").on("shown.bs.modal", "#modal-series", function (e) {
                    var event = $(e.relatedTarget);
                    var Identificador = event.data("identificador");
                    var modalTitle = "Series de facturación ";
                    var modal = $(this);

                    modal.find(".modal-title").html(modalTitle);
                });

                $(".ClickActualiza").click(function () {
                    var Botton = this;
                    var Date = $(this).parent().parent().find('td:eq(2)').children().get();
                    var serie = $(this).parent().parent().find('td:eq(0)');
                    var vals = Date[0].value
                    var valre = false;
                    var Msj = "";
                    if (Date[0].value.length <= 9) {
                        if (Date[0].value !== "" || Date[0].value === null) {
                            Swal.fire({
                                title: "Este cambio, alterara tus series y folios de las facturas. <br>¿Estas seguro de realizar este cambio?",
                                background: "#E9E9E9",
                                cancelButtonColor: "#EC7063",
                                showCancelButton: true,
                                confirmButtonText: "Estoy seguro",
                                cancelButtonText: "Cancelar",
                                icon: "warning",
                                reverseButtons: true
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    jQuery.ajax({
                                        type: "POST",
                                        url: "getByAjax.php",
                                        dataType: "json",
                                        cache: false,
                                        data: {"Origen": "ActualizaSeries", "Llave": Botton.dataset.llave, "Value": Date[0].value, "Usr": "<?= $usuarioSesion ?>"},
                                        success: function (data) {
                                            if (data.Success) {
                                                serie.html("");
                                                serie.html(vals);
                                                $(".Nams").val("");
                                            }
                                            Swal.fire({
                                                icon: data.img,
                                                position: 'top-end',
                                                iconColor: 'green',
                                                title: data.Msj,
                                                background: data.color,
                                                toast: true,
                                                timer: 3000,
                                                showConfirmButton: false
                                            });
                                        }
                                    });
                                }
                            });
                        } else {
                            Msj = "El valor no puede ir vacío o nulo<br> ¡Favor de verificar!";
                            valre = true;
                        }
                    } else {
                        Msj = "El valor no puede exceder mas de 9 caracteres <br> ¡Favor de verificar!";
                        valre = true;
                    }
                    if (valre == true) {
                        /*ENTRAMOS ALGUN ERROR*/
                        Swal.fire({
                            icon: "error",
                            position: 'top-end',
                            iconColor: 'red',
                            title: Msj,
                            background: "#FADBD8",
                            confirmButtonColor: "#EC7063",
                            toast: true,
                            timer: 5000,
                            showConfirmButton: true
                        });
                    }
                });

            });
        </script>
        <style type="text/css">
            #Selector{
                border: 0px solid #fff !important;
                padding-bottom: 10px !important;
            }
        </style>
        <?php $paginador->script(); ?>
    </head>

    <body onload="pop();">

        <?php BordeSuperior(); ?>

        <div id="Selector">
            <table aria-hidden="true" style="border: 1px solid #808B96;border-radius: 15px;">
                <tbody>
                    <tr>
                        <?php if ($tipo == 2) { ?>
                            <td style="background-color: #CACACA;width: 33%;border-radius: 15px 0px 0px 15px;"><a href="facturas.php?tipo=1">Facturación a Clientes</a></td>
                            <td style="background-color: #CACACA;width: 33%;"><a href="facturas.php?tipo=3">Facturación en Linea</a></td>
                            <td style="background-color: #FF6633;width: 33%;border-radius: 0px 15px 15px 0px;">Facturación Público en General</td>
                        <?php } elseif ($tipo == 3) { ?>
                            <td style="background-color: #CACACA;width: 33%;border-radius: 15px 0px 0px 15px;"><a href="facturas.php?tipo=1">Facturación a Clientes</a></td>
                            <td style="background-color: #FF6633;width: 33%;">Facturación en Linea</td>
                            <td style="background-color: #CACACA;width: 33%;border-radius: 0px 15px 15px 0px;"><a href="facturas.php?tipo=2">Facturación Público en General</a></td>
                        <?php } else { ?>
                            <td style="background-color: #FF6633;width: 33%;border-radius: 15px 0px 0px 15px;">Facturación a Clientes</td>
                            <td style="background-color: #CACACA;width: 33%;"><a href="facturas.php?tipo=3">Facturación en Linea</a></td>
                            <td style="background-color: #CACACA;width: 33%;border-radius: 0px 15px 15px 0px;"><a href="facturas.php?tipo=2">Facturación Público en General</a></td>
                        <?php } ?>
                    </tr>
                </tbody>
            </table>
        </div>
        <form name="form1" method="post" action="">
            <div style="font-family: sans-serif ;font-size: 12px;display: inline-block;width: 98%;text-align: right">
                Status : 
                <div class="content-select">
                    <select name="BusquedaStatus" id="BusquedaStatus" onchange="form1.submit();">
                        <option value="4">Todos</option>
                        <option value="1">Timbrado</option>
                        <option value="3">Cancelado S/T</option>
                        <option value="2">Cancelado</option>
                        <option value="0">Abierto</option>
                    </select>
                    <em></em>
                </div>
            </div>
            <?php
            $VC = "SELECT * FROM variables_corporativo WHERE llave = 'PermisoCambio'";
            $VcR = utils\IConnection::execSql($VC);
            if ($VcR["valor"] == 1) {
                if ($usuarioSesion->getLevel() == 9 && $usuarioSesion->getTeam() === "Administrador") {
                    ?>
                    <div style="font-family: sans-serif ;font-size: 12px;display: inline-block;font-weight: bold;"  data-toggle="modal"  data-target="#modal-series" >
                        Series <em class="icon fa fa-lg fa-edit"></em>
                    </div>
                    <?php
                }
            }
            ?>
        </form>
        <div id="TablaDatos">
            <table class="paginador" aria-hidden="true">
                <?php
                if (empty($session->getSessionAttribute("returnLink"))) {
                    echo $paginador->headers(array("Editar", "Detalle", "Pdf", "Xml"), array("Status", "Origen"));
                    while ($paginador->next()) {
                        $row = $paginador->getDataRow();
                        $title = "Id: " . $row["id"] . " Tipo de cliente: '" . $row["tipodepago"] . "' Forma de pago: '" . $row["descripcion"] . "' UUID: " . $row["uuid"];
                        ?>
                        <tr title="<?= $title ?>">
                            <td style="text-align: center;"><a href="<?= $cLink ?>?busca=<?= $row['id'] ?>"><i class="icon fa fa-lg fa-edit" aria-hidden="true"></i></a></td>
                            <td style="text-align: center;"><a href="<?= $cLinkd ?>?criteria=ini&cVarVal=<?= $row['id'] ?>"><i class="icon fa fa-lg fa-file-text" aria-hidden="true"></i></a></td>
                            <?php
                            if ($row['uuid'] !== FcDAO::SIN_TIMBRAR && !empty($row['uuid'])) {
                                if ($row['status'] != StatusFactura::CANCELADO) {
                                    if ($row['receptor'] !== FcDAO::RFC_GENERIC) {
                                        ?>
                                        <td style="text-align: center;">
                                            <a style="color: red;" href="javascript:winuni('enviafile.php?id=<?= $row['uuid'] ?>&type=pdf&formato=0')"><i class="icon fa fa-lg fa-file-pdf-o" title="Obtener PDF Tamaño Carta" aria-hidden="true"></i></a>
                                            <a style="color: graytext;" href="javascript:winuni('enviafile.php?id=<?= $row['uuid'] ?>&type=pdf&formato=1')"><i class="icon fa fa-lg fa-file-pdf-o" title="Obtener PDF Formato Ticket" aria-hidden="true"></i></a>
                                        </td>
                                    <?php } else {
                                        ?>
                                        <td style="text-align: center;">
                                            <a style="color: red;" href="javascript:winuni('enviafile.php?id=<?= $row['uuid'] ?>&type=pdf&formato=2')">
                                                <i class="icon fa fa-lg fa-file-pdf-o" title="Obtener PDF Tamaño Carta" aria-hidden="true"></i>
                                            </a>
                                        </td>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <td style="text-align: center;">
                                        <a style="color: red;" href="javascript:winuni('acusecanpdf.php?table=fc&busca=<?= $row['id'] ?>')">
                                            <i class="icon fa fa-lg fa-file-pdf-o" title="Obtener Acuse de Cancelación" aria-hidden="true"></i>
                                        </a>
                                    </td>
                                <?php }
                                ?>
                                <td style="text-align: center;">
                                    <a href="enviafile.php?id=<?= $row['uuid'] ?>&type=xml">
                                        <i class="icon fa fa-lg fa-file-code-o" aria-hidden="true"></i>
                                    </a>
                                </td>
                            <?php } else {
                                ?>
                                <td/>
                                <td/>
                                <?php
                            }
                            echo $paginador->formatRow();
                            ?>
                            <td style="text-align: center;" width="150px">
                                <a href="canfactura.php?busca=<?= $row['id'] ?>">
                                    <?= statusCFDI($row["status"]) ?>
                                    <?php if ($row['status'] == StatusFactura::CANCELADO && $row['stCancelacion'] != StatusCancelacionFactura::CANCELADA_CONFIRMADA) { ?>
                                        <i class="icon fa fa-lg fa-clock-o" title="En proceso de cancelación" aria-hidden="true"></i>
                                    <?php }
                                    ?>
                                </a>
                            </td>
                            <td style="text-align: center;" class="origen"><?= origenCFDI($row["origen"]) ?></td>
                        </tr>
                        <?php
                    }
                } else {
                    echo $paginador->headers(array(" "), array("Origen"));
                    while ($paginador->next()) {
                        $row = $paginador->getDataRow();
                        ?>
                        <tr>
                            <td style="text-align: center;"><a href="<?= $rLink ?>Factura=<?= $row['id'] ?>">seleccionar</a></td>
                            <?php echo $paginador->formatRow(); ?>
                            <td style="text-align: center;"><?= origenCFDI($row["origen"]) ?></td>
                        </tr>
                        <?php
                    }
                }
                ?> 
            </table>
        </div>

        <?php
        $nLink = array();
        if (!empty($session->getSessionAttribute("backLink"))) {
            $nLink["<i class=\"icon fa fa-lg fa-arrow-circle-left\" aria-hidden=\"true\"></i> Regresar"] = $session->getSessionAttribute("backLink");
        }
        echo $paginador->footer(($usuarioSesion->getLevel() >= 7 && empty($session->getSessionAttribute("returnLink")) && $tipo !== "3") || ($usuarioSesion->getLevel() == 6) || ($usuarioSesion->getRol() == 2), $nLink);
        echo $paginador->filter();
        echo "<div class='mensajes'>$Msj</div>";
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
    </body>
</html>
<div class="modal fade" id="modal-series">
    <div class="modal-dialog modal-lg">
        <form name="formModal1" id="formModal1" method="post" action="">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">                                    
                    <div class="form-group row">
                        <div class="col-12">
                            <div id="div_print">
                                <?php
                                $Series = "SELECT llave,valor,descripcion FROM variables_corporativo where llave like '%serie_%';";
                                $Srs = utils\IConnection::getRowsFromQuery($Series);
                                ?>
                                <table aria-hidden="true" style="width: 100%;border: 1px solid #808B96;border-radius: 5px;">
                                    <thead>
                                        <tr style="background-color: #52BE80;color:white">
                                            <th scope = "col" style="width: 15%;">Serie</th>
                                            <th scope = "col">Descripcion</th>
                                            <th></th>
                                            <th scope = "col">Actualizar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($Srs as $ssr) {
                                            ?>
                                            <tr style="height: 45px;">
                                                <td><?= $ssr["valor"] ?></td>
                                                <td><?= $ssr["descripcion"] ?></td>
                                                <td>
                                                    <input type="text" name="NvaSerie" class="Nams">
                                                </td>
                                                <td>
                                                    <input type="button" name="Actualiza" value="Actualiza" class="ClickActualiza" data-llave="<?= $ssr["llave"] ?>">
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>                        
                    </div>
                </div>                
            </div>
            <!-- /.modal-content -->
            <input type="hidden" name="Identificador" class="Identificador">
            <input type="hidden" name="ParamValidator" class="ParamValidator">
        </form>
    </div>
</div>
