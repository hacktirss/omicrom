<?php
session_start();
error_reporting(0);
header("Content-type: text/xml");

include_once "libnvo/lib.php";
$mysqli = iconnect();
$usuarioSesion = getSessionUsuario();

$selectTanques = "
                SELECT tanques.tanque,com.descripcion,tanques.clave_producto,
                tanques.volumen_actual,tanques.fecha_hora_s,com.clavei,
                tanques.capacidad_total,tanques.temperatura,tanques.agua,tanques.fecha_hora_s,
                tanques.volumen_operativo,tanques.altura,com.color,tanques.volumen_actual/prm.prm prm
                FROM tanques,com LEFT JOIN 
                (SELECT (SUM(volumen) / 7) prm,producto FROM rm 
                WHERE fecha_venta >= date_format(date_add(NOW(), INTERVAL -7 DAY),'%Y%m%d')
                AND tipo_venta='D' GROUP BY producto) prm  on prm.producto=com.clavei
                WHERE tanques.clave_producto = com.clave AND com.activo='Si' AND tanques.estado = 1
                ORDER BY tanque";
?>

<?php
$selectPosiciones = "
    SELECT man.posicion,e.estado,e.venta,e.volumen,LOWER(com.descripcion) producto,e.folio,
    CASE 
    WHEN e.estado = 'd' THEN 'despachando'
    WHEN e.estado = 'e' THEN 'en espera'
    WHEN e.estado = 'b' THEN 'bloqueado'
    WHEN e.estado = 'i' THEN 'inhabilitado'
    ELSE 'desconctda'
    END accion,
    CASE 
    WHEN e.estado = 'd' THEN 'imgvd.png'
    WHEN e.estado = 'e' THEN 'imgna.png'
    WHEN e.estado = 'b' THEN 'imgrj.png'
    WHEN e.estado = 'i' THEN 'imgrj.png'
    ELSE 'imgng.png'
    END imagen
    FROM man 
    LEFT JOIN estado_posiciones e ON man.posicion = e.posicion
    LEFT JOIN com ON e.producto = com.clavei
    WHERE man.activo='Si'    AND man.posicion > 0
    ORDER BY man.posicion";
$result = $mysqli->query($selectPosiciones);

$sql2 = "SELECT d.mensaje,v.corteautomatico,d.titulo FROM display d LEFT JOIN variables v ON TRUE";
$DisA = $mysqli->query($sql2);
$Dis = $DisA->fetch_array();

$sql3 = "SELECT * FROM islas WHERE activo = 'Si'";
$CtA = $mysqli->query($sql3);
$Ct = $CtA->fetch_array();

