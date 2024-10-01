<?php
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("comboBoxes.php");

use com\softcoatl\utils as utils;

$Titulo = "Favor de confirmar sus datos";
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

require_once './services/NotasCreditodService.php';

$pacDAO = new ProveedorPACDAO();
$ppac = $pacDAO->getActive();

$HeA = $mysqli->query("SELECT nc.id,nc.cliente,cli.nombre,cli.direccion,cli.rfc,cli.codigo,
         cli.colonia,cli.municipio,cli.telefono,cli.correo,nc.fecha,cli.numeroext,nc.iva,cli.enviarcorreo,
         cli.cuentaban,cli.estado,cli.formadepago,nc.importe,nc.iva,nc.ieps,nc.total,cli.numeroint,
         nc.observaciones,cli.estado
         FROM nc LEFT JOIN cli ON nc.cliente=cli.id
         WHERE nc.id='$cVarVal'");

$He = $HeA->fetch_array();

$ncVO = new NcVO();
$clienteVO = new ClientesVO();
if (is_numeric($cVarVal)) {
    $ncVO = $ncDAO->retrieve($cVarVal);
    $clienteVO = $clienteDAO->retrieve($ncVO->getCliente());
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <?= $lBd ? "<meta http-equiv='refresh' content='2;url=gennotacredito331.php?Boton=Genera' />" : "" ?>
        <script>
            $(document).ready(function () {
                $('#Relacioncfdi').attr('disabled', 'true').attr('name', 'Relacioncfdi').val('<?= $ncVO->getRelacioncfdi() ?>');
                $('#Formadepago').val('<?= $ncVO->getFormadepago() ?>');
                $('#Metododepago').val('<?= $ncVO->getMetododepago() ?>');
                $('#tiporelacion').val('<?= $ncVO->getTipoRelacion() ?>');
                $('#cuso').val('<?= $ncVO->getUsocfdi() ?>').attr('required', 'true');
            });
        </script>
    </head>

    <body>
        <?php BordeSuperior(); ?>

        <?php if ($ppac->getPruebas() === "1") { ?>
            <div style="background-color: red; color: white; text-align:center; font-family: Helvetica, Arial, Verdana, Tahoma, sans-serif; font-size:14px; font-weight:bold;">
                ALERTA FACTURANDO EN MODO DE DEMOSTRACIÓN
            </div>
        <?php } ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="notascred.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">
                    <form name="form1" id="form1" method="post" action="">
                        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
                            <?php
                            cInput("(&nbsp;<span style='color: red;'><strong>*&nbsp;</strong></span>) ", "Text", "10", "", "right", "<strong> Campos obligatorios para timbrar su nota de crédito</strong>", "15", false, true, "");
                            cInput("Folio :", "Text", "5", "Id", "right", $ncVO->getId(), "40", false, true, "");
                            cInput("<span style='color: red;'><strong>*&nbsp;</strong></span>R.f.c.:", "Text", "15", "Rfc", "right", $clienteVO->getRfc(), "15", true, false, '', " required='required'  ");
                            cInput("<span style='color: red;'><strong>*&nbsp;</strong></span>Nombre: ", "Text", "50", "Nombre", "right", $clienteVO->getNombre(), "200", true, false, " <span  class='nombre_cliente'> &nbsp;&nbsp; Num. Cliente: </span><strong>" . $clienteVO->getId() . "</strong>", " required='required'  ");
                            ?>

                            <tr>
                                <td align="right" bgcolor="#e1e1e1" class="nombre_cliente"><span style='color: red;'><strong>*&nbsp;</strong></span>Uso de CFDI: &nbsp;</td>
                                <td align="left"><?= ComboboxUsoCFDI::generate("cuso", "360px"); ?></td>
                            </tr>
                            <tr>
                                <td align="right" bgcolor="#e1e1e1" class="nombre_cliente"><span style='color: red;'><strong>*&nbsp;</strong></span>CFDI relacionado: &nbsp;</td>
                                <td align="left">&nbsp;<input type="text" name="Relacioncfdi" id="Relacioncfdi" class="texto_tablas" size="10"/>
                                    &nbsp;<?php ComboboxTipoRelacion::generate('tiporelacion', "260px"); ?>
                                </td>
                            </tr>
                            <tr style="height: 25px;">
                                <td align="right" bgcolor="#e1e1e1" class="nombre_cliente"><span style='color: red;'><strong>*&nbsp;</strong></span>Forma de pago: &nbsp;</td>
                                <td align="left">
                                    <?php ComboboxFormaDePago::generate("Formadepago", "360px"); ?>
                                </td>
                            </tr>
                            <tr style="height: 25px;">
                                <td align="right" bgcolor="#e1e1e1" class="nombre_cliente"><span style='color: red;'><strong>*&nbsp;</strong></span>Método de pago: &nbsp;</td>
                                <td align="left">
                                    <?php ComboboxMetodoDePago::generate("Metododepago", "360px"); ?>
                                </td>
                            </tr>
                            <tr style="height: 25px;">
                                <td align="right" bgcolor="#e1e1e1" class="nombre_cliente">Correo electronico: &nbsp;</td>
                                <td align="left">&nbsp;<input type="text" name="Correo" value="<?= $clienteVO->getCorreo() ?>" class="texto_tablas" size="38"> &nbsp; enviar correo
                                    <?php
                                    if ($clienteVO->getEnviarcorreo() === "Si") {
                                        echo "<input type='checkbox' name='Enviarcorreo' value='Si' checked>";
                                    } else {
                                        echo "<input type='checkbox' name='Enviarcorreo' value='Si'>";
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr style="height: 25px;">
                                <td align="right" bgcolor="#e1e1e1" class="nombre_cliente">Observaciones: &nbsp;</td>
                                <td align="left">
                                    &nbsp;<input type="text" name="Observaciones" value="<?= $ncVO->getObservaciones() ?>" class="texto_tablas" size="55" >
                                </td>
                            </tr>

                            <tr style="height: 25px;">
                                <td></td>
                                <td align="left">
                                    &nbsp;<input type="submit" class='nombre_cliente' name="Boton" value="Guardar estos cambios" title="">
                                </td>
                            </tr>

                        </table>

                        <div align='center' style="width: 100%;" class="texto_tablas">
                            <?php
                            if ($request->hasAttribute("Boton")) {
                                if ($request->getAttribute("Boton") === "Timbra nota de credito") {
                                    ?>
                                    <i class="fa fa-spinner fa-pulse fa-4x" aria-hidden="true"></i>
                                    <span class="sr-only">Loading...</span>
                                    <?php
                                }
                            } else {
                                ?>
                                <br/>
                                <div id="DatosEncabezado">
                                    <table aria-hidden="true">
                                        <tr align="center">
                                            <td><input type='submit' name='Boton' value='Timbra nota de credito'/>
                                        </tr>
                                    </table>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                        <div class='mensajes' style="padding-top: 5px;"><?= $Msj ?></div>
                    </form>
                </td>
            </tr>
        </table>

        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>
