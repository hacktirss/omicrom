<?php

set_time_limit(720);

#Librerias
include_once ('data/FcDAO.php');
include_once ('data/FcdDAO.php');
include_once ('data/TrasladosDAO.php');
include_once ('data/TrasladosDetalleDAO.php');
include_once ('data/IngresosDAO.php');
include_once ('data/Ingresos_detalleDAO.php');
include_once ('data/ClientesDAO.php');
include_once ('data/CargasDAO.php');
include_once ('data/CombustiblesDAO.php');
include_once ('data/CxcDAO.php');
include_once ('data/IslaDAO.php');
include_once ('data/RmDAO.php');
include_once ('data/VentaAditivosDAO.php');
include_once ('data/ProductoDAO.php');
include_once ('data/ProveedorPACDAO.php');
include_once ('data/PagoDAO.php');
include_once ('data/V_CorporativoDAO.php');

require_once ('data/FacturaDetisa.php');
require_once ('data/FacturaDetisaGeneral.php');
require_once ('data/FacturaDetisaMonedero.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "trasladosd.php?";

$clientesDAO = new ClientesDAO();

$ciaDAO = new CiaDAO();
$rmDAO = new RmDAO();
$ventaAditivosDAO = new VentaAditivosDAO();
$productoDAO = new ProductoDAO();
$pacDAO = new ProveedorPACDAO();
$pagoDAO = new PagoDAO();
$TrasladosDAO = new TrasladosDAO();
$IngresosDAO = new IngresosDAO();
$IngresosDetalleDAO = new Ingresos_detalleDAO();
$trasladosDetalleDAO = new TrasladosDetalleDAO();

$nameVariableSession = "CatalogoTrasladosdetalle"; /* pagosd33 */

if ($request->hasAttribute("cVarVal")) {
    utils\HTTPUtils::setSessionBiValue($nameVariableSession, "cVarVal", $request->getAttribute("cVarVal"));
}

$cVarVal = utils\HTTPUtils::getSessionBiValue($nameVariableSession, "cVarVal");

$ciaVO = $ciaDAO->retrieve(1);
$TrasladosVO = new TrasladosVO();
$TrasladosVO = $TrasladosDAO->retrieve($cVarVal);
$IngresosVO = new IngresosVO();
$IngresosVO = $IngresosDAO->retrieve($cVarVal);

$clienteVO = new ClientesVO();
if (is_numeric($cVarVal)) {
    $trasladosDetalleVO = $trasladosDetalleDAO->retrieve($cVarVal);
    //error_log(print_r($trasladosDetalleVO, true));
}

if (!$request->hasAttribute("Boton")) {
    utils\HTTPUtils::setSessionValue("cGeneric", 0);
}

$lBd = false; /* Indica cuando inicia el proceso de timbrado */

if (is_string($request->getAttribute("Producto")) && !is_numeric($request->getAttribute("NoPedido")) && !is_numeric($request->getAttribute("NoSalida"))) {
    $isValid = TRUE;
    $Msj = utils\Messages::RESPONSE_VALID_CREATE;
    if ($request->getAttribute("Producto") <= 4) {
        $cSQL = "SELECT com.descripcion, com.precio, com.iva, com.ieps,com.clavei,com.id,com.clave 
                        FROM com JOIN inv ON inv.descripcion = com.descripcion
                        WHERE inv.id = '" . $request->getAttribute("Producto") . "'";

        if ($request->getAttribute("Importe") > 0) {
            $Tipo = "I";
            $Importe = ROUND($request->getAttribute("Importe"), 6);
        } else {
            $Tipo = "C";
            $Cnt = ROUND($request->getAttribute("Cantidad"), 3);
        }
    } else {
        $cSQL = "SELECT inv.descripcion, inv.precio, ROUND( cia.iva/100, 2 ) iva, 0.0000 ieps
                        FROM inv JOIN cia ON 1=1
                        WHERE inv.id = '" . $request->getAttribute("Producto") . "'";

        if ($request->getAttribute("Importe") > 0) {
            $Msj = "Para estos productos no es posible dar importe, favor de dar unicamente la <strong>Cantidad";
            $sql = "SELECT inv.retiene_iva,inv.porcentaje FROM inv WHERE inv.id = '" . $request->getAttribute("Producto") . "'";
            $retiene = $mysqli->query($sql)->fetch_array();
            if ($retiene["retiene_iva"] === "Si") {
                $Tipo = "I";
                $isValid = TRUE;
            } else {
                $isValid = TRUE;
            }
        }

        if ($request->getAttribute("Cantidad") > 0) {
            $Tipo = "C";
            $Cnt = ROUND($request->getAttribute("Cantidad"), 0);
        }
    }

    if ($isValid && !empty($request->getAttribute("Producto"))) {
        $Inv = $mysqli->query($cSQL)->fetch_array();
        $Iva = $Inv['iva'];
        $Ieps = $Inv['ieps'];
        $PrecioxLitro = $request->getAttribute("PrecioxLitro");
        $PrecioU = ROUND(($PrecioxLitro - $Ieps) / (1 + $Iva), 3);
        $PrecioB = $PrecioxLitro;
        $Importe = $request->getAttribute("Cantidad") * $PrecioxLitro;
        $Cnt = $request->getAttribute("Cantidad");
        $Ieps = $Ieps;
        if (($Importe > 0 && $PrecioU > 0) || ($request->getAttribute("Producto") > 4)) {
            $RmDAO = new RmDAO();
            $RmVO = new RmVO();
            $dateNow = date("Y-m-d H:i:s");
            $RmVO->setProducto($Inv["clavei"]);
            $RmVO->setPrecio($PrecioxLitro);
            $RmVO->setInicio_venta($dateNow);
            $RmVO->setFin_venta($dateNow);
            $RmVO->setPesos($Importe);
            $RmVO->setPesosp($PrecioxLitro);
            $RmVO->setVolumen($Cnt);
            $RmVO->setVolumenp($Cnt);
            $RmVO->setImporte($Importe);
            $RmVO->setIva($Iva);
            $RmVO->setIeps($Ieps);
            $RmVO->setCliente($IngresosVO->getId_cli());
            $RmVO->setDispensario(0);
            $RmVO->setManguera(0);
            $RmVO->setCorte(1);
            $RmVO->setDis_mang(0);
            $RmVO->setTurno(0);
            $RmVO->setEnviado(0);
            $RmVO->setIdcxc(0);
            $RmVO->setDepto(0);
            $RmVO->getKilometraje(0);
            $RmVO->setPosicion(0);
            $RmVO->setVendedor(0);
            $RmVO->setVendedor(0);
            $idrm = $RmDAO->create($RmVO);

            $CargasDAO = new CargasDAO();
            $CargasVO = new CargasVO();
            $CargasVO->setTanque($Inv["id"]);
            $CargasVO->setProducto($Inv["descripcion"]);
            $CargasVO->setClave_producto($Inv["clave"]);
            $CargasVO->setT_inicial(0);
            $CargasVO->setT_final(0);
            $CargasVO->setVol_inicial(0);
            $CargasVO->setFecha_inicio(date("Y-m-d H:i:s"));
            $CargasVO->setVol_final($Cnt);
            $NuevaFecha = strtotime('+20 minute', strtotime(date("Y-m-d H:i:s")));
            $CargasVO->setFecha_fin(date("Y-m-d H:i:s"));
            $CargasVO->setFecha_insercion(date("Y-m-d H:i:s"));
            $CargasVO->setAumento($Cnt);
            $CargasVO->setInicia_carga($CargasVO->getFecha_inicio());
            $CargasVO->setFinaliza_carga($CargasVO->getFecha_fin());
            $CargasVO->setEntrada(0);
            $CargasVO->setTipo(0);
            $CargasVO->setFolioenvios($IngresosVO->getFolio());
            $CargasVO->setEnviado(0);
            $CargasVO->setVol_doc(0);
            $CargasDAO->create($CargasVO);

            if (utils\HTTPUtils::getSessionObject("Tipo") == 1) {
                $trasladoDetalleVO = new TrasladosDetalleVO();
                $trasladoDetalleVO->setId($cVarVal);
                $trasladoDetalleVO->setProducto($request->getAttribute("Producto"));
                $trasladoDetalleVO->setCantidad($Cnt);
                $trasladoDetalleVO->setPrecio($PrecioU);
                $trasladoDetalleVO->setIeps($Ieps);
                $trasladoDetalleVO->setIva($Iva);
                $trasladoDetalleVO->setImporte($Importe);
                $trasladoDetalleVO->setPreciob($PrecioB);
                if (($id = $trasladosDetalleDAO->create($trasladoDetalleVO)) > 0) {
                    $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } else if (utils\HTTPUtils::getSessionObject("Tipo") == 2) {
                $ingresososdDetalleVO = new Ingresos_detalleVO();
                $ingresosDetalleDAO = new Ingresos_detalleDAO();
                $ingresososdDetalleVO->setId($cVarVal);
                $ingresososdDetalleVO->setProducto($request->getAttribute("Producto"));
                $ingresososdDetalleVO->setCantidad($Cnt);
                $ingresososdDetalleVO->setPrecio($PrecioU);
                $ingresososdDetalleVO->setIeps($Ieps);
                $ingresososdDetalleVO->setIva($Iva);
                $ingresososdDetalleVO->setImporte($Importe);
                $ingresososdDetalleVO->setPreciob($PrecioB);
                if (($id = $ingresosDetalleDAO->create($ingresososdDetalleVO)) > 0) {
                    $Update = "UPDATE ingresos_detalle SET id_rm = " . $idrm . " WHERE idnvo = " . $id;
                    error_log($Update);
                    utils\IConnection::execSql($Update);
                    TotalizaCartaPorte($cVarVal);
                    $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            }
        }
    } else {
        $Msj = "Los parametros ingresados son invalidos";
    }
    utils\HTTPUtils::getSessionObject("Tipo") == 1 ? TotalizaTraslados($cVarVal, $TrasladosDAO) : TotalizaIngresos($cVarVal, $IngresosDAO);
} else if (is_numeric($request->getAttribute("NoPedido"))) {
    if (utils\HTTPUtils::getSessionObject("Tipo") != 2) {
        $BuscaId = "SELECT id FROM traslados_detalle WHERE id_rm in ( SELECT id FROM rm WHERE idcxc = " . $request->getAttribute("NoPedido") . " AND manguera=0);";
        $Rs_ = utils\IConnection::execSql($BuscaId);
        if (!($Rs_["id"] > 0)) {
            $InsertPedido = "INSERT INTO traslados_detalle (id,producto,cantidad,preciob,precio,iva,ieps,importe,id_rm) "
                    . "SELECT $cVarVal,inv.id,rm.volumen,0,0,0,0,0,rm.id FROM rm LEFT JOIN com ON rm.producto = com.clavei "
                    . "LEFT JOIN inv ON com.descripcion=inv.descripcion WHERE rm.idcxc = " . $request->getAttribute("NoPedido") . ";"; //AND rm.dispensario=0 AND rm.manguera=0; ";
        } else {
            $Msj = utils\Messages::MESSAGES_FACTURAS_EXISTS . " No." . $Rs_["id"];
        }
    } else {
        $BuscaId = "SELECT id FROM ingresos_detalle WHERE id_rm in ( SELECT id FROM rm WHERE idcxc = " . $request->getAttribute("NoPedido") . " AND manguera=0);";
        $Rs_ = utils\IConnection::execSql($BuscaId);
        if (!($Rs_["id"] > 0)) {
            $InsertPedido = "INSERT INTO ingresos_detalle (id,producto,cantidad,preciob,precio,iva,ieps,importe,id_rm) "
                    . "SELECT $cVarVal,inv.id,rm.volumen,0,0,0,0,0,rm.id FROM rm LEFT JOIN com ON rm.producto = com.clavei "
                    . "LEFT JOIN inv ON com.descripcion=inv.descripcion WHERE rm.idcxc = " . $request->getAttribute("NoPedido") . ";"; //AND rm.dispensario=0 AND rm.manguera=0; ";
        } else {
            $Msj = utils\Messages::MESSAGES_FACTURAS_EXISTS . " No." . $Rs_["id"];
        }
    }
    utils\IConnection::execSql($InsertPedido);
    header("location: trasladosd.php?Msj=$Msj");
} else if ($request->getAttribute("NoSalida") > 0) {
    if (utils\HTTPUtils::getSessionObject("Tipo") != 2) {
        $BuscaId = "SELECT id FROM traslados_detalle WHERE id_rm in (" . $request->getAttribute("NoSalida") . ");";
        $Rs_ = utils\IConnection::execSql($BuscaId);
        if (!($Rs_["id"] > 0)) {
            $InsertSalida = "INSERT INTO traslados_detalle (id,producto,cantidad,preciob,precio,iva,ieps,importe,id_rm) "
                    . "SELECT $cVarVal,inv.id,rm.volumen,0,0,0,0,0,rm.id FROM rm LEFT JOIN com ON rm.producto = com.clavei "
                    . "LEFT JOIN inv ON com.descripcion=inv.descripcion WHERE rm.id = " . $request->getAttribute("NoSalida") . ";"; // AND rm.dispensario=0 AND rm.manguera=0; ";
        } else {
            $Msj = utils\Messages::MESSAGES_FACTURAS_EXISTS . " No." . $Rs_["id"];
        }
    } else {
        $BuscaId = "SELECT id FROM ingresos_detalle WHERE id_rm in (" . $request->getAttribute("NoSalida") . ");";
        $Rs_ = utils\IConnection::execSql($BuscaId);
        if (!($Rs_["id"] > 0)) {
            $InsertSalida = "INSERT INTO ingresos_detalle (id,producto,cantidad,preciob,precio,iva,ieps,importe,id_rm) "
                    . "SELECT $cVarVal,inv.id,rm.volumen,0,0,0,0,0,rm.id FROM rm LEFT JOIN com ON rm.producto = com.clavei "
                    . "LEFT JOIN inv ON com.descripcion=inv.descripcion WHERE rm.id = " . $request->getAttribute("NoSalida") . ";"; /* AND rm.dispensario=0 AND rm.manguera=0; "; */
        } else {
            $Msj = utils\Messages::MESSAGES_FACTURAS_EXISTS . " No." . $Rs_["id"];
        }
    }
    utils\IConnection::execSql($InsertSalida);
    header("location: trasladosd.php?Msj=$Msj");
}

if ($request->getAttribute("op") === "Si") {
    if (utils\HTTPUtils::getSessionObject("Tipo") != 2) {
        $trasladosDetalleDAO->remove($request->getAttribute("cId"));
    } else {
        $DeleteRm = "DELETE FROM rm WHERE id IN (SELECT id_rm FROM ingresos_detalle WHERE idnvo = " . $request->getAttribute("cId") . " )";
        utils\IConnection::execSql($DeleteRm);
        $IngresosDetalleDAO->remove($request->getAttribute("cId"));
        TotalizaCartaPorte($cVarVal);
    }
    $Msj = utils\Messages::RESPONSE_VALID_DELETE;
}

/**
 * 
 * @param int $traslados
 * @param TrasladosVO $trasladosVO;
 * @param trasladosDAO $tasladosDAO;
 */
function TotalizaTraslados($traslados, $trasladosDAO) {

    error_log("Totaliza Traslados");
    $mysqli = iconnect();
    $cSQL = "
        SELECT 
        cantidad, total, importe, iva, total-importe-iva ieps 
        FROM (
        SELECT 
           ROUND( sum( cantidad ), 3) cantidad,
           ROUND( sum( total ), 2) total,
           ROUND( sum( cantidad * ( preciob - factorieps ) / (1 + factoriva) ), 2) importe,
           ROUND( sum( cantidad * ( preciob - factorieps ) / (1 + factoriva) ) * factoriva, 2) iva
        FROM (
           SELECT 
              iva factoriva,
              ieps factorieps,
              cantidad,
              importe total,
              preciob
           FROM traslados_detalle WHERE id = '$traslados') as SUB
        ) SUBQ
    ";
    error_log($cSQL);

    $Ddd = $mysqli->query($cSQL)->fetch_array();

    $Cnt = 0;
    $Importe = 0;
    $Iva = 0;
    $Ieps = 0;
    $Total = 0;

    if ($Ddd[0] != 0) {
        $Cnt = $Ddd[0];
        $Importe = $Ddd['importe'];
        $Iva = $Ddd['iva'];
        $Ieps = $Ddd['ieps'];
        $Total = $Ddd['total'];
    }
    $trasladosVO = $trasladosDAO->retrieve($traslados);
    $trasladosVO->setCantidad($Cnt);
    $trasladosVO->setImporte($Importe);
    $trasladosVO->setIva($Iva);
    $trasladosVO->setIeps($Ieps);
    $trasladosVO->setTotal($Total);
    $trasladosVO->setStatus(0);
    error_log("Cargamos el objeto");
    error_log(print_r($trasladosVO, true));
    if (!$trasladosDAO->update($trasladosVO)) {
        error_log("Ha ocurrido un error");
    }
}

function TotalizaIngresos($ingresos, $ingresosDAO) {

    $mysqli = iconnect();
    $cSQL = "
        SELECT 
        cantidad, total, importe, iva, total-importe-iva ieps 
        FROM (
        SELECT 
           ROUND( sum( cantidad ), 3) cantidad,
           ROUND( sum( total ), 2) total,
           ROUND( sum( cantidad * ( preciob - factorieps ) / (1 + factoriva) ), 2) importe,
           ROUND( sum( cantidad * ( preciob - factorieps ) / (1 + factoriva) ) * factoriva, 2) iva
        FROM (
           SELECT 
              iva factoriva,
              ieps factorieps,
              cantidad,
              importe total,
              preciob
           FROM ingresos_detalle WHERE id = '$ingresos') as SUB
        ) SUBQ
    ";

    $Ddd = $mysqli->query($cSQL)->fetch_array();

    $Cnt = 0;
    $Importe = 0;
    $Iva = 0;
    $Ieps = 0;
    $Total = 0;

    if ($Ddd[0] != 0) {
        $Cnt = $Ddd[0];
        $Importe = $Ddd['importe'];
        $Iva = $Ddd['iva'];
        $Ieps = $Ddd['ieps'];
        $Total = $Ddd['total'];
    }
    $ingresosVO = $ingresosDAO->retrieve($traslados);
    $ingresosVO->setCantidad($Cnt);
    $ingresosVO->setImporte($Importe);
    $ingresosVO->setIva($Iva);
    $ingresosVO->setIeps($Ieps);
    $ingresosVO->setTotal($Total);
    $ingresosVO->setStatus(0);
    error_log("Cargamos el objeto");
    error_log(print_r($ingresosVO, true));
    if (!$ingresosDAO->update($ingresosVO)) {
        error_log("Ha ocurrido un error");
    }
}

function TotalizaCartaPorte($cVar) {
    utils\IConnection::execSql("UPDATE ingresos i LEFT JOIN (select SUM(cantidad) cantidad,SUM(precio * cantidad) importe,SUM((precio * cantidad)*iva) iva, 
                        (ieps * SUM(cantidad)) ieps,SUM(importe) total,id  from ingresos_detalle where producto >= 1 AND id=$cVar) id  ON 
                        i.id=id.id SET i.importe=id.importe,i.cantidad=id.cantidad,i.iva=id.iva,i.ieps=id.ieps,i.total=id.total WHERE i.id=$cVar;");
}