$sql4 = "SELECT count(1) n FROM tanques LEFT JOIN com ON tanques.clave_producto = com.clave WHERE estado = 1 AND activo='Si'";
$CtA = $mysqli->query($sql4);
$Count = $CtA->fetch_array();
$ColorBorder = "#566573";
$Colspan = $Count["n"] > 3 ? 3 : $Count["n"];
$stl = "border-radius: 20px 20px 0px 0px;border-top:1px solid $ColorBorder;border-right:1px solid $ColorBorder;border-left:1px solid $ColorBorder;";
?> 
<table width="95%" class="texto_tablas" cellpadding="0" cellspacing="2" aria-hidden="true">
    <tbody>
        <tr style="height: 22px;">
            <td></td>
            <td align='center' colspan="<?= $Count["n"] ?>" bgcolor="#e1e1e1" style="<?= $stl ?>"><strong>Tanques</strong></td>
            <td align='center' colspan="<?= $Colspan ?>" bgcolor="#e1e1e1" style="<?= $stl ?>"><strong>Venta del corte actual</strong></td>
        </tr>
        <tr bgcolor="#e1e1e1">
            <td width="25%" align="center" style="text-align: left;border-radius: 20px 20px 20px 20px;border-top: 1px solid <?= $ColorBorder ?>;border: 1px solid <?= $ColorBorder ?>;">
                <?php if ($Dis["mensaje"] !== "-----") { ?>
                    <i class="fa fa-spinner fa-pulse fa-2x" aria-hidden="true" style="margin-right: 5px;margin-left: 10px;"></i><span class="sr-only">Loading...</span>
                <?php } else { ?>
                    <?php if ($Dis["corteautomatico"] === 'Si') { ?>
                        <div style="color: limegreen;display: inline-block;" title="Corte automatico activo"><i class="icon fa fa-lg fa-square" aria-hidden="true" style="margin-right: 5px;margin-left: 10px;"></i></div>
                    <?php } else { ?>
                        <div style="color: gray;display: inline-block;" title="Corte automatico inactivo"><i class="icon fa fa-lg fa-square" aria-hidden="true" style="margin-right: 5px;margin-left: 10px;"></i></div>
                    <?php } ?>
                <?php } ?>
                <?= $Dis["titulo"] ?>
            </td>
            <?php
            $date = date('Y-m-d H:i:s', time() - 60 * 30);
            $result0 = $mysqli->query($selectTanques);
            $NoT = $mysqli->affected_rows;
            $i = 1;
            while ($rg = $result0->fetch_array()) {
                if ($i == 1) {
                    $Style = "border-radius: 0px 0px 0px 20px;border-left:1px solid $ColorBorder;border-bottom:1px solid $ColorBorder;";
                } else if ($i == $NoT) {
                    $Style = "border-radius: 0px 0px 20px 0px;border-right:1px solid $ColorBorder;border-bottom:1px solid $ColorBorder;";
                } else {
                    $Style = ";border-bottom:1px solid $ColorBorder;";
                }
                $i++;
                $Fecha = $rg[fecha_hora_s];
                if ($date > $Fecha) {
                    $color = "class='flash'";
                    $BuscaRg = "SELECT * FROM bitacora_eventos WHERE descripcion_evento  like '%TANQUE : " . $rg["descripcion"] . " DESCONECTADO%' AND fecha_evento = '" . date("Y-m-d") . "'";
                    $rst = $mysqli->query($BuscaRg)->fetch_array();
                    if (!($rst["id_bitacora"] > 0)) {
                        BitacoraDAO::getInstance()->saveLogSn("Omicrom", "UCC", "TANQUE : " . $rg["descripcion"] . " DESCONECTADO");
                    }
                }
                if ($Pre["color"] === "RED") {
                    $ColorP = "#E74C3C";
                } else if ($Pre["color"] === "GREEN") {
                    $ColorP = "#27AE60";
                } else if ($Pre["color"] === "BLUE") {
                    $ColorP = "#2670a9";
                } else {
                    $ColorP = "#2C3E50";
                }
                $StsDias = $rg["prm"] < 3 ? "color: #CB4335" : "";
                ?>
                <td <?= $color ?> width="100" style="font-size: 11px;background-color: <?= $ColorT ?>;<?= $Style ?>" title="Ultima fecha de lectura <?= $rg[fecha_hora_s] ?>">
                    <p style="padding-left: 5px;line-height: 3px;">
                        <?= $rg["descripcion"] ?>
                    </p>
                    <p style="padding-left: 5px;line-height: 3px;">
                        Cnt. <?= number_format($rg[volumen_actual]) ?> Lts.
                    </p>
                    <p style="padding-left: 5px;line-height: 3px;font-size: 8px;">
                        <?= $rg[fecha_hora_s] ?>
                    </p>
                    <p style="padding-left: 5px;line-height: 3px;<?= $StsDias ?>;">
                        <?= number_format($rg["prm"]) ?> Dias.
                    </p>
                </td>
                <?php
            }
            $i = 1;
            ?>
            <?php
            $sql4 = "SELECT id,clavei producto,precio,ieps,descripcion,color FROM com WHERE activo='Si'";
            $PreA = $mysqli->query($sql4);
            $No = $mysqli->affected_rows;
            $i = 1;
            while ($Pre = $PreA->fetch_array()) {
                if ($i == 1) {
                    $Style = ";border-radius: 0px 0px 0px 20px;border-left:1px solid $ColorBorder;border-bottom:1px solid $ColorBorder;";
                } else if ($i == $No) {
                    $Style = ";border-radius: 0px 0px 20px 0px;border-right:1px solid $ColorBorder;border-bottom:1px solid $ColorBorder;";
                } else {
                    $Style = ";border-bottom:1px solid $ColorBorder;";
                }
                $i++;
                $Ct = "SELECT isla,turno,status,corte FROM islas WHERE activo='Si' ORDER BY isla";
                $cVar = $mysqli->query($Ct)->fetch_array();

                $ImpVol = "SELECT sum(importe) imp, sum(volumen) vl FROM omicrom.rm "
                        . "where corte = '" . $cVar["corte"] . "' AND producto = '" . $Pre["producto"] . "';";

                $IVol = $mysqli->query($ImpVol)->fetch_array();

                if ($Pre["color"] === "RED") {
                    $ColorP = "#E74C3C";
                } else if ($Pre["color"] === "BLUE") {
                    $ColorP = "#2670a9";
                } else if ($Pre["color"] === "GREEN") {
                    $ColorP = "#27AE60";
                } else if ($Pre["color"] === "BLACK") {
                    $ColorP = "#2C3E50";
                } else {
                    $ColorP = $Pre["color"];
                }

                $Der = 'style="display: inline-block;width: 40%;padding-left: 5px;"';
                $Izq = 'style="display: inline-block;width: 49%;text-align: right;"';
                ?>
                <td width="120" style="font-size: 11px;background-color: <?= $ColorP ?><?= $Style ?>;color: white;">
                    <p style="padding-left: 5px;line-height: 0px;" title="Ingresar para actualizar Ieps: <?= number_format($Pre["ieps"], 6) ?>"> 
                        <a onclick="javascript:winieps('modifica_ieps.php?busca=<?= $Pre["id"] ?>');">
                            <strong  class="TextoProductos"><?= $Pre["descripcion"] ?></strong> 
                        </a>
                    </p>
                    <div style="width: 100%;">
                        <div <?= $Der ?>>Precio </div>
                        <div <?= $Izq ?>>$<?= number_format($Pre["precio"], 2) ?></div>
                    </div>
                    <?php
                    if ($usuarioSesion->getTeam() === "Administrador" || $usuarioSesion->getTeam() === "Supervisor") {
                        ?>
                        <div style="width: 100%;">
                            <div <?= $Der ?>>Importe </div>
                            <div <?= $Izq ?>>$<?= number_format($IVol["imp"]) ?></div>
                        </div>
                        <div style="width: 100%; height: 17px;">
                            <div <?= $Der ?>>Volumen </div>
                            <div <?= $Izq ?>><?= number_format($IVol["vl"]) ?> Lts.</div>
                        </div>
                        <?php
                    }
                    ?>
                </td>
                <?php
            }
            ?>
        </tr>
    </tbody>
