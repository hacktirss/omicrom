<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();

require_once './services/CambioTurnoService.php';

$Titulo = "Depósitos del corte $Corte ";
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$ctVO = new CtVO();
if ($Corte > 0) {
    $ctVO = $ctDAO->retrieve($Corte);
}

$objectVO = new BancosVO();

if ($request->hasAttribute("Banco")) {
    $tipo = substr($request->getAttribute("Banco"), 0, 1);
    $valor = substr($request->getAttribute("Banco"), 1);
    $objectVO = $bancosDAO->retrieve($valor);
    if ($tipo === "B") {
        $Concepto = "Fec: " . $ctVO->getFecha() . " corte: $Corte tno: " . $ctVO->getTurno();
    } elseif ($tipo === "D") {
        $Concepto = "Faltantes del corte: " . $Corte;
    }
}
$addSql = $ctVO->getStatus() === "Cerrado" && $ctVO->getStatusctv() === "Abierto" ? "" : "bancos.id > 0 AND";
$selectEgr = "SELECT egr.clave,bancos.banco nombre,bancos.cuenta,bancos.concepto cptcuenta,egr.concepto,
            egr.importe,egr.id,egr.tipo_cambio,egr.plomo
            FROM egr, bancos 
            WHERE $addSql TRUE AND egr.clave = bancos.id AND bancos.rubro = '" . RubroBanco::EGRESOS . "'
            AND egr.corte = '$Corte' AND egr.importe <> 0
            ORDER BY egr.clave ASC";
$registros = utils\IConnection::getRowsFromQuery($selectEgr);

$selectEgr_ = "SELECT egr.clave,bancos.banco nombre,bancos.cuenta,bancos.concepto cptcuenta,egr.concepto,
            egr.importe,egr.id,egr.tipo_cambio,egr.plomo
            FROM egr, bancos 
            WHERE TRUE AND egr.clave = bancos.id AND bancos.rubro = '" . RubroBanco::VENDEDORES . "'
            AND egr.corte = '$Corte' AND egr.importe <> 0
            ORDER BY egr.clave ASC";
$registros_ = utils\IConnection::getRowsFromQuery($selectEgr_);

$selectBancos = "SELECT id,banco,cuenta,concepto FROM bancos WHERE activo = 1 AND rubro = '" . RubroBanco::EGRESOS . "' ORDER BY id";
$registrosBancos = utils\IConnection::getRowsFromQuery($selectBancos);

$selectVendedores = "SELECT bancos.id, ven.id vendedor, ven.nombre FROM ven,bancos 
                    WHERE TRUE AND ven.id = bancos.cuenta AND bancos.rubro = '" . RubroBanco::VENDEDORES . "'
                    AND ven.id >=  50 AND ven.activo = 'Si' 
                    ORDER BY ven.id";
$registrosVendedores = utils\IConnection::getRowsFromQuery($selectVendedores);

$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
$returnLink = "movgastos.php";

