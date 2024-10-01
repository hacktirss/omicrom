<?php

/*
 * FcDAO
 * omicrom®
 * © 2017, Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since jul 2017
 */

include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('BasicEnum.php');
include_once ('FcVO.php');

class FcDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "fc";
    const SIN_TIMBRAR = "-----";
    const RFC_GENERIC = "XAXX010101000";

    private $conn;

    public function __construct() {
        $this->conn = getConnection();
    }

    public function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \FcVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = FcVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "serie, "
                . "folio, "
                . "cliente, "
                . "fecha, "
                . "status, "
                . "usr, "
                . "origen, "
                . "formadepago, "
                . "tdoctorelacionado, "
                . "tiporelacion, "
                . "relacioncfdi, "
                . "uuid, "
                . "descuento,"
                . "cancelacion"
                . ") "
                . "SELECT ?,IFNULL( ( SELECT MAX( folio ) FROM fc WHERE fc.serie =  ? ), 0 ) + 1, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, '-----', ?,? LIMIT 1";
        //error_log($sql);
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssssssssss",
                    $objectVO->getSerie(),
                    $objectVO->getSerie(),
                    $objectVO->getCliente(),
                    $objectVO->getStatus(),
                    $objectVO->getUsr(),
                    $objectVO->getOrigen(),
                    $objectVO->getFormadepago(),
                    $objectVO->getDocumentoRelacion(),
                    $objectVO->getTiporelacion(),
                    $objectVO->getRelacioncfdi(),
                    $objectVO->getDescuento(),
                    $objectVO->getCancelacion()
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
     * @return \FcVO
     */
    public function fillObject($rs) {
        $objectVO = new FcVO();
        if (is_array($rs)) {
            $objectVO->setId($rs['id']);
            $objectVO->setSerie($rs['serie']);
            $objectVO->setFolio($rs['folio']);
            $objectVO->setFecha($rs['fecha']);
            $objectVO->setCliente($rs['cliente']);
            $objectVO->setCantidad($rs['cantidad']);
            $objectVO->setImporte($rs['importe']);
            $objectVO->setIva($rs['iva']);
            $objectVO->setIeps($rs['ieps']);
            $objectVO->setStatus($rs['status']);
            $objectVO->setTotal($rs['total']);
            $objectVO->setUuid($rs['uuid']);
            $objectVO->setTicket($rs['ticket']);
            $objectVO->setObservaciones($rs['observaciones']);
            $objectVO->setUsr($rs['usr']);
            $objectVO->setOrigen($rs['origen']);
            $objectVO->setStCancelacion($rs['stCancelacion']);
            $objectVO->setRelacioncfdi($rs['relacioncfdi']);
            $objectVO->setTiporelacion($rs['tiporelacion']);
            $objectVO->setUsocfdi($rs['usocfdi']);
            $objectVO->setFormadepago($rs['formadepago']);
            $objectVO->setMetododepago($rs['metododepago']);
            $objectVO->setRelacionfolio($rs['relacionfolio']);
            $objectVO->setDocumentoRelacion($rs['tdoctorelacionado']);
            $objectVO->setSello($rs['sello']);
            $objectVO->setPeriodo($rs['periodo']);
            $objectVO->setMeses($rs['meses']);
            $objectVO->setAno($rs['ano']);
            $objectVO->setDescuento($rs['descuento']);
            $objectVO->setCancelacion($rs["cancelacion"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \FcVO
     */
    public function getAll($sql) {
        
    }

    /**
     * 
     * @param int $idObjectVO Llave primaria o identificador 
     * @param string $field Nombre del campo para borrar
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function remove($idObjectVO, $field = "id") {
        
    }

    /**
     * 
     * @param int $idObjectVO Llave primaria o identificador 
     * @param string $field Nombre del campo a buscar
     * @return \FcVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new FcVO();
        $sql = "SELECT " . self::TABLA . ".*, CASE WHEN fc.tdoctorelacionado = 'ANT' THEN pagos.id ELSE R.folio END relacionfolio, "
                . "IFNULL(ExtractValue(facturas.cfdi_xml, '/cfdi:Comprobante/@Sello'),'') sello "
                . "FROM " . self::TABLA . " "
                . "LEFT JOIN fc R ON R.id = fc.relacioncfdi LEFT JOIN pagos ON pagos.id = fc.relacioncfdi "
                . "LEFT JOIN facturas ON fc.uuid = facturas.uuid "
                . "WHERE " . self::TABLA . "." . $field . " = '" . $idObjectVO . "'";
        //error_log($sql);
        if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            $objectVO = $this->fillObject($rs);
        } else {
            error_log($this->conn->error);
        }
        return $objectVO;
    }

    /**
     * 
     * @param \FcVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = FcVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "serie = ?, "
                . "folio = ?, "
                . "fecha = ?, "
                . "cliente = ?, "
                . "cantidad = ?, "
                . "importe = ?, "
                . "iva = ?, "
                . "ieps = ?, "
                . "status = ?, "
                . "total = ?, "
                . "uuid = ?, "
                . "ticket = ?, "
                . "observaciones = ?, "
                . "usr = ?, "
                . "origen = ?, "
                . "stCancelacion = ?, "
                . "tiporelacion = ?, "
                . "relacioncfdi = ?, "
                . "tdoctorelacionado = ?, "
                . "usocfdi = ?, "
                . "formadepago = ?, "
                . "metododepago = ?, "
                . "periodo = ?, "
                . "meses = ?, "
                . "ano = ? ,"
                . "descuento = ?, "
                . "cancelacion = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssssssssssssssssssssssi",
                    $objectVO->getSerie(),
                    $objectVO->getFolio(),
                    $objectVO->getFecha(),
                    $objectVO->getCliente(),
                    $objectVO->getCantidad(),
                    $objectVO->getImporte(),
                    $objectVO->getIva(),
                    $objectVO->getIeps(),
                    $objectVO->getStatus(),
                    $objectVO->getTotal(),
                    $objectVO->getUuid(),
                    $objectVO->getTicket(),
                    $objectVO->getObservaciones(),
                    $objectVO->getUsr(),
                    $objectVO->getOrigen(),
                    $objectVO->getStCancelacion(),
                    $objectVO->getTiporelacion(),
                    $objectVO->getRelacioncfdi(),
                    $objectVO->getDocumentoRelacion(),
                    $objectVO->getUsocfdi(),
                    $objectVO->getFormadepago(),
                    $objectVO->getMetododepago(),
                    $objectVO->getPeriodo(),
                    $objectVO->getMeses(),
                    $objectVO->getAno(),
                    $objectVO->getDescuento(),
                    $objectVO->getCancelacion(),
                    $objectVO->getId()
            );
            if ($ps->execute())
                return true;
        }
        error_log($this->conn->error);
        return false;
    }

    public function updateByField($idObjectVO, $field, $value) {
        $sql = "UPDATE fc SET " . $field . " = '" . $value . "' WHERE id = " . $idObjectVO . " LIMIT 1";
        if ($this->conn->query($sql)) {
            return true;
        }
        error_log($this->conn->error);
        return false;
    }

}

abstract class StatusFactura extends BasicEnum {

    const ABIERTO = 0;
    const CERRADO = 1;
    const CANCELADO = 2;
    const CANCELADO_ST = 3;

}

abstract class OrigenFactura extends BasicEnum {

    const OMICROM = 1;
    const TERMINAL = 2;
    const ONLINE = 3;

}

abstract class TipoDocumento extends BasicEnum {

    const FACTURA = "FAC";
    const ANTICIPO = "ANT";

}

abstract class StatusCancelacionFactura extends BasicEnum {

    const SIN_CANCELAR = 0;
    const PENDIENTE_CANCELAR = 1;
    const CANCELADA_SIN_CONFIRMAR = 2;
    const CANCELADA_CONFIRMADA = 3;

}

abstract class Series extends BasicEnum {

    const GENERAL = "serie_general";
    const CONTADO = "serie_contado";
    const CREDITO = "serie_credito";
    const DEBITO = "serie_debito";
    const MONEDERO = "serie_monederos_xml";

}
