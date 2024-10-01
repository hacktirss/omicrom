<?php

/**
 * Description of PedidosDAO
 * omicrom®
 * © 2021, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since oct 2022
 */
include_once ('mysqlUtils.php');
include_once ('PedidosVO.php');
include_once ('FunctionsDAO.php');

class PedidosDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "pedidos";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \PedidosVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "id_user, "
                . "fecha, "
                . "volumen, "
                . "producto, "
                . "fechafin,"
                . "terminal_almacenamiento,"
                . "alert"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssii",
                    $objectVO->getId_user(),
                    $objectVO->getFecha(),
                    $objectVO->getVolumen(),
                    $objectVO->getProducto(),
                    $objectVO->getFechafin(),
                    $objectVO->getTerminal_almacenamiento(),
                    $objectVO->getAlert()
            );
            $id = $ps->execute() ? $ps->insert_id : -1;
            error_log(mysqli_error($this->conn));
            $ps->close();
            return $id;
        }
        return 0;
    }

    /**
     * 
     * @param array() $rs
     * @return \PedidosVO
     */
    public function fillObject($rs) {
        $objectVO = new PedidosVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setId_user($rs["id_user"]);
            $objectVO->setFecha($rs["fecha"]);
            $objectVO->setVolumen($rs["volumen"]);
            $objectVO->setProducto($rs["producto"]);
            $objectVO->setStatus($rs["status"]);
            $objectVO->setFechafin($rs["fechafin"]);
            $objectVO->setTerminal_almacenamiento($rs["terminal_almacenamiento"]);
            $objectVO->setAlert($rs["alert"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param int $idObjectVO Llave primaria o identificador 
     * @param string $field Nombre del campo a buscar
     * @return \PedidosVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new PedidosVO();
        $sql = "SELECT * FROM " . self::TABLA . " WHERE " . $field . " = " . $idObjectVO;
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
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \PedidosVO
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
     * @param \PedidosVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "id_user = ?, "
                . "fecha = ?, "
                . "volumen = ?, "
                . "producto = ?, "
                . "status = ?, "
                . "fechafin= ?, "
                . "terminal_almacenamiento = ? ,"
                . "alert = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssssiii",
                    $objectVO->getId_user(),
                    $objectVO->getFecha(),
                    $objectVO->getVolumen(),
                    $objectVO->getProducto(),
                    $objectVO->getStatus(),
                    $objectVO->getFechafin(),
                    $objectVO->getTerminal_almacenamiento(),
                    $objectVO->getAlert(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
    }

}
