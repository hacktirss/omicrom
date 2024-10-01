<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("importeletras.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();

$busca = $request->getAttribute("busca");
$ciaDAO = new CiaDAO();
$ciaVO = $ciaDAO->retrieve(1);

$sql = "SELECT inicio_venta FROM rm LIMIT 1";
$Cpo = $mysqli->query($sql)->fetch_array();

$sqlTerminal = "SELECT printed_serial,serial,model,status FROM pos_catalog WHERE status ='A' and dispositivo = 'T'";
$Cpo1 = $mysqli->query($sqlTerminal);

$sqlServidor = "SELECT serial,model,status FROM pos_catalog WHERE status ='A' and dispositivo = ('S') LIMIT 1";
$Servidor = $mysqli->query($sqlServidor);
$ser = $Servidor->fetch_array();

$sqlInterfaz = "SELECT printed_serial,serial,model,status FROM pos_catalog WHERE status ='A' and dispositivo = ('I')  LIMIT 1";
$Interfaz = $mysqli->query($sqlInterfaz);
$interfaz = $Interfaz->fetch_array();

$months = array();
setlocale(LC_TIME, 'es_MX.UTF-8');
for ($m = 1; $m <= 12; $m++) {
    $months[cZeros($m, 2, "LEFT")] = strftime("%B", mktime(0, 0, 0, $m, 12));
}

$nMes = (int) date("m");

$aMes = array("-", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");

$Titulo = "Acuse de recibo de archivo de control volumetrico";

$cFecha = date("d") . " de " . $aMes[$nMes] . " del " . date("Y");
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports_print.php'; ?> 
        <title><?= $Gcia ?></title>
        <style>
            @page {
                size: A5 /*landscape*/;
            }
        </style>
        <script type="text/javascript">
            /*$(document).ready(function () {
             
             });*/
        </script>
        <script src='https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js'></script>
        <script>
            function Export2Doc(element, filename = '') {

                var preHtml = "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'><head><meta charset='utf-8'><title>Export HTML To Doc</title></head><body>";
                var postHtml = "</body></html>";

                var html = preHtml + document.getElementById(element).innerHTML + postHtml;

                var blob = new Blob(['\ufeff', html], {
                    type: 'application/msword'
                });

                var url = 'data:application/vnd.ms-word;charset=utf-8,' + encodeURIComponent(html);


                filename = filename ? filename + '.doc' : 'document.doc';


                var downloadLink = document.createElement("a");

                document.body.appendChild(downloadLink);

                if (navigator.msSaveOrOpenBlob) {
                    navigator.msSaveOrOpenBlob(blob, filename);
                } else {

                    downloadLink.href = url;
                    downloadLink.download = filename;
                    downloadLink.click();
                }

                document.body.removeChild(downloadLink);
            }

        </script>

    </head>

    <!-- Set "A5", "A4" or "A3" for class name -->
    <!-- Set also "landscape" if you need -->
    <body class="A5">
        <div class="iconos">
            <table aria-hidden="true">
                <tr>
                    <td style="text-align: left"><?= $Titulo ?></td>
                    <td>&nbsp;</td>
                    <td style="text-align: center"><i onclick="Export2Doc('exportContent', 'AvisoSat');" title="Guardar" class='icon fa fa-file-word-o fa-lg' aria-hidden="true"></i></td>
                    <td style="text-align: center"><i onclick="print();" title="Imprimir" class='icon fa fa-lg fa-print' aria-hidden="true"></i></td>
                </tr>
            </table>
        </div>
        <!-- Each sheet element should have the class "sheet" -->
        <!-- "padding-**mm" is optional: you can set 10, 15, 20 or 25 -->
        <div class="sheet padding-05mm">


            <?php nuevoEncabezadoPrint(null) ?>

            <div id="exportContent" >
                <table style="width: 100%;font-family: sans-serif;" aria-hidden="true">
                    <tr>
                        <td style="font-weight: bold;text-align: right;"><h3>Asunto: Aviso de Controles Volumétricos <br>
                                Fecha: <?= $cFecha ?> </h3> </td>
                    </tr>
                    <tr>
                        <td>
                            <strong><br><h3 style="text-decoration: underline" >Servicio de Administración Tributaria (SAT)</h3></strong>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p style="text-align: justify; font-family: sans-serif; font-size: 18px;">
                                Por medio de la presente y bajo protesta de decir verdad manifiesto que los datos abajo descritos referentes a los sistemas de monitoreo y control a distancia, tanques, dispensarios, instrumentos y otros equipos y/o sistemas que se vinculan a los dispensarios, que existen en la estación de servicio, en el entendido que al adquirir o dar de baja algún equipo o al cambiar cualquier dato contenido en este aviso, tengo que enterar al SAT de acuerdo a lo solicitado Formato 283/CFF: Aviso de controles volumétricos.
                            </p>
                        </td>
                    </tr>
                    <?php
                    $sqlCre = "SELECT permiso FROM omicrom.permisos_cre WHERE llave = 'PERMISO_CRE'";
                    $rsCre = utils\IConnection::execSql($sqlCre);
                    ?>
                    <tr><td style="font-weight: bold;text-align: left;"><h2>Datos de la Estación de Servicio</h2></td></tr>
                    <tr><td style="text-align: justify; font-size: 18px;" >Permiso CRE: <?= $rsCre["permiso"] ?></td></tr>
                    <tr><td style="text-align: justify; font-size: 18px;" >Razón Social: <?= $ciaVO->getCia() ?></td></tr>
                    <tr><td style="text-align: justify; font-size: 18px;" >R.F.C: <?= $ciaVO->getRfc() ?></td></tr>
                    <tr><td style="text-align: justify; font-size: 18px;" >Domicilio: <?= $ciaVO->getDireccion() ?> No. <?= $ciaVO->getNumeroext() ?> <?= $ciaVO->getNumeroint() ?>, <?= $ciaVO->getColonia() ?> 
                            ,<?= $ciaVO->getCiudad() ?>, <?= $ciaVO->getEstado() ?>, <?= $ciaVO->getCodigo() ?></td></tr>
                    <tr><td style="text-align: justify; font-size: 18px;" >Teléfono(s): <?= $ciaVO->getTelefono() ?></td></tr>
                    <tr><td style="text-align: justify; font-size: 18px;" >Correo electrónico: </td></tr>
                    <tr><td style="text-align: justify; font-size: 18px;">Nombre del representante legal: <?= $ciaVO->getRepresentante_legal() ?></td></tr>
                    <tr><td style="text-align: justify; font-size: 18px;" >R.F.C del representante legal: <?= $ciaVO->getRfc_representante_legal() ?></td></tr>
                    <tr><td style="text-align: justify; font-size: 18px;" >Domicilio para recibir notificaciones: <?= $ciaVO->getDireccion() ?> No. <?= $ciaVO->getNumeroext() ?> <?= $ciaVO->getNumeroint() ?>, <?= $ciaVO->getColonia() ?> 
                            ,<?= $ciaVO->getCiudad() ?>, <?= $ciaVO->getEstado() ?>, <?= $ciaVO->getCodigo() ?></td></tr>
                    <tr><td style="text-align: justify; font-size: 18px;" >Fecha inicio: <?= " " . $aMes[(int) date("m", strtotime($Cpo[0]))] . " del " . date("Y", strtotime($Cpo[0])) ?></td></tr>  
                    <tr><td style="font-weight: bold;text-align: left;"><h2>CONTROL VOLUMÉTRICO OMICROM</h2></td></tr>
                    <tr><td><h2 style="font-weight: bold;text-align: left;">Proveedor:</h2></td></tr>
                    <tr><td style="text-align: justify; font-size: 18px;"><ul>
                                <li>Razón Social: Deti Desarrollo y Transferencia de Informática SA DE CV</li>
                                <li>RFC: DDT120330J39</li>
                                <li>Domicilio: NORMAL DE MAESTROS No. 10 COL. SANTA MARÍA TULANTONGO TEXCOCO, ESTADO DE MÉXICO CP. 56217</li>
                                <li>Contacto: recepcion@detisa.com.mx</li>
                            </ul></td></td></tr>

                    <tr><td><h2 style="font-weight: bold;text-align: left;">Descripción:</h2>
                            <p style="text-align: justify ;font-weight: normal; font-size: 18px;">
                                OMICROM está desarrollado en plataforma 100 % web, alojado en servidores Linux libre de virus. Es un sistema integral que concentra todo en un sólo módulo, para lo cual sólo requieres de un navegador de internet y puedes operarlo desde tu estación de servicio.
                                Funciones que realiza:	Cortes de ventas de la estación, impresión de tickets, control de   inventarios, generación y envió de archivos de control volumétrico, cambio de precios, respaldo de base de datos. Bloque de dispensarios y ventas prefijadas por medio de terminales M.POS.
                            </p></td></tr>

                    <tr><td style="font-weight: bold;text-align: left;"><h2>Datos de los equipos de cómputo y dispositivos.</h2></td></tr>
                    <?php
                    $output = "/home/omicrom/xml/respuesta.txt";
                    $command = "sudo dmidecode -t system | grep Serial | cut -d' ' -f3 > $output";
                    exec($command);
                    $txt_file = fopen('/home/omicrom/xml/respuesta.txt', 'r');
                    while ($line = fgets($txt_file)) {
                        $Cervidor = $line;
                    }
                    fclose($txt_file);
                    ?>
                    <tr><td style="text-align: justify; font-size: 18px;" ><strong>SERVIDOR:</strong>HP - <?= $Cervidor ?></td></tr>
                    <tr><td style="text-align: justify; font-size: 18px;" ><strong>Modelo / Nombre:</strong>HP ENTERPRISE MICRO SERVER GEN10</td></tr>
                    <tr><td style="text-align: justify; font-size: 18px;" ><strong>Número de Serie / Versión: </strong><?= $ser["serial"] ?></td></tr>
                    <tr><td style="text-align: justify; font-size: 18px;" ><strong>Funciones que realiza: </strong>Administración de los programas del control distancia exclusivamente, almacenamiento de los programas y de todos los registros de control Volumétricos.</td></tr>
                    <tr><td style="text-align: justify; font-size: 18px;" ><strong>Ubicación física dentro de la E.S.: </strong>Oficina Operativa.</td></tr>

                    <tr><td style="font-weight: bold;text-align: left;text-decoration: underline"><h2>INTERFAZ DE COMUNICACIÓN: </h2></td></tr>
                    <tr><td style="text-align: justify; font-size: 18px;" ><strong>Nombre del Sistema/ Modelo: </strong>OMICROM / MOD. ÉPSILON</td></tr>
                    <tr><td style="text-align: justify; font-size: 18px;" ><strong>Número de Serie / Versión: </strong><?= $interfaz["printed_serial"] ?>/3.3</td></tr>
                    <tr><td style="text-align: justify; font-size: 18px;" ><strong>Funciones que realiza: </strong>La comunicación entre los dispensarios y el servidor, ejecutando la operación que servicio IOTA le indique como: cambio de precios, Bloque de dispensarios, ventas prefijadas por medio de terminales M.POS. Toma de lecturas electrónicas, Poner el dispensario en modo programación.</td></tr>
                    <tr><td style="text-align: justify; font-size: 18px;" ><strong>Ubicación física dentro de la E.S.: </strong>Oficina Operativa.</td></tr>

                    <?php
                    $i = 0;
                    while ($rg = $Cpo1->fetch_array()) {
                        ?>
                        <tr><td></td></tr>
                        <tr><td style="font-weight: bold;text-align: left;text-decoration: underline"><h2>TERMINAL: <?= ++$i ?> </h2></td></tr>
                        <tr><td style="text-align: justify; font-size: 18px;" ><strong>Nombre del Sistema/ Modelo: </strong><?= $rg["model"] ?> WI-FI</td></tr>
                        <tr><td style="text-align: justify; font-size: 18px;" ><strong>Número de Serie / Versión: </strong><?= $rg["printed_serial"] ?> <?= $rg["serial"] ?></td></tr>
                        <tr><td style="text-align: justify; font-size: 18px;" ><strong>Funciones que realiza: </strong>Impresión de tickets, impresión de cortes de turno, lectura de tarjeta magnéticas, chip y mifare. Control de flotillas asignando ventas prefijadas por importe o litros.</td></tr>
                        <tr><td></td></tr>
                    <?php } ?>
                    <tr><td style="text-align: center; font-size: 18px;" ><strong>Atentamente</strong></td></tr>
                    <tr><td><br> </td></tr>
                    <tr><td><br> </td></tr>
                    <tr><td><br> </td></tr>
                    <tr><td><br> </td></tr>
                    <tr><td><br> </td></tr>
                    <tr><td><br> </td></tr>
                    <tr><td><br> </td></tr>
                    <tr><td style="text-align: center; font-size: 18px; text-decoration:overline;" ><?= $ciaVO->getRepresentante_legal() ?> </td></tr>
                </table>
            </div>

        </div>

    </body>
</html>     

