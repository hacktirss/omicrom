<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("comboBoxes.php");
include_once ("data/MensajesDAO.php");
include_once ("data/ClientesDAO.php");
include_once ("data/ClientesVO.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();
if ($request->hasAttribute("criteria")) {
    utils\HTTPUtils::setSessionValue("Cliente", $request->getAttribute("cliente"));
    utils\HTTPUtils::setSessionValue("Saldo", $request->getAttribute("saldo"));
}
$ClienteRs = utils\HTTPUtils::getSessionValue("Cliente");
$SaldoRs = utils\HTTPUtils::getSessionValue("Saldo");

$ClienteDAO = new ClientesDAO();
$ClienteVO = new ClientesVO();
$ClienteVO = $ClienteDAO->retrieve($ClienteRs);
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));
$Titulo = "Transfere tickets de clientes";
$Id = 5;
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php include './config_omicrom.php'; ?> 
        <title><?= $Gcia ?></title>
    </head>

    <body>
        <?php BordeSuperior(); ?>
        <input type="hidden" name="NameAuth" id="NameAuth" value="<?= $usuarioSesion->getNombre() ?>"> 
        <input type="hidden" name="IdAuth" id="IdAuth" value="<?= $ClienteRs ?>">
        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;width: 10%;" class="nombre_cliente" >
                    <a href="cxc.php"><div class="RegresarCss " alt="Flecha regresar" style="">Regresar</div></a>
                </td>
                <td style="vertical-align: top;">
                    <div id="FormulariosBoots">
                        <div class="container">
                            <div class="row background">
                                <div class="col-6 align-left title">Cliente: <span id="Producto"><?= $ClienteVO->getNombre() ?></span></div>
                                <div class="col-5 align-left title"><span id="Producto">Saldo : $<?= $SaldoRs ?></span></div>
                                <div class="col-1" style="text-align: right;padding-right:15px;"> <input type="button" name="Transferir" id="Transferir" value="Transferir"></div>
                            </div>

                            <div class="row background">                                
                                <div class="col-5 no-margin"> 
                                    Cliente
                                    <div style="position: relative;">
                                        <input type="search" style="width: 100%" class="texto_tablas" name="ClienteS" id="autocomplete" placeholder="Buscar cliente" required>
                                    </div>
                                    <div id="autocomplete-suggestions"></div>
                                </div>
                                <div class="col-5 no-margin">
                                    Producto : <?= ComboboxCombustibles::generate("ProductoTransfer", "95%") ?>
                                </div>
                                <div class="col-2 no-margin">
                                    Fecha : 
                                    <input type="date" name="FechaTransfer" id="FechaTransfer">
                                </div>                          
                                <div class="col-2 no-margin">
                                    Importe : <input type="text" name="ImporteTransfer" id="ImporteTransfer" placeholder="$ 0.00">
                                </div>
                                <div class="col-2 no-margin">
                                    Corte :
                                    <input type="number" name="CorteTransfer" id="CorteTransfer">
                                </div>
                                <div class="col-3 no-margin"></div>
                                <div class="col-3 no-margin">Importe : <div id="SumaTT" style="font-size: 19px;font-weight: bold;"></div></div>
                                <div class="col-2 no-margin" style="padding-top: 10px;"> 
                                    <input type="button" name="Busca_Ticket" id="Busca_Ticket" value="Busca" style="width: 100%; height: 25px;">
                                </div>
                            </div>
                            <div id="HtmlStructure"></div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <script>
            $(document).ready(function () {
                $("#autocomplete").activeComboBox(
                        $("[name='form1']"),
                        "SELECT data, value FROM (SELECT id as data, CONCAT(id, ' | ', tipodepago, ' | ', nombre) value FROM cli " +
                        "WHERE TRUE AND cli.tipodepago NOT REGEXP 'Contado|Puntos') sub WHERE TRUE",
                        "value"
                        );
                $('#autocomplete').focus();
//                $("#Transferir").hide();
                $("#Busca_Ticket").click(function () {
                    $.ajax({
                        type: "POST",
                        url: "getTicketsDisponibles.php",
                        data: {
                            "ClienteIngreso": <?= $ClienteRs ?>,
                            "ClienteExtraccion": $("#autocomplete").val(),
                            "Producto": $("#ProductoTransfer").val(),
                            "Importe": $("#ImporteTransfer").val(),
                            "Fecha": $("#FechaTransfer").val(),
                            "Corte": $("#CorteTransfer").val(),
                            "Op": "ObtenerTickets"
                        },
                        beforeSend: function (xhr, opts) {

                        },
                        success: function (data) {
                            $("#HtmlStructure").hide();
                            $("#HtmlStructure").show();
                            $("#HtmlStructure").html(data);
                            $("#Transferir").show();
                        },
                        error: function (jqXHR, ex) {
                            console.log("Status: " + jqXHR.status);
                            console.log("Uncaught Error.\n" + jqXHR.responseText);
                            console.log(ex);
                        }
                    });
                });
            });
        </script>
        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
    </body>
</html>
