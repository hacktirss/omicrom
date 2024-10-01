<?php

/**
 * Description of CxcDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('CxcVO.php');

class CxcDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "cxc";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \CxcVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "cliente,"
                . "placas,"
                . "referencia,"
                . "fecha,"
                . "hora,"
                . "tm,"
                . "concepto,"
                . "cantidad,"
                . "importe,"
                . "recibo,"
                . "corte,"
                . "producto,"
                . "rubro,"
                . "factura"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssssssssssss",
                    $objectVO->getCliente(),
                    $objectVO->getPlacas(),
                    $objectVO->getReferencia(),
                    $objectVO->getFecha(),
                    $objectVO->getHora(),
                    $objectVO->getTm(),
                    $objectVO->getConcepto(),
                    $objectVO->getCantidad(),
                    $objectVO->getImporte(),
                    $objectVO->getRecibo(),
                    $objectVO->getCorte(),
                    $objectVO->getProducto(),
                    $objectVO->getRubro(),
                    $objectVO->getFactura()
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
     * @return \CxcVO
     */
    public function fillObject($rs) {
        $objectVO = new CxcVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setCliente($rs["cliente"]);
            $objectVO->setPlacas($rs["placas"]);
            $objectVO->setReferencia($rs["referencia"]);
            $objectVO->setFecha($rs["fecha"]);
            $objectVO->setHora($rs["hora"]);
            $objectVO->setTm($rs["tm"]);
            $objectVO->setConcepto($rs["concepto"]);
            $objectVO->setCantidad($rs["cantidad"]);
            $objectVO->setImporte($rs["importe"]);
            $objectVO->setRecibo($rs["recibo"]);
            $objectVO->setCorte($rs["corte"]);
            $objectVO->setProducto($rs["producto"]);
            $objectVO->setRubro($rs["rubro"]);
            $objectVO->setFactura($rs["factura"]);
            $objectVO->setClienteDescripcion($rs["clienteDescripcion"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \CxcVO
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
     * @return \CxcVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new CxcVO();
        $sql = "SELECT " . self::TABLA . ".*, CONCAT(cli.id, ' | ', cli.tipodepago, ' | ', cli.nombre) clienteDescripcion "
                . "FROM " . self::TABLA . " "
                . "LEFT JOIN cli ON " . self::TABLA . ".cliente = cli.id "
                . "WHERE " . self::TABLA . "." . $field . " = '" . $idObjectVO . "'";
        //error_log($sql);
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
     * @param \CxcVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "cliente = ?, "
                . "placas = ?, "
                . "referencia = ?, "
                . "fecha = ?, "
                . "hora = ?, "
                . "tm = ?, "
                . "concepto = ?, "
                . "cantidad = ?, "
                . "importe = ?, "
                . "recibo = ?, "
                . "corte = ?, "
                . "producto = ?, "
                . "rubro = ?, "
                . "factura = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssssssssssssi",
                    $objectVO->getCliente(),
                    $objectVO->getPlacas(),
                    $objectVO->getReferencia(),
                    $objectVO->getFecha(),
                    $objectVO->getHora(),
                    $objectVO->getTm(),
                    $objectVO->getConcepto(),
                    $objectVO->getCantidad(),
                    $objectVO->getImporte(),
                    $objectVO->getRecibo(),
                    $objectVO->getCorte(),
                    $objectVO->getProducto(),
                    $objectVO->getRubro(),
                    $objectVO->getFactura(),
                    $objectVO->getId()
            );
            if ($ps->execute()) {
                return true;
            } else {
                error_log($this->conn->error);
                return false;
            }
        }
    }

    /**
     * Ingresmos cxc update or create, validamos los datos al ingresar  y verificamos que ese registro no se duplique 
     * Insert --> Se necesitan todos los campos llenos.
     * Update --> Solo se puede actualizar el campo Placas, para actualizar otros datos utilizar la funcion update.
     * @param \CxcVO $objectVO el objeto debe estar completamente lleno listo para ser insertado o id
     * mas placa para poder ser actualizado
     * @return boolean Si la operación fue exitosa devolvera TRUE o FALSE en caso contrario
     */
    public function insertValidation($objectVO) {
        $IdCxc = "SELECT id FROM cxc WHERE referencia = " . $objectVO->getReferencia() . " AND cliente = " . $objectVO->getCliente() . " AND tm='C'";
        if (($query = $this->conn->query($IdCxc)) && ($rs = $query->fetch_assoc())) {
            $Placas = $objectVO->getPlacas();
            $objectVO = $this->retrieve($rs["id"]);
            $objectVO->setPlacas($Placas);
            $sts = $this->update($objectVO) ? true : false;
        } else {
            $sts = $this->create($objectVO) ? true : false;
        }
        return $sts;
    }

}
