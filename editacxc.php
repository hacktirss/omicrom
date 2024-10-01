<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$arrayFilter = array("Fecha" => "", "Tm" => "*", "Factura" => "", "Cliente" => "*");
$nameSession = "catalogoCxc";
$session = new OmicromSession("cxc.fecha", "cxc.fecha", $nameSession, $arrayFilter, "Filtros");

$Id = 45;
$Titulo = "Registro de cuentas por cobrar";

$busca = $session->getSessionAttribute("criteria");
$Msj = utf8_encode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

foreach ($arrayFilter as $key => $value) {
    ${$key} = utils\HTTPUtils::getSessionBiValue($nameSession, $key);
}

$conditions = "cxc.cliente > 0 ";

if ($Cliente !== '*' && trim($Cliente) !== "") {
    $conditions .= " AND cxc.cliente = '$Cliente'";
}
if (!empty($Fecha)) {
    $conditions .= "AND DATE(cxc.fecha) = DATE('$Fecha')";
}
if ($Tm !== '*' && trim($Tm) !== "") {
    $conditions .= " AND cxc.tm = '$Tm'";
}
if ($Factura !== "*" && trim($Factura) !== "") {
    $conditions .= " AND cxc.factura = '$Factura' ";
}

$paginador = new Paginador($Id,
        "cxc.id",
        "LEFT JOIN cli on cxc.cliente = cli.id",
        "",
        $conditions,
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

?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#autocomplete").focus();
                
                $('#Fecha').val('<?= $Fecha ?>').attr('size', '8').addClass('texto_tablas');
                $('#cFecha').css('cursor', 'hand').click(function () {
                    displayCalendar($('#Fecha')[0], 'yyyy-mm-dd', $(this)[0]);
                    $('#Corte').val('');
                });    
                $('#Tm').val('<?= $Tm ?>').addClass('texto_tablas');
                $('#Factura').val('<?= $Factura ?>').addClass('texto_tablas');
                $('#Cliente').val('<?= $Cliente ?>').addClass('texto_tablas');
            });
        </script>
        <?php $paginador->script(); ?>
    </head>

    <body>

        <?php BordeSuperior(); ?>
        
        <div id="TablaDatos">
             <table class="paginador" aria-hidden="true">
                <?php
                echo $paginador->headers(array("Editar"), array());
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>
                        <td style="text-align: center;"><a href="<?= $cLink ?>?busca=<?= $row['id'] ?>"><i class="icon fa fa-lg fa-edit" aria-hidden="true"></i></a></td>
                        <?php
                        echo $paginador->formatRow();
                        ?>
                    </tr>
                <?php }
                ?> 
            </table>
        </div>
        <?php
        echo $paginador->footer($usuarioSesion->getLevel() >= 7);
        echo $paginador->filter();
        echo "<div class='mensajes'>$Msj</div>";
        ?>
       <form name="form1" id="form1" method="post" action="">
            <table class="texto_tablas" style="width: 100%;border-collapse: collapse; border: 1px solid #066;margin-top: 5px;" aria-hidden="true">
                <tr>
                    <td style="background-color: #f1f1f1"> &nbsp;
                        &nbsp;&nbsp;Cliente: 
                        <select name='Cliente' id="Cliente" style="width: 200px">
                            <?php
                            echo "<option value='*'>Todos</option>";
                            $sql2 = "SELECT id,nombre FROM cli WHERE id > 10 AND tipodepago<>'Contado' ORDER BY id";
                            $ManA = $mysqli->query($sql2);
                            while ($Man = $ManA->fetch_array()) {
                                echo "<option value='$Man[0]'>$Man[0] | $Man[1]</option>";
                            }
                            ?>
                        </select>

                        &nbsp; &nbsp;Fecha: 
                        <input type="text" id="Fecha" name="Fecha"> 
                        <img id="cFecha" src="libnvo/calendar.png" alt="Calendario">

                        &nbsp; &nbsp;Tipo de movimiento: 
                        <select name='Tm' id="Tm">
                            <option selected value='*'> * </option>
                            <option value='C'>Cargo</option>
                            <option value='H'>Abono</option>
                        </select>

                        &nbsp;&nbsp;&nbsp Factura: 
                        <input type='number' name='Factura'  min='1' max='1000000' id="Factura"> 

                        &nbsp;&nbsp;<input class='nombre_cliente' type='submit' name='Filtros' id="Filtros" value='Buscar'>

                    </td>
                </tr>
            </table>
            <span id="message" style="text-align: center;color: red;font-weight: bold"></span>
            <input type="hidden" name="pagina" value="1">

        </form>
        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
    </body>
</html>