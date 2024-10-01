<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("importeletras.php");
include_once ("data/TanqueDAO.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();

$busca = $request->getAttribute("busca");
$ciaDAO = new CiaDAO();
$ciaVO = $ciaDAO->retrieve(1);

$sql = "SELECT p.id,DATE(p.fecha)fecha,TIME(p.fecha) hora,cli.nombre,inv.descripcion,p.cliente,p.puntos "
        . "FROM puntos p,cli,inv "
        . "WHERE p.cliente=cli.id AND p.producto=inv.id AND p.id='$busca'";
$Cpo = $mysqli->query($sql)->fetch_array();

$months = array();
setlocale(LC_TIME, 'es_MX.UTF-8');
for ($m = 1; $m <= 12; $m++) {
    $months[cZeros($m, 2, "LEFT")] = strftime("%B", mktime(0, 0, 0, $m, 12));
}
$cFecha = $ciaVO->getColonia() . " " . $ciaVO->getCiudad() . " a " . date("d") . " de " . $months[date("m")] . " de " . date("Y");

$Titulo = "Dirección General de Verificación de Combustibles PROFECO";
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports_print.php'; ?> 
        <title><?= $Gcia ?></title>
        <style>
            @page {
                size: A4 /*landscape*/;
            }
        </style>
        <script type="text/javascript">
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
                    <td>&nbsp;</td>
                    <td style="text-align: center">
                        <em onclick="Export2Doc('exportContent', 'avisoProfeco');" class='icon fa fa-lg fa-file-word-o' title="Descargar tipo Word" style="margin-right: 20px;"></em>
                        <em onclick="print();" class='icon fa fa-lg fa-print' aria-hidden="true" title="Descargar tipo PDF"></em>
                    </td>
                </tr>
            </table>
        </div>
        <!-- Each sheet element should have the class "sheet" -->
        <!-- "padding-**mm" is optional: you can set 10, 15, 20 or 25 -->
        <div class="sheet padding-10mm">

            <?php nuevoEncabezadoPrint(null) ?>
            <div id="exportContent">
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
                <table style="width: 100%;" aria-hidden="true">
                    <tr><td style="font-weight: bold;text-align: right;"><em>Asunto: Aviso de sistema de monitoreo y control a distancia</em></td></tr>
                    <tr>
                        <td>
                            <strong>Dirección General de Verificación de Combustibles
                                PROFECO</strong>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <p>
                                Por medio de la presente y bajo protesta de decir verdad manifiesto que los datos abajo descritos referentes a los 
                                sistemas de monitoreo y control a distancia, tanques, dispensarios, instrumentos y otros equipos y/o sistemas que 
                                se vinculan a los dispensarios, que existen en la estación de servicio, en el entendido que al adquirir o dar de baja 
                                algún equipo o al cambiar cualquier dato contenido en este aviso, tengo que enterar a PROFECO, realizando 
                                nuevamente este trámite. (Se anexan 3 hojas conteniendo la información de los tanques, dispensarios, instrumentos y 
                                otros equipos vinculados a los dispensarios)
                            </p>
                        </td>
                    </tr>
                    <tr><td style="font-weight: bold;text-align: right;"><em>Asunto: Aviso de sistema de monitoreo y control a distancia</em></td></tr>
                    <?php
                    $sqlCre = "SELECT permiso FROM omicrom.permisos_cre WHERE llave = 'PERMISO_CRE'";
                    $rsCre = utils\IConnection::execSql($sqlCre);
                    ?>
                    <tr><td><strong>Permiso CRE:</strong> <?= $rsCre["permiso"] ?></td></tr>
                    <tr><td><strong>Razón Social:</strong> <?= $ciaVO->getCia() ?></td></tr>
                    <tr><td><strong>Domicilio:</strong> <?= $ciaVO->getDireccion() . " " . $ciaVO->getColonia() . " " . $ciaVO->getCiudad() . ", " . $ciaVO->getEstadoexp() . " C.P. " . $ciaVO->getCodigo() ?></td></tr>
                    <tr><td><strong>Teléfono(s):</strong> <?= $ciaVO->getTelefono() ?></td></tr>
                    <tr><td><strong>Correo electrónico: </strong> </td></tr>
                    <tr><td><strong>Nombre del representante legal:</strong> <?= $ciaVO->getRepresentante_legal() ?></td></tr>
                    <tr><td><strong>RFC del representante legal:</strong> <?= $ciaVO->getRfc_representante_legal() ?></td></tr>
                    <tr><td><strong>Domicilio para recibir notificaciones: </strong> MISMO DOMICILIO</td></tr>
                    <tr><td style="border-top: 1px solid #606c84"><strong style="margin-right: 20px;">Consola de control a distancia</strong></td></tr>
                    <tr style="font-weight: bold;"><td>Marca: DETISA</td></tr>
                    <tr style="font-weight: bold;"><td>Modelo / Nombre del Sistema: OMICROM</td></tr>
                    <tr style="font-weight: bold;"><td>Número de Serie / Versión: <?= VERSION ?></td></tr>
                    <tr>
                        <td><strong>Funciones que realiza:</strong>	Cortes de ventas de la estación, impresión de tickets, control de   inventarios, 
                            generación y envió de archivos de control volumétrico, cambio de precios, respaldo de base de datos. 
                            Bloque de dispensarios y ventas prefijadas por medio de terminales M.POS.
                        </td>
                    </tr>
                    <tr><td><strong>Ubicación física dentro de la E.S.:</strong> Oficina Operativa.</td></tr>
                    <tr><td><strong>Razón Social del proveedor:</strong> DETI DESARROLLO Y TRANSFERENCIA DE INFORMÁTICA S.A. DE C.V.</td></tr>
                    <tr><td><strong>Teléfono de proveedor:</strong>(01)-595-92-50401, (01)-595-111-7518</td></tr>
                    <tr><td style="font-weight: bold;text-align: right;"><em>Datos de los equipos de cómputo y dispositivos.</em></td></tr>
                    <tr><td><strong>SERVIDOR:</strong> </td></tr>
                    <?PHP
                    $ServidorL = "SELECT * FROM omicrom.pos_catalog WHERE dispositivo = 'S';";
                    $Serv = utils\IConnection::execSql($ServidorL);
                    ?>
                    <tr><td><strong>Modelo / Nombre: <?= $Serv["model"] ?></strong></td></tr>
                    <tr><td><strong>Número de Serie / Versión: <?= $Serv["serial"] ?></strong></td></tr>
                    <tr><td><strong>Funciones que realiza:</strong> Administración de los programas del control distancia exclusivamente, almacenamiento de los programas y de todos los registros de control Volumétricos. </td></tr>
                    <tr><td><strong>Ubicación física dentro de la E.S.:</strong> Oficina Operativa.</td></tr> 
                    <tr><td style="height: 15px;"></td></tr>
                    <?PHP
                    $ServidorI = "SELECT * FROM omicrom.pos_catalog WHERE dispositivo = 'I';";
                    $ServI = utils\IConnection::execSql($ServidorI);
                    ?>
                    <tr><td><strong>INTERFAZ: </strong></td></tr>
                    <tr><td><strong>Nombre del Sistema/ Modelo:</strong>	OMICROM / MOD. <?= $ServI["model"] ?></td></tr>
                    <tr><td><strong>Número de Serie / Versión:</strong>	  <?= $ServI["printed_serial"] ?> | <?= $ServI["serial"] ?>/3.3</td></tr>
                    <tr><td><strong>Funciones que realiza:</strong>	La comunicación entre los dispensarios y el servidor, ejecutando la operación que servicio IOTA le indique como: cambio de precios, Bloque de dispensarios, ventas prefijadas por medio de terminales M.POS. Toma de lecturas electrónicas, Poner el dispensario en modo programación.</td></tr>
                    <tr><td><strong>Ubicación física dentro de la E.S.:</strong> Oficina Operativa.</td></tr>
                    <tr><td style="height: 15px;"></td></tr>
                    <?php
                    $Terminales = "SELECT * FROM omicrom.pos_catalog WHERE dispositivo = 'T' and status='A';";
                    $rsTrm = utils\IConnection::getRowsFromQuery($Terminales);
                    $i = 1;
                    foreach ($rsTrm as $Trm) {
                        ?>
                        <tr><td><strong>TERMINAL <?= $i ?></strong></td></tr>
                        <tr><td><strong>Modelo / Nombre del Sistema:</strong> <?= $Trm["model"] ?></td></tr>
                        <tr><td><strong>Número de Serie / Versión:</strong> <?= $Trm["printed_serial"] ?>  <?= $Trm["serial"] ?></td></tr>
                        <tr><td><strong>Funciones que realiza:</strong>	Impresión de tickets, impresión de cortes de turno, lectura de tarjeta magnéticas, chip y bifare. Control de flotillas asignando ventas prefijadas por importe o litros.</td></tr>
                        <tr><td><strong>Ubicación física dentro de la E.S.:</strong> En toda la estación de servicio por su tipo de tecnología WIFI.</td></tr>
                        <tr><td style="height: 15px;"></td></tr>
                        <?php
                        $i++;
                    }
                    $TanquesDAO = new TanqueDAO();
                    $TanquesVO = new TanqueVO();
                    $TanquesVO = $TanquesDAO->retrieve(1);

                    $VeederV = "SELECT * FROM omicrom.pos_catalog WHERE dispositivo = 'V';";
                    $VeeV = utils\IConnection::execSql($VeederV);

                    $PVeederV = "SELECT * FROM prv  WHERE proveedorde = 'Equipo' limit 1";
                    $PVeeV = utils\IConnection::execSql($PVeederV);
                    ?>
                    <tr><td style="font-weight: bold;text-align: right;"><em>Datos de equipo de monitoreo de tanques.</em></td></tr>
                    <tr><td><strong>Consola de monitoreo de tanques Marca:</strong> <?= $PVeeV["alias"] ?></td></tr>
                    <tr><td><strong>Modelo / Nombre del Sistema:</strong> <?= $VeeV["model"] ?></td></tr>
                    <tr><td><strong>Número de Serie / Versión:</strong><?= $VeeV["serial"] ?></td></tr>
                    <tr><td><strong>Funciones que realiza:</strong>	Monitoreo de Inventarios de Gasolina, monitoreo de sensores de fuga de líquido y gases, generación de información volumétrica para controles volumétricos.</td></tr>
                    <tr><td><strong>Ubicación física dentro de la E.S:</strong> Cuarto Eléctrico, parte posterior de oficinas</td></tr>
                    <tr><td><strong>Razón Social del proveedor:</strong><?= $PVeeV["nombre"] ?></td></tr>
                    <tr><td><strong>Teléfono de proveedor:</strong><?= $PVeeV["telefono"] ?> </td></tr>
                    <tr><td style="height: 15px;"></td></tr>
                    <tr><td>Otro tipo de equipo o sistema vinculado a los dispensarios o consolas <strong>Control Volumétrico</strong></td></tr>
                    <tr><td><strong>Marca: OMICROM</strong> </td></tr>
                    <tr><td><strong>Modelo / Nombre del Sistema: ÉPSILON</strong>	</td></tr>
                    <tr><td><strong>Número de Serie / Versión: <?= VERSION ?></strong></td></tr>
                    <tr><td><strong>Funciones que realiza:</strong>	Comunicación con Sistema de monitoreo de tanques TIPSA, generación de archivos de control volumétrico, encriptación y envió de los mismos.</td></tr>
                    <tr><td><strong>Ubicación física dentro de la E.S:</strong> Oficina operativa.</td></tr>
                    <tr><td><strong>Razón Social del proveedor:</strong>	DETI DESARROLLO Y TRANSFERENCIA DE INFORMÁTICA S.A. DE C.V.</td></tr>
                    <tr><td><strong>Teléfono de proveedor:</strong>	595-92-50401, 595-931 6903</td></tr>
                    <tr><td style="font-weight: bold;text-align: right;"><em>Datos de los tanques, instrumentos y dispensarios.</em></td></tr>
                    <tr>
                        <td>
                            <strong>Estación de servicio número:</strong>  <?= $ciaVO->getNumestacion() ?> 
                            <strong style="margin-left: 50px;">Razón Social:</strong>   <?= $ciaVO->getCia() ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table class="StyleTable" summary="Datos generales de la instalación">
                                <tr>
                                    <th scope="col" colspan="4"></th>
                                    <th scope="col" colspan="3" style="text-align: center;">Mangueras</th>
                                    <th scope="col"></th></tr>
                                <tr>
                                    <th scope="col">Isla</th>
                                    <th scope="col">Marca</th>
                                    <th scope="col">Modelo</th>
                                    <th scope="col">Serie</th>
                                    <th scope="col">Magna</th>
                                    <th scope="col">Premium</th>
                                    <th scope="col">Diesel</th>
                                    <th scope="col">Observaciones</th>
                                </tr>
                                <?php
                                $Dispensarios = "SELECT * FROM (SELECT lv.valor_lista_valor,man.isla_pos isla,man.posicion
                                FROM man 
                                    LEFT JOIN man_pro on man_pro.posicion = man.posicion 
                                    LEFT JOIN listas_valor lv ON man.marca = lv.llave_lista_valor
                                         WHERE man.activo='Si'  GROUP BY man_pro.dispensario) dispP
                                 left JOIN ( SELECT @i := @i + 1 as idt,p.* 
                                            FROM pos_catalog p 
                                                CROSS JOIN (SELECT @i := 0)p  where p.status = 'A' and p.dispositivo = 'D' ) dispS ON dispP.isla = dispS.idt";
                                $rsDsp = utils\IConnection::getRowsFromQuery($Dispensarios);
                                $i = 1;
                                foreach ($rsDsp as $Dsp) {
                                    $SubSql = "SELECT com.descripcion,clave,count(1) num FROM omicrom.man_pro mp LEFT JOIN com ON mp.producto=com.clavei WHERE mp.dispensario = " . $Dsp["isla"] . " and mp.activo = 'Si' group by descripcion;";
                                    $subDsp = utils\IConnection::getRowsFromQuery($SubSql);
                                    $numD = $numM = $numP = 0;
                                    foreach ($subDsp as $SubDsp) {
                                        if ($SubDsp["clave"] === "34006") {
                                            $numD = $SubDsp["num"];
                                        }
                                        if ($SubDsp["clave"] === "32011") {
                                            $numM = $SubDsp["num"];
                                        }
                                        if ($SubDsp["clave"] === "32012") {
                                            $numP = $SubDsp["num"];
                                        }
                                    }
                                    ?>
                                    <tr>
                                        <td><?= $Dsp["isla"] ?></td>
                                        <td><?= $Dsp["valor_lista_valor"] ?></td>
                                        <td><?= $Dsp["serial"] ?></td>
                                        <td><?= $Dsp["model"] ?></td>
                                        <td><?= $numM ?></td>
                                        <td><?= $numP ?></td>
                                        <td><?= $numD ?></td>
                                        <td></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                <tr>

                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr><td style="width: 15px;"></td></tr>
                    <tr>
                        <td>
                            <table class="StyleTable"  summary="Descripcion de cada uno de los tanques">
                                <tr>
                                    <th>No. Tanque</th>
                                    <th>Capacidad (litros)</th>
                                    <th>Tipo de Combustible</th>
                                    <th style="width: 35%;">Interconectado con los tanques para transvase (indique cuales)</th>
                                </tr>
                                <?php
                                $Dispensarios = "SELECT tanque,capacidad_total,producto FROM omicrom.tanques;";
                                $rsDsp = utils\IConnection::getRowsFromQuery($Dispensarios);
                                $i = 1;
                                foreach ($rsDsp as $Dsp) {
                                    ?>
                                    <tr>
                                        <td><?= $Dsp["tanque"] ?></td>
                                        <td><?= $Dsp["capacidad_total"] ?></td>
                                        <td><?= $Dsp["producto"] ?></td>
                                        <td></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                <tr>

                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr><td>
                            <table style="width: 100%;" summary="Firma de autorización">
                                <tr><th scope="col" colspan="3" style="height: 80px;"></th></tr>
                                <tr>
                                    <th style="width: 33%" scope="col"></th>
                                    <th style="width: 33%;border-top: 1px solid #606c84;text-align: center;" scope="col">
                                        <strong><?= $ciaVO->getRepresentante_legal() ?></strong>
                                    </th>
                                    <th style="width: 33%" scope="col"></th>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

            </div>
        </div>
    </body>
    <style>
        .StyleTable{
            width: 100%;
            border: 1px solid #606c84;
            border-radius: 0px 0px 20px 20px;
        }
        .StyleTable tr:nth-child(2){
            background-color: #CCD1D1;
        }
        .StyleTable tr:nth-child(1){
            background-color: #CCD1D1;
        }

    </style>
</html>     

