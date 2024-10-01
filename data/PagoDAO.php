<?php

/**
 * Description of PagoDAO
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
include_once ('PagoVO.php');

class PagoDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "pagos";
    const SIN_TIMBRAR = "-----";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \PagoVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = PagoVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "cliente,"
                . "fecha,"
                . "fechar,"
                . "fecha_deposito,"
                . "concepto,"
                . "importe,"
                . "aplicado,"
                . "referencia,"
                . "status,"
                . "banco,"
                . "formapago,"
                . "numoperacion,"
                . "tiporelacion,"
                . "relacioncfdi,"
                . "usr,"
                . "status_pago,"
                . "uuid,"
                . "relacion,"
                . "usocfdi,"
                . "fecha_ini,"
                . "fecha_fin,"
                . "montonoreconocido"
                . ") "
                . "VALUES(?, NOW(), NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("issdsssssssssisissss",
                    $objectVO->getCliente(),
                    $objectVO->getFecha_deposito(),
                    $objectVO->getConcepto(),
                    $objectVO->getImporte(),
                    $objectVO->getAplicado(),
                    $objectVO->getReferencia(),
                    $objectVO->getStatus(),
                    $objectVO->getBanco(),
                    $objectVO->getFormapago(),
                    $objectVO->getNumoperacion(),
                    $objectVO->getTiporelacion(),
                    $objectVO->getRelacioncfdi(),
                    $objectVO->getUsr(),
                    $objectVO->getStatus_pago(),
                    $objectVO->getUuid(),
                    $objectVO->getRelacion(),
                    $objectVO->getUsocfdi(),
                    $objectVO->getFecha_ini(),
                    $objectVO->getFecha_fin(),
                    $objectVO->getMontonoreconocido()
            );
            if ($ps->execute()) {
                $id = $ps->insert_id;
                $ps->close();
                return $id;
            } else {
                error_log($ps->error);
                error_log($this->conn->info);
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
     * @return \PagoVO
     */
    public function fillObject($rs) {
        $objectVO = new PagoVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setCliente($rs["cliente"]);
            $objectVO->setFecha($rs["fecha"]);
            $objectVO->setFecha_deposito($rs["fecha_deposito"]);
            $objectVO->setConcepto($rs["concepto"]);
            $objectVO->setImporte($rs["importe"]);
            $objectVO->setAplicado($rs["saldo"]);
            $objectVO->setReferencia($rs["referencia"]);
            $objectVO->setStatus($rs["status"]);
            $objectVO->setBanco($rs["banco"]);
            $objectVO->setFormapago($rs["formapago"]);
            $objectVO->setNumoperacion($rs["numoperacion"]);
            $objectVO->setUuid($rs["uuid"]);
            $objectVO->setStatusCFDI($rs["statusCFDI"]);
            $objectVO->setStCancelacion($rs["stCancelacion"]);
            $objectVO->setFechar($rs["fechar"]);
            $objectVO->setTiporelacion($rs["tiporelacion"]);
            $objectVO->setRelacioncfdi($rs["relacioncfdi"]);
            $objectVO->setUsr($rs["usr"]);
            $objectVO->setStatus_pago($rs["status_pago"]);
            $objectVO->setFechaD($rs["fechaD"]);
            $objectVO->setHoraD($rs["horaD"]);
            $objectVO->setDetalle($rs["sumDetalle"]);
            $objectVO->setRelacion($rs["relacion"]);
            $objectVO->setUsocfdi($rs["usocfdi"]);
            $objectVO->setSaldoFavor($rs["saldoFavor"]);
            $objectVO->setFecha_ini($rs["fecha_ini"]);
            $objectVO->setFecha_fin($rs["fecha_fin"]);
            $objectVO->setMontonoreconocido($rs["montonoreconocido"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \PagoVO
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
     * @return \PagoVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new PagoVO();
        $sql = "SELECT " . self::TABLA . ".*,DATE_FORMAT(pagos.fecha_deposito,'%Y-%m-%d') fechaD,DATE_FORMAT(pagos.fecha_deposito,'%H:%i:%s') horaD,
                (pagos.importe - IFNULL(SUM(pagose.importe),0)) saldo, IFNULL(SUM(pagose.importe),0) sumDetalle
                FROM " . self::TABLA . " 
                LEFT JOIN pagose ON pagos.id = pagose.id
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
     * @param \PagoVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = PagoVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "cliente = ?, "
                . "fecha = ?, "
                . "fecha_deposito = ?, "
                . "concepto = ?, "
                . "importe = ?, "
                . "aplicado = ?, "
                . "referencia = ?, "
                . "status = ?, "
                . "banco = ?, "
                . "formapago = ?, "
                . "numoperacion = ?, "
                . "uuid = ?, "
                . "statusCFDI = ?, "
                . "stCancelacion = ?, "
                . "fechar = ?, "
                . "tiporelacion = ?, "
                . "relacioncfdi = ?, "
                . "usr = ?, "
                . "status_pago = ?, "
                . "relacion = ?, "
                . "usocfdi = ?, "
                . "fecha_ini = ?, "
                . "fecha_fin = ? ,"
                . "montonoreconocido = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssssssssssssssssssssssi",
                    $objectVO->getCliente(),
                    $objectVO->getFecha(),
                    $objectVO->getFecha_deposito(),
                    $objectVO->getConcepto(),
                    $objectVO->getImporte(),
                    $objectVO->getAplicado(),
                    $objectVO->getReferencia(),
                    $objectVO->getStatus(),
                    $objectVO->getBanco(),
                    $objectVO->getFormapago(),
                    $objectVO->getNumoperacion(),
                    $objectVO->getUuid(),
                    $objectVO->getStatuscfdi(),
                    $objectVO->getStcancelacion(),
                    $objectVO->getFechar(),
                    $objectVO->getTiporelacion(),
                    $objectVO->getRelacioncfdi(),
                    $objectVO->getUsr(),
                    $objectVO->getStatus_pago(),
                    $objectVO->getRelacion(),
                    $objectVO->getUsocfdi(),
                    $objectVO->getFecha_ini(),
                    $objectVO->getFecha_fin(),
                    $objectVO->getMontonoreconocido(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}

abstract class StatusPago extends BasicEnum {

    const ABIERTO = "Abierta";
    const CERRADO = "Cerrada";
    const CANCELADO = "Cancelado";

}

abstract class StatusPagoPrepago extends BasicEnum {

    const SIN_LIBERAR = 1;
    const LIBERADO = 2;
    const CON_ANTICIPO = 3;
    const CON_FACTURA_CONSUMOS = 4;
    const CON_NOTA_CREDITO = 5;

}

abstract class StatusPagoCFDI extends BasicEnum {

    const ABIERTO = 0;
    const CERRADO = 1;
    const CANCELADO = 2;
    const CANCELADO_ST = 3;

}
