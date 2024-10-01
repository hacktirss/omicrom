<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();

require_once './services/CambioTurnoService.php';

$Titulo = "Gastos del corte $Corte ";
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$ctVO = new CtVO();
if ($Corte > 0) {
    $ctVO = $ctDAO->retrieve($Corte);
}

$cSql = "SELECT ctpagos.corte,ctpagos.cliente,cli.nombre as nombrec,ctpagos.concepto,
        ctpagos.importe,ctpagos.idnvo 
        FROM ctpagos LEFT JOIN cli ON ctpagos.cliente=cli.id
        WHERE ctpagos.corte = '$Corte' 
        ORDER BY ctpagos.idnvo";

$result = $mysqli->query($cSql);

$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
$returnLink = "movpagos.php";
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php include './config_omicrom.php'; ?>    
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#returnLink").val("<?= $returnLink ?>");
                $("#Concepto").focus();
            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>
        <?php TotalizaCorte(); ?>

        <?php
        if ($ctVO->getStatusctv() === StatusCorte::ABIERTO) {
            ?>
            <div style='width: 100%;text-align: left' class="texto_tablas">
                <form name='form1' method='get' action=''>
                    <input type="hidden" name="returnLink" id="returnLink">
                    &nbsp;<input type='text' name='Concepto' id='Concepto' size='30' class='texto_tablas' placeholder='Capture su concepto' onkeyup="mayus(this);" required="required">
                    &nbsp;Importe: &nbsp;<input type='text' name='Importe' value='' size='10' class='texto_tablas' required="required"> 
                    &nbsp;<input type='submit' name='Boton' value='Agregar' class='nombre_cliente'>
                </form>
            </div>
            <?php
        }
        ?>

        <div style="text-align: center;color: #990000;"><?= $Msj ?></div>
        
        <div id="TablaDatos">
            <table aria-hidden="true">
                <tr>
                    <td class="fondoNaranja">Transaccion</td>
                    <td class="fondoNaranja">Cta</td>
                    <td class="fondoNaranja">Cliente</td>
                    <td class="fondoNaranja">Concepto</td>
                    <td class="fondoNaranja">Importe</td>
                    <td class="fondoNaranja">Borrar</td>
                </tr>

                <?php
                while ($rg = $result->fetch_array()) {
                    ?>
                    <tr>
                        <td align="right"><?= $rg["idnvo"] ?></td>
                        <td><?= $rg["cliente"] ?></td>
                        <td><?= substr(ucwords(strtolower($rg["nombrec"])), 0, 40) ?></td>
                        <td><?= $rg["concepto"] ?></td>
                        <td align="right"><?= number_format($rg["importe"], "2") ?></td>
                        <td style="text-align: center;">
                            <?php if ($ctVO->getStatusctv() === StatusCorte::ABIERTO) { ?>
                                <a class="textosCualli_i_n" href=javascript:confirmar("Deseas&nbsp;eliminar&nbsp;el&nbsp;registro?","<?= $self ?>?cId=<?= $rg["idnvo"] ?>&op=Gastos&returnLink=<?= $returnLink ?>");><i class="icon fa fa-lg fa-trash" aria-hidden="true"></i></a>
                            <?php } ?>
                        </td>                       
                    </tr>
                    <?php
                    $nImpCre += $rg["importe"];
                }
                ?>

                <tr>
                    <td class="upTitles" colspan="4"><strong>Total: </strong></td>                                                   
                    <td class="upTitles" style="color: #F63"><?= number_format($nImpCre, "2") ?></td>
                    <td class="upTitles"></td>
                </tr>
            </table>
        </div> 

        <?php echo $paginador->footer(false, null, false, false, 0, false); ?>

        <?php BordeSuperiorCerrar() ?>
        <?php PieDePagina(); ?>

    </body>
</html>
