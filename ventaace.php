<?php
#Librerias
session_start();

include_once ("auth.php");
include_once ("authconfig.php");
include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$arrayFilter = array("Fecha" => date("Y-m-d"), "Disponible" => "N", "Corte" => "",
    "Turno" => "*", "Posicion" => "*", "Producto" => "*");
$request = utils\HTTPUtils::getRequest();
$nameSession = "VentaAditivos";
$session = new OmicromSession("vt.id", "vt.id", $nameSession, $arrayFilter, "Filtros");
$usuarioSesion = getSessionUsuario();
$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

foreach ($arrayFilter as $key => $value) {
    ${$key} = utils\HTTPUtils::getSessionBiValue($nameSession, $key);
}
if (strpos($session->getSessionAttribute("criteriaField"), "vt.id") === false || empty($busca)) {
    if (!empty($Fecha)) {
        $conditions .= "vt.fecha like '%" . $Fecha . "%' AND";
    }
    if ($Corte > 0) {
        $conditions .= " AND vt.corte = $Corte AND";
        $Turno = "*";
    }
    if ($Posicion !== '*' && trim($Posicion) !== "") {
        $conditions .= " AND vt.posicion = '$Posicion' AND";
    }
}
$Titulo = "Venta de aditivos";
$Id = 81;

$paginador = new Paginador($Id,
        "",
        "LEFT JOIN cli ON vt.cliente = cli.id",
        "",
        $conditions . " vt.tm = 'C' ",
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
            });
        </script>
        <?php $paginador->script(); ?>
    </head>

    <body>
        <?php BordeSuperior(); ?>

        <div id="TablaDatos">
            <table class="paginador" aria-hidden="true">
                <?php
                echo $paginador->headers(array("Editar", "Imp"), array());
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>
                        <td style="text-align: center;"><a href="<?= $cLink ?>?busca=<?= $row['id'] ?>"><i class="icon fa fa-lg fa-edit" aria-hidden="true"></i></a></td>
                        <td style="text-align: center;"><a href=javascript:winmin('impvtaace.php?busca=<?= $row['id'] ?>');><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></a></td>
                        <?php echo $paginador->formatRow(); ?>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>
        <?php
        $data = array("Nombre" => $Titulo, "Reporte" => 81,
            "Fecha" => $Fecha, "Corte" => $Corte,
            "Posicion" => $Posicion,
            "busca" => $busca, "Criterio" => $session->getSessionAttribute("criteriaField"));
        $nLink = array("<i class=\"icon fa fa-lg fa-download\" aria-hidden=\"true\"></i> Exportar" => "report_excel.php?" . http_build_query($data));
        echo $paginador->footer(false, $nLink, false, true);
        echo $paginador->filter();
        ?>
        <form name="form1" id="form1" method="post" action="">
            <table class="quicksearch" style="width: 100%;border-collapse: collapse; border: 1px solid #066;margin-top: 5px;" aria-hidden="true">
                <tr>
                    <td style="text-align: right;"> &nbsp;
                        Fecha: 
                        <input type="text" id="Fecha" name="Fecha" style="width: 150px;"> 
                        <img id="cFecha" src="libnvo/calendar.png" alt="Calendario" style="margin-right: 25px;">
                        &nbsp;&nbsp; Posicion: 
                        <select name="Posicion" class="nombre_cliente" id="Posicion"  style="margin-right: 25px;">
                            <?php
                            $sql2 = "SELECT '*' posicion
                                    UNION
                                    SELECT posicion FROM man 
                                    WHERE activo='Si' ORDER BY posicion";
                            $ManA = $mysqli->query($sql2);
                            while ($Man = $ManA->fetch_array()) {
                                echo "<option value='$Man[0]'>$Man[0]</option>";
                            }
                            ?>
                        </select>  
                        Corte: 
                        <input type="number" name="Corte" class="nombre_cliente"  min="1" max="10000" id="Corte" style="margin-right: 35px;"> 
                        <input class="nombre_cliente" type="submit" name="Filtros" id="Filtros" value="Buscar"  style="margin-right: 25px;">
                    </td>
                </tr>
            </table>
            <span id="message" style="text-align: center;color: red;font-weight: bold"></span>
            <input type="hidden" name="pagina" value="1">
        </form>
        <?php
        echo "<div class='mensajes'>$Msj</div>";
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
        <script type="text/javascript">
            $(document).ready(function () {
                $("#cFecha").css("cursor", "hand").click(function () {
                    displayCalendar($("#Fecha")[0], "yyyy-mm-dd", $(this)[0]);
                    $("#Corte").val("");
                });
                $("#Fecha").val("<?= $Fecha ?>");
                $("#Posicion").val("<?= $Posicion ?>");
                $("#Corte").val("<?= $Corte ?>");
            });
        </script>
    </body>
</html>