</table>
<table width="95%" align="center" border="2" cellpadding="0" cellspacing="5" aria-hidden="true" style="margin-top: 5px;border-radius: 5px;">
    <tbody>
        <?php
        $nSec = 1;
        while ($row = $result->fetch_assoc()) {
            ?>
            <?php if ($nSec == 1) { ?>
                <tr height="70">
                    <?php
                } elseif ($nSec == 5) {
                    echo "</tr><tr height=\"70\">";
                    $nSec = 1;
                }
                ?>
                <td width="130" align="center" valign="top" class="OnTable" style="border-radius: 10px;">
                    <table width="180" class="texto_tablas" aria-hidden="true" style="border-radius: 5px;">
                        <tbody>
                            <tr>
                                <td colspan="2">
                                    <strong><?= $row["posicion"] ?></strong> <?= $row["accion"] ?>
                                    <?php if ($usuarioSesion->getLevel() > 5) { ?>
                                        <a style="color: gray; font-size: 11px;" onclick="window.location = 'remisiones.php?criteria=ini&Servicio=1&Posicion=<?= $row["posicion"] ?>'" title="Ventas por posicion">
                                            <i class="icon fa fa-lg fa-file-text-o" aria-hidden="true"></i>
                                        </a>
                                    <?php } else { ?>
                                        <i class="icon fa fa-lg fa-file-text-o" aria-hidden="true"></i>
                                    <?php } ?>
                                </td>
                                <td align="right">
                                    <?php if ($usuarioSesion->getLevel() > 5) { ?>
                                        <a style="color: gray;text-decoration: none;" onclick="window.location = 'mdepositos.php?Corte=<?= $Ct["corte"] ?>&criteria=ini'" title="Depositos por posicion">
                                            <i class="icon fa fa-lg fa-user-plus" aria-hidden="true"></i>
                                        </a>
                                    <?php } else { ?>
                                        <i class="icon fa fa-lg fa-user-plus" aria-hidden="true"></i>
                                    <?php } ?>
                                    &nbsp;
                                    <a class="textosCualli" onclick=javascript:winmin("impticketdetick.php?busca=<?= $row["folio"] ?>&op=1");  title="Imprimir ultima venta">
                                        <i class="icon fa fa-lg fa-print" aria-hidden="true"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td width="40" height="55" rowspan="3">
                                    <div class="sombra_movil">
                                        <?php if ($usuarioSesion->getLevel() > 5) { ?>
                                            <a onclick=javascript:winmin("gencomando.php?busca=<?= $row["posicion"] ?>");>
                                                <img src="libnvo/<?= $row["imagen"] ?>" alt=""/>
                                            </a>
                                        <?php } else { ?>
                                            <img src="libnvo/<?= $row["imagen"] ?>" alt=""/>
                                        <?php } ?>
                                    </div>
                                </td>
                                <td align="right">Monto: </td>
                                <td align="right" width="60" style="color: #F63;"><?= number_format($row["venta"], 2) ?></td>
                            </tr>
                            <tr>
                                <td align="right">Litros: </td>
                                <td align="right"  style="color: #F63;"><?= number_format($row["volumen"], 3) ?></td>
                            </tr>
                            <tr>
                                <td align="right">Producto: </td>
                                <td align="right"  style="color: #F63;"><?= $row["producto"] ?></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
                <?php
                $nSec++;
            }
            ?>
            <?php if ($nSec > 1) { ?>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>

<?php
mysqli_close($mysqli);
?>
<div align="center" class="texto_bienvenida_usuario"><?= $Dis["mensaje"] ?></div>