$VcDepositovBruto = "SELECT valor FROM variables_corporativo WHERE llave= 'depositoBruto'";
$Db = utils\IConnection::execSql($VcDepositovBruto);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <script type="text/javascript" src="js/GeneracionArchivosGG.js"></script>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                var banco = "<?= $objectVO->getId() ?>";
                $("#returnLink").val("<?= $returnLink ?>");

                if (banco > 0) {
                    $("#Importe").focus();
                }
            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>
        <?php TotalizaCorte(); ?>

        <?php if ($ctVO->getStatusctv() === StatusCorte::ABIERTO) { ?>

            <form name="form1" method="get" action="">
                <input type="hidden" name="returnLink" id="returnLink">
                <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
                    <?php if (!is_numeric($objectVO->getId())) { ?>
                        <tr height="22">
                            <td>

                                <div class="content-select">
                                    <select name="Banco" class="texto_tablas" onChange="form1.submit();">
                                        <option selected="selected" disabled value="">Seleccionar la cuenta</option>
                                        <optgroup label="Bancos">
                                            <?php foreach ($registrosBancos as $rg) { ?>
                                                <option value="B<?= $rg["id"] ?>"><?= ucwords(strtolower($rg["banco"] . " | " . $rg["cuenta"] . " | " . $rg["concepto"])) ?></option>
                                            <?php } ?>
                                        </optgroup>
                                        <optgroup label="Despachadores">
                                            <?php foreach ($registrosVendedores as $rg) { ?>
                                                <option value="D<?= $rg["id"] ?>"><?= ucwords(strtolower($rg["vendedor"] . " | " . $rg["nombre"])) ?></option>
                                            <?php } ?>
                                        </optgroup>
                                    </select>
                                    <em></em>
                                </div>
                            </td>
                            <td style="text-align: center;color: #990000;"><?= $Msj ?></td>
                        </tr>
                    <?php } else { ?>

                        <tr height="22">
                            <td>
                                <?= $objectVO->getBanco() ?> &nbsp; &nbsp; 
                                <strong>Cuenta:</strong> <?= $objectVO->getCuenta() ?> &nbsp; &nbsp; 
                                <strong>Concepto:</strong> <?= $objectVO->getBanco() ?>
                            </td>
                        </tr>

                        <tr height="22">
                            <td>
                                No.plomo: <input type="text" name="Plomo" value="" size="10" class="texto_tablas" onkeyup=mayus(this);>
                                Concepto: <input type="text" name="Descripcion" value="<?= $Concepto ?>" size="40" class="texto_tablas" required="required">
                                <?php if ($objectVO->getTipo_moneda() == 2) { ?>
                                    <strong>Tipo cambio:<?= number_format($objectVO->getTipo_cambio(), 2) ?></strong>&nbsp; No.dls: 
                                <?php } else { ?>
                                    Importe: 
                                <?php } ?>
                                <input type="text" name="Importe" id="Importe" size="7" class="texto_tablas" required="required">
                                <input type="submit" name="Boton" value="Agregar" class="nombre_cliente">
                                <a href="movgastos.php" id="Cancelar" class="textosCualli_i_n">Cancelar</a>
                                <input type="hidden" name="Banco" value="<?= $objectVO->getId() ?>">
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </form>
        <?php } ?>

        <div style="text-align: center" class="texto_tablas"><strong>Depositos bancarios</strong></div>

        <div id="TablaDatos" style="min-height: 130px;"> 
            <table aria-hidden="true">
                <tr>
                    <td class="fondoNaranja">Id</td>
                    <td class="fondoNaranja">Cuenta</td>
                    <td class="fondoNaranja">Banco</td>
                    <td class="fondoNaranja">Plomo</td>
                    <td class="fondoNaranja">Concepto</td>
                    <td class="fondoNaranja">Dls</td>
                    <td class="fondoNaranja">T_cambio</td>
                    <td class="fondoNaranja">Importe</td>
                    <td class="fondoNaranja">Borrar</td>
                </tr>

                <?php
                $nImpB = 0;
                foreach ($registros as $rg) {
                    ?>
                    <tr>
                        <td><?= $rg["clave"] ?></td>
                        <td><?= $rg["cuenta"] ?></td>
                        <td><?= ucwords(strtolower($rg["nombre"] . " | " . $rg["cptcuenta"])) ?></td>
                        <td><?= $rg["plomo"] ?></td>
                        <td><?= $rg["concepto"] ?></td>
                        <td align="right">
                            <?php if ($rg[tipo_cambio] != 1) { ?>
                                <?= number_format($rg["importe"] / $rg[tipo_cambio], 2) ?>
                            <?php } ?>
                        </td>   
                        <td align="right"><?= number_format($rg[tipo_cambio], 2) ?></td>
                        <td align="right"><?= number_format($rg["importe"], 2) ?></td>
                        <td align="center">
                            <?php if ($ctVO->getStatusctv() === StatusCorte::ABIERTO && abs($rg["importe"]) > 0) { ?>
                                <a class="textosCualli_i_n" href=javascript:confirmar("Deseas&nbsp;eliminar&nbsp;el&nbsp;registro?","<?= $self ?>?cId=<?= $rg["id"] ?>&op=Bancos&returnLink=<?= $returnLink ?>");><i class="icon fa fa-lg fa-trash" aria-hidden="true"></i></a>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php
                    $nImpB += $rg["importe"];
                }
                ?>

                <tr><td class="upTitles" colspan="7">Total</td><td class="upTitles"><?= number_format($nImpB, 2) ?></td><td class="upTitles"></td></tr>
            </table>
        </div>

        <div style="text-align: center" class="texto_tablas"><strong>Faltantes de despachadores</strong></div>

        <div id="TablaDatos" style="min-height: 100px;"> 
            <table aria-hidden="true">
                <tr>
                    <td class="fondoNaranja">Id</td>
                    <td class="fondoNaranja">Cuenta</td>
                    <td class="fondoNaranja">Despachador</td>
                    <td class="fondoNaranja">Concepto</td>
                    <td class="fondoNaranja">T_cambio</td>
                    <td class="fondoNaranja">Importe</td>
                    <td class="fondoNaranja">Borrar</td>
                </tr>
                <?php
                $nImpD = 0;
                foreach ($registros_ as $rg) {
                    ?>
                    <tr>
                        <td><?= $rg["clave"] ?></td>
                        <td><?= $rg["cuenta"] ?></td>
                        <td><?= ucwords(strtolower($rg["nombre"] . " | " . $rg["cptcuenta"])) ?></td>
                        <td><?= $rg["concepto"] ?></td>                        
                        <td align="right"><?= number_format($rg[tipo_cambio], 2) ?></td>
                        <td align="right"><?= number_format($rg["importe"], 2) ?></td>
                        <td align="center">
                            <?php if ($ctVO->getStatusctv() === StatusCorte::ABIERTO && abs($rg["importe"]) > 0) { ?>
                                <a class="textosCualli_i_n" href=javascript:confirmar("Deseas&nbsp;eliminar&nbsp;el&nbsp;registro?","<?= $self ?>?cId=<?= $rg["id"] ?>&op=Bancos&returnLink=<?= $returnLink ?>");><i class="icon fa fa-lg fa-trash" aria-hidden="true"></i></a>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php
                    $nImpD += $rg["importe"];
                }
                ?>
                <tr><td class="upTitles" colspan="5">Total</td><td class="upTitles"><?= number_format($nImpD, 2) ?></td><td class="upTitles"></td></tr>
                <tr><td class="upTitlesSin" colspan="7"></td></tr>
                <tr><td class="upTitlesSin" colspan="5">Diferencia</td><td class="upTitlesSin"><?= number_format($nEfectivo - $nBancos, 2) ?></td><td class="upTitlesSin"></td></tr>
            </table>
        </div>
        <input type="hidden" id="CorteHidden" name="CorteHidden" value="<?= $Corte ?>">
        <?php
        $sql = "SELECT valor FROM variables_corporativo WHERE llave = 'Bandera_GES'";
        $RsBGes = utils\IConnection::execSql($sql);
        $vGs = $RsBGes["valor"] === "" || $RsBGes["valor"] == null ? 0 : $RsBGes["valor"];
        ?>
        <input type="hidden" id="OpGrupo" name="OpGrupo" value="<?= $vGs ?>">
        <?php
        $nLink = array();
        if ($ctVO->getStatus() === StatusCorte::CERRADO && $ctVO->getStatusctv() === StatusCorte::ABIERTO) {
            $nEfectivo = $Db["valor"] == 1 ? $nEfectivoDisplay : $nEfectivo;
            if (abs($nEfectivo - $nBancos) <= 1) {
                ?>
                <div id="CerrarCt"><em class='icon fa fa-flag parpadea' aria-hidden=\"true\"></em>Corte cuadrado, da click aqui para cerrarlo <em class='icon fa fa-flag parpadea' aria-hidden=\"true\"></em></div>
                    <?php
                }
            }

            echo $paginador->footer(false, $nLink, false, false, 0, false);
            BordeSuperiorCerrar();
            PieDePagina();
            ?>

    </body>
</html>
