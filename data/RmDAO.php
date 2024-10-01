<?php

/**
 * Description of RmDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('BasicEnum.php');
include_once ('RmVO.php');

class RmDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "rm";
    const TIPO = "C";

    private $conn;

    public function __construct() {
        $this->conn = getConnection();
    }

    public function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \RmVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = RmVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "dispensario,"
                . "posicion,"
                . "manguera,"
                . "dis_mang,"
                . "producto,"
                . "precio,"
                . "inicio_venta,"
                . "fin_venta,"
                . "pesos,"
                . "volumen,"
                . "pesosp,"
                . "volumenp,"
                . "importe,"
                . "completo,"
                . "turno,"
                . "corte,"
                . "vendedor,"
                . "iva,"
                . "ieps,"
                . "tipo_venta,"
                . "procesado,"
                . "enviado,"
                . "cliente,"
                . "pagoreal,"
                . "factor,"
                . "pagado,"
                . "fecha_venta,"
                . "descuento,"
                . "codigo,"
                . "idcxc"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?, 'D', 0, 0, ?, ?, ?, 0,DATE_FORMAT(?,'%Y%m%d'), ?,0,?)";
        if (($ps = $this->conn->prepare($sql))) {
            $cli = $objectVO->getCliente() == null ? 0 : $objectVO->getCliente();
            $idCxc = $objectVO->getIdcxc() == null ? 0 : $objectVO->getIdcxc();
            $ps->bind_param("sssssssssssssssssssssssi",
                    $objectVO->getDispensario(),
                    $objectVO->getPosicion(),
                    $objectVO->getManguera(),
                    $objectVO->getDis_mang(),
                    $objectVO->getProducto(),
                    $objectVO->getPrecio(),
                    $objectVO->getInicio_venta(),
                    $objectVO->getFin_venta(),
                    $objectVO->getPesos(),
                    $objectVO->getVolumen(),
                    $objectVO->getPesosp(),
                    $objectVO->getVolumenp(),
                    $objectVO->getPesos(),
                    $objectVO->getTurno(),
                    $objectVO->getCorte(),
                    $objectVO->getVendedor(),
                    $objectVO->getIva(),
                    $objectVO->getIeps(),
                    $cli,
                    $objectVO->getPesos(),
                    $objectVO->getFactor(),
                    $objectVO->getFin_venta(),
                    $objectVO->getDescuento(),
                    $idCxc
            );
            if ($ps->execute()) {
                $id = $ps->insert_id;
                $ps->close();
                return $id;
            } else {
                error_log($this->conn->error);
            }
            $ps->close();
        } else {
            error_log($this->conn->error);
        }
        return $id;
    }

    /**
     * 
     * @param array() $rs
     * @return \RmVO
     */
    public function fillObject($rs) {
        $objectVO = new RmVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setDispensario($rs["dispensario"]);
            $objectVO->setPosicion($rs["posicion"]);
            $objectVO->setManguera($rs["manguera"]);
            $objectVO->setDis_mang($rs["dis_mang"]);
            $objectVO->setProducto($rs["producto"]);
            $objectVO->setPrecio($rs["precio"]);
            $objectVO->setInicio_venta($rs["inicio_venta"]);
            $objectVO->setFin_venta($rs["fin_venta"]);
            $objectVO->setPesos($rs["pesos"]);
            $objectVO->setVolumen($rs["volumen"]);
            $objectVO->setPesosp($rs["pesosp"]);
            $objectVO->setVolumenp($rs["volumenp"]);
            $objectVO->setImporte($rs["importe"]);
            $objectVO->setComprobante($rs["comprobante"]);
            $objectVO->setFactor($rs["factor"]);
            $objectVO->setCompleto($rs["completo"]);
            $objectVO->setVendedor($rs["vendedor"]);
            $objectVO->setTurno($rs["turno"]);
            $objectVO->setCorte($rs["corte"]);
            $objectVO->setIva($rs["iva"]);
            $objectVO->setIeps($rs["ieps"]);
            $objectVO->setTipo_venta($rs["tipo_venta"]);
            $objectVO->setProcesado($rs["procesado"]);
            $objectVO->setEnviado($rs["enviado_cv"]);
            $objectVO->setCliente($rs["cliente"]);
            $objectVO->setPlacas($rs["placas"]);
            $objectVO->setCodigo($rs["codigo"]);
            $objectVO->setKilometraje($rs["kilometraje"]);
            $objectVO->setUuid($rs["uuid"]);
            $objectVO->setDepto($rs["depto"]);
            $objectVO->setVdm($rs["vdm"]);
            $objectVO->setPagado($rs["pagado"]);
            $objectVO->setPuntos($rs["puntos"]);
            $objectVO->setInformacorporativo($rs["informacorporativo"]);
            $objectVO->setInventario($rs["inventario"]);
            $objectVO->setPagoreal($rs["pagoreal"]);
            $objectVO->setIdcxc($rs["idcxc"]);
            $objectVO->setTipodepago($rs["tipodepago"]);
            $objectVO->setTotalizadorvi($rs["totalizadorVI"]);
            $objectVO->setTotalizadorvf($rs["totalizadorVF"]);
            $objectVO->setDescuento($rs["descuento"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \RmVO
     */
    public function getAll($sql) {
        $array = array();
        if (($query = $this->conn->query($sql))) {
            while (($rs = $query->fetch_assoc())) {
                $objectVO = $this->fillObject($rs);
                array_push($array, $objectVO);
            }
        } else {
            error_log($this->conn->error);
        }
        return $array;
    }

    /**
     * 
     * @param int $idObjectVO Llave primaria o identificador 
     * @param string $field Nombre del campo para borrar
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function remove($idObjectVO, $field = "id") {
        $sql = "DELETE FROM " . self::TABLA . " WHERE " . $field . " = ? LIMIT 1";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("s", $idObjectVO
            );
            return $ps->execute();
        }
    }

    /**
     * 
     * @param int $idObjectVO Llave primaria o identificador 
     * @param string $field Nombre del campo a buscar
     * @return \RmVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new RmVO();
        $sql = "SELECT rm.id,
                rm.dispensario,
                rm.posicion,
                rm.manguera,
                rm.dis_mang,
                rm.producto,
                rm.precio,
                rm.inicio_venta,
                rm.fin_venta,
                ROUND(rm.pesos, 2) pesos,
                ROUND(rm.volumen, 3) volumen,
                ROUND(rm.pesosp, 2) pesosp,
                ROUND(rm.volumenp, 3) volumenp,
                ROUND(rm.importe, 2) importe,
                rm.comprobante,
                rm.factor,
                rm.completo,
                rm.vendedor,
                rm.turno,
                rm.corte,
                rm.iva,
                rm.ieps,
                rm.tipo_venta,
                rm.procesado,
                rm.enviado,
                rm.cliente,
                rm.placas,
                rm.codigo,
                rm.kilometraje,
                rm.uuid,
                rm.depto,
                rm.vdm,
                rm.pagado,
                rm.puntos,
                rm.informacorporativo,
                rm.inventario,
                ROUND(rm.pagoreal, 2) pagoreal,
                rm.idcxc,
                rm.tipodepago,
                rm.totalizadorVI,
                rm.totalizadorVF,
                rm.fecha_venta, rm.descuento,
                COUNT(lg.id) enviado_cv  FROM " . self::TABLA . " 
                LEFT JOIN logenvios20 lg ON DATE(lg.fecha_informacion) = DATE(" . self::TABLA . ".fin_venta) 
                WHERE " . self::TABLA . "." . $field . " = '" . $idObjectVO . "'";
        if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            $objectVO = $this->fillObject($rs);
            return $objectVO;
        } else {
            error_log($this->conn->error);
        }
        return $objectVO;
    }

    /**
     * 
     * @param \RmVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = RmVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "dispensario = ?, "
                . "posicion = ?, "
                . "manguera = ?, "
                . "dis_mang = ?, "
                . "producto = ?, "
                . "precio = ?, "
                . "inicio_venta = ?, "
                . "fin_venta = ?, "
                . "pesos = ?, "
                . "volumen = ?, "
                . "pesosp = ?, "
                . "volumenp = ?, "
                . "importe = ?, "
                . "comprobante = ?, "
                . "factor = ?, "
                . "completo = ?, "
                . "vendedor = ?, "
                . "turno = ?, "
                . "corte = ?, "
                . "iva = ?, "
                . "ieps = ?, "
                . "tipo_venta = ?, "
                . "procesado = ?, "
                . "enviado = ?, "
                . "cliente = ?, "
                . "placas = ?, "
                . "codigo = ?, "
                . "kilometraje = ?, "
                . "uuid = ?, "
                . "depto = ?, "
                . "vdm = ?, "
                . "pagado = ?, "
                . "puntos = ?, "
                . "informacorporativo = ?, "
                . "inventario = ?, "
                . "pagoreal = ?, "
                . "idcxc = ?, "
                . "tipodepago = ? ,"
                . "descuento = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssssssssssssssssssssssssssssssssssi",
                    $objectVO->getDispensario(),
                    $objectVO->getPosicion(),
                    $objectVO->getManguera(),
                    $objectVO->getDis_mang(),
                    $objectVO->getProducto(),
                    $objectVO->getPrecio(),
                    $objectVO->getInicio_venta(),
                    $objectVO->getFin_venta(),
                    $objectVO->getPesos(),
                    $objectVO->getVolumen(),
                    $objectVO->getPesosp(),
                    $objectVO->getVolumenp(),
                    $objectVO->getImporte(),
                    $objectVO->getComprobante(),
                    $objectVO->getFactor(),
                    $objectVO->getCompleto(),
                    $objectVO->getVendedor(),
                    $objectVO->getTurno(),
                    $objectVO->getCorte(),
                    $objectVO->getIva(),
                    $objectVO->getIeps(),
                    $objectVO->getTipo_venta(),
                    $objectVO->getProcesado(),
                    $objectVO->getEnviado(),
                    $objectVO->getCliente(),
                    $objectVO->getPlacas(),
                    $objectVO->getCodigo(),
                    $objectVO->getKilometraje(),
                    $objectVO->getUuid(),
                    $objectVO->getDepto(),
                    $objectVO->getVdm(),
                    $objectVO->getPagado(),
                    $objectVO->getPuntos(),
                    $objectVO->getInformacorporativo(),
                    $objectVO->getInventario(),
                    $objectVO->getPagoreal(),
                    $objectVO->getIdcxc(),
                    $objectVO->getTipodepago(),
                    $objectVO->getDescuento(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}

abstract class TipoVenta extends BasicEnum {

    const NORMAL = "D";
    const JARREO = "J";
    const UVA = "A";
    const CONSIGNACION = "N";

}

function getResumenVentas($Connection, $TipoVenta, $FechaInicial, $FechaFinal, $Vol, $pesos, $Periodo) {
    /* Este apartado esta para el reporte de concentradodiacnt donde damos importes y le restamos a las ventas tipo d */
    /* RM.$Variable siempre debe de ser volumen, importe, pesos nada de p porque a consignaciónes no se procesa en la información */
    if ($Periodo === "Cortes") {
        $PeriodoGen = "DATE(ct.fecha) BETWEEN DATE('$FechaInicial') AND DATE('$FechaFinal') ";
        $pr = "pesosp";
        $volx = "volumenp";
        if ($TipoVenta === "'N'") {
            $volx = "volumen";
        }
    } else {
        $PeriodoGen = "rm.fecha_venta BETWEEN " . str_replace("-", "", $FechaInicial) . " AND " . str_replace("-", "", $FechaFinal) . " ";
        $pr = "importe";
        $volx = "importe/rm.precio";
    }
    $TipoDesucentoVolumen = $Periodo === "Cortes" ? "if(rm.tipo_venta='N',rm.$Vol,rm.volumenp - IFNULL(rmPd.Producido,0))" : "if(rm.tipo_venta='N',rm.$Vol,rm.volumenp)";
    $TipoDesucentoImporte = $Periodo === "Cortes" ? " ROUND(if(rm.tipo_venta='N',rm.$pesos,rm.$pr - IFNULL(rmPd.ProducidoP,0)),2)" : " ROUND(if(rm.tipo_venta='N',rm.$pesos,rm.$pr),2)";
    $sql = "SELECT com.descripcion producto, IFNULL(rm.ventas,0) ventas, rm.precios precio, rm.iva, rm.descuento,
	$TipoDesucentoVolumen volumen, 
                $TipoDesucentoImporte pesos,
                if(rm.tipo_venta='N',(rm.$Vol * rm.ieps),(rm.volumenp * rm.ieps) - IFNULL(rmPd.ieps,0)) ieps, 
                rm.tipo_venta FROM com LEFT JOIN ( 
                    SELECT DATE(ct.fecha) fecha, rm.corte,rm.iva, rm.tipo_venta, rm.producto, com.descripcion, rm.precio precios, 
                        COUNT(rm.id) ventas, AVG(rm.ieps) ieps, SUM($volx) volumen, SUM(rm.pesos) pesos, SUM($volx)
                        volumenp, SUM(rm.pesosp) pesosp, ROUND(SUM(rm.importe),2) importe,ROUND(SUM(rm.descuento),2) descuento
                        FROM com, rm, ct WHERE TRUE AND com.clavei = rm.producto AND com.activo = 'Si' AND rm.corte = ct.id 
                        AND rm.tipo_venta IN ($TipoVenta) AND $PeriodoGen
                        GROUP BY rm.producto, rm.precio ORDER BY rm.tipo_venta,rm.producto DESC ) rm ON rm.producto = com.clavei 
                LEFT JOIN (
                    SELECT rm.producto,rm.precio,sum(volumen) - sum($volx) Producido,SUM(pesos) - SUM(pesosp) ProducidoP,((sum(volumen) - sum(volumenp)) * rm.ieps) ieps
                        FROM com, rm, ct WHERE TRUE AND com.clavei = rm.producto AND com.activo = 'Si' AND rm.corte = ct.id 
                        AND rm.tipo_venta IN ('N') AND $PeriodoGen
                        GROUP BY rm.producto, rm.precio ORDER BY rm.producto DESC) rmPd ON rmPd.precio = rm.precios AND rmPd.producto=rm.producto
                WHERE TRUE AND com.activo = 'Si' GROUP BY rm.producto,rm.precios ORDER BY com.clave ASC";
    //echo $sql . "<br><br>";
    if (($query = $Connection->query($sql))) {
        while (($rs = $query->fetch_array())) {
            $object[] = $rs;
        }
    }
    return $object;
}
