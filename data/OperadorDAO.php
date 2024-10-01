<?php

/**
 * Description of OperadorDAO
 * omicrom®
 * © 2022, Detisa 
 * http://www.detisa.com.mx
 * @author Alan Rodriguez 
 * @version 1.0
 * @since ago 2022
 */

include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('BasicEnum.php');
include_once ('OperadorVO.php');

class OperadorDAO implements FunctionsDAO{
    
    const RESPONSE_VALID = "OK";
    const TABLA = "catalogo_operadores";
    
    function __construct() {
        $this->conn= getConnection();
    } 
    function __destruct() {
        $this->conn->close();
    }

    /**
     * @param \OperadorVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = OperadorVO) {
        $id = -1;
        
        $sql = "INSERT INTO " . self::TABLA . " ("  
                . "rfc_operador,"
                . "nombre,"
                . "num_licencia"
                . ") "
                . "VALUES(?, ?, ?)";
        error_log("El valor de sql: " . $sql);
        if(($ps = $this->conn->prepare($sql))){
            $ps->bind_param("sss",
                    $objectVO->getRfc_operador(),
                    $objectVO->getNombre(),
                    $objectVO->getNum_licencia()
                                       
                );
            if ($ps->execute()) {
                $id = $ps->insert_id;
                $ps->close();
                return $id;
            } else {
                error_log($this->conn->error);
            }
            $ps->close();
        }else {
            error_log($this->conn->error);
        }
        return $id;
    }

    public function fillObject($rs) {
        $objectVO = new OperadorVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setRfc_operador($rs["rfc_operador"]);
            $objectVO->setNombre($rs["nombre"]);
            $objectVO->setNum_licencia($rs["num_licencia"]);
        }
        return $objectVO;          
    }

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

    public function remove($idObjectVO, $field = "id") {
        $sql = "DELETE FROM " . self::TABLA . " WHERE " . $field . " = ? LIMIT 1";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("s", $idObjectVO );
            return $ps->execute();
        }
    }

    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new OperadorVO();
        $sql = "SELECT * FROM " . self::TABLA . " WHERE " . $field . " = '" . $idObjectVO . "'";
        if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            $objectVO = $this->fillObject($rs);
            return $objectVO;
        } else {
            error_log($this->conn->error);
        }
        return $objectVO;
        
    }

    public function update($objectVO) {
        //$objectVO = new OperadorVO();
        $sql = "UPDATE " . self::TABLA . " SET "
                . "rfc_operador = ?, "
                . "nombre = ?, "
                . "num_licencia = ? "
                . "WHERE id = ? ";
        error_log("El valor de qry : ". $sql);
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssi",
                    $objectVO->getRfc_operador(),
                    $objectVO->getNombre(),
                    $objectVO->getNum_licencia(),
                    $objectVO->getId()
                    );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}   