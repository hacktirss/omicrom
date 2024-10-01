<?php

/**
 * Description of PagoseDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('PagoseVO.php');

class PagoseDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "pagose";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \PagoseVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "id,"
                . "factura,"
                . "referencia,"
                . "importe,"
                . "tipo,"
                . "imp,"
                . "iva,"
                . "ieps,"
                . "porcentaje"
                . ") "
                . "VALUES (?,?,?,?,?,?,?,?,?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("iiidissss",
                    $objectVO->getIdPago(),
                    $objectVO->getFactura(),
                    $objectVO->getReferencia(),
                    $objectVO->getImporte(),
                    $objectVO->getTipo(),
                    $objectVO->getImp(),
                    $objectVO->getIva(),
                    $objectVO->getIeps(),
                    $objectVO->getPorcentaje()
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
     * @return \PagoseVO
     */
    public function fillObject($rs) {
        $objectVO = new PagoseVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["idnvo"]);
            $objectVO->setIdPago($rs["id"]);
            $objectVO->setFactura($rs["factura"]);
            $objectVO->setReferencia($rs["referencia"]);
            $objectVO->setImporte($rs["importe"]);
            $objectVO->setTipo($rs["tipo"]);
            $objectVO->setImp($rs["imp"]);
            $objectVO->setIva($rs["iva"]);
            $objectVO->setIeps($rs["ieps"]);
            $objectVO->setPorcentaje($rs["porcentaje"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \PagoseVO
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
     * @param string $field Nombre del campo para borrar
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function removeLogic($idObjectVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "id = -id, "
                . "factura = -factura, "
                . "referencia = -referencia, "
                . "importe = -importe "
                . "WHERE idnvo = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("i", $idObjectVO);
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

    /**
     * 
     * @param int $idObjectVO Llave primaria o identificador 
     * @param string $field Nombre del campo a buscar
     * @return \PagoseVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new PagoseVO();
        $sql = "SELECT * FROM " . self::TABLA . " WHERE " . $field . " = '" . $idObjectVO . "'";
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
     * @param \PagoseVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO, $vv = true) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "id = ?, "
                . "factura = ?, "
                . "referencia = ?, "
                . "importe = ?, "
                . "tipo = ?, "
                . "imp = ?, "
                . "iva = ?, "
                . "ieps = ?, "
                . "porcentaje = ? "
                . "WHERE idnvo = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("iiidissssi",
                    $objectVO->getIdPago(),
                    $objectVO->getFactura(),
                    $objectVO->getReferencia(),
                    $objectVO->getImporte(),
                    $objectVO->getTipo(),
                    $objectVO->getImp(),
                    $objectVO->getIva(),
                    $objectVO->getIeps(),
                    $objectVO->getPorcentaje(),
                    $objectVO->getId()
            );
            $sqlR = $ps->execute() ? true : false;
            error_log($ps->error);
            if ($vv) {
                $this->calculoPorcetajePagado($objectVO->getIdPago(), $objectVO->getFactura());
            }
            return $sqlR;
        }
        return false;
    }

    public function calculoPorcetajePagado($idPago, $Factura) {

        $dtCalculado = "SELECT
                                fc.id,pagose.importe ImpPagado, ROUND((((pagose.importe*100)/fc.total)/100)*100,2) porcentaje,
                                fc.iva * ROUND(((pagose.importe*100)/fc.total)/100,4) iva ,fc.ieps * ROUND(((pagose.importe*100)/fc.total)/100,4) ieps,
                                fc.importe * ROUND(((pagose.importe*100)/fc.total)/100,4) importe,pagose.idnvo
                                FROM pagose 
                                left join pagos on pagos.id=pagose.id
                                JOIN fc ON fc.id = pagose.factura
                                WHERE pagose.id = " . $idPago . " AND pagose.factura= " . $Factura;
        if (($query = $this->conn->query($dtCalculado)) && ($Calculado = $query->fetch_assoc())) {
            $pagoseVO = $this->retrieve($Calculado["idnvo"], "idnvo");
            $pagoseVO->setImp($Calculado["importe"]);
            $pagoseVO->setIva($Calculado["iva"]);
            $pagoseVO->setIeps($Calculado["ieps"]);
            $pagoseVO->setPorcentaje($Calculado["porcentaje"]);
            $this->update($pagoseVO, false);
        }
    }

}

abstract class TipoPagoDetalle extends BasicEnum {

    const FACTURA = 0;
    const COMBUSTIBLE = 1;
    const ACEITES = 2;

}
